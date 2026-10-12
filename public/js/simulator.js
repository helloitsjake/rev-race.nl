// Simulatiepagina 2.0 (12-10-2026). Keuze als zin, startlichten terwijl de server rekent, dan de
// race op de echte simulatiedata en direct daarna de keuzekaarten met aanbod. De embed gebruikt
// nog simulation.js. Gedeelde stukken (veer-filter, lichten, race-rekenwerk) komen uit site.js.
(() => {
  const cfg = window.REVRACE || {};
  const RR = window.RevRace || {};
  const RUST = Boolean(RR.RUST);

  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => [...r.querySelectorAll(s)];
  const esc = (s) => String(s ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const csrf = () => qs('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const wait = (ms) => new Promise((r) => setTimeout(r, ms));
  const comma = (n, d = 2) => Number(n).toFixed(d).replace('.', ',');
  const debounce = (fn, ms = 180) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

  const selected = { A: null, B: null };
  const options = { road_type: 'straight', road_condition: 'dry', distance_m: 500, speed_kmh: 100 };
  let last = null; // laatste uitslag, voor "kijk terug" zonder nieuwe simulatie
  let busy = false;

  const root = qs('[data-sim]');
  if (!root) return;
  const form = qs('[data-sim-form]', root);
  const panel = qs('[data-race-panel]', root);
  const status = qs('[data-status]', root);
  const runBtn = qs('[data-run]', root);
  const runLabel = qs('[data-run-label]', root);
  const lanes = { a: qs('[data-lane="a"]', root), b: qs('[data-lane="b"]', root) };
  const resultBox = qs('[data-result]', root);

  /* ---------- status, limiet, meldingen ---------- */

  const setStatus = (html) => { status.innerHTML = html; };

  const renderLimit = (limit) => {
    if (!limit) return;
    cfg.limit = limit;
    const el = qs('[data-limit]', root);
    if (limit.blocked) {
      const reset = limit.reset_at ? new Date(limit.reset_at).toLocaleString('nl-NL', { weekday: 'short', hour: '2-digit', minute: '2-digit' }) : null;
      el.innerHTML = `Daglimiet bereikt${reset ? `, weer races vanaf <b>${esc(reset)}</b>` : ''}.`;
    } else {
      el.innerHTML = `<b>${limit.remaining}</b> van ${limit.limit} gratis races over vandaag`;
    }
    syncRun();
  };

  const syncRun = () => {
    runBtn.disabled = busy || !selected.A || !selected.B || Boolean(cfg.limit?.blocked);
  };

  const message = (text) => {
    const box = qs('[data-message]', root);
    box.textContent = text || '';
    box.hidden = !text;
  };

  /* ---------- motor kiezen ---------- */

  const bikeIcon = (category) => qs(`template[data-bike-icon="${category === 'sport' ? 'sport' : 'other'}"]`).innerHTML;

  const pickMotor = (side, motor) => {
    selected[side] = motor;
    const input = qs(`[data-motor-input="${side}"]`, root);
    input.value = motor.label;
    qs(`[data-pickfield="${side}"]`, root).classList.add('is-set');
    closeList(side);
    const lane = lanes[side.toLowerCase()];
    lane.classList.remove('is-empty');
    const bike = qs('[data-bike]', lane);
    bike.innerHTML = bikeIcon(motor.category);
    bike.style.setProperty('--p', 0);
    bike.style.setProperty('--v', 0);
    if (!RUST) { bike.classList.remove('is-rolling'); void bike.offsetWidth; bike.classList.add('is-rolling'); }
    qs('[data-lane-time]', lane).innerHTML = '&nbsp;';
    if (selected.A && selected.B) setStatus('Klaar voor de start');
    else setStatus(`Kies motor ${selected.A ? 'B' : 'A'}`);
    syncRun();
  };

  const closeList = (side) => {
    const list = qs(`[data-suggestions="${side}"]`, root);
    list.hidden = true;
    qs(`[data-motor-input="${side}"]`, root).setAttribute('aria-expanded', 'false');
  };

  const search = async (side, query) => {
    const list = qs(`[data-suggestions="${side}"]`, root);
    const input = qs(`[data-motor-input="${side}"]`, root);
    if (query.trim().length < 2) { closeList(side); return; }
    let motors = [];
    try {
      const res = await fetch(`${cfg.routes.motors}?q=${encodeURIComponent(query)}`, { headers: { Accept: 'application/json' } });
      motors = (await res.json()).motors || [];
    } catch (e) { motors = []; }
    if (input.value !== query) return; // er is intussen verder getypt
    list.innerHTML = motors.map((m, i) => `
      <button type="button" role="option" class="pickfield__opt" data-i="${i}">
        <span>${esc(m.label)}</span><small>${m.power_hp} pk · ${m.weight_kg} kg</small>
      </button>`).join('') + `
      <button type="button" role="option" class="pickfield__opt pickfield__opt--ai" data-ai>
        <span>${motors.length ? 'Niet de goede?' : 'Staat er niet bij.'} Zoek &ldquo;${esc(query.trim())}&rdquo; op met AI</span>
      </button>`;
    qsa('[data-i]', list).forEach((b) => b.addEventListener('click', () => pickMotor(side, motors[Number(b.dataset.i)])));
    qs('[data-ai]', list).addEventListener('click', () => lookupAi(side));
    list.hidden = false;
    input.setAttribute('aria-expanded', 'true');
  };

  const lookupAi = async (side) => {
    const input = qs(`[data-motor-input="${side}"]`, root);
    const query = input.value.trim();
    closeList(side);
    if (query.length < 3) { message('Vul eerst merk, model en bouwjaar in, dan kan de AI ernaar zoeken.'); return; }
    const field = qs(`[data-pickfield="${side}"]`, root);
    field.classList.add('is-loading');
    message('');
    try {
      const res = await fetch(cfg.routes.lookup, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: JSON.stringify({ query }),
      });
      const data = await res.json().catch(() => ({}));
      if (res.status === 401) { message('Opzoeken met AI kan als je bent ingelogd. Je kunt de specs ook zelf invullen.'); showManual(side, query); return; }
      if (!res.ok) { message(data.message || 'Geen betrouwbare specs gevonden. Vul ze hieronder zelf in.'); showManual(side, query); return; }
      pickMotor(side, data.motor);
    } catch (e) {
      message('Opzoeken lukte niet. Probeer het nog eens.');
    } finally {
      field.classList.remove('is-loading');
    }
  };

  const showManual = (side, query) => {
    const wrap = qs(`[data-manual-wrap="${side}"]`, root);
    const formEl = qs(`[data-manual-form="${side}"]`, wrap);
    const year = (query.match(/\b(19[5-9]\d|20\d{2})\b/) || [''])[0];
    const rest = query.replace(year, '').trim().split(/\s+/);
    const set = (f, v) => { const el = qs(`[data-manual="${f}"]`, formEl); if (el && !el.value) el.value = v; };
    set('brand', rest.shift() || ''); set('model', rest.join(' ')); set('year', year);
    wrap.hidden = false;
    formEl.hidden = false;
  };

  const submitManual = async (side) => {
    const wrap = qs(`[data-manual-wrap="${side}"]`, root);
    const btn = qs(`[data-manual-submit="${side}"]`, wrap);
    const fields = ['brand', 'model', 'year', 'engine_type', 'power_hp', 'torque_nm', 'weight_kg', 'displacement_cc'];
    const numeric = ['year', 'power_hp', 'torque_nm', 'weight_kg', 'displacement_cc'];
    const values = {};
    if (fields.some((f) => { const v = qs(`[data-manual="${f}"]`, wrap)?.value.trim(); values[f] = numeric.includes(f) ? Number(v) : v; return !v; })) {
      message('Vul alle velden in voordat je opslaat.');
      return;
    }
    btn.disabled = true;
    try {
      const res = await fetch(cfg.routes.manual, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: JSON.stringify(values),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) { message(data.message || 'Opslaan lukte niet, check de ingevulde waarden.'); return; }
      wrap.hidden = true;
      message('');
      pickMotor(side, data.motor);
    } catch (e) {
      message('Opslaan lukte niet. Probeer het nog eens.');
    } finally {
      btn.disabled = false;
    }
  };

  ['A', 'B'].forEach((side) => {
    const input = qs(`[data-motor-input="${side}"]`, root);
    input.addEventListener('input', debounce((e) => search(side, e.target.value)));
    input.addEventListener('input', () => {
      // Typen na een keuze maakt de keuze ongedaan, anders race je met iets anders dan er staat.
      if (selected[side] && input.value !== selected[side].label) {
        selected[side] = null;
        qs(`[data-pickfield="${side}"]`, root).classList.remove('is-set');
        syncRun();
      }
    });
    input.addEventListener('keydown', (e) => {
      const list = qs(`[data-suggestions="${side}"]`, root);
      const opts = qsa('.pickfield__opt', list);
      if (e.key === 'Enter') {
        // Enter kiest de eerste suggestie in plaats van het formulier te versturen.
        e.preventDefault();
        if (!list.hidden && opts[0] && !opts[0].dataset.ai) opts[0].click();
        else if (selected.A && selected.B) form.requestSubmit();
        return;
      }
      if (list.hidden || !opts.length) return;
      const i = opts.indexOf(document.activeElement);
      if (e.key === 'ArrowDown') { e.preventDefault(); opts[Math.min(i + 1, opts.length - 1)].focus(); }
      if (e.key === 'Escape') closeList(side);
    });
    qs(`[data-suggestions="${side}"]`, root).addEventListener('keydown', (e) => {
      const opts = qsa('.pickfield__opt', e.currentTarget);
      const i = opts.indexOf(document.activeElement);
      if (e.key === 'ArrowDown') { e.preventDefault(); opts[Math.min(i + 1, opts.length - 1)].focus(); }
      if (e.key === 'ArrowUp') { e.preventDefault(); (i <= 0 ? input : opts[i - 1]).focus(); }
      if (e.key === 'Escape') { closeList(side); input.focus(); }
    });
    qs(`[data-manual-submit="${side}"]`, root)?.addEventListener('click', () => submitManual(side));
  });
  document.addEventListener('click', (e) => {
    if (!e.target.closest('[data-pickfield]')) ['A', 'B'].forEach(closeList);
  });

  /* ---------- opties ---------- */

  const syncOptions = () => {
    const mode = options.road_type;
    qsa('[data-when="condition"]', root).forEach((el) => { el.hidden = mode === 'topspeed'; });
    qsa('[data-when="distance"]', root).forEach((el) => { el.hidden = !(mode === 'straight' || mode === 'twisty'); });
    qsa('[data-when="speed"]', root).forEach((el) => { el.hidden = mode !== 'braking'; });
  };
  const NUMERIC = ['distance_m', 'speed_kmh'];
  qsa('select[data-option]', root).forEach((sel) => sel.addEventListener('change', () => {
    options[sel.dataset.option] = NUMERIC.includes(sel.dataset.option) ? Number(sel.value) : sel.value;
    if (sel.dataset.option === 'road_type') syncOptions();
  }));

  /* ---------- race ---------- */

  const niceMax = (v) => {
    if (v <= 0) return 10;
    const m = 10 ** Math.floor(Math.log10(v)), r = v / m;
    return (r <= 1 ? 1 : r <= 2 ? 2 : r <= 2.5 ? 2.5 : r <= 5 ? 5 : 10) * m;
  };

  const setMarks = (max, unit) => {
    const labels = [0, 1, 2, 3, 4, 5].map((i) => {
      const v = (max / 5) * i;
      const txt = max >= 2000 && unit === 'm' ? `${comma(v / 1000, v % 1000 ? 1 : 0)}` : String(Math.round(v));
      return `<span>${txt}${i === 5 ? (max >= 2000 && unit === 'm' ? ' km' : ` ${unit}`) : ''}</span>`;
    });
    qs('[data-marks]', root).innerHTML = labels.join('');
  };

  // Bouwt per motor een functie van speeltijd (s) naar { x, v } plus het eindpunt, voor elk
  // soort race. x is de positie op de baan als fractie, v de snelheid in km/u.
  const plan = (r) => {
    if (r.mode === 'race') {
      const d = options.distance_m_ran;
      const sides = { a: RR.raceTrace({ t: r.time_a_s, s: r.samples?.a || [] }, d), b: RR.raceTrace({ t: r.time_b_s, s: r.samples?.b || [] }, d) };
      const vMax = Math.max(...(r.samples?.a || []).map((p) => p.v), ...(r.samples?.b || []).map((p) => p.v), 1);
      const scale = Math.min(r.time_a_s, r.time_b_s) / 3.2; // snelste ongeveer 3,2 s in beeld
      const at = (s, t) => { const p = RR.raceAt(sides[s], t * scale); return { x: p.x / d, v: p.v, vn: p.v / vMax }; };
      return {
        marks: [d, 'm'],
        end: { a: r.time_a_s / scale, b: r.time_b_s / scale },
        at,
        final: { a: comma(r.time_a_s) + ' s', b: comma(r.time_b_s) + ' s' },
      };
    }
    if (r.mode === 'topspeed') {
      const top = { a: r.top_speed_kmh_a, b: r.top_speed_kmh_b };
      const max = niceMax(Math.max(top.a, top.b) * 1.04);
      const T = { a: 3 * top.a / Math.max(top.a, top.b), b: 3 * top.b / Math.max(top.a, top.b) };
      const at = (s, t) => { const f = Math.min(t / T[s], 1); const v = top[s] * (1 - (1 - f) ** 3); return { x: v / max, v, vn: f < 1 ? 1 - f : 0 }; };
      return { marks: [max, 'km/u'], end: T, at, final: { a: `${top.a} km/u`, b: `${top.b} km/u` } };
    }
    // Remweg: beide motoren remmen vanaf dezelfde snelheid, de kortste stopt het eerst.
    const dist = { a: r.braking_distance_m_a, b: r.braking_distance_m_b };
    const max = niceMax(Math.max(dist.a, dist.b) * 1.08);
    const T = { a: 2.6 * dist.a / Math.max(dist.a, dist.b), b: 2.6 * dist.b / Math.max(dist.a, dist.b) };
    const at = (s, t) => { const f = Math.min(t / T[s], 1); return { x: (dist[s] * (1 - (1 - f) ** 2)) / max, v: r.speed_kmh * (1 - f), vn: 1 - f }; };
    return { marks: [max, 'm'], end: T, at, final: { a: `${comma(dist.a, 1)} m`, b: `${comma(dist.b, 1)} m` } };
  };

  let frame = 0;
  const playRace = (r) => new Promise((resolve) => {
    cancelAnimationFrame(frame);
    const p = plan(r);
    setMarks(...p.marks);
    const winner = r.winner.toLowerCase();
    Object.values(lanes).forEach((l) => l.classList.remove('is-winner', 'is-done', 'is-stop'));
    panel.classList.toggle('is-brake', r.mode === 'braking');
    const draw = (t) => ['a', 'b'].forEach((s) => {
      const lane = lanes[s], bike = qs('[data-bike]', lane);
      const done = t >= p.end[s];
      const pos = p.at(s, Math.min(t, p.end[s]));
      bike.style.setProperty('--p', Math.min(pos.x, 1).toFixed(4));
      bike.style.setProperty('--v', done ? 0 : Math.max(0, pos.vn).toFixed(3));
      lane.classList.toggle('is-done', done);
      qs('[data-lane-time]', lane).textContent = done ? p.final[s] : `${Math.round(pos.v)} km/u`;
    });
    const end = Math.max(p.end.a, p.end.b);
    const finish = () => { draw(end); lanes[winner].classList.add('is-winner'); resolve(); };
    if (RUST) return finish();
    const t0 = performance.now();
    const tick = (now) => {
      const t = (now - t0) / 1000;
      if (t >= end) return finish();
      draw(t);
      frame = requestAnimationFrame(tick);
    };
    frame = requestAnimationFrame(tick);
  });

  const lightsOn = async () => {
    const lights = qsa('.lights i', panel);
    lights.forEach((l) => l.classList.remove('on'));
    if (RUST) return;
    for (const l of lights) { l.classList.add('on'); await wait(230); }
  };
  const lightsOff = () => qsa('.lights i', panel).forEach((l) => l.classList.remove('on'));

  const scrollToRace = () => {
    const top = panel.getBoundingClientRect().top;
    const header = qs('.header')?.offsetHeight || 64;
    // Alleen scrollen als de baan niet al goed in beeld staat.
    if (top < header || top > window.innerHeight * 0.45) {
      window.scrollTo({ top: window.scrollY + top - header - 8, behavior: RUST ? 'auto' : 'smooth' });
    }
  };

  /* ---------- uitslag en aanbod ---------- */

  const verdict = (r) => {
    const w = r.winner === 'A' ? selected.A : selected.B;
    const name = esc(w.short_label || w.label);
    if (r.mode === 'race') {
      const road = { straight: 'het rechte stuk', twisty: 'een kronkelweg' }[r.road_type_ran];
      const cond = { dry: 'droog', wet: 'vochtig', rain: 'nat' }[r.road_condition_ran];
      const d = r.distance_ran >= 1000 ? `${comma(r.distance_ran / 1000, r.distance_ran % 1000 ? 1 : 0)} km` : `${r.distance_ran} m`;
      return [`${name} <span>wint.</span>`, `<b>${comma(r.delta_s)} s</b> sneller over ${d} op ${road}, ${cond} asfalt.`];
    }
    if (r.mode === 'topspeed') {
      const diff = Math.abs(r.top_speed_kmh_a - r.top_speed_kmh_b);
      return [`${name} <span>wint.</span>`, diff ? `<b>${diff} km/u</b> hogere opgegeven topsnelheid.` : 'Allebei dezelfde opgegeven topsnelheid.'];
    }
    const diff = Math.abs(r.braking_distance_m_a - r.braking_distance_m_b);
    return [`${name} <span>wint.</span>`, `Staat <b>${comma(diff, 1)} m</b> eerder stil vanaf ${r.speed_kmh} km/u.`];
  };

  const strengths = (m, o, won) => [
    won ? 'Wint deze race' : null,
    m.power_hp > o.power_hp ? `${m.power_hp - o.power_hp} pk meer` : null,
    m.torque_nm > o.torque_nm ? `${m.torque_nm - o.torque_nm} Nm meer koppel` : null,
    m.weight_kg < o.weight_kg ? `${o.weight_kg - m.weight_kg} kg lichter` : null,
    m.a2 && !o.a2 ? 'Mag met A2-rijbewijs' : null,
  ].filter(Boolean);

  const garageBtn = (m) => `
    <a class="gbtn" href="${esc(cfg.routes.register)}?bewaar=${m.id}" data-garage="${m.id}" data-state="idle" rel="nofollow">
      <svg class="gbtn__icon" viewBox="0 0 20 20" aria-hidden="true"><circle class="gbtn__ring" cx="10" cy="10" r="8" pathLength="1"/><path class="gbtn__plus" d="M10 6v8M6 10h8"/><path class="gbtn__check" d="M6 10.5l2.6 2.6L14 7.6" pathLength="1"/></svg>
      <span class="gbtn__lab" data-lab="idle">Bewaar in garage</span><span class="gbtn__lab" data-lab="busy">Bewaren</span><span class="gbtn__lab" data-lab="done">In je garage</span><span class="gbtn__lab" data-lab="full">Garage vol, bekijk</span>
    </a>`;

  const renderPick = (r) => {
    const sides = [['a', selected.A, selected.B, r.offer_url_a], ['b', selected.B, selected.A, r.offer_url_b]];
    const winner = r.winner.toLowerCase();
    // Winnaar eerst: die kaart is de hoofdactie (één rode knop per sectie).
    if (winner === 'b') sides.reverse();
    qs('[data-pick]', root).innerHTML = sides.map(([s, m, o, offer], i) => `
      <article class="sim-card sim-card--${s}${s === winner ? ' is-p1' : ''}" style="--i:${i}">
        <p class="sim-card__pos">P${i + 1}</p>
        <div class="sim-card__body">
          <p class="sim-card__cat">${esc(m.category_label || '')}${m.a2 ? '<span class="a2">A2</span>' : ''}</p>
          <h3><a href="${esc(m.url)}">${esc(m.label)}</a></h3>
          <p class="sim-card__specs"><span><b>${m.power_hp}</b> pk</span><span><b>${m.weight_kg}</b> kg</span><span><b>${comma(m.power_hp / Math.max(m.weight_kg, 1))}</b> pk/kg</span></p>
          ${(() => { const l = strengths(m, o, s === winner); return l.length ? `<ul class="sim-card__plus">${l.map((x) => `<li>${esc(x)}</li>`).join('')}</ul>` : ''; })()}
        </div>
        <div class="sim-card__acts">
          <a class="btn ${s === winner ? 'btn--red' : 'btn--line'}" href="${esc(offer)}" target="_blank" rel="nofollow noopener">Bekijk aanbod op Marktplaats</a>
          ${garageBtn(m)}
        </div>
      </article>`).join('') + (r.compare_url ? `
      <a class="sim__compare" href="${esc(r.compare_url)}">Volledige vergelijking: specs, nat en droog, en wat de cijfers betekenen <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10h12M11 5l5 5-5 5"/></svg></a>` : '');
  };

  const renderShare = (r) => {
    const box = qs('[data-share]', root);
    box.hidden = !r.share_url;
    if (!r.share_url) return;
    const w = r.winner === 'A' ? selected.A : selected.B;
    const line = `${w.short_label || w.label} wint met ${comma(r.delta_s)} s verschil op RevRace`;
    qs('[data-share-copy]', box).dataset.url = r.share_url;
    qs('[data-share-social="whatsapp"]', box).href = `https://wa.me/?text=${encodeURIComponent(`${line} ${r.share_url}`)}`;
    qs('[data-share-social="x"]', box).href = `https://twitter.com/intent/tweet?text=${encodeURIComponent(line)}&url=${encodeURIComponent(r.share_url)}`;
    qs('[data-share-social="facebook"]', box).href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(r.share_url)}`;
  };
  qs('[data-share-copy]', root).addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    try { await navigator.clipboard.writeText(btn.dataset.url); btn.textContent = 'Gekopieerd'; } catch (err) { btn.textContent = btn.dataset.url; }
    setTimeout(() => { btn.textContent = 'Kopieer link'; }, 2000);
  });

  const svgEl = (tag, attrs = {}) => { const el = document.createElementNS('http://www.w3.org/2000/svg', tag); Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v)); return el; };
  const interp = (samples, x) => {
    if (!samples.length) return 0;
    for (let i = 0; i < samples.length - 1; i++) {
      if (samples[i].x <= x && samples[i + 1].x >= x) {
        const span = samples[i + 1].x - samples[i].x;
        return samples[i].v + (span ? (x - samples[i].x) / span : 0) * (samples[i + 1].v - samples[i].v);
      }
    }
    return x < samples[0].x ? samples[0].v : samples[samples.length - 1].v;
  };

  const renderChart = (r) => {
    const wrap = qs('[data-chart-wrap]', root);
    const sa = r.samples?.a || [], sb = r.samples?.b || [];
    wrap.hidden = r.mode !== 'race' || (!sa.length && !sb.length);
    if (wrap.hidden) return;
    const svg = qs('[data-chart-svg]', wrap), readout = qs('[data-chart-readout]', wrap);
    // viewBox = werkelijke breedte, zodat tekst en lijnen niet meeschalen op brede schermen.
    const W = Math.max(300, Math.round(svg.getBoundingClientRect().width) || 640), H = 220, P = { l: 40, r: 14, t: 12, b: 26 };
    const pw = W - P.l - P.r, ph = H - P.t - P.b;
    const xMax = r.distance_ran, yMax = niceMax(Math.max(...sa.map((p) => p.v), ...sb.map((p) => p.v), 1));
    const mx = (x) => P.l + (x / xMax) * pw, my = (v) => P.t + ph - (v / yMax) * ph;
    svg.textContent = '';
    svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
    const yStep = yMax / ([5, 4].find((n) => Number.isInteger(yMax / n / 10)) || 4);
    for (let v = 0; v <= yMax + 0.01; v += yStep) {
      const y = my(v);
      svg.appendChild(svgEl('line', { x1: P.l, x2: W - P.r, y1: y, y2: y, class: 'chart-grid' }));
      const t = svgEl('text', { x: P.l - 8, y: y + 3, class: 'chart-axis-label', 'text-anchor': 'end' }); t.textContent = Math.round(v); svg.appendChild(t);
    }
    for (let i = 0; i <= 5; i++) {
      const v = (xMax / 5) * i;
      const t = svgEl('text', { x: mx(v), y: H - 6, class: 'chart-axis-label', 'text-anchor': 'middle' }); t.textContent = `${Math.round(v)} m`; svg.appendChild(t);
    }
    const names = { a: selected.A.short_label || selected.A.label, b: selected.B.short_label || selected.B.label };
    [['a', sa, 'var(--redline)'], ['b', sb, 'var(--midrange)']].forEach(([s, pts, c]) => {
      qs(`[data-legend="${s}"]`, wrap).textContent = names[s];
      if (!pts.length) return;
      const path = svgEl('path', { d: pts.map((p, i) => `${i ? 'L' : 'M'}${mx(p.x).toFixed(1)},${my(p.v).toFixed(1)}`).join(' '), fill: 'none', stroke: c, 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round', pathLength: 1, class: 'sim__line-path' });
      svg.appendChild(path);
    });
    const cross = svgEl('line', { x1: P.l, x2: P.l, y1: P.t, y2: H - P.b, class: 'chart-crosshair' });
    svg.appendChild(cross);
    const hit = svgEl('rect', { x: P.l, y: P.t, width: pw, height: ph, fill: 'transparent' });
    svg.appendChild(hit);
    const def = 'Beweeg over de grafiek voor de snelheid per punt.';
    readout.textContent = def;
    hit.addEventListener('pointermove', (e) => {
      const rect = svg.getBoundingClientRect();
      const lx = Math.min(Math.max((e.clientX - rect.left) * (W / rect.width), P.l), W - P.r);
      const d = ((lx - P.l) / pw) * xMax;
      cross.setAttribute('x1', lx); cross.setAttribute('x2', lx); cross.style.opacity = 1;
      readout.textContent = `Op ${Math.round(d)} m: ${names.a} ${Math.round(interp(sa, d))} km/u, ${names.b} ${Math.round(interp(sb, d))} km/u`;
    });
    hit.addEventListener('pointerleave', () => { cross.style.opacity = 0; readout.textContent = def; });
  };

  const showResult = (r) => {
    const [title, sub] = verdict(r);
    qs('[data-result-title]', root).innerHTML = title;
    qs('[data-result-sub]', root).innerHTML = sub;
    renderPick(r);
    renderShare(r);
    resultBox.hidden = false;
    renderChart(r); // na het tonen, anders is de breedte 0
    if (!RUST) { resultBox.classList.remove('is-in'); void resultBox.offsetWidth; }
    resultBox.classList.add('is-in');
    setStatus('Finish');
    // Breng de aanbodkaarten in beeld, maar laat de onderkant van de baan zichtbaar.
    const pick = qs('[data-pick]', root).getBoundingClientRect();
    if (pick.bottom > window.innerHeight) {
      const target = window.scrollY + resultBox.getBoundingClientRect().top - window.innerHeight * 0.22;
      setTimeout(() => window.scrollTo({ top: target, behavior: RUST ? 'auto' : 'smooth' }), RUST ? 0 : 450);
    }
  };

  /* ---------- starten ---------- */

  const payload = () => {
    const kg = (n) => { const v = Number(qs(`[name="${n}"]`, root)?.value || 0); return v > 0 && v <= 180 ? Math.round(v) : 0; };
    const body = {
      motor_a_id: selected.A.id,
      motor_b_id: selected.B.id,
      road_type: options.road_type,
      road_condition: options.road_condition,
      // Eén rijder op beide motoren: zo vergelijk je de motoren, niet de rijders.
      rider_a_kg: kg('rider_kg'),
      rider_b_kg: kg('rider_kg'),
    };
    if (options.road_type === 'straight' || options.road_type === 'twisty') body.distance_m = options.distance_m;
    if (options.road_type === 'braking') body.speed_kmh = options.speed_kmh;
    return body;
  };

  const start = async (e) => {
    e.preventDefault();
    if (runBtn.disabled) return;
    if (selected.A.id === selected.B.id) { message('Kies twee verschillende motoren.'); return; }
    if (options.road_type === 'topspeed') {
      const missing = [selected.A, selected.B].find((m) => !m.top_speed_kmh);
      if (missing) { message(`Van de ${missing.label} is geen opgegeven topsnelheid bekend. Kies een andere race.`); return; }
    }
    busy = true; syncRun(); message('');
    scrollToRace();
    resultBox.hidden = true;
    Object.values(lanes).forEach((l) => {
      l.classList.remove('is-winner', 'is-done');
      const bike = qs('[data-bike]', l); bike.style.setProperty('--p', 0); bike.style.setProperty('--v', 0);
      qs('[data-lane-time]', l).innerHTML = '&nbsp;';
    });
    setStatus('Motoren op de grid');
    const body = payload();
    const request = fetch(cfg.routes.simulate, {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
      body: JSON.stringify(body),
    }).then(async (res) => ({ ok: res.ok, data: await res.json().catch(() => ({})) }))
      .catch(() => ({ ok: false, data: { message: 'Geen verbinding met de server. Probeer het nog eens.' } }));

    // De lichten lopen terwijl de server rekent; de race start pas als beide klaar zijn.
    const [res] = await Promise.all([request, lightsOn()]);
    if (!res.ok) {
      lightsOff();
      busy = false;
      renderLimit(res.data.limit);
      message(res.data.message || 'De simulatie kon niet starten.');
      // De melding staat boven de baan; zet hem ook in de statusregel, die is in beeld.
      setStatus(esc(res.data.message || 'De simulatie kon niet starten.'));
      syncRun();
      return;
    }
    if (!RUST) await wait(260 + Math.random() * 380); // de echte lights out heeft ook een willekeurige pauze
    lightsOff();
    setStatus('Lights out');
    last = Object.assign(res.data.result, {
      distance_ran: body.distance_m, road_type_ran: body.road_type, road_condition_ran: body.road_condition,
    });
    options.distance_m_ran = body.distance_m;
    renderLimit(res.data.limit);
    await playRace(last);
    showResult(last);
    busy = false;
    runLabel.textContent = 'Nieuwe race';
    syncRun();
  };
  form.addEventListener('submit', start);

  qs('[data-replay]', root).addEventListener('click', async () => {
    if (!last || busy) return;
    busy = true; syncRun();
    options.distance_m_ran = last.distance_ran;
    await lightsOn();
    if (!RUST) await wait(300);
    lightsOff();
    setStatus('Lights out');
    await playRace(last);
    setStatus('Finish');
    busy = false; syncRun();
  });

  /* ---------- begin ---------- */

  syncOptions();
  renderLimit(cfg.limit);
  ['A', 'B'].forEach((s) => { if (cfg.preselect?.[s]) pickMotor(s, cfg.preselect[s]); });
  if (!selected.A) setStatus('Kies twee motoren');
})();
