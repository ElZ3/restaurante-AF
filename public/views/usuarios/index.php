<?php
$titulo = 'Usuarios';
$pagina = 'usuarios';
$usuarios = is_array($usuarios ?? null) ? $usuarios : [];
$rolesUsuario = ['admin' => 'Admin', 'mesero' => 'Mesero', 'cocina' => 'Cocina', 'cajero' => 'Cajero'];
$datosUsuarios = json_encode($usuarios, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
ob_start();
?>
<div class="tarjeta mb-3">
  <div class="barra-filtros">
    <div><label class="form-label" for="fUsuario">Buscar</label><input id="fUsuario" type="search" class="form-control" placeholder="Nombre o correo"></div>
    <div><label class="form-label" for="fRol">Rol</label><select id="fRol" class="form-select"><option value="">Todos</option><?php foreach ($rolesUsuario as $rol => $nombre): ?><option value="<?= e($rol) ?>"><?= e($nombre) ?></option><?php endforeach; ?></select></div>
    <div><label class="form-label" for="fEstadoUsuario">Estado</label><select id="fEstadoUsuario" class="form-select"><option value="">Todos</option><option value="activo">Activos</option><option value="inactivo">Inactivos</option></select></div>
    <div class="flex-grow-0 ms-auto"><button type="button" id="btnNuevoUsuario" class="btn btn-olivo"><i class="bi bi-plus-lg me-1"></i>Nuevo usuario</button></div>
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
<div class="tarjeta mb-3">
  <div class="tarjeta-titulo">Permisos por rol</div>
  <div class="table-responsive">
    <table class="table tabla-olivo">
      <thead><tr><th>Módulo</th><th>Admin.</th><th>Mesero</th><th>Cocina</th><th>Cajero</th></tr></thead>
      <tbody>
        <?php foreach ([
          ['Dashboard', 'Sí', 'Sí', 'Sí', 'Sí'],
          ['Reservas', 'Sí', 'Sí', '—', 'Sí'],
          ['Mesas y áreas', 'Sí', 'Sí', '—', 'Sí'],
          ['Pedidos', 'Sí', 'Sí', 'Sí', 'Sí'],
          ['Cocina', 'Sí', '—', 'Sí', '—'],
          ['Menú', 'Sí', 'Sí', 'Sí', '—'],
          ['Cuentas y pagos', 'Sí', '—', '—', 'Sí'],
          ['Reportes', 'Sí', '—', '—', 'Sí'],
          ['Usuarios y configuración', 'Sí', '—', '—', '—'],
        ] as $fila): ?>
          <tr><?php foreach ($fila as $i => $celda): ?><td<?= $i === 0 ? ' class="fw-semibold"' : '' ?>><?= e($celda) ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="small text-secondary mb-0 mt-2">Los permisos se validan en el servidor en cada acción, no solo ocultando botones.</p>
</div>
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="tituloUsuario" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><form class="modal-content" id="formUsuario">
    <div class="modal-header"><h2 class="modal-title fs-5" id="tituloUsuario">Nuevo usuario</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
    <div class="modal-body">
      <div class="mb-3"><label class="form-label fw-semibold" for="uNombre">Nombre</label><input id="uNombre" name="nombre" class="form-control" required maxlength="100" data-validar="nombre" autocomplete="off"></div>
      <div class="mb-3"><label class="form-label fw-semibold" for="uCorreo">Correo</label><input id="uCorreo" name="correo" type="email" class="form-control" required maxlength="120"></div>
      <div class="mb-3"><label class="form-label fw-semibold" for="uPassword">Contraseña <span class="text-secondary small" id="ayudaPassword"></span></label><input id="uPassword" name="contrasena" type="password" class="form-control" minlength="10" autocomplete="new-password"></div>
      <div class="row g-3">
        <div class="col-sm-6"><label class="form-label fw-semibold" for="uRol">Rol</label><select id="uRol" name="rol" class="form-select" required><?php foreach ($rolesUsuario as $rol => $nombre): ?><option value="<?= e($rol) ?>"><?= e($nombre) ?></option><?php endforeach; ?></select></div>
        <div class="col-sm-6"><label class="form-label fw-semibold" for="uEstado">Estado</label><select id="uEstado" name="estado" class="form-select"><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
      </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-olivo" id="btnGuardarUsuario">Guardar usuario</button></div>
  </form></div>
</div>
<?php
$contenido = ob_get_clean();
$scripts_extra = '<script>window.USUARIOS = ' . ($datosUsuarios ?: '[]') . '; window.ROLES_USUARIO = ' . json_encode($rolesUsuario) . '; const CSRF = ' . json_encode(Csrf::token()) . ';</script>
<script>
(function () {
  const usuarios = window.USUARIOS || [];
  const tbody = document.getElementById("tbodyUsuarios");
  const modal = new bootstrap.Modal(document.getElementById("modalUsuario"));
  const form = document.getElementById("formUsuario");
  let editId = null;
  const roles = window.ROLES_USUARIO || {};
  const esc = s => String(s == null ? "" : s).replace(/[&<>"\\u0027]/g, c => {
    if (c === "&") return "&amp;";
    if (c === "<") return "&lt;";
    if (c === ">") return "&gt;";
    if (c === "\\"") return "&quot;";
    return "&#39;";
  });
  function pintar() {
    const q = document.getElementById("fUsuario").value.trim().toLowerCase();
    const rol = document.getElementById("fRol").value;
    const estado = document.getElementById("fEstadoUsuario").value;
    const lista = usuarios.filter(u => {
      const st = String(u.estado || "activo").toLowerCase();
      return (!q || String(u.nombre || "").toLowerCase().includes(q) || String(u.correo || "").toLowerCase().includes(q))
        && (!rol || u.rol === rol) && (!estado || st === estado);
    });
    tbody.innerHTML = lista.length ? lista.map(u => {
      const activo = String(u.estado || "activo") === "activo";
      const creado = String(u.creado_en || "").slice(0, 10).split("-").reverse().join("/");
      return `<tr><td><div class="fw-semibold">${esc(u.nombre)}</div><small class="text-secondary">${esc(u.correo)}</small></td><td><span class="rol ${u.rol === "admin" ? "rol-admin" : ""}">${esc(roles[u.rol] || u.rol)}</span></td><td><span class="estado estado-${activo ? "activo" : "inactivo"}">${activo ? "Activo" : "Inactivo"}</span></td><td><small>${creado || "—"}</small></td><td class="text-end text-nowrap"><button type="button" class="btn btn-outline-olivo btn-icono" data-editar="${u.id}" aria-label="Editar ${esc(u.nombre)}"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-outline-secondary btn-icono" data-estado="${u.id}" data-activo="${activo ? "1" : "0"}" aria-label="${activo ? "Desactivar" : "Activar"} ${esc(u.nombre)}"><i class="bi ${activo ? "bi-person-dash" : "bi-person-check"}"></i></button></td></tr>`;
    }).join("") : `<tr><td colspan="5" class="text-center text-secondary py-5">No hay usuarios que coincidan con los filtros.</td></tr>`;
  }
  function abrir(u) {
    editId = u ? Number(u.id) : null;
    form.reset();
    document.getElementById("tituloUsuario").textContent = editId ? "Editar usuario" : "Nuevo usuario";
    document.getElementById("ayudaPassword").textContent = editId ? "(dejar vacío para conservarla)" : "(mínimo 10 caracteres)";
    document.getElementById("uPassword").required = !editId;
    if (u) {
      document.getElementById("uNombre").value = u.nombre || "";
      document.getElementById("uCorreo").value = u.correo || "";
      document.getElementById("uRol").value = u.rol || "mesero";
      document.getElementById("uEstado").value = u.estado || "activo";
    }
    modal.show();
  }
  document.getElementById("btnNuevoUsuario").addEventListener("click", () => abrir(null));
  ["fUsuario","fRol","fEstadoUsuario"].forEach(id => document.getElementById(id).addEventListener(id === "fUsuario" ? "input" : "change", pintar));
  tbody.addEventListener("click", async e => {
    const edit = e.target.closest("[data-editar]");
    const changeState = e.target.closest("[data-estado]");
    if (edit) { const u = usuarios.find(x => Number(x.id) === Number(edit.dataset.editar)); if (u) abrir(u); }
    if (changeState && window.confirm(changeState.dataset.activo === "1" ? "¿Desactivar este usuario?" : "¿Activar este usuario?")) {
      try {
        const u = usuarios.find(x => Number(x.id) === Number(changeState.dataset.estado));
        if (!u) return;
        const activar = changeState.dataset.activo !== "1";
        const res = activar
          ? await API.post("/usuarios/actualizar/" + u.id, {nombre: u.nombre, correo: u.correo, rol: u.rol, estado: "activo", csrf: CSRF})
          : await API.post("/usuarios/eliminar/" + u.id, {csrf: CSRF});
        if (!res.success) { aviso(res.message || "No se pudo cambiar el estado.", "danger"); return; }
        u.estado = activar ? "activo" : "inactivo";
        pintar(); aviso(res.message, "success");
      } catch (err) { aviso("Error al desactivar: " + err.message, "danger"); }
    }
  });
  form.addEventListener("submit", async e => {
    e.preventDefault();
    const btn = document.getElementById("btnGuardarUsuario");
    if (!Validar.formulario(form)) return;
    const datos = Object.fromEntries(new FormData(form).entries());
    datos.csrf = CSRF;
    conSpinner(btn);
    try {
      const res = await API.post(editId ? "/usuarios/actualizar/" + editId : "/usuarios/crear", datos);
      if (!res.success) { aviso(res.message || "No se pudo guardar el usuario.", "danger"); return; }
      const u = res.usuario;
      const index = usuarios.findIndex(x => Number(x.id) === Number(u.id));
      if (index >= 0) usuarios.splice(index, 1, u); else usuarios.unshift(u);
      pintar(); modal.hide(); aviso(res.message, "success");
    } catch (err) { aviso("Error al guardar: " + err.message, "danger"); }
    finally { liberarSpinner(btn); }
  });
  pintar();
})();
</script>';
require_once __DIR__ . '/../layout.php';
render_app_layout($titulo, $pagina, $contenido, $scripts_extra);
