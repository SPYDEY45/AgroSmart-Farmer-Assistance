/**
 * AgroSmart – Client Side Interactions
 * BCA Field Project JavaScript Engine
 */

document.addEventListener('DOMContentLoaded', function () {
  // Confirm Delete Dialogs
  const deleteButtons = document.querySelectorAll('.btn-confirm-delete');
  deleteButtons.forEach(btn => {
    btn.addEventListener('click', function (e) {
      const entity = this.getAttribute('data-entity') || 'item';
      if (!confirm(`Are you sure you want to delete this ${entity}? This action cannot be undone.`)) {
        e.preventDefault();
      }
    });
  });

  // Auto Dismiss Alerts after 5 seconds
  const autoAlerts = document.querySelectorAll('.alert-dismissible');
  autoAlerts.forEach(alert => {
    setTimeout(() => {
      try {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      } catch (e) {
        // Ignored if manually closed
      }
    }, 6000);
  });

  // Client-side Price calculation helper on product forms
  const qtyInput = document.getElementById('calc_quantity');
  const priceInput = document.getElementById('calc_price');
  const totalDisplay = document.getElementById('calc_total_display');

  if (qtyInput && priceInput && totalDisplay) {
    const updateTotal = () => {
      const q = parseFloat(qtyInput.value) || 0;
      const p = parseFloat(priceInput.value) || 0;
      const total = q * p;
      totalDisplay.innerText = '₹ ' + total.toLocaleString('en-IN', { maximumFractionDigits: 2 });
    };
    qtyInput.addEventListener('input', updateTotal);
    priceInput.addEventListener('input', updateTotal);
  }
});

/**
 * Switch Language Helper
 */
function setAppLanguage(lang) {
  const url = new URL(window.location.href);
  url.searchParams.set('set_lang', lang);
  window.location.href = url.toString();
}
