document.addEventListener('DOMContentLoaded', () => {
  const menu = document.querySelector('[data-mobile-menu]');
  const openBtn = document.querySelector('[data-menu-open]');
  const closeBtn = document.querySelector('[data-menu-close]');

  if (menu && openBtn && closeBtn) {
    openBtn.addEventListener('click', () => menu.classList.add('show'));
    closeBtn.addEventListener('click', () => menu.classList.remove('show'));
  }

  const filterBar = document.querySelector('[data-partner-filters]');
  const grid = document.querySelector('[data-partner-grid]');

  if (filterBar && grid) {
    filterBar.addEventListener('click', (event) => {
      const button = event.target.closest('[data-filter]');
      if (!button) return;

      filterBar.querySelectorAll('[data-filter]').forEach((el) => el.classList.remove('active'));
      button.classList.add('active');

      const filter = button.dataset.filter;
      grid.querySelectorAll('[data-partner-category]').forEach((card) => {
        card.style.display = filter === 'alle' || card.dataset.partnerCategory === filter ? '' : 'none';
      });
    });
  }
});
