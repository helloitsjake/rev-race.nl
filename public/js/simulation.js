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
  return `
    <div class="spec-row"><span class="spec-label">Vermogen</span><span class="spec-value">${motor.power_hp} pk</span></div>
    <div class="spec-row"><span class="spec-label">Koppel</span><span class="spec-value">${motor.torque_nm} Nm</span></div>
    <div class="spec-row"><span class="spec-label">Gewicht</span><span class="spec-value">${motor.weight_kg} kg</span></div>
    <div class="spec-row"><span class="spec-label">Motorblok</span><span class="spec-value">${motor.engine_type}</span></div>
    <div class="spec-row"><span class="spec-label">Inhoud</span><span class="spec-value">${motor.displacement_cc} cc</span></div>
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

  panel.classList.add('show');
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

function bindSimulation() {
  const form = qs('[data-simulation-form]');
  if (!form) return;

  bindPickers();
  bindChoices();
  renderLimit(cfg.limit);
  form.addEventListener('submit', runSimulation);
}

document.addEventListener('DOMContentLoaded', bindSimulation);
