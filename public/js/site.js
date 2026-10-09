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

/* ==========================================================================
   RevRace 2.0: interactielaag en signature-momenten.
   Zie Website/redesign-v2/_design-system-2.md.
   ========================================================================== */
(() => {
  const RUST = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const ease = (t) => { t = Math.min(1, Math.max(0, t)); return t * t * (3 - 2 * t); };

  // Rekveer, letterlijk uit design-bibliotheek/demos/veer-tabindicator.html (zakelijk filter).
  class Rekveer {
    constructor(o, teken) { this.o = o; this.teken = teken; this.p = [0, 0]; this.v = [0, 0]; this.t = [0, 0]; this.kop = 1; this.trip = 1; this.loopt = false; }
    zet(a, b) { this.p = [a, b]; this.t = [a, b]; this.v = [0, 0]; this.teken(this); }
    naar(a, b) {
      if (RUST) return this.zet(a, b);
      const nu = (this.p[0] + this.p[1]) / 2, doel = (a + b) / 2;
      this.kop = doel >= nu ? 1 : 0; this.t = [a, b];
      this.trip = Math.max(1, Math.abs(this.t[this.kop] - this.p[this.kop]));
      if (!this.loopt) { this.loopt = true; this.vorige = performance.now(); requestAnimationFrame(this.lus); }
    }
    stap(h) {
      const { K, C, kMin, kMax, zeta } = this.o, k = this.kop, s = 1 - k;
      this.v[k] += (-K * (this.p[k] - this.t[k]) - C * this.v[k]) * h; this.p[k] += this.v[k] * h;
      const home = 1 - ease(Math.abs(this.p[k] - this.t[k]) / this.trip);
      const KB = kMin + (kMax - kMin) * home, CB = zeta * 2 * Math.sqrt(KB);
      this.v[s] += (-KB * (this.p[s] - this.t[s]) - CB * this.v[s]) * h; this.p[s] += this.v[s] * h;
    }
    rust() { return [0, 1].every((i) => Math.abs(this.v[i]) < 4 && Math.abs(this.p[i] - this.t[i]) < 0.3); }
    lus = (nu) => {
      let dt = Math.min(0.05, (nu - this.vorige) / 1000); this.vorige = nu;
      while (dt > 0) { const h = Math.min(dt, 1 / 240); this.stap(h); dt -= h; }
      if (this.rust()) { this.p = [...this.t]; this.v = [0, 0]; this.loopt = false; }
      this.teken(this);
      if (this.loopt) requestAnimationFrame(this.lus);
    };
  }
  const FILTER = { K: 320, C: 32, kMin: 150, kMax: 320, zeta: 0.95 };

  // Signature: vijf startlichten aan, uit, dan rijden de motoren de startopstelling in.
  const runs = new WeakMap();
  const lightsOut = (scope, { slow = false } = {}) => {
    const lights = [...scope.querySelectorAll('.lights i')];
    const cars = [...scope.querySelectorAll('.slot__car')];
    const run = (runs.get(scope) || 0) + 1; runs.set(scope, run);
    const live = () => runs.get(scope) === run;
    if (RUST || !lights.length) { cars.forEach((c) => c.classList.add('is-in')); return; }
    const step = slow ? 200 : 100;
    lights.forEach((l, i) => setTimeout(() => live() && l.classList.add('on'), i * step));
    setTimeout(() => {
      if (!live()) return;
      lights.forEach((l) => l.classList.remove('on'));
      cars.forEach((c, i) => setTimeout(() => live() && c.classList.add('is-in'), i * 85));
    }, lights.length * step + (slow ? 320 : 120));
  };
  window.RevRace = { lightsOut };

  document.addEventListener('DOMContentLoaded', () => {
    // Header krijgt een achtergrond zodra er gescrold is.
    const header = document.querySelector('.header');
    if (header) {
      const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
      window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
      const toggle = document.getElementById('nav-toggle');
      if (toggle) toggle.addEventListener('change', () => header.classList.toggle('is-open', toggle.checked));
    }

    // Scroll-reveal, timing-flash, finishvlag.
    const io = new IntersectionObserver((entries) => entries.forEach((e) => {
      if (!e.isIntersecting) return;
      e.target.classList.add('is-in'); io.unobserve(e.target);
    }), { threshold: 0.15 });
    document.querySelectorAll('[data-reveal], .timing, .finish').forEach((el) => io.observe(el));

    // Tellers die oplopen als ze in beeld komen.
    if (!RUST) {
      const counter = new IntersectionObserver((entries) => entries.forEach((e) => {
        if (!e.isIntersecting) return;
        counter.unobserve(e.target);
        const el = e.target, end = Number(el.dataset.count), t0 = performance.now();
        const tick = (t) => { const p = Math.min((t - t0) / 900, 1); el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(tick); };
        requestAnimationFrame(tick);
      }), { threshold: 0.5 });
      document.querySelectorAll('[data-count]').forEach((el) => counter.observe(el));
    }

    // Startopstelling die bij laden al resultaten heeft (wizard).
    document.querySelectorAll('[data-lights-on-load]').forEach((scope) => lightsOut(scope, { slow: true }));

    // Rijstijlkiezer op de home: veer-filters plus startopstelling uit data-advice.
    document.querySelectorAll('[data-style-picker]').forEach((picker) => {
      const data = JSON.parse(picker.querySelector('script[type="application/json"]').textContent);
      const grid = picker.querySelector('[data-startgrid]');
      const link = picker.querySelector('[data-advice-link]');
      const state = { voorkeur: 'bochten', ervaring: 'beginner' };
      const fmt = (n) => n.toFixed(2).replace('.', ',');
      const esc = (s) => s.replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

      const render = () => {
        const rows = (data[state.ervaring] || {})[state.voorkeur] || [];
        grid.innerHTML = rows.length ? rows.map((m, i) => `
          <div class="slot" style="--row:${i}">
            <div class="slot__pos">P${i + 1}</div>
            <div class="slot__car">
              <div class="slot__name"><a href="${esc(m.url)}">${esc(m.label)}</a>${m.a2 ? '<span class="a2">A2</span>' : ''}</div>
              <div class="slot__specs"><span><b>${m.hp}</b> pk</span><span><b>${m.kg}</b> kg</span><span><b>${fmt(m.hp / m.kg)}</b> pk/kg</span></div>
            </div>
          </div>`).join('') : '<p class="grid-empty">Nog geen motoren voor deze combinatie.</p>';
        if (link) {
          const url = new URL(link.href, window.location.origin);
          url.searchParams.set('ervaring', state.ervaring); url.searchParams.set('voorkeur', state.voorkeur);
          link.href = url.pathname + url.search + '#advies';
        }
        lightsOut(picker, { slow: false });
      };

      picker.querySelectorAll('.filter').forEach((f) => {
        const base = f.querySelector('.filter__base'), pill = f.querySelector('.filter__pill');
        pill.innerHTML = [...base.children].map((b) => `<span>${b.textContent}</span>`).join('');
        const veer = new Rekveer(FILTER, (v) => { pill.style.setProperty('--l', v.p[0] + 'px'); pill.style.setProperty('--r', v.p[1] + 'px'); });
        const edges = (b) => [b.offsetLeft - 4, b.offsetLeft - 4 + b.offsetWidth];
        const init = () => veer.zet(...edges(base.querySelector('[aria-pressed="true"]')));
        (document.fonts ? document.fonts.ready : Promise.resolve()).then(init);
        window.addEventListener('resize', init);
        base.querySelectorAll('button').forEach((b) => b.addEventListener('click', () => {
          if (b.getAttribute('aria-pressed') === 'true') return;
          base.querySelectorAll('button').forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
          veer.naar(...edges(b));
          state[f.dataset.key] = b.dataset.value;
          render();
        }));
      });

      grid.querySelectorAll('.slot__car').length ? lightsOut(picker, { slow: true }) : render();
    });

    // Inhoudsopgave volgt de sectie die in beeld is.
    const tocLinks = [...document.querySelectorAll('.toc a')];
    if (tocLinks.length) {
      const setActive = (id) => tocLinks.forEach((a) => a.classList.toggle('is-active', a.getAttribute('href') === '#' + id));
      const tocIo = new IntersectionObserver((es) => es.forEach((e) => e.isIntersecting && setActive(e.target.id)), { rootMargin: '-20% 0px -70% 0px' });
      tocLinks.forEach((a) => { const h = document.getElementById(a.getAttribute('href').slice(1)); if (h) tocIo.observe(h); });
      setActive(tocLinks[0].getAttribute('href').slice(1));
    }

    // Wizard: sectorbalk loopt vol terwijl het formulier ingevuld wordt.
    const wiz = document.querySelector('[data-wizard-form]');
    if (wiz) {
      const bars = wiz.querySelectorAll('.sectorbar span');
      const progress = () => {
        const filled = [
          wiz.querySelector('[name=ervaring]:checked'),
          wiz.querySelector('[name=voorkeur]:checked'),
          wiz.querySelector('[name="terrein[]"]:checked'),
          ['leeftijd', 'lengte', 'gewicht'].some((n) => wiz.elements[n] && wiz.elements[n].value),
        ];
        bars.forEach((b, i) => b.classList.toggle('done', Boolean(filled[i])));
      };
      wiz.addEventListener('input', progress); wiz.addEventListener('change', progress); progress();
    }
  });
})();
