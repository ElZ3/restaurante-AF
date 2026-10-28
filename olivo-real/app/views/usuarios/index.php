
<div class="tarjeta mb-3">
  <div class="barra-filtros">
    <div>
      <label class="form-label" for="fBuscar">Buscar</label>
      <input type="search" id="fBuscar" class="form-control" placeholder="Nombre o correo">
    </div>
    <div>
      <label class="form-label" for="fRol">Rol</label>
      <select id="fRol" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($roles as $k => $t): ?><option value="<?= $k ?>"><?= $t ?></option><?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label" for="fEstado">Estado</label>
      <select id="fEstado" class="form-select">
        <option value="">Todos</option>
        <option value="activo">Activos</option>
        <option value="inactivo">Inactivos</option>
      </select>
    </div>
    <div class="flex-grow-0 ms-auto">
      <button type="button" id="btnNuevo" class="btn btn-olivo"><i class="bi bi-plus-lg me-1"></i>Nuevo usuario</button>
    </div>
  </div>
</div>

<div class="tarjeta mb-3">
  <div class="table-responsive">
    <table class="table tabla-olivo">
      <thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Creado por</th><th class="text-end">Acciones</th></tr></thead>
      <tbody id="tbodyUsuarios"></tbody>
    </table>
  </div>
</div>

<div class="tarjeta">
  <div class="tarjeta-titulo">Permisos por rol</div>
  <div class="table-responsive">
    <table class="table tabla-olivo">
      <thead><tr><th>Módulo</th><th>Admin.</th><th>Mesero</th><th>Cocina</th><th>Cajero</th></tr></thead>
      <tbody>
        <?php foreach ($matriz as $fila): ?>
          <tr><?php foreach ($fila as $i => $celda): ?><td<?= $i === 0 ? ' class="fw-semibold"' : '' ?>><?= htmlspecialchars($celda) ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="small text-secondary mb-0 mt-2">Los permisos se validan en el servidor en cada acción, no solo ocultando botones.</p>
</div>

<!-- Modal: crear / editar usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="tituloUsr" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formUsuario" novalidate autocomplete="off">
        <div class="modal-header">
          <h2 class="modal-title fs-5" id="tituloUsr">Nuevo usuario</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="usuario_id" value="">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="uNombre">Nombre completo</label>
              <input type="text" class="form-control" id="uNombre" name="nombre" maxlength="100">
              <div class="invalid-feedback" id="errNombre"></div>
            </div>
            <div class="col-12">
              <label class="form-label" for="uCorreo">Correo electrónico</label>
              <input type="email" class="form-control" id="uCorreo" name="correo" maxlength="120">
              <div class="invalid-feedback" id="errCorreo"></div>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="uRol">Rol</label>
              <select class="form-select" id="uRol" name="rol">
                <?php foreach ($roles as $k => $t): ?><option value="<?= $k ?>"><?= $t ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-sm-6 d-flex align-items-end">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" role="switch" id="uActivo" name="activo" checked>
                <label class="form-check-label" for="uActivo">Cuenta activa</label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label" for="uClave" id="lblClave">Contraseña</label>
              <div class="input-group">
                <input type="password" class="form-control" id="uClave" name="clave" autocomplete="new-password" maxlength="72">
                <button class="btn btn-outline-secondary" type="button" id="verClave" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
              </div>
              <div class="invalid-feedback" id="errClave"></div>
              <div class="form-text" id="ayudaClave">Mínimo 8 caracteres, con letras y números.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-olivo">Guardar usuario</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>window.DATOS = <?= json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>

