const cfg = window.REVRACE || {};

function qs(selector, root = document) {
  return root.querySelector(selector);
}

function qsa(selector, root = document) {
  return Array.from(root.querySelectorAll(selector));
}

function csrf() {
  return qs('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function debounce(fn, wait = 180) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), wait);
  };
}

function setText(selector, value) {
  const el = qs(selector);
  if (el) el.textContent = value;
}

function renderLimit(limit) {
  if (!limit) return;

  setText('[data-limit-used]', limit.used);
  setText('[data-limit-total]', limit.limit);
  setText('[data-limit-remaining]', limit.remaining);

  const fill = qs('[data-limit-fill]');
  if (fill) fill.style.width = `${Math.min(100, (limit.used / limit.limit) * 100)}%`;

  const box = qs('[data-limit-box]');
  const run = qs('[data-run]');
  if (box) box.classList.toggle('blocked', Boolean(limit.blocked));
  if (run) run.disabled = Boolean(limit.blocked) || !selected.A || !selected.B;

  if (limit.blocked && limit.reset_at) {
    const date = new Date(limit.reset_at);
    setText('[data-limit-reset]', date.toLocaleString('nl-NL'));
  }
}

const selected = { A: null, B: null };
const options = {
  road_type: 'straight',
  road_condition: 'dry',
  distance_m: 500,
};

function motorSpec(motor) {
  const ratio = (motor.power_hp / motor.weight_kg).toFixed(2);

  return `
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
      <div><div class="metric-value" style="font-size:24px">${motor.power_hp}</div><div class="metric-label">pk</div></div>
      <div><div class="metric-value" style="font-size:24px">${motor.weight_kg}</div><div class="metric-label">kg</div></div>
      <div><div class="metric-value" style="font-size:24px">${ratio}</div><div class="metric-label">pk/kg</div></div>
    </div>
  `;
}

function pickMotor(side, motor) {
  selected[side] = motor;
  qs(`[data-motor-id="${side}"]`).value = motor.id;
  qs(`[data-motor-input="${side}"]`).value = motor.label;
  qs(`[data-suggestions="${side}"]`).classList.remove('show');
  qs(`[data-specs="${side}"]`).innerHTML = motorSpec(motor);
  setText(`[data-lane-name="${side}"]`, motor.label);

  const run = qs('[data-run]');
  if (run) run.disabled = !selected.A || !selected.B || Boolean(cfg.limit?.blocked);
}

async function searchMotors(side, query) {
  const list = qs(`[data-suggestions="${side}"]`);
  if (!list) return;

  if (query.trim().length < 2) {
    list.classList.remove('show');
    return;
  }

  const url = `${cfg.routes.motors}?q=${encodeURIComponent(query)}`;
  const response = await fetch(url, { headers: { Accept: 'application/json' } });
  const data = await response.json();

  list.innerHTML = '';
  data.motors.forEach((motor) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'suggestion';
    btn.textContent = motor.label;
    btn.addEventListener('click', () => pickMotor(side, motor));
    list.appendChild(btn);
  });
  list.classList.toggle('show', data.motors.length > 0);
}

function guessMotorParts(query) {
  const yearMatch = query.match(/\b(19[5-9]\d|20\d{2})\b/);
  const year = yearMatch ? yearMatch[0] : '';
  const rest = query.replace(year, '').trim().split(/\s+/);
  const brand = rest.shift() || '';
  const model = rest.join(' ');

  return { brand, model, year };
}

function showManualForm(side, query) {
  const form = qs(`[data-manual-form="${side}"]`);
  if (!form) return;

  const guess = guessMotorParts(query);
  const setValue = (field, value) => {
    const field_el = qs(`[data-manual="${field}"]`, form);
    if (field_el && !field_el.value) field_el.value = value;
  };
  setValue('brand', guess.brand);
  setValue('model', guess.model);
  setValue('year', guess.year);

  form.hidden = false;
}

