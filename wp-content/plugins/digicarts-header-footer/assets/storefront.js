(() => {
  const header = document.querySelector('.dchf-header');
  const banner = document.querySelector('.dchf-discount-banner');
  if (banner) {
    const key = `dchf-offer-${banner.dataset.bannerKey}`;
    try { banner.hidden = sessionStorage.getItem(key) === 'dismissed'; } catch (_) {}
    banner.querySelector('.dchf-dismiss-banner')?.addEventListener('click', () => {
      banner.hidden = true;
      try { sessionStorage.setItem(key, 'dismissed'); } catch (_) {}
      header?.querySelector('.dchf-logo')?.focus({preventScroll:true});
    });
  }
  if (header) {
    const adminBar = document.getElementById('wpadminbar');
    let scheduled = false;
    const updateOffset = () => {
      const offset = adminBar ? Math.max(0, adminBar.getBoundingClientRect().bottom) : 0;
      header.style.setProperty('--dchf-sticky-offset', `${offset}px`);
      document.documentElement.style.scrollPaddingTop = `${header.offsetHeight + offset + 16}px`;
      scheduled = false;
    };
    window.addEventListener('scroll', () => {
      if (!scheduled) { scheduled = true; requestAnimationFrame(updateOffset); }
    }, {passive:true});
    window.addEventListener('resize', updateOffset);
    if ('ResizeObserver' in window) new ResizeObserver(updateOffset).observe(header);
    updateOffset();
  }

  // The built-in wishlist stays on the visitor's browser and contains public product data.
  const dialog = document.getElementById('dchf-wishlist-dialog');
  const storageKey = 'digicarts-wishlist-v1';
  const safeUrl = (value, sameOrigin = false) => {
    try {
      const url = new URL(value, location.origin);
      return ['http:', 'https:'].includes(url.protocol) && (!sameOrigin || url.origin === location.origin) ? url.href : '';
    } catch (_) { return ''; }
  };
  const normalize = value => {
    if (!value || !Number.isInteger(Number(value.id)) || Number(value.id) < 1 || typeof value.name !== 'string') return null;
    const url = safeUrl(value.url, true);
    if (!url) return null;
    return {id:Number(value.id), name:value.name.slice(0,250), url, image:safeUrl(value.image || ''), price:String(value.price || '').slice(0,200)};
  };
  let saved = [];
  try {
    const stored = JSON.parse(localStorage.getItem(storageKey) || '[]');
    if (Array.isArray(stored)) saved = [...new Map(stored.slice(0,100).map(normalize).filter(Boolean).map(item => [item.id,item])).values()];
  } catch (_) {}
  const heart = '<svg class="dchf-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20S3 14.7 3 8.7A4.7 4.7 0 0 1 12 7a4.7 4.7 0 0 1 9 1.7C21 14.7 12 20 12 20Z"/></svg>';
  const updateSavedButtons = () => {
    document.querySelectorAll('[data-dchf-product]').forEach(button => {
      try {
        const product = JSON.parse(button.dataset.dchfProduct);
        const active = saved.some(item => item.id === Number(product.id));
        button.setAttribute('aria-pressed', String(active));
        button.setAttribute('aria-label', `${active ? 'Remove from' : 'Save to'} wishlist: ${product.name}`);
        const label = button.querySelector('span');
        if (label) label.textContent = active ? 'Saved to wishlist' : 'Save to wishlist';
      } catch (_) {}
    });
    document.querySelectorAll('.dchf-wishlist-count').forEach(count => {
      count.textContent = String(saved.length); count.hidden = saved.length === 0;
    });
    document.querySelectorAll('[data-open-wishlist]').forEach(button => button.setAttribute('aria-label', `Open wishlist, ${saved.length} saved products`));
  };
  const persist = () => {
    try { localStorage.setItem(storageKey, JSON.stringify(saved)); return true; }
    catch (_) { return false; }
  };
  const renderWishlist = () => {
    if (!dialog) return;
    const container = dialog.querySelector('.dchf-wishlist-items');
    container.replaceChildren();
    if (!saved.length) {
      const empty = document.createElement('p');
      empty.textContent = 'Your wishlist is empty. Tap a heart on a product to save it here.';
      container.append(empty);
    }
    saved.forEach(product => {
      const row = document.createElement('div'); row.className = 'dchf-wishlist-item';
      const link = document.createElement('a'); link.href = product.url;
      if (product.image) {
        const img = document.createElement('img'); img.src = product.image; img.alt = ''; img.loading = 'lazy'; link.append(img);
      }
      const name = document.createElement('span'); name.textContent = product.name; link.append(name);
      const remove = document.createElement('button'); remove.type = 'button'; remove.textContent = 'Remove';
      remove.setAttribute('aria-label', `Remove ${product.name} from wishlist`);
      remove.addEventListener('click', () => {
        saved = saved.filter(item => item.id !== product.id); persist(); renderWishlist(); updateSavedButtons();
        dialog.querySelector('.dchf-wishlist-items button, .dchf-wishlist-close')?.focus();
      });
      row.append(link, remove); container.append(row);
    });
  };
  document.addEventListener('click', event => {
    const button = event.target.closest('[data-dchf-product]');
    if (!button || !dialog) return;
    let product;
    try { product = normalize(JSON.parse(button.dataset.dchfProduct)); } catch (_) { return; }
    if (!product) return;
    const index = saved.findIndex(item => item.id === product.id);
    if (index >= 0) saved.splice(index,1);
    else if (saved.length < 100) saved.push(product);
    else { renderWishlist(); dialog.showModal(); dialog.querySelector('.dchf-wishlist-feedback').textContent = 'Your wishlist has 100 items. Remove one to save another.'; return; }
    const persisted = persist();
    updateSavedButtons(); renderWishlist();
    dialog.querySelector('.dchf-wishlist-feedback').textContent = persisted ? '' : 'Browser storage is unavailable. Saved items will last only while this page is open.';
  });
  document.querySelectorAll('[data-open-wishlist]').forEach(button => button.addEventListener('click', () => { renderWishlist(); dialog?.showModal(); }));
  dialog?.querySelector('.dchf-wishlist-close').addEventListener('click', () => dialog.close());
  dialog?.addEventListener('click', event => {
    const rect = dialog.getBoundingClientRect();
    if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close();
  });
  updateSavedButtons();

  const wrap = header?.querySelector('.dchf-search-wrap');
  if (!wrap || !window.dchfStorefront) return;
  const form = wrap.querySelector('form');
  const input = form.querySelector('[name="s"]');
  const category = form.querySelector('[name="product_cat"]');
  const results = wrap.querySelector('.dchf-search-results');
  const status = wrap.querySelector('.dchf-search-status');
  let timer, controller, revision = 0;
  const dismiss = () => {
    clearTimeout(timer); controller?.abort(); revision++;
    results.hidden = true; results.removeAttribute('aria-busy'); input.setAttribute('aria-expanded','false');
  };
  // Enhance the native select; retain its value and change event for AJAX/form submission.
  const picker = document.createElement('div'); picker.className = 'dchf-category-picker';
  const trigger = document.createElement('button'); trigger.type = 'button'; trigger.className = 'dchf-category-trigger';
  trigger.setAttribute('aria-haspopup','listbox'); trigger.setAttribute('aria-expanded','false');
  trigger.setAttribute('aria-controls','dchf-search-category-options');
  const selectedLabel = document.createElement('span'); trigger.append(selectedLabel);
  trigger.insertAdjacentHTML('beforeend','<svg class="dchf-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>');
  const choices = document.createElement('ul'); choices.id = 'dchf-search-category-options';
  choices.className = 'dchf-category-options'; choices.setAttribute('role','listbox');
  choices.setAttribute('aria-label','Product category'); choices.hidden = true;
  const options = [...category.options].map(option => {
    const item = document.createElement('li'); item.textContent = option.textContent;
    item.dataset.value = option.value; item.tabIndex = -1; item.setAttribute('role','option');
    choices.append(item); return item;
  });
  category.before(picker); picker.append(trigger,choices,category); category.hidden = true;
  const syncCategory = () => {
    const label = category.options[category.selectedIndex]?.textContent || 'All categories';
    selectedLabel.textContent = label; trigger.title = label;
    trigger.setAttribute('aria-label', `Product category: ${label}`);
    options.forEach(item => item.setAttribute('aria-selected',String(item.dataset.value === category.value)));
  };
  const closeCategories = (restoreFocus = false) => {
    choices.hidden = true; trigger.setAttribute('aria-expanded','false');
    if (restoreFocus) trigger.focus({preventScroll:true});
  };
  const openCategories = () => {
    dismiss(); choices.hidden = false; trigger.setAttribute('aria-expanded','true');
    (options.find(item => item.dataset.value === category.value) || options[0])?.focus({preventScroll:true});
  };
  const chooseCategory = item => {
    category.disabled = false; category.value = item.dataset.value;
    syncCategory(); closeCategories(true); category.dispatchEvent(new Event('change',{bubbles:true}));
  };
  trigger.addEventListener('click', () => choices.hidden ? openCategories() : closeCategories());
  trigger.addEventListener('keydown', event => {
    if (['ArrowDown','ArrowUp'].includes(event.key)) { event.preventDefault(); openCategories(); }
  });
  choices.addEventListener('click', event => {
    const item = event.target.closest('[role="option"]'); if (item) chooseCategory(item);
  });
  let typed = '', typedTimer;
  choices.addEventListener('keydown', event => {
    const index = options.indexOf(document.activeElement);
    let next = index;
    if (event.key === 'ArrowDown') next = (index + 1) % options.length;
    else if (event.key === 'ArrowUp') next = (index - 1 + options.length) % options.length;
    else if (event.key === 'Home') next = 0;
    else if (event.key === 'End') next = options.length - 1;
    else if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); if (options[index]) chooseCategory(options[index]); return; }
    else if (event.key === 'Escape') { event.preventDefault(); event.stopPropagation(); closeCategories(true); return; }
    else if (event.key === 'Tab') { closeCategories(true); return; }
    else if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
      typed += event.key.toLocaleLowerCase(); clearTimeout(typedTimer); typedTimer = setTimeout(() => { typed = ''; },600);
      next = options.findIndex(item => item.textContent.toLocaleLowerCase().startsWith(typed));
      if (next < 0) return;
    } else return;
    event.preventDefault(); options[next]?.focus(); options[next]?.scrollIntoView({block:'nearest'});
  });
  document.addEventListener('click', event => { if (!picker.contains(event.target)) closeCategories(); });
  picker.addEventListener('focusout', () => setTimeout(() => { if (!picker.contains(document.activeElement)) closeCategories(); },0));
  category.addEventListener('change', syncCategory);
  form.addEventListener('reset', () => setTimeout(syncCategory,0));
  syncCategory();
  const showMessage = text => {
    results.replaceChildren(); const paragraph = document.createElement('p'); paragraph.textContent = text;
    results.append(paragraph); results.hidden = false; input.setAttribute('aria-expanded','true'); status.textContent = text;
  };
  const search = async () => {
    controller?.abort(); const ownRevision = ++revision;
    const query = input.value.trim();
    if (query.length < 2) { dismiss(); return; }
    controller = new AbortController();
    const url = new URL(dchfStorefront.searchUrl, location.href);
    url.searchParams.set('q', query.slice(0,80)); url.searchParams.set('category', category.value);
    showMessage('Searching products…'); results.setAttribute('aria-busy','true');
    try {
      const response = await fetch(url, {signal:controller.signal, credentials:'same-origin'});
      if (!response.ok) throw new Error('Search unavailable');
      const data = await response.json();
      if (ownRevision !== revision) return;
      results.replaceChildren();
      const products = Array.isArray(data.products) ? data.products.map(normalize).filter(Boolean) : [];
      if (!products.length) showMessage('No matching products. Try another search or category.');
      products.forEach(product => {
        const row = document.createElement('div'); row.className = 'dchf-search-result';
        const link = document.createElement('a'); link.href = product.url;
        if (product.image) { const img = document.createElement('img'); img.src = product.image; img.alt = ''; img.loading = 'lazy'; link.append(img); }
        const copy = document.createElement('span');
        const title = document.createElement('strong'); title.textContent = product.name;
        const price = document.createElement('small'); price.textContent = product.price;
        copy.append(title,price); link.append(copy); row.append(link);
        if (dialog) {
          const save = document.createElement('button'); save.type = 'button'; save.className = 'dchf-save-product';
          save.dataset.dchfProduct = JSON.stringify(product); save.innerHTML = heart; row.append(save);
        }
        results.append(row);
      });
      const all = document.createElement('a'); all.className = 'dchf-search-all';
      const fullSearch = new URL(form.action); fullSearch.searchParams.set('s', query); fullSearch.searchParams.set('post_type','product');
      if (category.value) fullSearch.searchParams.set('product_cat',category.value);
      all.href = fullSearch.href; all.textContent = 'View all search results'; results.append(all);
      status.textContent = products.length ? `${products.length} product suggestions available.` : 'No matching products.';
      updateSavedButtons();
    } catch (error) {
      if (error.name !== 'AbortError' && ownRevision === revision) showMessage('Search is temporarily unavailable. Please try again.');
    } finally { if (ownRevision === revision) results.removeAttribute('aria-busy'); }
  };
  input.addEventListener('input', () => { dismiss(); if (input.value.trim().length >= 2) timer = setTimeout(search,250); });
  category.addEventListener('change', () => { category.disabled = false; clearTimeout(timer); search(); });
  form.addEventListener('submit', event => {
    event.preventDefault(); category.disabled = false; clearTimeout(timer);
    if (input.value.trim().length < 2) { dismiss(); showMessage('Enter at least two characters to search.'); input.focus(); }
    else search();
  });
  wrap.addEventListener('keydown', event => {
    if (event.key === 'Escape') { dismiss(); input.focus(); }
    if (event.key === 'ArrowDown' && event.target === input && !results.hidden) { event.preventDefault(); results.querySelector('a')?.focus(); }
  });
  document.addEventListener('click', event => { if (!wrap.contains(event.target)) dismiss(); });
  wrap.addEventListener('focusout', () => setTimeout(() => { if (!wrap.contains(document.activeElement)) dismiss(); },0));
})();
