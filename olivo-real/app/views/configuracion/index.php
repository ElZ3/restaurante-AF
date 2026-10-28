
<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabGen" type="button" role="tab">Datos del restaurante</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMesas" type="button" role="tab">Áreas y mesas</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabOpc" type="button" role="tab">Opciones generales</button></li>
</ul>

<div class="tab-content">

  <!-- Datos del restaurante -->
  <div class="tab-pane fade show active" id="tabGen" role="tabpanel">
    <div class="tarjeta" style="max-width:720px">
      <form id="formGen" novalidate>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label" for="gNombre">Nombre del restaurante</label>
            <input type="text" class="form-control" id="gNombre" value="Olivo Real" maxlength="80" required>
            <div class="invalid-feedback">El nombre es obligatorio.</div>
          </div>
          <div class="col-12">
            <label class="form-label" for="gEslogan">Eslogan</label>
            <input type="text" class="form-control" id="gEslogan" value="El arte de compartir una buena mesa." maxlength="120">
          </div>
          <div class="col-12">
            <label class="form-label" for="gDir">Dirección (opcional)</label>
            <input type="text" class="form-control" id="gDir" maxlength="150" placeholder="Ej. Av. Principal #123, San Salvador">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="gTel">Teléfono (opcional)</label>
            <input type="tel" class="form-control" id="gTel" maxlength="20" placeholder="+503 0000-0000">
            <div class="invalid-feedback">Usa solo números, espacios, + - ( ) (7 a 20 caracteres).</div>
          </div>
        </div>
        <button type="submit" class="btn btn-olivo mt-4">Guardar cambios</button>
      </form>
    </div>
  </div>

  <!-- Áreas y mesas -->
  <div class="tab-pane fade" id="tabMesas" role="tabpanel">
    <div class="row g-3">
      <div class="col-lg-4">
        <div class="tarjeta">
          <div class="tarjeta-titulo">Áreas</div>
          <div id="listaAreas" class="mb-3"></div>
          <label class="form-label" for="nuevaArea">Nueva área</label>
          <div class="input-group">
            <input type="text" class="form-control" id="nuevaArea" maxlength="60" placeholder="Ej. Barra">
            <button type="button" class="btn btn-olivo" id="btnArea">Agregar</button>
          </div>
          <div class="small text-danger mt-1 d-none" id="errArea"></div>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="tarjeta">
          <div class="tarjeta-titulo">Mesas</div>
          <div class="table-responsive">
            <table class="table tabla-olivo">
              <thead><tr><th>Mesa</th><th>Área</th><th style="width:110px">Capacidad</th><th>Fuera de servicio</th></tr></thead>
              <tbody id="tbodyMesas"></tbody>
            </table>
          </div>
          <p class="small text-secondary">Las mesas no se eliminan, porque tienen historial de pedidos: se marcan como fuera de servicio.</p>
          <hr>
          <div class="row g-2 align-items-end">
            <div class="col-4 col-md-3"><label class="form-label" for="nmNum">Número</label>
              <input type="number" class="form-control" id="nmNum" min="1" max="999"></div>
            <div class="col-8 col-md-4"><label class="form-label" for="nmArea">Área</label>
              <select class="form-select" id="nmArea"></select></div>
            <div class="col-6 col-md-3"><label class="form-label" for="nmCap">Capacidad</label>
              <input type="number" class="form-control" id="nmCap" min="1" max="20" value="4"></div>
            <div class="col-6 col-md-2"><button type="button" class="btn btn-olivo w-100" id="btnMesa">Agregar</button></div>
          </div>
          <div class="small text-danger mt-1 d-none" id="errMesa"></div>
          <button type="button" class="btn btn-outline-olivo mt-3" id="btnGuardarMesas">Guardar cambios de mesas</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Opciones generales -->
  <div class="tab-pane fade" id="tabOpc" role="tabpanel">
    <div class="tarjeta" style="max-width:720px">
      <form id="formOpc" novalidate>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="oDur">Separación mínima entre reservas (minutos)</label>
            <input type="number" class="form-control" id="oDur" min="30" max="300" step="15" value="120">
            <div class="invalid-feedback">Entre 30 y 300 minutos.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="oDem">Aviso de demora en cocina (minutos)</label>
            <input type="number" class="form-control" id="oDem" min="5" max="90" value="15">
            <div class="invalid-feedback">Entre 5 y 90 minutos.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="oMon">Símbolo de moneda</label>
            <input type="text" class="form-control" id="oMon" maxlength="3" value="$">
            <div class="invalid-feedback">Escribe un símbolo (máximo 3 caracteres).</div>
          </div>
          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" id="oDesc" checked>
              <label class="form-check-label" for="oDesc">Permitir descuentos al cobrar (solo administrador)</label>
            </div>
          </div>
        </div>
        <div class="alert alert-light border small mt-4 mb-0">
          <i class="bi bi-shield-lock me-1"></i>Las credenciales de la base de datos no se configuran aquí: viven en <code>config.php</code>, fuera de lo público y sin subirse al repositorio.
        </div>
        <button type="submit" class="btn btn-olivo mt-4">Guardar opciones</button>
      </form>
    </div>
  </div>
</div>

<script>window.DATOS = <?= json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>