async function lookupWithAi(side) {
  const input = qs(`[data-motor-input="${side}"]`);
  const button = qs(`[data-lookup-ai="${side}"]`);
  const query = input?.value.trim();

  if (!query || query.length < 3) {
    showMessage('Vul eerst merk, model en bouwjaar in, dan kan de AI ernaar zoeken.', 'errors');
    return;
  }

  const originalText = button.textContent;
  button.disabled = true;
  button.textContent = 'Zoeken met AI...';

  try {
    const response = await fetch(cfg.routes.lookup, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf(),
      },
      body: JSON.stringify({ query }),
    });

    const data = await response.json();

    if (!response.ok) {
      showMessage(data.message || 'Kon geen betrouwbare specs vinden voor deze motor. Vul ze hieronder zelf in.', 'errors');
      showManualForm(side, query);
      return;
    }

    pickMotor(side, data.motor);
    showMessage(`${data.motor.label} gevonden en opgeslagen voor volgende keer.`);
  } catch (error) {
    showMessage('Er ging iets mis bij het zoeken met AI. Probeer het nog eens.', 'errors');
  } finally {
    button.disabled = false;
    button.textContent = originalText;
  }
}

async function submitManualMotor(side) {
  const form = qs(`[data-manual-form="${side}"]`);
  const button = qs(`[data-manual-submit="${side}"]`);
  if (!form) return;

  const fields = ['brand', 'model', 'year', 'engine_type', 'power_hp', 'torque_nm', 'weight_kg', 'displacement_cc'];
  const values = {};
  let missing = false;

  fields.forEach((field) => {
    const el = qs(`[data-manual="${field}"]`, form);
    const value = el?.value.trim();
    if (!value) missing = true;
    values[field] = ['year', 'power_hp', 'torque_nm', 'weight_kg', 'displacement_cc'].includes(field) ? Number(value) : value;
  });

  if (missing) {
    showMessage('Vul alle velden in voordat je opslaat.', 'errors');
    return;
  }

  const originalText = button.textContent;
  button.disabled = true;
  button.textContent = 'Opslaan...';

  try {
    const response = await fetch(cfg.routes.manual, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf(),
      },
      body: JSON.stringify(values),
    });

    const data = await response.json();

    if (!response.ok) {
      showMessage(data.message || 'Kon deze specs niet opslaan, check de ingevulde waarden.', 'errors');
      return;
    }

    pickMotor(side, data.motor);
    form.hidden = true;
    showMessage(`${data.motor.label} opgeslagen en geselecteerd.`);
  } catch (error) {
    showMessage('Er ging iets mis bij het opslaan. Probeer het nog eens.', 'errors');
  } finally {
    button.disabled = false;
    button.textContent = originalText;
  }
}

function bindPickers() {
  ['A', 'B'].forEach((side) => {
    const input = qs(`[data-motor-input="${side}"]`);
    if (!input) return;
    input.addEventListener('input', debounce((event) => searchMotors(side, event.target.value)));

    const lookupButton = qs(`[data-lookup-ai="${side}"]`);
    lookupButton?.addEventListener('click', () => lookupWithAi(side));

    const manualButton = qs(`[data-manual-submit="${side}"]`);
    manualButton?.addEventListener('click', () => submitManualMotor(side));
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('.suggest-wrap')) {
      qsa('.suggestions').forEach((el) => el.classList.remove('show'));
    }
  });
}

function bindChoices() {
  qsa('[data-choice]').forEach((button) => {
    button.addEventListener('click', () => {
      const group = button.dataset.group;
      const value = button.dataset.value;
      qsa(`[data-choice][data-group="${group}"]`).forEach((el) => el.classList.remove('active'));
      button.classList.add('active');
      options[group] = group === 'distance_m' ? Number(value) : value;
    });
  });
}

function payload() {
  const useProfile = qs('[data-use-profile]')?.checked;
  const riderA = useProfile ? Number(qs('[name="rider_a_kg"]')?.value || 0) : 0;
  const riderB = useProfile ? Number(qs('[name="rider_b_kg"]')?.value || riderA) : 0;

  return {
    motor_a_id: Number(qs('[data-motor-id="A"]').value),
    motor_b_id: Number(qs('[data-motor-id="B"]').value),
    road_type: options.road_type,
    road_condition: options.road_condition,
    distance_m: options.distance_m,
    rider_a_kg: riderA,
    rider_b_kg: riderB,
  };
}

function showMessage(text, type = 'notice') {
  const box = qs('[data-message]');
  if (!box) return;
  box.className = type;
  box.textContent = text;
  box.hidden = false;
}

