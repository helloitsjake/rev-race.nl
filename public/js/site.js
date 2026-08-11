document.addEventListener('DOMContentLoaded', () => {
  // Nav-uitklap (Ontdekken) en mobiel menu zijn native <details> + CSS-only
  // checkbox-hack in het redesign — geen JS meer nodig voor navigatie.

  document.querySelectorAll('[data-filter-bar]').forEach((filterBar) => {
    const grid = document.querySelector(`[data-filter-grid="${filterBar.dataset.filterBar}"]`);
    if (!grid) return;

    filterBar.addEventListener('click', (event) => {
      const button = event.target.closest('[data-filter]');
      if (!button) return;

      filterBar.querySelectorAll('[data-filter]').forEach((el) => el.classList.remove('is-active'));
      button.classList.add('is-active');

      const filter = button.dataset.filter;
      grid.querySelectorAll('[data-filter-category]').forEach((card) => {
        card.style.display = filter === 'alle' || card.dataset.filterCategory === filter ? '' : 'none';
      });
    });
  });
});
