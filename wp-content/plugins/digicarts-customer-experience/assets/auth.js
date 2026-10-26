(() => {
  const card = document.querySelector('.dce-auth-card');
  const nav = card?.querySelector('.dce-auth-tabs');
  if (!nav) return;
  const tabs = [...nav.querySelectorAll('[data-auth-tab]')];
  nav.setAttribute('role', 'tablist');
  tabs.forEach((tab) => {
    tab.setAttribute('role', 'tab');
    tab.setAttribute('aria-controls', `dce-panel-${tab.dataset.authTab}`);
    const panel = document.getElementById(`dce-panel-${tab.dataset.authTab}`);
    panel.setAttribute('role', 'tabpanel');
    panel.setAttribute('aria-labelledby', tab.id);
  });
  function activate(name, focus = false, updateUrl = true) {
    const selected = tabs.find((tab) => tab.dataset.authTab === name) || tabs[0];
    tabs.forEach((tab) => {
      const active = tab === selected;
      tab.classList.toggle('is-active', active);
      tab.setAttribute('aria-selected', String(active));
      tab.tabIndex = active ? 0 : -1;
      document.getElementById(`dce-panel-${tab.dataset.authTab}`).hidden = !active;
    });
    if (focus) selected.focus();
    if (updateUrl) {
      const url = new URL(location.href);
      url.searchParams.set('account_tab', selected.dataset.authTab);
      history.replaceState(null, '', url);
    }
  }
  activate(card.dataset.activeTab);
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', (event) => {
      event.preventDefault();
      activate(tab.dataset.authTab);
    });
    tab.addEventListener('keydown', (event) => {
      let next;
      if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
      if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
      if (event.key === 'Home') next = 0;
      if (event.key === 'End') next = tabs.length - 1;
      if (event.key === ' ') next = index;
      if (next === undefined) return;
      event.preventDefault();
      activate(tabs[next].dataset.authTab, true);
    });
  });
  card.addEventListener('invalid', (event) => {
    const panel = event.target.closest('.dce-auth-panel');
    if (panel?.hidden) activate(panel.id.replace('dce-panel-', ''));
  }, true);
  window.addEventListener('popstate', () => activate(new URL(location.href).searchParams.get('account_tab') || 'login', false, false));
})();