function renderResult(result) {
  const panel = qs('[data-result]');
  if (!panel) return;

  const winnerName = result.winner === 'A' ? result.motor_a : result.motor_b;
  setText('[data-result-title]', `${winnerName} wint met ${result.delta_s.toFixed(3)}s verschil`);
  setText('[data-time-a]', `${result.time_a_s.toFixed(3)}s`);
  setText('[data-time-b]', `${result.time_b_s.toFixed(3)}s`);

  const slowest = Math.max(result.time_a_s, result.time_b_s);
  qs('[data-bar-a]').style.width = `${(result.time_a_s / slowest) * 100}%`;
  qs('[data-bar-b]').style.width = `${(result.time_b_s / slowest) * 100}%`;

  const share = qs('[data-share]');
  if (share) {
    share.href = result.share_url;
    share.textContent = result.share_url;
  }

  const shareCopy = qs('[data-share-copy]');
  if (shareCopy) shareCopy.dataset.shareUrl = result.share_url;

  const searchA = qs('[data-search-online="A"]');
  if (searchA) searchA.href = `https://www.google.com/search?q=${encodeURIComponent(result.motor_a)}`;
  const searchB = qs('[data-search-online="B"]');
  if (searchB) searchB.href = `https://www.google.com/search?q=${encodeURIComponent(result.motor_b)}`;

  const shareText = `${winnerName} wint met ${result.delta_s.toFixed(2)}s verschil op RevRace!`;
  const whatsapp = qs('[data-share-social="whatsapp"]');
  if (whatsapp) whatsapp.href = `https://wa.me/?text=${encodeURIComponent(`${shareText} ${result.share_url}`)}`;
  const x = qs('[data-share-social="x"]');
  if (x) x.href = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(result.share_url)}`;
  const facebook = qs('[data-share-social="facebook"]');
  if (facebook) facebook.href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(result.share_url)}`;

  renderChart(result);
  panel.classList.add('show');
}

const CHART = { W: 640, H: 220, PAD: { left: 40, right: 12, top: 12, bottom: 26 } };

function niceMax(value) {
  if (value <= 0) return 10;
  const magnitude = 10 ** Math.floor(Math.log10(value));
  const residual = value / magnitude;
  const step = residual <= 1 ? 1 : residual <= 2 ? 2 : residual <= 5 ? 5 : 10;
  return step * magnitude;
}

function svgEl(tag, attrs = {}) {
  const el = document.createElementNS('http://www.w3.org/2000/svg', tag);
  Object.entries(attrs).forEach(([key, value]) => el.setAttribute(key, value));
  return el;
}

function interpolateSpeed(samples, x) {
  if (!samples.length) return 0;
  if (x <= samples[0].x) return samples[0].v;
  for (let i = 0; i < samples.length - 1; i++) {
    if (samples[i].x <= x && samples[i + 1].x >= x) {
      const span = samples[i + 1].x - samples[i].x;
      const t = span === 0 ? 0 : (x - samples[i].x) / span;
      return samples[i].v + t * (samples[i + 1].v - samples[i].v);
    }
  }
  return samples[samples.length - 1].v;
}

