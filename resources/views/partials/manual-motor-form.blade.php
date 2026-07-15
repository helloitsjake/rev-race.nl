<div class="card" data-manual-form="{{ $side }}" hidden style="margin-top:10px">
    <p class="section-sub" style="margin:0 0 10px">AI kon geen betrouwbare specs vinden. Vul ze zelf in:</p>
    <div class="sim-grid">
        <div class="form-row"><label class="form-label">Merk</label><input class="input" data-manual="brand"></div>
        <div class="form-row"><label class="form-label">Model</label><input class="input" data-manual="model"></div>
    </div>
    <div class="sim-grid">
        <div class="form-row"><label class="form-label">Bouwjaar</label><input class="input" type="number" data-manual="year"></div>
        <div class="form-row"><label class="form-label">Motortype</label><input class="input" data-manual="engine_type" placeholder="Bijv. Inline-4"></div>
    </div>
    <div class="sim-grid">
        <div class="form-row"><label class="form-label">Vermogen (pk)</label><input class="input" type="number" data-manual="power_hp"></div>
        <div class="form-row"><label class="form-label">Koppel (Nm)</label><input class="input" type="number" data-manual="torque_nm"></div>
    </div>
    <div class="sim-grid">
        <div class="form-row"><label class="form-label">Gewicht (kg)</label><input class="input" type="number" data-manual="weight_kg"></div>
        <div class="form-row"><label class="form-label">Cilinderinhoud (cc)</label><input class="input" type="number" data-manual="displacement_cc"></div>
    </div>
    <button class="btn primary" type="button" data-manual-submit="{{ $side }}" style="width:100%">Opslaan en gebruiken</button>
</div>
