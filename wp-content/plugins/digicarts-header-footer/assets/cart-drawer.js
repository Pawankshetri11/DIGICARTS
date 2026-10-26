(() => {
  const config = window.dchfCart;
  const drawer = document.getElementById('dchf-cart-drawer');
  if (!config || !drawer) return;
  const content = drawer.querySelector('.dchf-drawer-content');
  const message = drawer.querySelector('.dchf-drawer-message');
  const checkout = drawer.querySelector('.dchf-drawer-checkout');
  const checkoutLink = drawer.querySelector('.dchf-checkout-link');
  let cart = null, nonce = '', busy = false, pendingRefresh = false, opener, previousOverflow = '', closeTimer;
  let couponDraft = '', addressOpen = false;
  const plain = value => new DOMParser().parseFromString(String(value ?? ''),'text/html').body.textContent || '';
  const node = (tag, className, text) => {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (text !== undefined) element.textContent = text;
    return element;
  };
  const button = (text, className, action) => {
    const element = node('button',className,text); element.type = 'button';
    if (action) element.addEventListener('click',action);
    return element;
  };
  const url = value => {
    try { const parsed = new URL(value,location.origin); return ['http:','https:'].includes(parsed.protocol) ? parsed.href : ''; } catch (_) { return ''; }
  };
  const money = (amount, details = cart?.totals || {}) => {
    const places = Number(details.currency_minor_unit ?? 2);
    const parts = (Number(amount || 0) / (10 ** places)).toFixed(places).split('.');
    const grouped = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g,details.currency_thousand_separator ?? ',');
    return plain(details.currency_prefix ?? details.currency_symbol ?? '') + grouped + (places ? (details.currency_decimal_separator ?? '.') + parts[1] : '') + plain(details.currency_suffix || '');
  };
  const amountWithTax = (amount, tax) => Number(amount || 0) + (config.taxInclusive ? Number(tax || 0) : 0);
  const notify = (text, error = false) => {
    message.textContent = plain(text); message.hidden = !text;
    message.classList.toggle('is-error',error); message.setAttribute('role',error ? 'alert' : 'status');
  };
  const setBusy = value => {
    busy = value; drawer.classList.toggle('is-busy',value); content.setAttribute('aria-busy',String(value));
    content.querySelectorAll('button,input,select').forEach(control => {
      if (value) { control.dataset.wasDisabled = control.disabled ? '1' : '0'; control.disabled = true; }
      else { control.disabled = control.dataset.wasDisabled === '1'; delete control.dataset.wasDisabled; }
    });
    checkoutLink.setAttribute('aria-disabled',String(value || !cart?.items?.length || !!cart?.errors?.length));
  };
  const endpoint = path => {
    const target = new URL(config.api,location.origin);
    if (target.searchParams.has('rest_route')) target.searchParams.set('rest_route',target.searchParams.get('rest_route').replace(/\/$/,'') + path);
    else target.pathname = target.pathname.replace(/\/$/,'') + path;
    return target.href;
  };
  const request = async (path = '', payload, retry = true) => {
    const response = await fetch(endpoint(path),{
      method:payload === undefined ? 'GET' : 'POST', credentials:'same-origin', cache:'no-store',
      headers:payload === undefined ? {'Accept':'application/json'} : {'Accept':'application/json','Content-Type':'application/json','Nonce':nonce},
      ...(payload === undefined ? {} : {body:JSON.stringify(payload)})
    });
    const renewed = response.headers.get('Nonce'); if (renewed) nonce = renewed;
    const data = await response.json();
    if (!response.ok) {
      if (retry && payload !== undefined && ['woocommerce_rest_invalid_nonce','woocommerce_rest_missing_nonce'].includes(data.code)) {
        await request(); return request(path,payload,false);
      }
      const error = new Error(plain(data.message || 'Your cart could not be updated. Please try again.'));
      error.cart = data.data?.cart; throw error;
    }
    return data;
  };
  const syncHeader = () => {
    document.querySelectorAll('.dchf-cart').forEach(link => {
      const count = link.querySelector('.dchf-count'), total = link.querySelector('.dchf-cart-total');
      if (count) count.textContent = String(cart.items_count || 0);
      if (total) total.textContent = money(amountWithTax(cart.totals.total_items,cart.totals.total_items_tax));
      link.setAttribute('aria-label',`Open cart, ${cart.items_count || 0} items`);
      link.setAttribute('aria-haspopup','dialog'); link.setAttribute('aria-controls',drawer.id);
    });
  };
  const refresh = async () => {
    if (busy) { pendingRefresh = true; return; }
    setBusy(true); notify('');
    try { cart = await request(); render(); }
    catch (error) {
      notify(error.message || 'Unable to load your cart. Please try again.',true);
      if (!cart) { content.replaceChildren(node('p','','Your cart could not be loaded.'),button('Try again','dchf-drawer-secondary',refresh)); }
    } finally {
      setBusy(false);
      if (pendingRefresh) { pendingRefresh = false; refresh(); }
    }
  };
  const mutate = async (path, payload, success, focusKey) => {
    if (busy) return;
    setBusy(true); notify('');
    try {
      if (!nonce) await request();
      cart = await request(path,payload);
      if (path === '/apply-coupon') couponDraft = '';
      render(); notify(success);
      if (window.jQuery) window.jQuery(document.body).trigger('wc_fragment_refresh');
      try { window.wp?.data?.dispatch('wc/store/cart')?.invalidateResolutionForStore?.(); } catch (_) {}
    } catch (error) {
      if (error.cart?.items) cart = error.cart;
      else { try { cart = await request(); } catch (_) {} }
      if (cart) render();
      notify(error.message || 'Your cart could not be updated.',true);
    } finally {
      setBusy(false);
      if (focusKey && drawer.open) [...content.querySelectorAll('[data-focus]')].find(element => element.dataset.focus === focusKey)?.focus({preventScroll:true});
      if (drawer.open && (!drawer.contains(document.activeElement) || document.activeElement === drawer)) (content.querySelector('.dchf-drawer-item-title,.dchf-drawer-shop') || drawer.querySelector('.dchf-drawer-close')).focus({preventScroll:true});
      if (pendingRefresh) { pendingRefresh = false; refresh(); }
    }
  };
  const close = () => {
    if (!drawer.open) return;
    drawer.classList.add('is-closing');
    clearTimeout(closeTimer);
    closeTimer = setTimeout(() => {
      drawer.close(); drawer.classList.remove('is-closing'); document.body.style.overflow = previousOverflow;
      if (opener?.isConnected) opener.focus({preventScroll:true});
    },window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 180);
  };
  const open = trigger => {
    clearTimeout(closeTimer); drawer.classList.remove('is-closing');
    if (!drawer.open) { opener = trigger || document.activeElement; previousOverflow = document.body.style.overflow; document.body.style.overflow = 'hidden'; drawer.showModal(); }
    drawer.querySelector('.dchf-drawer-close').focus({preventScroll:true}); refresh();
  };
  drawer.querySelector('.dchf-drawer-close').addEventListener('click',close);
  drawer.querySelector('.dchf-continue-shopping').addEventListener('click',close);
  drawer.addEventListener('cancel',event => { event.preventDefault(); close(); });
  drawer.addEventListener('click',event => {
    const bounds = drawer.getBoundingClientRect();
    if (event.target === drawer && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) close();
  });
  checkoutLink.addEventListener('click',event => { if (checkoutLink.getAttribute('aria-disabled') === 'true') event.preventDefault(); });
  document.addEventListener('click',event => {
    const link = event.target.closest('a[href]');
    if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || link.target === '_blank') return;
    const actual = new URL(link.href), expected = new URL(config.cartUrl,location.origin);
    const samePath = actual.origin === expected.origin && actual.pathname.replace(/\/$/,'') === expected.pathname.replace(/\/$/,'');
    const samePageId = !expected.searchParams.has('page_id') || actual.searchParams.get('page_id') === expected.searchParams.get('page_id');
    if (link.classList.contains('dchf-cart') || (samePath && samePageId)) { event.preventDefault(); open(link); }
  });
  if (window.jQuery) window.jQuery(document.body).on('added_to_cart',() => open()).on('removed_from_cart',() => { if (drawer.open) refresh(); });
  document.body.addEventListener('wc-blocks_added_to_cart',() => open());
  window.addEventListener('focus',() => { if (drawer.open) refresh(); });

  function renderItem(item) {
    const row = node('article','dchf-drawer-item');
    const photo = node('a','dchf-drawer-image'); photo.href = url(item.permalink) || config.shopUrl; photo.setAttribute('aria-label',plain(item.name));
    if (item.images?.[0]) { const img = node('img'); img.src = url(item.images[0].thumbnail || item.images[0].src); img.alt = ''; img.loading = 'lazy'; photo.append(img); }
    const info = node('div','dchf-drawer-item-info');
    const name = node('a','dchf-drawer-item-title',plain(item.name)); name.href = photo.href; info.append(name);
    (item.variation || []).forEach(variation => info.append(node('p','dchf-drawer-meta',`${plain(variation.attribute)}: ${plain(variation.value)}`)));
    (item.item_data || []).forEach(detail => info.append(node('p','dchf-drawer-meta',`${plain(detail.name || detail.key)}: ${plain(detail.value || detail.display)}`)));
    info.append(node('p','dchf-drawer-item-price',money(amountWithTax(item.totals.line_total,item.totals.line_total_tax),item.totals)));
    const controls = node('div','dchf-drawer-item-controls');
    const limits = item.quantity_limits || {}, minimum = Number(limits.minimum || 1), maximum = Number(limits.maximum ?? 9999), step = Number(limits.multiple_of || 1);
    if (limits.editable === false || item.sold_individually) controls.append(node('span','dchf-drawer-fixed-qty',`Qty: ${item.quantity}`));
    else {
      const quantity = node('div','dchf-drawer-quantity');
      const change = value => {
        if (!Number.isFinite(value) || value < minimum || value > maximum || Math.abs(value / step - Math.round(value / step)) > .00001) {
          notify(`Choose a quantity between ${minimum} and ${maximum}, in steps of ${step}.`,true); input.value = item.quantity; return;
        }
        if (value !== Number(item.quantity)) mutate('/update-item',{key:item.key,quantity:value},'Quantity updated.',`quantity-${item.key}`);
      };
      const minus = button('−','',() => change(Number((Number(item.quantity)-step).toFixed(6)))); minus.disabled = Number(item.quantity) <= minimum;
      minus.setAttribute('aria-label',`Decrease quantity of ${plain(item.name)}`);
      const input = node('input'); input.type = 'number'; input.min = minimum; input.max = maximum; input.step = step; input.value = item.quantity; input.inputMode = step < 1 ? 'decimal' : 'numeric'; input.dataset.focus = `quantity-${item.key}`;
      input.setAttribute('aria-label',`Quantity of ${plain(item.name)}`); input.addEventListener('change',() => change(Number(input.value)));
      const plus = button('+','',() => change(Number((Number(item.quantity)+step).toFixed(6)))); plus.disabled = Number(item.quantity) >= maximum;
      plus.setAttribute('aria-label',`Increase quantity of ${plain(item.name)}`); quantity.append(minus,input,plus); controls.append(quantity);
    }
    controls.append(button('Remove','dchf-drawer-remove',() => mutate('/remove-item',{key:item.key},'Product removed.')));
    info.append(controls); row.append(photo,info); return row;
  }
  function renderCoupons() {
    const section = node('section','dchf-drawer-coupons'); section.append(node('h3','','Have a coupon?'));
    const form = node('form','dchf-drawer-coupon-form'); const label = node('label','dchf-sr','Coupon code'); label.htmlFor = 'dchf-cart-coupon';
    const input = node('input'); input.id = 'dchf-cart-coupon'; input.type = 'text'; input.placeholder = 'Enter coupon code'; input.maxLength = 100; input.value = couponDraft; input.required = true; input.autocomplete = 'off'; input.dataset.focus = 'coupon';
    input.addEventListener('input',() => { couponDraft = input.value; });
    const apply = node('button','','Apply'); apply.type = 'submit'; form.append(label,input,apply);
    form.addEventListener('submit',event => { event.preventDefault(); if (input.value.trim()) mutate('/apply-coupon',{code:input.value.trim()},'Coupon applied.','coupon'); }); section.append(form);
    (cart.coupons || []).forEach(coupon => {
      const line = node('div','dchf-drawer-coupon');
      line.append(node('span','',plain(coupon.code)),node('strong','',`−${money(amountWithTax(coupon.totals.total_discount,coupon.totals.total_discount_tax),coupon.totals)}`));
      const remove = button('×','',() => mutate('/remove-coupon',{code:coupon.code},'Coupon removed.','coupon')); remove.setAttribute('aria-label',`Remove coupon ${plain(coupon.code)}`); line.append(remove); section.append(line);
    });
    return section;
  }
  function renderShipping() {
    const section = node('section','dchf-drawer-shipping'); section.append(node('h3','','Delivery estimate'));
    const details = node('details'); details.open = addressOpen; details.addEventListener('toggle',() => { addressOpen = details.open; });
    details.append(node('summary','','Change destination'));
    const form = node('form','dchf-drawer-address');
    const address = cart.shipping_address || {};
    const makeField = (key,title,field) => {
      const label = node('label','',title); field.name = key; label.append(field); form.append(label); return field;
    };
    const country = makeField('country','Country',node('select')); country.required = true;
    country.append(new Option('Choose country',''));
    Object.entries(config.countries || {}).forEach(([code,name]) => country.append(new Option(plain(name),code)));
    country.value = address.country || '';
    const stateWrap = node('label','','State / region'); form.append(stateWrap);
    const updateState = selected => {
      stateWrap.querySelector('select,input')?.remove();
      const states = config.states?.[country.value]; let state;
      if (states && Object.keys(states).length) { state = node('select'); state.append(new Option('Choose state','')); Object.entries(states).forEach(([code,name]) => state.append(new Option(plain(name),code))); }
      else { state = node('input'); state.type = 'text'; }
      state.name = 'state'; state.value = selected || ''; stateWrap.append(state);
    };
    updateState(address.state); country.addEventListener('change',() => updateState(''));
    [['city','City'],['postcode','Postal code'],['address_1','Street address (optional)']].forEach(([key,title]) => { const input = makeField(key,title,node('input')); input.value = address[key] || ''; input.type = 'text'; input.autocomplete = key === 'postcode' ? 'postal-code' : key === 'city' ? 'address-level2' : 'address-line1'; });
    const save = node('button','dchf-drawer-secondary','Update estimate'); save.type = 'submit'; form.append(save);
    form.addEventListener('submit',event => { event.preventDefault(); mutate('/update-customer',{shipping_address:Object.fromEntries(new FormData(form))},'Delivery estimate updated.'); });
    details.append(form); section.append(details);
    let ratesFound = false;
    (cart.shipping_rates || []).forEach(pack => {
      const group = node('fieldset','dchf-drawer-rates'); group.append(node('legend','',plain(pack.name || 'Shipping options')));
      (pack.shipping_rates || []).forEach(rate => {
        ratesFound = true; const label = node('label'); const input = node('input'); input.type = 'radio'; input.name = `shipping-${pack.package_id}`; input.checked = !!rate.selected;
        input.addEventListener('change',() => mutate('/select-shipping-rate',{package_id:pack.package_id,rate_id:rate.rate_id},'Delivery option updated.'));
        label.append(input,node('span','',plain(rate.name)),node('strong','',money(amountWithTax(rate.price,rate.taxes),rate))); group.append(label);
      }); section.append(group);
    });
    if (!ratesFound) section.append(node('p','dchf-drawer-note','Enter your destination to see available delivery options.'));
    return section;
  }
  function render() {
    content.replaceChildren(); syncHeader(); drawer.querySelector('.dchf-drawer-count').textContent = String(cart.items_count || 0);
    const items = cart.items || []; checkout.hidden = !items.length;
    if (!items.length) {
      const empty = node('div','dchf-drawer-empty'); empty.append(node('div','dchf-empty-symbol','＋'),node('h3','','Make room for your next idea.'),node('p','','Your cart is empty. Explore the store and find something you love.'));
      const shop = node('a','dchf-drawer-shop','Explore products'); shop.href = config.shopUrl; empty.append(shop); content.append(empty); return;
    }
    const list = node('div','dchf-drawer-items'); items.forEach(item => list.append(renderItem(item))); content.append(list);
    if (config.couponsEnabled) content.append(renderCoupons());
    if (cart.needs_shipping) content.append(renderShipping());
    const summary = node('dl','dchf-drawer-summary');
    const line = (label,value,className = '') => { const row = node('div',className); row.append(node('dt','',label),node('dd','',value)); summary.append(row); };
    const totals = cart.totals;
    line('Subtotal',money(amountWithTax(totals.total_items,totals.total_items_tax)));
    if (Number(totals.total_discount)) line('Discount',`−${money(amountWithTax(totals.total_discount,totals.total_discount_tax))}`,'dchf-summary-discount');
    if (Number(totals.total_fees)) line('Fees',money(amountWithTax(totals.total_fees,totals.total_fees_tax)));
    if (cart.needs_shipping) line('Shipping',totals.total_shipping === null ? 'Enter destination' : money(amountWithTax(totals.total_shipping,totals.total_shipping_tax)));
    if (Number(totals.total_tax)) line(config.taxInclusive ? 'Tax included' : 'Tax',money(totals.total_tax));
    content.append(summary,node('p','dchf-drawer-note','Final taxes and delivery charges, if any, are confirmed at checkout.'));
    (cart.errors || []).forEach(error => content.append(node('p','dchf-drawer-cart-error',plain(error.message || error))));
    checkout.querySelector('.dchf-drawer-total strong').textContent = money(totals.total_price);
    // Preserve each newly rendered control's stock/quantity disabled state while a request finishes.
    if (busy) content.querySelectorAll('button,input,select').forEach(control => { control.dataset.wasDisabled = control.disabled ? '1' : '0'; control.disabled = true; });
  }
  const page = new URL(location.href);
  if (config.autoOpen || page.searchParams.get('open-cart') === '1') { page.searchParams.delete('open-cart'); history.replaceState(null,'',page.href); open(); }
})();
