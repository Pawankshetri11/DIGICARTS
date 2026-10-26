(() => {
  const header = document.querySelector('.dchf-header');
  const closeDisclosure = button => {
    button.setAttribute('aria-expanded', 'false');
    const target = document.getElementById(button.getAttribute('aria-controls'));
    if (!target) return;
    target.classList.remove('is-open');
    if (button.classList.contains('dchf-browse-toggle')) target.hidden = true;
  };
  if (header) {
    const controls = header.querySelectorAll('.dchf-mobile-toggle, .dchf-browse-toggle, .dchf-submenu-toggle');
    controls.forEach(button => button.addEventListener('click', () => {
      const opening = button.getAttribute('aria-expanded') !== 'true';
      const target = document.getElementById(button.getAttribute('aria-controls'));
      if (!target) return;
      if (!opening) { closeDisclosure(button); return; }
      button.setAttribute('aria-expanded', 'true'); target.classList.add('is-open');
      if (button.classList.contains('dchf-browse-toggle')) target.hidden = false;
    }));
    header.addEventListener('keydown', event => {
      if (event.key !== 'Escape') return;
      const open = [...controls].filter(button => button.getAttribute('aria-expanded') === 'true');
      open.forEach(closeDisclosure); open.at(-1)?.focus();
    });
    document.addEventListener('click', event => {
      controls.forEach(button => {
        const target = document.getElementById(button.getAttribute('aria-controls'));
        if (!button.contains(event.target) && target && !target.contains(event.target)) closeDisclosure(button);
      });
    });
    header.querySelectorAll('nav a').forEach(link => link.addEventListener('click', () => controls.forEach(closeDisclosure)));
    window.matchMedia('(min-width: 901px)').addEventListener('change', () => controls.forEach(closeDisclosure));
    const search = header.querySelector('.dchf-search');
    search?.addEventListener('submit', () => {
      const category = search.querySelector('[name="product_cat"]');
      if (category && !category.value) category.disabled = true;
    });
  }
  document.querySelectorAll('.dchf-back-top').forEach(button => button.addEventListener('click', () => {
    window.scrollTo({top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'});
  }));
  if (window.wp?.data && (document.body.classList.contains('woocommerce-cart') || document.body.classList.contains('woocommerce-checkout'))) {
    let previous;
    const syncCart = () => {
      const store = window.wp.data.select('wc/store/cart');
      if (!store?.hasFinishedResolution?.('getCartData')) return;
      const cart = store.getCartData();
      if (!Number.isInteger(cart?.itemsCount)) return;
      const totals = store.getCartTotals();
      const fingerprint = JSON.stringify([cart.itemsCount, totals?.total_items, totals?.total_items_tax, totals?.currency_minor_unit]);
      if (fingerprint === previous) return;
      previous = fingerprint;
      document.querySelectorAll('.dchf-cart').forEach(link => {
        const badge = link.querySelector('.dchf-count');
        if (badge) badge.textContent = String(cart.itemsCount);
        link.setAttribute('aria-label', `Shopping cart, ${cart.itemsCount} ${cart.itemsCount === 1 ? 'item' : 'items'}`);
      });
      if (window.jQuery) window.jQuery(document.body).trigger('wc_fragment_refresh');
    };
    window.wp.data.subscribe(syncCart); syncCart();
  }
})();
