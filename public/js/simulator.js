// Simulatiepagina, redesign 12-10-2026. Configurator links (alle opties zichtbaar), racescherm
// rechts. Startlichten lopen terwijl de server rekent, dan de race op de echte simulatiedata, dan
// het oordeel met een oplopend verschil en de keuzekaarten met aanbod. De embed gebruikt nog
// simulation.js. Gedeelde stukken (veer-filter, race-rekenwerk) komen uit site.js.
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
  const short = (m) => m.short_label || m.label;
  const mobile = () => window.matchMedia('(max-width: 960px)').matches;

  const root = qs('[data-sim]');
  if (!root) return;
  const form = qs('[data-sim-form]', root);
  const monitor = qs('[data-race-panel]', root);
  const status = qs('[data-status]', root);
  const runBtn = qs('[data-run]', root);
  const runLabel = qs('[data-run-label]', root);
  const lanes = { a: qs('[data-lane="a"]', root), b: qs('[data-lane="b"]', root) };
  const verdictBox = qs('[data-verdict]', root);
  const offers = qs('[data-offers]');

  const DISTANCES = [100, 250, 402, 500, 805, 1000, 2000];
  const MODE = {
    straight: { label: 'Rechte lijn', hint: 'Wie is het eerst over de streep, vanuit stilstand.' },
    twisty: { label: 'Bochten', hint: 'Een weg met bochten: grip en gewicht tellen zwaarder dan pk.' },
    topspeed: { label: 'Topsnelheid', hint: 'De opgegeven topsnelheid van de fabrikant.' },
    braking: { label: 'Remweg', hint: 'Hoeveel meter je nodig hebt om stil te staan.' },
  };
  const COND = { dry: 'droog', wet: 'vochtig', rain: 'nat' };

  const selected = { A: null, B: null };
  const options = { road_type: 'straight', road_condition: 'dry', distance_m: 500, speed_kmh: 100 };
  const history = [];
  let shown = null; // de uitslag die nu in beeld is
  let busy = false;

  /* ---------- status, knop, limiet ---------- */

  const setStatus = (html) => { status.innerHTML = html; };

  const syncRun = () => {
    const ready = selected.A && selected.B;
    runBtn.disabled = busy || !ready || Boolean(cfg.limit?.blocked);
    runLabel.textContent = busy ? 'Race loopt' : !selected.A ? 'Kies motor A' : !selected.B ? 'Kies motor B' : shown ? 'Race opnieuw' : 'Start de race';
    root.classList.toggle('is-ready', Boolean(ready) && !busy);
  };

  const renderLimit = (limit) => {
    if (!limit) return;
    cfg.limit = limit;
    const el = qs('[data-limit]', root);
    qs('[data-limit-fill]', root).style.transform = `scaleX(${Math.min(1, limit.used / limit.limit)})`;
    if (limit.blocked) {
      const reset = limit.reset_at ? new Date(limit.reset_at).toLocaleString('nl-NL', { weekday: 'short', hour: '2-digit', minute: '2-digit' }) : null;
      el.innerHTML = `Daglimiet bereikt${reset ? `, weer races vanaf ${esc(reset)}` : ''}`;
    } else {
      el.innerHTML = `Nog <b>${limit.remaining}</b> van ${limit.limit} gratis races vandaag`;
    }
    qs('[data-limit-box]', root).classList.toggle('is-blocked', Boolean(limit.blocked));
    syncRun();
  };

  const message = (text) => {
    const box = qs('[data-message]', root);
    box.textContent = text || '';
    box.hidden = !text;
  };

  // Na een uitslag: elke wijziging maakt de uitslag "oud" tot er opnieuw gestart is.
  const markStale = () => {
    if (!shown || busy) return;
    monitor.classList.add('is-stale');
    qs('[data-stale]', root).hidden = false;
    setStatus('Klaar voor een nieuwe race');
    syncRun();
  };
  const clearStale = () => {
    monitor.classList.remove('is-stale');
    qs('[data-stale]', root).hidden = true;
  };

  /* ---------- motor kiezen ---------- */

  const bikeIcon = (category) => qs(`template[data-bike-icon="${category === 'sport' ? 'sport' : 'other'}"]`).innerHTML;

  const resetLane = (lane) => {
    lane.classList.remove('is-winner', 'is-done');
    const bike = qs('[data-bike]', lane);
    bike.style.setProperty('--p', 0);
    bike.style.setProperty('--v', 0);
    qs('[data-lane-time]', lane).innerHTML = '&nbsp;';
  };

  const paintSide = (side) => {
    const motor = selected[side];
    const field = qs(`[data-pickfield="${side}"]`, root);
    const lane = lanes[side.toLowerCase()];
    field.classList.toggle('is-set', Boolean(motor));
    qs(`[data-clear="${side}"]`, root).hidden = !motor;
    lane.classList.toggle('is-empty', !motor);
    resetLane(lane);
    if (!motor) {
      qs(`[data-specs="${side}"]`, root).innerHTML = '';
      qs('[data-lane-name]', lane).textContent = `Motor ${side}`;
      qs('[data-bike]', lane).innerHTML = bikeIcon(null);
      return;
    }
    qs(`[data-motor-input="${side}"]`, root).value = motor.label;
    qs(`[data-specs="${side}"]`, root).innerHTML = `<b>${motor.power_hp}</b> pk <i></i> <b>${motor.weight_kg}</b> kg <i></i> <b>${comma(motor.power_hp / Math.max(motor.weight_kg, 1))}</b> pk/kg${motor.category_label ? ` <i></i> ${esc(motor.category_label)}` : ''}${motor.a2 ? ' <span class="a2">A2</span>' : ''}`;
    qs('[data-lane-name]', lane).textContent = short(motor);
    const bike = qs('[data-bike]', lane);
    bike.innerHTML = bikeIcon(motor.category);
    if (!RUST) { bike.classList.remove('is-rolling'); void bike.offsetWidth; bike.classList.add('is-rolling'); }
  };

  const afterPick = () => {
    qs('[data-empty]', root).hidden = Boolean(selected.A && selected.B) || Boolean(shown);
    if (!shown) setStatus(selected.A && selected.B ? 'Klaar voor de start' : `Kies motor ${selected.A ? 'B' : 'A'}`);
    markStale();
    syncRun();
  };

  const closeList = (side) => {
    qs(`[data-suggestions="${side}"]`, root).hidden = true;
    qs(`[data-motor-input="${side}"]`, root).setAttribute('aria-expanded', 'false');
  };

  const pickMotor = (side, motor) => {
    selected[side] = motor;
    closeList(side);
    paintSide(side);
    afterPick();
  };

  // Het getypte deel vet in de suggestie (componentregels: autocomplete).
  const mark = (label, query) => {
    const q = query.trim();
    const i = label.toLowerCase().indexOf(q.toLowerCase());
    if (i < 0 || !q) return esc(label);
    return `${esc(label.slice(0, i))}<b>${esc(label.slice(i, i + q.length))}</b>${esc(label.slice(i + q.length))}`;
  };

  const search = async (side, query) => {
    const list = qs(`[data-suggestions="${side}"]`, root);
    const input = qs(`[data-motor-input="${side}"]`, root);
    if (query.trim().length < 2) { closeList(side); return; }
    if (list.hidden) { list.innerHTML = '<div class="mfield__skel"><i></i><i></i><i></i></div>'; list.hidden = false; }
    let motors = [];
    try {
      const res = await fetch(`${cfg.routes.motors}?q=${encodeURIComponent(query)}`, { headers: { Accept: 'application/json' } });
      motors = (await res.json()).motors || [];
    } catch (e) { motors = []; }
    if (input.value !== query) return;
    list.innerHTML = motors.map((m, i) => `
      <button type="button" role="option" class="mfield__opt" data-i="${i}" style="--i:${i}">
        <span>${mark(m.label, query)}</span><small>${m.power_hp} pk · ${m.weight_kg} kg</small>
      </button>`).join('') + `
      <button type="button" role="option" class="mfield__opt mfield__opt--ai" data-ai>
        <span>${motors.length ? 'Niet de goede?' : 'Niet gevonden.'} Zoek &ldquo;${esc(query.trim())}&rdquo; op met AI</span>
      </button>`;
    qsa('[data-i]', list).forEach((b) => b.addEventListener('click', () => pickMotor(side, motors[Number(b.dataset.i)])));
    qs('[data-ai]', list).addEventListener('click', () => lookupAi(side));
    list.hidden = false;
    input.setAttribute('aria-expanded', 'true');
  };

  const lookupAi = async (side) => {
    const query = qs(`[data-motor-input="${side}"]`, root).value.trim();
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
      if (res.status === 401) { message('Opzoeken met AI kan als je bent ingelogd. Je kunt de specs ook hieronder zelf invullen.'); showManual(side, query); return; }
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
    const box = qs(`[data-manual-form="${side}"]`, wrap);
    const year = (query.match(/\b(19[5-9]\d|20\d{2})\b/) || [''])[0];
    const rest = query.replace(year, '').trim().split(/\s+/);
    const set = (f, v) => { const el = qs(`[data-manual="${f}"]`, box); if (el && !el.value) el.value = v; };
    set('brand', rest.shift() || ''); set('model', rest.join(' ')); set('year', year);
    wrap.hidden = false;
    box.hidden = false;
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
    const list = qs(`[data-suggestions="${side}"]`, root);
    input.addEventListener('input', debounce((e) => search(side, e.target.value)));
    input.addEventListener('input', () => {
      // Typen na een keuze maakt de keuze ongedaan, anders race je met iets anders dan er staat.
      if (selected[side] && input.value !== selected[side].label) {
        selected[side] = null;
        paintSide(side);
        afterPick();
      }
    });
    input.addEventListener('focus', () => { if (selected[side]) input.select(); });
    input.addEventListener('keydown', (e) => {
      const opts = qsa('.mfield__opt', list);
      if (e.key === 'Enter') {
        e.preventDefault();
        if (!list.hidden && opts[0] && !opts[0].dataset.ai) opts[0].click();
        else if (!runBtn.disabled) form.requestSubmit();
        return;
      }
      if (e.key === 'ArrowDown' && !list.hidden && opts.length) { e.preventDefault(); opts[0].focus(); }
      if (e.key === 'Escape') closeList(side);
    });
    list.addEventListener('keydown', (e) => {
      const opts = qsa('.mfield__opt', list);
      const i = opts.indexOf(document.activeElement);
      if (e.key === 'ArrowDown') { e.preventDefault(); opts[Math.min(i + 1, opts.length - 1)].focus(); }
      if (e.key === 'ArrowUp') { e.preventDefault(); (i <= 0 ? input : opts[i - 1]).focus(); }
      if (e.key === 'Escape') { closeList(side); input.focus(); }
    });
    qs(`[data-clear="${side}"]`, root).addEventListener('click', () => {
      selected[side] = null;
      input.value = '';
      paintSide(side);
      afterPick();
      input.focus();
    });
    qs(`[data-manual-submit="${side}"]`, root)?.addEventListener('click', () => submitManual(side));
  });
  document.addEventListener('click', (e) => {
    if (!e.target.closest('[data-pickfield]')) ['A', 'B'].forEach(closeList);
  });

  qs('[data-swap]', root).addEventListener('click', (e) => {
    [selected.A, selected.B] = [selected.B, selected.A];
    ['A', 'B'].forEach((s) => { if (!selected[s]) qs(`[data-motor-input="${s}"]`, root).value = ''; paintSide(s); });
    const btn = e.currentTarget;
    btn.classList.remove('is-spun'); void btn.offsetWidth; btn.classList.add('is-spun');
    afterPick();
  });

  /* ---------- opties ---------- */

  const filters = [];
  const syncOptions = () => {
    const mode = options.road_type;
    qsa('[data-when="condition"]', root).forEach((el) => { el.hidden = mode === 'topspeed'; });
    qsa('[data-when="distance"]', root).forEach((el) => { el.hidden = !(mode === 'straight' || mode === 'twisty'); });
    qsa('[data-when="speed"]', root).forEach((el) => { el.hidden = mode !== 'braking'; });
    const hint = qs('[data-mode-hint]', root);
    hint.textContent = MODE[mode].hint;
    if (!RUST) { hint.classList.remove('is-new'); void hint.offsetWidth; hint.classList.add('is-new'); }
    // Een filter die verborgen was heeft geen maat: opnieuw meten zodra hij zichtbaar is.
    requestAnimationFrame(() => filters.forEach((f) => f && f.reset && f.reset()));
  };
  qsa('.filter[data-option]', root).forEach((f) => {
    if (!RR.bindFilter) return;
    filters.push(RR.bindFilter(f, (value) => {
      const key = f.dataset.option;
      options[key] = key === 'speed_kmh' ? Number(value) : value;
      if (key === 'road_type') syncOptions();
      markStale();
    }));
  });

  const range = qs('[data-distance]', root);
  const out = qs('[data-distance-out]', root);
  const fmtDist = (d) => (d >= 1000 ? `${comma(d / 1000, d % 1000 ? 1 : 0)} km` : `${d} m`);
  const syncRange = () => {
    const d = DISTANCES[Number(range.value)];
    options.distance_m = d;
    out.textContent = fmtDist(d);
    range.setAttribute('aria-valuetext', d >= 1000 ? `${d / 1000} kilometer` : `${d} meter`);
    range.style.setProperty('--fill', `${(Number(range.value) / 6) * 100}%`);
    qsa('.range__ticks span', root).forEach((t, i) => t.classList.toggle('is-on', i === Number(range.value)));
  };
  range.addEventListener('input', () => {
    syncRange();
    if (!RUST) { out.classList.remove('is-pop'); void out.offsetWidth; out.classList.add('is-pop'); }
    markStale();
  });
  qsa('.range__ticks span', root).forEach((t, i) => t.addEventListener('click', () => { range.value = i; range.dispatchEvent(new Event('input')); }));
  qs('[name="rider_kg"]', root).addEventListener('input', markStale);

  /* ---------- race ---------- */

  const niceMax = (v) => {
    if (v <= 0) return 10;
    const m = 10 ** Math.floor(Math.log10(v)), r = v / m;
    return (r <= 1 ? 1 : r <= 2 ? 2 : r <= 2.5 ? 2.5 : r <= 5 ? 5 : 10) * m;
  };

  const setMarks = (max, unit) => {
    const km = unit === 'm' && max >= 1000;
    qs('[data-marks]', root).innerHTML = [0, 1, 2, 3, 4, 5].map((i) => {
      const v = (max / 5) * i;
      const txt = km ? comma(v / 1000, v % 1000 ? 1 : 0) : String(Math.round(v));
      return `<span>${txt}${i === 5 ? (km ? ' km' : ` ${unit}`) : ''}</span>`;
    }).join('');
  };

  // Per soort race: een functie van speeltijd (s) naar { x (0..1), v (km/u), vn (0..1) }.
  const plan = (r) => {
    if (r.mode === 'race') {
      const d = r.run.distance_m;
      const sa = r.samples?.a || [], sb = r.samples?.b || [];
      const sides = { a: RR.raceTrace({ t: r.time_a_s, s: sa }, d), b: RR.raceTrace({ t: r.time_b_s, s: sb }, d) };
      const vMax = Math.max(...sa.map((p) => p.v), ...sb.map((p) => p.v), 1);
      const scale = Math.min(r.time_a_s, r.time_b_s) / 3.4; // de snelste ongeveer 3,4 s in beeld
      const at = (s, t) => { const p = RR.raceAt(sides[s], t * scale); return { x: p.x / d, v: p.v, vn: p.v / vMax }; };
      return { marks: [d, 'm'], end: { a: r.time_a_s / scale, b: r.time_b_s / scale }, at, final: { a: `${comma(r.time_a_s)} s`, b: `${comma(r.time_b_s)} s` } };
    }
    if (r.mode === 'topspeed') {
      const top = { a: r.top_speed_kmh_a, b: r.top_speed_kmh_b };
      const hi = Math.max(top.a, top.b);
      const max = niceMax(hi * 1.04);
      const T = { a: 3 * top.a / hi, b: 3 * top.b / hi };
      const at = (s, t) => { const f = Math.min(t / T[s], 1); const v = top[s] * (1 - (1 - f) ** 3); return { x: v / max, v, vn: 1 - f }; };
      return { marks: [max, 'km/u'], end: T, at, final: { a: `${top.a} km/u`, b: `${top.b} km/u` } };
    }
    const dist = { a: r.braking_distance_m_a, b: r.braking_distance_m_b };
    const hi = Math.max(dist.a, dist.b);
    const max = niceMax(hi * 1.08);
    const T = { a: 2.6 * dist.a / hi, b: 2.6 * dist.b / hi };
    const at = (s, t) => { const f = Math.min(t / T[s], 1); return { x: (dist[s] * (1 - (1 - f) ** 2)) / max, v: r.run.speed_kmh * (1 - f), vn: 1 - f }; };
    return { marks: [max, 'm'], end: T, at, final: { a: `${comma(dist.a, 1)} m`, b: `${comma(dist.b, 1)} m` } };
  };

  let frame = 0;
  const playRace = (r) => new Promise((resolve) => {
    cancelAnimationFrame(frame);
    const p = plan(r);
    setMarks(...p.marks);
    const winner = r.winner.toLowerCase();
    Object.values(lanes).forEach(resetLane);
    monitor.classList.toggle('is-brake', r.mode === 'braking');
    monitor.classList.add('is-racing');
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
    const finish = () => { draw(end); lanes[winner].classList.add('is-winner'); monitor.classList.remove('is-racing'); resolve(); };
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
    const lights = qsa('.lights i', monitor);
    lights.forEach((l) => l.classList.remove('on'));
    if (RUST) return;
    for (const l of lights) { l.classList.add('on'); await wait(230); }
  };
  const lightsOff = () => qsa('.lights i', monitor).forEach((l) => l.classList.remove('on'));

  const scrollToMonitor = () => {
    const top = monitor.getBoundingClientRect().top;
    const header = qs('.header')?.offsetHeight || 64;
    if (top < header || top + monitor.offsetHeight * 0.6 > window.innerHeight) {
      window.scrollTo({ top: window.scrollY + top - header - 12, behavior: RUST ? 'auto' : 'smooth' });
    }
  };

  /* ---------- uitslag ---------- */

  const describe = (r) => {
    const run = r.run;
    if (r.mode === 'race') return `${MODE[run.road_type].label}, ${fmtDist(run.distance_m)}, ${COND[run.road_condition]}`;
    if (r.mode === 'topspeed') return 'Topsnelheid';
    return `Remweg vanaf ${run.speed_kmh} km/u, ${COND[run.road_condition]}`;
  };

  const countUp = (el, to, decimals) => {
    if (RUST) { el.textContent = comma(to, decimals); return; }
    const t0 = performance.now(), dur = 900;
    const tick = (now) => {
      const f = Math.min((now - t0) / dur, 1);
      el.textContent = comma(to * (1 - (1 - f) ** 3), decimals);
      if (f < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };

  const showVerdict = (r) => {
    const w = r.winner === 'A' ? r.motors.A : r.motors.B;
    let num, dec, unit, sub;
    if (r.mode === 'race') {
      num = r.delta_s; dec = 2; unit = 's sneller';
      sub = `${short(r.motors.A)} ${comma(r.time_a_s)} s, ${short(r.motors.B)} ${comma(r.time_b_s)} s`;
    } else if (r.mode === 'topspeed') {
      num = Math.abs(r.top_speed_kmh_a - r.top_speed_kmh_b); dec = 0; unit = 'km/u harder';
      sub = `${short(r.motors.A)} ${r.top_speed_kmh_a} km/u, ${short(r.motors.B)} ${r.top_speed_kmh_b} km/u`;
    } else {
      num = Math.abs(r.braking_distance_m_a - r.braking_distance_m_b); dec = 1; unit = 'm eerder stil';
      sub = `${short(r.motors.A)} ${comma(r.braking_distance_m_a, 1)} m, ${short(r.motors.B)} ${comma(r.braking_distance_m_b, 1)} m`;
    }
    qs('[data-verdict-label]', root).textContent = `P1 · ${describe(r)}`;
    qs('[data-verdict-name]', root).textContent = short(w);
    qs('[data-verdict-unit]', root).textContent = unit;
    qs('[data-verdict-sub]', root).textContent = sub;
    verdictBox.hidden = false;
    qs('[data-empty]', root).hidden = true;
    if (!RUST) { verdictBox.classList.remove('is-in'); void verdictBox.offsetWidth; }
    verdictBox.classList.add('is-in');
    countUp(qs('[data-verdict-num]', root), num, dec);
    setStatus('Finish');
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

  const renderShare = (r) => {
    const box = qs('[data-share]', offers);
    box.hidden = !r.share_url;
    if (!r.share_url) return;
    const w = r.winner === 'A' ? r.motors.A : r.motors.B;
    const line = `${short(w)} wint met ${comma(r.delta_s)} s verschil op RevRace`;
    qs('[data-share-copy]', box).dataset.url = r.share_url;
    qs('[data-share-social="whatsapp"]', box).href = `https://wa.me/?text=${encodeURIComponent(`${line} ${r.share_url}`)}`;
    qs('[data-share-social="x"]', box).href = `https://twitter.com/intent/tweet?text=${encodeURIComponent(line)}&url=${encodeURIComponent(r.share_url)}`;
    qs('[data-share-social="facebook"]', box).href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(r.share_url)}`;
  };

  const renderOffers = (r) => {
    const winner = r.winner.toLowerCase();
    const sides = [['a', r.motors.A, r.motors.B, r.offer_url_a], ['b', r.motors.B, r.motors.A, r.offer_url_b]];
    if (winner === 'b') sides.reverse();
    qs('[data-pick]', offers).innerHTML = sides.map(([s, m, o, offer], i) => {
      const plus = strengths(m, o, s === winner);
      return `
      <article class="pick-card pick-card--${s}${s === winner ? ' is-p1' : ''}" style="--i:${i}">
        <p class="pick-card__cat"><span class="pick-card__pos">P${i + 1}</span>${esc(m.category_label || '')}${m.a2 ? '<span class="a2">A2</span>' : ''}</p>
        <h3><a href="${esc(m.url)}">${esc(m.label)}</a></h3>
        <p class="pick-card__specs"><span><b>${m.power_hp}</b> pk</span><span><b>${m.weight_kg}</b> kg</span><span><b>${comma(m.power_hp / Math.max(m.weight_kg, 1))}</b> pk/kg</span></p>
        ${plus.length ? `<ul class="pick-card__plus">${plus.map((x) => `<li>${esc(x)}</li>`).join('')}</ul>` : ''}
        <div class="pick-card__acts">
          <a class="btn ${s === winner ? 'btn--red' : 'btn--ghost'}" href="${esc(offer)}" target="_blank" rel="nofollow noopener">Bekijk aanbod op Marktplaats</a>
          ${garageBtn(m)}
        </div>
      </article>`;
    }).join('');
    const compare = qs('[data-compare]', offers);
    compare.hidden = !r.compare_url;
    compare.href = r.compare_url || '#';
    renderShare(r);
    offers.hidden = false;
    if (!RUST) { offers.classList.remove('is-in'); void offers.offsetWidth; }
    offers.classList.add('is-in');
    const chart = qs('[data-chart-wrap]', offers);
    chart.hidden = r.mode !== 'race';
    if (chart.open) renderChart(r);
  };
  qs('[data-share-copy]', offers).addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    try { await navigator.clipboard.writeText(btn.dataset.url); btn.textContent = 'Link gekopieerd'; } catch (err) { btn.textContent = btn.dataset.url; }
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
    if (!r || r.mode !== 'race') return;
    const wrap = qs('[data-chart-wrap]', offers);
    const sa = r.samples?.a || [], sb = r.samples?.b || [];
    const svg = qs('[data-chart-svg]', wrap), readout = qs('[data-chart-readout]', wrap);
    const W = Math.max(300, Math.round(svg.getBoundingClientRect().width) || 640), H = 220, P = { l: 40, r: 14, t: 12, b: 26 };
    const pw = W - P.l - P.r, ph = H - P.t - P.b;
    const xMax = r.run.distance_m, yMax = niceMax(Math.max(...sa.map((p) => p.v), ...sb.map((p) => p.v), 1));
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
      const t = svgEl('text', { x: mx(v), y: H - 6, class: 'chart-axis-label', 'text-anchor': i === 5 ? 'end' : 'middle' }); t.textContent = `${Math.round(v)} m`; svg.appendChild(t);
    }
    const names = { a: short(r.motors.A), b: short(r.motors.B) };
    [['a', sa, 'var(--redline)'], ['b', sb, 'var(--midrange)']].forEach(([s, pts, c]) => {
      qs(`[data-legend="${s}"]`, wrap).textContent = names[s];
      if (!pts.length) return;
      svg.appendChild(svgEl('path', { d: pts.map((p, i) => `${i ? 'L' : 'M'}${mx(p.x).toFixed(1)},${my(p.v).toFixed(1)}`).join(' '), fill: 'none', stroke: c, 'stroke-width': 2.2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round', pathLength: 1, class: 'simx-line' }));
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
  qs('[data-chart-wrap]', offers).addEventListener('toggle', (e) => { if (e.target.open) renderChart(shown); });

  /* ---------- eerdere races ---------- */

  const renderHistory = () => {
    const box = qs('[data-history]', root);
    box.hidden = history.length < 2;
    qs('[data-history-list]', box).innerHTML = history.map((r, i) => {
      const w = r.winner === 'A' ? r.motors.A : r.motors.B;
      const cur = r === shown;
      return `<li><button type="button" data-h="${i}"${cur ? ' class="is-current" aria-current="true"' : ''}>
        <span class="h-duel">${esc(short(r.motors.A))} <i>vs</i> ${esc(short(r.motors.B))}</span>
        <span class="h-meta">${esc(describe(r))} · P1 ${esc(short(w))}</span></button></li>`;
    }).join('');
    qsa('[data-h]', box).forEach((b) => b.addEventListener('click', () => reopen(history[Number(b.dataset.h)])));
  };

  // Een eerdere uitslag terug in beeld, zonder nieuwe simulatie (telt niet mee voor de limiet).
  const reopen = async (r) => {
    if (busy || !r) return;
    shown = r;
    selected.A = r.motors.A; selected.B = r.motors.B;
    ['A', 'B'].forEach(paintSide);
    clearStale();
    busy = true; syncRun();
    verdictBox.hidden = true;
    renderHistory();
    scrollToMonitor();
    await playRace(r);
    busy = false;
    showVerdict(r);
    renderOffers(r);
    syncRun();
  };

  /* ---------- starten ---------- */

  const payload = () => {
    const raw = Number(qs('[name="rider_kg"]', root)?.value || 0);
    const kg = raw > 0 && raw <= 180 ? Math.round(raw) : 0;
    const body = {
      motor_a_id: selected.A.id,
      motor_b_id: selected.B.id,
      road_type: options.road_type,
      road_condition: options.road_condition,
      rider_a_kg: kg,
      rider_b_kg: kg,
    };
    if (options.road_type === 'straight' || options.road_type === 'twisty') body.distance_m = options.distance_m;
    if (options.road_type === 'braking') body.speed_kmh = options.speed_kmh;
    return body;
  };

  const start = async (e) => {
    e?.preventDefault();
    if (runBtn.disabled) return;
    if (selected.A.id === selected.B.id) { message('Kies twee verschillende motoren.'); return; }
    if (options.road_type === 'topspeed') {
      const missing = [selected.A, selected.B].find((m) => !m.top_speed_kmh);
      if (missing) { message(`Van de ${missing.label} is geen opgegeven topsnelheid bekend. Kies een andere soort race.`); return; }
    }
    busy = true; message(''); clearStale(); syncRun();
    verdictBox.hidden = true;
    qs('[data-empty]', root).hidden = true;
    if (mobile()) scrollToMonitor();
    Object.values(lanes).forEach(resetLane);
    setStatus('Motoren op de grid');
    const body = payload();
    const motors = { A: selected.A, B: selected.B };
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
      setStatus('Niet gestart');
      if (shown) verdictBox.hidden = false;
      syncRun();
      return;
    }
    if (!RUST) await wait(240 + Math.random() * 420); // net als echt: een korte, willekeurige pauze
    lightsOff();
    setStatus('Lights out');
    const r = Object.assign(res.data.result, { run: body, motors });
    shown = r;
    history.unshift(r);
    if (history.length > 6) history.pop();
    renderLimit(res.data.limit);
    await playRace(r);
    busy = false;
    showVerdict(r);
    renderOffers(r);
    renderHistory();
    syncRun();
  };
  form.addEventListener('submit', start);

  qs('[data-replay]', root).addEventListener('click', async () => {
    if (!shown || busy) return;
    busy = true; syncRun();
    verdictBox.hidden = true;
    await lightsOn();
    if (!RUST) await wait(300);
    lightsOff();
    setStatus('Lights out');
    await playRace(shown);
    busy = false;
    showVerdict(shown);
    syncRun();
  });

  qs('[data-to-offers]', root).addEventListener('click', (e) => {
    e.preventDefault();
    offers.scrollIntoView({ behavior: RUST ? 'auto' : 'smooth', block: 'start' });
  });

  qsa('[data-duel]', root).forEach((b) => b.addEventListener('click', () => {
    const duel = cfg.duels?.[Number(b.dataset.duel)];
    if (!duel || busy) return;
    pickMotor('A', duel.a);
    pickMotor('B', duel.b);
    start();
  }));

  /* ---------- begin ---------- */

  syncRange();
  syncOptions();
  renderLimit(cfg.limit);
  ['A', 'B'].forEach((s) => { if (cfg.preselect?.[s]) { selected[s] = cfg.preselect[s]; paintSide(s); } });
  afterPick();
})();