<?php
$scripts_extra = <<<'JS'
<script>
(function () {
  const D = window.DATOS;
  const A = D.areas.map(a => ({ n: a[0], on: !!a[1] }));
  const M = D.mesas.map(m => ({ num: m[0], area: m[1], cap: m[2], fuera: !!m[3] }));
  const $ = id => document.getElementById(id);
  const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const opcionesAreas = sel => A.map((a, i) => `<option value="${i}" ${i === sel ? 'selected' : ''}>${esc(a.n)}</option>`).join('');

  /* ---------- Datos del restaurante (BACKEND: UPDATE configuracion) ---------- */
  $('formGen').addEventListener('submit', e => {
    e.preventDefault();
    const nom = $('gNombre'), tel = $('gTel');
    const telOk = !tel.value.trim() || /^[0-9+()\-\s]{7,20}$/.test(tel.value.trim());
    nom.classList.toggle('is-invalid', !nom.value.trim());
    tel.classList.toggle('is-invalid', !telOk);
    if (!nom.value.trim() || !telOk) return;
    aviso('Datos del restaurante guardados (demo)');
  });

  /* ---------- Áreas ---------- */
  function pintarAreas() {
    $('listaAreas').innerHTML = A.map((a, i) => `
      <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
        <div><div class="fw-semibold small">${esc(a.n)}</div>
          <small class="text-secondary">${M.filter(m => m.area === i).length} mesas</small></div>
        <div class="form-check form-switch m-0">
          <input class="form-check-input" type="checkbox" role="switch" data-area="${i}" ${a.on ? 'checked' : ''} aria-label="Área ${esc(a.n)} activa">
        </div>
      </div>`).join('');
    $('nmArea').innerHTML = opcionesAreas(0);
  }
  $('listaAreas').addEventListener('change', e => {
    const s = e.target.closest('[data-area]');
    if (!s) return;
    A[+s.dataset.area].on = s.checked;
    aviso('Área ' + (s.checked ? 'activada' : 'desactivada') + ' (demo)');
  });
  $('btnArea').addEventListener('click', () => {
    const inp = $('nuevaArea'), err = $('errArea'), n = inp.value.trim();
    let msg = '';
    if (n.length < 3) msg = 'Escribe el nombre del área (mínimo 3 letras).';
    else if (A.some(a => a.n.toLowerCase() === n.toLowerCase())) msg = 'Ya existe un área con ese nombre.';
    err.textContent = msg; err.classList.toggle('d-none', !msg);
    if (msg) return;
    A.push({ n: n, on: true }); inp.value = '';
    pintarAreas(); pintarMesas();
    aviso('Área agregada (demo)');
  });

  /* ---------- Mesas ---------- */
  function pintarMesas() {
    $('tbodyMesas').innerHTML = M.slice().sort((a, b) => a.num - b.num).map(m => `
      <tr>
        <td class="fw-semibold">Mesa ${m.num}</td>
        <td><select class="form-select form-select-sm" data-m="${m.num}" data-campo="area" aria-label="Área de la mesa ${m.num}">${opcionesAreas(m.area)}</select></td>
        <td><input type="number" class="form-control form-control-sm" min="1" max="20" value="${m.cap}" data-m="${m.num}" data-campo="cap" aria-label="Capacidad de la mesa ${m.num}"></td>
        <td><div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" data-m="${m.num}" data-campo="fuera" ${m.fuera ? 'checked' : ''} aria-label="Mesa ${m.num} fuera de servicio"></div></td>
      </tr>`).join('');
    pintarAreasConteo();
  }
  function pintarAreasConteo() { // actualiza el conteo de mesas por área sin perder el foco
    document.querySelectorAll('#listaAreas small').forEach((s, i) => { s.textContent = M.filter(m => m.area === i).length + ' mesas'; });
  }
  $('tbodyMesas').addEventListener('change', e => {
    const t = e.target, m = M.find(x => x.num === +t.dataset.m);
    if (!m) return;
    if (t.dataset.campo === 'cap') {
      const v = parseInt(t.value, 10);
      if (!(v >= 1 && v <= 20)) { t.value = m.cap; aviso('La capacidad debe estar entre 1 y 20.', 'warning'); return; }
      m.cap = v;
    }
    if (t.dataset.campo === 'area') { m.area = +t.value; pintarAreasConteo(); }
    if (t.dataset.campo === 'fuera') m.fuera = t.checked;
  });
  $('btnMesa').addEventListener('click', () => {
    const num = parseInt($('nmNum').value, 10), cap = parseInt($('nmCap').value, 10), err = $('errMesa');
    let msg = '';
    if (!(num >= 1)) msg = 'Escribe el número de la mesa.';
    else if (M.some(m => m.num === num)) msg = 'La mesa ' + num + ' ya existe.';
    else if (!(cap >= 1 && cap <= 20)) msg = 'La capacidad debe estar entre 1 y 20.';
    err.textContent = msg; err.classList.toggle('d-none', !msg);
    if (msg) return;
    M.push({ num: num, area: +$('nmArea').value, cap: cap, fuera: false });
    $('nmNum').value = '';
    pintarMesas();
    aviso('Mesa ' + num + ' agregada (demo)');
  });
  // BACKEND: aquí se enviarán los cambios al servidor (UPDATE/INSERT en mesas y areas)
  $('btnGuardarMesas').addEventListener('click', () => aviso('Cambios de áreas y mesas guardados (demo)'));

  /* ---------- Opciones generales (BACKEND: guardar en la tabla configuracion) ---------- */
  $('formOpc').addEventListener('submit', e => {
    e.preventDefault();
    const rango = (id, min, max) => { const v = parseInt($(id).value, 10), ok = v >= min && v <= max; $(id).classList.toggle('is-invalid', !ok); return ok; };
    const a = rango('oDur', 30, 300), b = rango('oDem', 5, 90);
    const mon = $('oMon').value.trim(), c = mon.length >= 1 && mon.length <= 3;
    $('oMon').classList.toggle('is-invalid', !c);
    if (!(a && b && c)) return;
    aviso('Opciones guardadas (demo)');
  });

  pintarAreas(); pintarMesas();
})();
</script>
JS;
