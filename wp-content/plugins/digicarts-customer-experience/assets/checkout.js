jQuery(($) => {
  const form = document.querySelector('form.checkout');
  if (!form) return;
  const syncAccount = () => {
    const create = form.querySelector('#createaccount');
    if (!create) return;
    form.querySelectorAll('.woocommerce-account-fields div.create-account input').forEach((input) => {
      input.disabled = !create.checked;
    });
  };
  const revealErrors = () => {
    const optional = form.querySelector('.dce-optional-details');
    if (!optional) return;
    const invalid = optional.querySelector('.woocommerce-invalid, [aria-invalid="true"]');
    if (invalid) optional.open = true;
    document.querySelectorAll('.woocommerce-error [data-id]').forEach((error) => {
      const field = document.getElementById(error.dataset.id);
      if (field && optional.contains(field)) optional.open = true;
    });
  };
  $(document.body).on('checkout_error', revealErrors);
  $(document.body).on('updated_checkout', syncAccount);
  $(form).on('change', '#createaccount', syncAccount);
  form.addEventListener('invalid', (event) => {
    const details = event.target.closest('details');
    if (details) details.open = true;
  }, true);
  syncAccount();
});
