/* ==========================================================================
   Overzichtspagina's (13-10-2026): merken, segment en A2.
   - grafiek: tooltip bij de stippen
   - modellijst: sorteren, A2- en segmentfilter, "Toon alle", stip oplichten bij hover
   - merken: segmentfilter en volgorde, tegels schuiven naar hun nieuwe plek (FLIP)
   Leunt op window.RR uit site.js (veer-filter).
   ========================================================================== */
(() => {
  const EASE = 'cubic-bezier(.22, 1, .36, 1)';

  document.addEventListener('DOMContentLoaded', () => {
    const RR = window.RR;
    if (!RR) return;
    const RUST = RR.RUST;

    // Grafiek: tooltip, ook bij oplichten vanuit de lijst.
    const plot = document.querySelector('[data-plot]');
    const tip = plot && plot.querySelector('.plot__tip');
    const showTip = (a) => {
      if (!tip) return;
      const dot = a.querySelector('circle').getBoundingClientRect(), box = plot.getBoundingClientRect();
      tip.querySelector('b').textContent = a.dataset.tip;
      tip.querySelector('span').innerHTML = a.dataset.meta;
      tip.style.setProperty('--x', `${dot.left + dot.width / 2 - box.left}px`);
      tip.style.setProperty('--y', `${dot.top - box.top}px`);
      tip.classList.toggle('is-left', dot.left - box.left > box.width * 0.62);
      tip.classList.add('is-on');
    };
    const hideTip = () => tip && tip.classList.remove('is-on');
    if (plot) {
      plot.querySelectorAll('.plot__dots a').forEach((a) => {
        a.addEventListener('pointerenter', () => showTip(a));
        a.addEventListener('pointerleave', hideTip);
      });
    }
    const dotFor = (id) => plot && plot.querySelector(`.plot__dots a[data-id="${id}"]`);

    // Modellijst.
    document.querySelectorAll('[data-speclist]').forEach((box) => {
      const list = box.querySelector('.speclist__rows');
      const rows = [...list.children];
      const more = box.querySelector('[data-more]');
      const empty = box.querySelector('.speclist__empty');
      const limit = Number(box.dataset.limit) || 12;
      const state = { sort: 'ratio', cat: 'all', a2: false, open: false };
      const key = {
        ratio: (r) => -r.dataset.ratio,
        pk: (r) => -r.dataset.pk,
        kg: (r) => +r.dataset.kg,
        year: (r) => -r.dataset.year,
      };

      const render = (animate = true) => {
        const shown = rows
          .filter((r) => (!state.a2 || r.dataset.a2 === '1') && (state.cat === 'all' || r.dataset.cat === state.cat))
          .sort((a, b) => key[state.sort](a) - key[state.sort](b) || key.ratio(a) - key.ratio(b));
        rows.forEach((r) => { r.hidden = true; r.classList.remove('is-extra'); });
        shown.forEach((r, i) => {
          list.appendChild(r);
          r.hidden = !state.open && i >= limit;
          r.querySelector('.spec-row__pos').textContent = i + 1;
        });
        if (more) {
          more.hidden = state.open || shown.length <= limit;
          more.textContent = `Toon alle ${shown.length} modellen`;
        }
        empty.hidden = shown.length > 0;

        // Stippen die buiten het filter vallen, dimmen.
        if (plot) {
          const ids = new Set(shown.map((r) => r.dataset.id));
          plot.querySelectorAll('.plot__dots a').forEach((a) => a.classList.toggle('is-dim', !ids.has(a.dataset.id)));
        }

        if (animate && !RUST) {
          shown.filter((r) => !r.hidden).slice(0, 16).forEach((r, i) => r.animate(
            [{ opacity: 0, transform: 'translateY(10px)' }, { opacity: 1, transform: 'none' }],
            { duration: 320, delay: i * 28, easing: EASE, fill: 'backwards' },
          ));
        }
      };

      box.querySelectorAll('.filter').forEach((f) => RR.bindFilter(f, (value) => {
        state[f.dataset.key] = value;
        render();
      }));

      const a2 = box.querySelector('[data-a2-toggle]');
      if (a2) a2.addEventListener('click', () => {
        state.a2 = !state.a2;
        a2.setAttribute('aria-pressed', String(state.a2));
        render();
      });

      if (more) more.addEventListener('click', () => {
        const before = rows.filter((r) => !r.hidden).length;
        state.open = true;
        render(false);
        if (!RUST) rows.filter((r) => !r.hidden).slice(before, before + 20).forEach((r, i) => r.animate(
          [{ opacity: 0, transform: 'translateY(10px)' }, { opacity: 1, transform: 'none' }],
          { duration: 320, delay: i * 24, easing: EASE, fill: 'backwards' },
        ));
      });

      // Rij aanwijzen laat de stip in de grafiek oplichten.
      rows.forEach((r) => {
        r.addEventListener('pointerenter', () => { const d = dotFor(r.dataset.id); if (d) d.classList.add('is-hot'); });
        r.addEventListener('pointerleave', () => { const d = dotFor(r.dataset.id); if (d) d.classList.remove('is-hot'); });
      });

      render(false);
    });

    // Merken.
    document.querySelectorAll('[data-brandgrid]').forEach((box) => {
      const grid = box.querySelector('.brands');
      const tiles = [...grid.children];
      const status = box.querySelector('[data-status]');
      const state = { seg: 'all', sort: 'count' };
      const segs = new Map(tiles.map((t) => [t, JSON.parse(t.dataset.segs || '{}')]));
      const n = (t) => (state.seg === 'all' ? Number(t.dataset.total) : Number(segs.get(t)[state.seg] || 0));

      const render = () => {
        const first = new Map(tiles.filter((t) => !t.hidden).map((t) => [t, t.getBoundingClientRect()]));
        const shown = tiles.filter((t) => n(t) > 0).sort((a, b) => (state.sort === 'az'
          ? a.dataset.brand.localeCompare(b.dataset.brand, 'nl')
          : n(b) - n(a) || a.dataset.brand.localeCompare(b.dataset.brand, 'nl')));
        const max = Math.max(1, ...shown.map(n));

        tiles.forEach((t) => { t.hidden = n(t) === 0; });
        shown.forEach((t, i) => {
          grid.appendChild(t);
          const count = n(t);
          t.querySelector('.brand-tile__num b').textContent = count;
          t.querySelector('.brand-tile__num small').textContent = count === 1 ? 'model' : 'modellen';
          t.querySelector('.brand-tile__pos').textContent = state.sort === 'az' ? '' : i + 1;
          t.querySelector('.meter i').style.setProperty('--w', (count / max).toFixed(3));
        });
        const total = shown.reduce((s, t) => s + n(t), 0);
        status.textContent = state.seg === 'all'
          ? `${shown.length} merken`
          : `${shown.length} merken met samen ${total} ${total === 1 ? 'model' : 'modellen'}`;

        if (RUST) return;
        shown.forEach((t, i) => {
          const was = first.get(t), now = t.getBoundingClientRect();
          if (was) {
            const dx = was.left - now.left, dy = was.top - now.top;
            if (dx || dy) t.animate([{ transform: `translate(${dx}px, ${dy}px)` }, { transform: 'none' }], { duration: 520, easing: EASE });
          } else {
            t.animate([{ opacity: 0, transform: 'scale(.96)' }, { opacity: 1, transform: 'none' }], { duration: 360, delay: Math.min(i, 12) * 25, easing: EASE, fill: 'backwards' });
          }
        });
      };

      box.querySelectorAll('.filter').forEach((f) => RR.bindFilter(f, (value) => {
        state[f.dataset.key] = value;
        render();
      }));
    });
  });
})();