<?php
$scripts_extra = <<<'JS'
<script>
(function () {
  const D = window.DATOS, U = D.usuarios, ROL = D.roles;
  const $ = id => document.getElementById(id);
  const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const tbody = $('tbodyUsuarios'), fB = $('fBuscar'), fR = $('fRol'), fE = $('fEstado');
  const esYo = u => u.n === D.actual;

  /* ---------- Tabla (BACKEND: se generará en PHP desde la base de datos) ---------- */
  function pintar() {
    const q = fB.value.trim().toLowerCase();
    const lista = U.filter(u => (!q || (u.n + ' ' + u.c).toLowerCase().includes(q)) &&
                                (!fR.value || u.rol === fR.value) && (!fE.value || u.estado === fE.value));
    if (!lista.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-secondary py-4">No hay usuarios que coincidan.</td></tr>';
      return;
    }
    tbody.innerHTML = lista.map(u => `
      <tr>
        <td><div class="fw-semibold">${esc(u.n)}${esYo(u) ? ' <small class="text-secondary fw-normal">(tú)</small>' : ''}</div>
            <small class="text-secondary">${esc(u.c)}</small></td>
        <td><span class="rol ${u.rol === 'admin' ? 'rol-admin' : ''}">${ROL[u.rol]}</span></td>
        <td><span class="estado estado-${u.estado}">${u.estado === 'activo' ? 'Activo' : 'Inactivo'}</span></td>
        <td><small>${esc(u.por)}<br><span class="text-secondary">${u.fecha.split('-').reverse().join('/')}</span></small></td>
        <td class="text-end text-nowrap">
          <button type="button" class="btn btn-outline-olivo btn-icono" data-editar="${u.id}" aria-label="Editar ${esc(u.n)}"><i class="bi bi-pencil"></i></button>
          <button type="button" class="btn btn-outline-secondary btn-icono" data-estado="${u.id}" ${esYo(u) ? 'disabled title="No puedes desactivar tu propia cuenta"' : ''}
            aria-label="${u.estado === 'activo' ? 'Desactivar' : 'Activar'} ${esc(u.n)}"><i class="bi ${u.estado === 'activo' ? 'bi-person-dash' : 'bi-person-check'}"></i></button>
        </td>
      </tr>`).join('');
  }
  [fB, fR, fE].forEach(x => { x.addEventListener('input', pintar); x.addEventListener('change', pintar); });

  // BACKEND: UPDATE usuarios SET estado. Una cuenta inactiva no puede iniciar sesión.
  tbody.addEventListener('click', e => {
    const b = e.target.closest('[data-estado]'), ed = e.target.closest('[data-editar]');
    if (ed) abrir(U.find(x => x.id === +ed.dataset.editar));
    if (b) {
      const u = U.find(x => x.id === +b.dataset.estado);
      const nuevo = u.estado === 'activo' ? 'inactivo' : 'activo';
      if (nuevo === 'inactivo' && !confirm('¿Desactivar la cuenta de ' + u.n + '? No podrá iniciar sesión.')) return;
      u.estado = nuevo; pintar();
      aviso(u.n + (nuevo === 'activo' ? ' fue activado' : ' fue desactivado') + ' (demo)');
    }
  });

  /* ---------- Modal ---------- */
  const modal = new bootstrap.Modal($('modalUsuario'));
  const form = $('formUsuario'), el = form.elements;
  $('verClave').addEventListener('click', () => { el.clave.type = el.clave.type === 'password' ? 'text' : 'password'; });

  function abrir(u) {
    form.reset();
    ['uNombre', 'uCorreo', 'uClave'].forEach(i => $(i).classList.remove('is-invalid'));
    ['errNombre', 'errCorreo', 'errClave'].forEach(i => { $(i).textContent = ''; $(i).classList.remove('d-block'); });
    el.clave.type = 'password';
    $('tituloUsr').textContent = u ? 'Editar usuario' : 'Nuevo usuario';
    el.usuario_id.value = u ? u.id : '';
    if (u) {
      el.nombre.value = u.n; el.correo.value = u.c; el.rol.value = u.rol; el.activo.checked = u.estado === 'activo';
    } else {
      el.rol.value = 'mesero'; el.activo.checked = true;
    }
    const yo = u && esYo(u);
    el.rol.disabled = yo; el.activo.disabled = yo; // evita quedarse sin administrador
    $('lblClave').textContent = u ? 'Nueva contraseña (opcional)' : 'Contraseña';
    $('ayudaClave').textContent = u ? 'Déjala en blanco para conservar la actual. Las contraseñas existentes nunca se muestran.'
                                    : 'Mínimo 8 caracteres, con letras y números.';
    modal.show();
  }
  $('btnNuevo').addEventListener('click', () => abrir(null));

  // BACKEND: repetir estas validaciones en PHP y guardar la contraseña con password_hash()
  form.addEventListener('submit', e => {
    e.preventDefault();
    let ok = true;
    const marca = (inp, err, msg) => {
      $(inp).classList.toggle('is-invalid', !!msg);
      $(err).textContent = msg || ''; $(err).classList.toggle('d-block', !!msg);
      if (msg) ok = false;
    };
    const id = el.usuario_id.value ? +el.usuario_id.value : null;
    const nombre = el.nombre.value.trim(), correo = el.correo.value.trim().toLowerCase(), clave = el.clave.value;

    marca('uNombre', 'errNombre', nombre.length < 3 ? 'Escribe el nombre completo (mínimo 3 letras).' : '');
    let m = '';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(correo)) m = 'Escribe un correo válido.';
    else if (U.some(x => x.c.toLowerCase() === correo && x.id !== id)) m = 'Ya existe un usuario con ese correo.';
    marca('uCorreo', 'errCorreo', m);
    m = '';
    if (!id && !clave) m = 'Escribe una contraseña para la cuenta nueva.';
    else if (clave && (clave.length < 8 || !/[A-Za-z]/.test(clave) || !/[0-9]/.test(clave))) m = 'Mínimo 8 caracteres, con letras y números.';
    marca('uClave', 'errClave', m);
    if (!ok) return;

    if (id) {
      const u = U.find(x => x.id === id);
      u.n = nombre; u.c = correo;
      if (!esYo(u)) { u.rol = el.rol.value; u.estado = el.activo.checked ? 'activo' : 'inactivo'; }
    } else {
      U.push({ id: U.reduce((n, x) => Math.max(n, x.id), 0) + 1, n: nombre, c: correo, rol: el.rol.value,
               estado: el.activo.checked ? 'activo' : 'inactivo', por: D.actual, fecha: new Date().toISOString().slice(0, 10) });
    }
    pintar(); modal.hide();
    aviso('Usuario guardado (demo: todavía no se almacena en la base de datos)');
  });

  pintar();
})();
</script>
JS;
