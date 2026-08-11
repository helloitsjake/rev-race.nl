<div data-manual-form="{{ $side }}" hidden style="margin-top:14px;padding-top:14px;border-top:1px solid var(--stone-line)">
    <p class="note-inline" style="margin-top:0">AI kon geen betrouwbare specs vinden. Vul ze zelf in:</p>
    <div class="form-grid">
        <div class="form-row"><label>Merk</label><input data-manual="brand"></div>
        <div class="form-row"><label>Model</label><input data-manual="model"></div>
    </div>
    <div class="form-grid">
        <div class="form-row"><label>Bouwjaar</label><input type="number" data-manual="year"></div>
        <div class="form-row"><label>Motortype</label><input data-manual="engine_type" placeholder="Bijv. Inline-4"></div>
    </div>
    <div class="form-grid">
        <div class="form-row"><label>Vermogen (pk)</label><input type="number" data-manual="power_hp"></div>
        <div class="form-row"><label>Koppel (Nm)</label><input type="number" data-manual="torque_nm"></div>
    </div>
    <div class="form-grid">
        <div class="form-row"><label>Gewicht (kg)</label><input type="number" data-manual="weight_kg"></div>
        <div class="form-row"><label>Cilinderinhoud (cc)</label><input type="number" data-manual="displacement_cc"></div>
    </div>
    <button class="btn btn--primary" type="button" data-manual-submit="{{ $side }}" style="width:100%">Opslaan en gebruiken</button>
</div>
