// Cookie-toestemming: Ahrefs Web Analytics + Google Tag Manager laden pas na
// expliciete toestemming, niet standaard. Zie ook resources/views/privacy.blade.php.
(() => {
  const STORAGE_KEY = 'revrace_consent';
  const GTM_ID = 'GTM-NFH7Z6V5';
  const AHREFS_KEY = 'x3pTCkZRmLD0nmLUPg2tpg';

  const getConsent = () => {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch (e) {
      return null;
    }
  };

  const setConsent = (analytics) => {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({ analytics, decidedAt: new Date().toISOString() }));
    } catch (e) {
      // localStorage kan geblokkeerd zijn (privémodus); toestemming werkt dan niet
      // persistent, maar de site blijft functioneren.
    }
  };

  const loadAnalytics = () => {
    if (window.__revraceAnalyticsLoaded) return;
    window.__revraceAnalyticsLoaded = true;

    const ahrefs = document.createElement('script');
    ahrefs.src = 'https://analytics.ahrefs.com/analytics.js';
    ahrefs.async = true;
    ahrefs.setAttribute('data-key', AHREFS_KEY);
    document.head.appendChild(ahrefs);

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
    const gtm = document.createElement('script');
    gtm.async = true;
    gtm.src = `https://www.googletagmanager.com/gtm.js?id=${GTM_ID}&l=dataLayer`;
    document.head.appendChild(gtm);
  };

  document.addEventListener('DOMContentLoaded', () => {
    const banner = document.getElementById('consent-banner');
    const consent = getConsent();

    if (consent && consent.analytics) {
      loadAnalytics();
    } else if (!consent && banner) {
      banner.hidden = false;
    }

    if (banner) {
      banner.addEventListener('click', (event) => {
        const action = event.target.closest('[data-consent-action]');
        if (!action) return;
        const accept = action.dataset.consentAction === 'accept';
        setConsent(accept);
        banner.hidden = true;
        if (accept) loadAnalytics();
      });
    }

    document.querySelectorAll('[data-consent-open]').forEach((btn) => {
      btn.addEventListener('click', () => {
        if (banner) banner.hidden = false;
      });
    });
  });
})();

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