function renderChart(result) {
  const svg = qs('[data-chart-svg]');
  const readout = qs('[data-chart-readout]');
  if (!svg) return;

  const samplesA = result.samples?.a || [];
  const samplesB = result.samples?.b || [];
  if (!samplesA.length && !samplesB.length) return;

  const { W, H, PAD } = CHART;
  const plotW = W - PAD.left - PAD.right;
  const plotH = H - PAD.top - PAD.bottom;

  const xMax = Math.max(samplesA[samplesA.length - 1]?.x || 0, samplesB[samplesB.length - 1]?.x || 0) || 1;
  const yValues = [...samplesA, ...samplesB].map((point) => point.v);
  const yMax = niceMax(Math.max(...yValues, 1));

  const mapX = (x) => PAD.left + (x / xMax) * plotW;
  const mapY = (v) => PAD.top + plotH - (v / yMax) * plotH;

  svg.textContent = '';
  svg.setAttribute('viewBox', `0 0 ${W} ${H}`);

  const ySteps = 4;
  for (let i = 0; i <= ySteps; i++) {
    const value = (yMax / ySteps) * i;
    const y = mapY(value);
    svg.appendChild(svgEl('line', { x1: PAD.left, x2: W - PAD.right, y1: y, y2: y, class: 'chart-grid' }));
    const label = svgEl('text', { x: PAD.left - 8, y: y + 3, class: 'chart-axis-label', 'text-anchor': 'end' });
    label.textContent = String(Math.round(value));
    svg.appendChild(label);
  }

  const xSteps = 5;
  for (let i = 0; i <= xSteps; i++) {
    const value = (xMax / xSteps) * i;
    const label = svgEl('text', { x: mapX(value), y: H - 6, class: 'chart-axis-label', 'text-anchor': 'middle' });
    label.textContent = `${Math.round(value)}m`;
    svg.appendChild(label);
  }

  const buildPath = (samples) => samples
    .map((point, i) => `${i === 0 ? 'M' : 'L'}${mapX(point.x).toFixed(1)},${mapY(point.v).toFixed(1)}`)
    .join(' ');

  const drawSeries = (samples, color) => {
    if (!samples.length) return;
    svg.appendChild(svgEl('path', {
      d: buildPath(samples), fill: 'none', stroke: color, 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round',
    }));
    const last = samples[samples.length - 1];
    svg.appendChild(svgEl('circle', {
      cx: mapX(last.x), cy: mapY(last.v), r: 4, fill: color, class: 'chart-dot',
    }));
  };

  drawSeries(samplesA, '#e85d00');
  drawSeries(samplesB, '#0d9488');

  const crosshair = svgEl('line', {
    x1: PAD.left, x2: PAD.left, y1: PAD.top, y2: H - PAD.bottom, class: 'chart-crosshair',
  });
  svg.appendChild(crosshair);

  const hitRect = svgEl('rect', {
    x: PAD.left, y: PAD.top, width: plotW, height: plotH, fill: 'transparent',
  });
  svg.appendChild(hitRect);

  const nameA = qs('[data-lane-name="A"]')?.textContent || 'Motor A';
  const nameB = qs('[data-lane-name="B"]')?.textContent || 'Motor B';
  const legendA = qs('[data-legend-a]');
  const legendB = qs('[data-legend-b]');
  if (legendA) legendA.textContent = nameA;
  if (legendB) legendB.textContent = nameB;

  const defaultReadout = 'Beweeg over de grafiek om de snelheid per punt te vergelijken.';

  const updateReadout = (clientX) => {
    const rect = svg.getBoundingClientRect();
    const scale = W / rect.width;
    const localX = (clientX - rect.left) * scale;
    const clamped = Math.min(Math.max(localX, PAD.left), W - PAD.right);
    const distance = ((clamped - PAD.left) / plotW) * xMax;

    crosshair.setAttribute('x1', clamped);
    crosshair.setAttribute('x2', clamped);
    crosshair.style.opacity = 1;

    const parts = [`Op ${Math.round(distance)}m:`];
    if (samplesA.length) parts.push(`${nameA} ${Math.round(interpolateSpeed(samplesA, distance))} km/h`);
    if (samplesB.length) parts.push(`${nameB} ${Math.round(interpolateSpeed(samplesB, distance))} km/h`);
    if (readout) readout.textContent = parts.join(' · ');
  };

  hitRect.addEventListener('pointermove', (event) => updateReadout(event.clientX));
  hitRect.addEventListener('pointerleave', () => {
    crosshair.style.opacity = 0;
    if (readout) readout.textContent = defaultReadout;
  });
}

async function runSimulation(event) {
  event.preventDefault();

  const run = qs('[data-run]');
  if (run) run.disabled = true;
  qs('[data-result]')?.classList.remove('show');

  const response = await fetch(cfg.routes.simulate, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf(),
    },
    body: JSON.stringify(payload()),
  });

  const data = await response.json();

  if (!response.ok) {
    renderLimit(data.limit);
    showMessage(data.message || 'Simulatie kon niet worden gestart.', 'errors');
    if (run) run.disabled = Boolean(data.limit?.blocked) || !selected.A || !selected.B;
    return;
  }

  cfg.limit = data.limit;
  renderLimit(data.limit);
  renderResult(data.result);
  showMessage('Simulatie opgeslagen. De deelbare link staat bij het resultaat.');
  if (run) run.disabled = Boolean(data.limit?.blocked) || !selected.A || !selected.B;
}

function bindShareCopy() {
  const button = qs('[data-share-copy]');
  if (!button) return;

  button.addEventListener('click', async (event) => {
    event.preventDefault();
    const url = button.dataset.shareUrl;
    if (!url) return;

    const originalText = button.textContent;
    try {
      await navigator.clipboard.writeText(url);
      button.textContent = 'Link gekopieerd';
    } catch (error) {
      button.textContent = url;
    }
    setTimeout(() => { button.textContent = originalText; }, 2000);
  });
}

function bindSimulation() {
  const form = qs('[data-simulation-form]');
  if (!form) return;

  bindPickers();
  bindChoices();
  bindShareCopy();
  renderLimit(cfg.limit);
  form.addEventListener('submit', runSimulation);
}

document.addEventListener('DOMContentLoaded', bindSimulation);
