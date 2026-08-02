document.addEventListener('DOMContentLoaded', () => {
  const menu = document.querySelector('[data-mobile-menu]');
  const openBtn = document.querySelector('[data-menu-open]');
  const closeBtn = document.querySelector('[data-menu-close]');

  if (menu && openBtn && closeBtn) {
    openBtn.addEventListener('click', () => menu.classList.add('show'));
    closeBtn.addEventListener('click', () => menu.classList.remove('show'));
  }

  const closeAllDropdowns = () => {
    document.querySelectorAll('[data-nav-dropdown].open').forEach((dropdown) => {
      dropdown.classList.remove('open');
      dropdown.querySelector('[data-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
    });
  };

  document.querySelectorAll('[data-nav-dropdown]').forEach((dropdown) => {
    const toggle = dropdown.querySelector('[data-dropdown-toggle]');
    if (!toggle) return;

    toggle.addEventListener('click', (event) => {
      event.stopPropagation();
      const isOpen = dropdown.classList.contains('open');
      closeAllDropdowns();
      if (!isOpen) {
        dropdown.classList.add('open');
        toggle.setAttribute('aria-expanded', 'true');
      }
    });
  });

  document.addEventListener('click', closeAllDropdowns);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeAllDropdowns();
  });

  document.querySelectorAll('[data-filter-bar]').forEach((filterBar) => {
    const grid = document.querySelector(`[data-filter-grid="${filterBar.dataset.filterBar}"]`);
    if (!grid) return;

    filterBar.addEventListener('click', (event) => {
      const button = event.target.closest('[data-filter]');
      if (!button) return;

      filterBar.querySelectorAll('[data-filter]').forEach((el) => el.classList.remove('active'));
      button.classList.add('active');

      const filter = button.dataset.filter;
      grid.querySelectorAll('[data-filter-category]').forEach((card) => {
        card.style.display = filter === 'alle' || card.dataset.filterCategory === filter ? '' : 'none';
      });
    });
  });
});
