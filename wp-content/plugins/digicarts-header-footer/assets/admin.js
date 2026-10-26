jQuery(($) => {
  const root = document.querySelector('.dchf-admin');
  const form = root?.querySelector('#dchf-settings-form');
  if (!form) return;

  const nav = root.querySelector('.dchf-admin-nav');
  const tabs = Array.from(nav.querySelectorAll('[data-panel]'));
  const panels = Array.from(root.querySelectorAll('.dchf-admin-card'));
  const storageKey = 'dchf-editor-tab:' + location.pathname;
  const status = root.querySelector('#dchf-save-status');
  const saveBar = root.querySelector('.dchf-save-bar');
  let dirty = false;
  let submitting = false;

  nav.setAttribute('role', 'tablist');
  nav.setAttribute('aria-orientation', 'vertical');
  tabs.forEach((tab) => {
    tab.setAttribute('role', 'tab');
    tab.setAttribute('aria-controls', tab.dataset.panel);
  });
  panels.forEach((panel) => {
    panel.setAttribute('role', 'tabpanel');
    panel.tabIndex = 0;
  });

  function activate(id, focus = false, remember = true) {
    const current = tabs.find((tab) => tab.dataset.panel === id) || tabs[0];
    tabs.forEach((tab) => {
      const selected = tab === current;
      tab.setAttribute('aria-selected', String(selected));
      tab.tabIndex = selected ? 0 : -1;
    });
    panels.forEach((panel) => { panel.hidden = panel.id !== current.dataset.panel; });
    if (focus) current.focus();
    if (remember) {
      try { sessionStorage.setItem(storageKey, current.dataset.panel); } catch (_) {}
      history.replaceState(null, '', '#' + current.dataset.panel);
    }
  }

  let remembered = '';
  try { remembered = sessionStorage.getItem(storageKey) || ''; } catch (_) {}
  const requested = location.hash.slice(1);
  activate(tabs.some((tab) => tab.dataset.panel === requested) ? requested : remembered);
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', (event) => {
      event.preventDefault();
      activate(tab.dataset.panel);
    });
    tab.addEventListener('keydown', (event) => {
      let next;
      if (event.key === 'ArrowDown' || event.key === 'ArrowRight') next = (index + 1) % tabs.length;
      if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
      if (event.key === 'Home') next = 0;
      if (event.key === 'End') next = tabs.length - 1;
      if (next === undefined) return;
      event.preventDefault();
      activate(tabs[next].dataset.panel, true);
    });
  });
  window.addEventListener('hashchange', () => {
    const id = location.hash.slice(1);
    if (tabs.some((tab) => tab.dataset.panel === id)) activate(id);
  });

  // Reveal invalid fields before the browser tries to focus them.
  let firstInvalid = null;
  let showingValidity = false;
  form.addEventListener('invalid', (event) => {
    if (showingValidity) return;
    event.preventDefault();
    if (firstInvalid) return;
    firstInvalid = event.target;
    const panel = firstInvalid.closest('.dchf-admin-card');
    if (panel) activate(panel.id);
    window.setTimeout(() => {
      firstInvalid.focus();
      showingValidity = true;
      firstInvalid.reportValidity();
      showingValidity = false;
      firstInvalid = null;
    }, 0);
  }, true);

  const baseline = $(form).serialize();
  function updateStatus() {
    dirty = $(form).serialize() !== baseline;
    saveBar.classList.toggle('is-dirty', dirty);
    status.textContent = dirty ? 'Unsaved changes — save when you’re ready.' : 'Changes across all tabs are saved together.';
  }
  function updateFieldLabels() {
    root.querySelectorAll('.dchf-field-checkbox').forEach((field) => {
      field.querySelector('.dchf-toggle-label').textContent = field.querySelector('[type="checkbox"]').checked ? 'Enabled' : 'Hidden';
    });
    root.querySelectorAll('.dchf-field-color').forEach((field) => {
      field.querySelector('output').textContent = field.querySelector('input').value;
    });
  }
  $(form).on('input change', ':input', () => {
    updateFieldLabels();
    updateStatus();
  });
  updateFieldLabels();
  $(root).on('change', '.dchf-field-menu select', function () {
    const link = this.closest('.dchf-field').querySelector('.dchf-edit-menu');
    const url = new URL(link.dataset.menuBase);
    if (this.value !== '0') {
      url.searchParams.set('action', 'edit');
      url.searchParams.set('menu', this.value);
    }
    link.href = url.toString();
  });
  form.addEventListener('submit', () => {
    submitting = true;
    status.textContent = 'Saving changes…';
  });
  window.addEventListener('beforeunload', (event) => {
    if (!dirty || submitting) return;
    event.preventDefault();
    event.returnValue = '';
  });

  $(root).on('click', '.dchf-select-image', function () {
    const field = $(this).closest('.dchf-image-field');
    const picker = wp.media({title: 'Choose an image', button: {text: 'Use this image'}, library: {type: 'image'}, multiple: false});
    picker.on('select', () => {
      const image = picker.state().get('selection').first().toJSON();
      field.find('input[type="hidden"]').val(image.id).trigger('change');
      const preview = $('<img>').attr('src', image.sizes?.medium?.url || image.url).attr('alt', image.alt || 'Selected image');
      field.find('.dchf-image-preview').empty().append(preview);
      field.find('.dchf-remove-image').prop('hidden', false);
    });
    picker.open();
  });
  $(root).on('click', '.dchf-remove-image', function () {
    const field = $(this).closest('.dchf-image-field');
    field.find('input[type="hidden"]').val('0').trigger('change');
    const fallback = field.find('.dchf-image-fallback')[0];
    field.find('.dchf-image-preview').empty().append(fallback.content.cloneNode(true));
    $(this).prop('hidden', true);
    field.find('.dchf-select-image').trigger('focus');
  });
});
