document.addEventListener('click', (event) => {
  const target = event.target.closest('[data-confirm]');
  if (target && !window.confirm(target.dataset.confirm)) {
    event.preventDefault();
  }
});

const bookingForm = document.querySelector('[data-booking-form]');
if (bookingForm) {
  const price = Number(bookingForm.dataset.price || 0);
  const travelers = bookingForm.querySelector('[name="travelers"]');
  const total = bookingForm.querySelector('[data-total]');
  const format = new Intl.NumberFormat('en-LK', { style: 'currency', currency: 'LKR' });
  travelers.addEventListener('input', () => {
    total.textContent = format.format(price * Math.max(1, Number(travelers.value || 1)));
  });
}

const revealTargets = document.querySelectorAll(
  '.hero, .section-head, .admin-head, .toolbar, .panel, .package-card, .stats article, .list-item, .bar-row'
);

if ('IntersectionObserver' in window) {
  revealTargets.forEach((target, index) => {
    target.classList.add('reveal');
    target.style.setProperty('--reveal-delay', `${Math.min(index * 45, 360)}ms`);
  });

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  revealTargets.forEach((target) => observer.observe(target));
} else {
  revealTargets.forEach((target) => target.classList.add('is-visible'));
}
