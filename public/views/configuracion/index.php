<?php
$titulo = 'Configuración';
$pagina = 'config';
$areas = is_array($areas ?? null) ? $areas : [];
$mesas = is_array($mesas ?? null) ? $mesas : [];
$configuracion = is_array($configuracion ?? null) ? $configuracion : [];
$datosAreas = json_encode($areas, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$datosMesas = json_encode($mesas, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
ob_start();
?>
<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabGen" type="button" role="tab">Datos del restaurante</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMesas" type="button" role="tab">Áreas y mesas</button></li>
  <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabOpc" type="button" role="tab">Opciones generales</button></li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="tabGen" role="tabpanel">
<div class="tarjeta" style="max-width:720px">
  <form id="formConfiguracion">
    <div class="row g-3">
      <div class="col-12"><label class="form-label" for="cNombre">Nombre del restaurante</label><input class="form-control" id="cNombre" name="nombre_restaurante" maxlength="80" required value="<?= e($configuracion['nombre_restaurante'] ?? 'Olivo Real') ?>"></div>
      <div class="col-12"><label class="form-label" for="gEslogan">Eslogan</label><input class="form-control" id="gEslogan" maxlength="120" value="<?= e($configuracion['eslogan'] ?? 'El arte de compartir una buena mesa.') ?>"></div>
      <div class="col-12"><label class="form-label" for="cDireccion">Dirección (opcional)</label><input class="form-control" id="cDireccion" name="direccion" maxlength="150" value="<?= e($configuracion['direccion'] ?? '') ?>"></div>
      <div class="col-md-6"><label class="form-label" for="cTelefono">Teléfono (opcional)</label><input class="form-control" id="cTelefono" name="telefono" maxlength="20" value="<?= e($configuracion['telefono'] ?? '') ?>"></div>
    </div>
    <div class="text-end mt-3"><button type="submit" class="btn btn-olivo" id="btnGuardarConfig">Guardar configuración</button></div>
  </form>
</div>
</div>
<div class="tab-pane fade" id="tabMesas" role="tabpanel">
<section class="tarjeta">
  <div class="d-flex justify-content-between align-items-center gap-2 mb-3"><h2 class="tarjeta-titulo mb-0">Áreas</h2><button class="btn btn-olivo btn-sm" type="button" id="btnNuevaArea">Nueva área</button></div>
  <div class="table-responsive"><table class="table tabla-olivo align-middle mb-0">
    <thead><tr><th>Área</th><th>Descripción</th><th>Mesas asignadas</th><th class="text-end">Acciones</th></tr></thead>
    <tbody id="tbodyAreas"></tbody>
  </table></div>
</section>
<div class="tarjeta mt-3">
  <div class="tarjeta-titulo">Mesas</div>
  <div class="table-responsive">
    <table class="table tabla-olivo">
      <thead><tr><th>Mesa</th><th>Área</th><th style="width:110px">Capacidad</th><th>Fuera de servicio</th></tr></thead>
      <tbody>
        <?php foreach ($mesas as $mesa): ?>
          <?php $numeroMesa = $mesa['numero_mesa'] ?? $mesa['numero'] ?? $mesa['id'] ?? ''; ?>
          <tr>
            <td class="fw-semibold">Mesa <?= e($numeroMesa) ?></td>
            <td><?= e($mesa['area_nombre'] ?? '') ?></td>
            <td><?= (int) ($mesa['capacidad'] ?? 0) ?></td>
            <td><?= strtolower((string) ($mesa['estado'] ?? '')) === 'fuera_servicio' ? 'Sí' : 'No' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</div>
<div class="tab-pane fade" id="tabOpc" role="tabpanel">
  <div class="tarjeta" style="max-width:720px">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label" for="oDur">Separación mínima entre reservas (minutos)</label>
        <input type="number" class="form-control" id="oDur" min="30" max="300" step="15" value="<?= e($configuracion['separacion_reservas_minutos'] ?? '120') ?>">
        <div class="invalid-feedback">Entre 30 y 300 minutos.</div>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="oDem">Aviso de demora en cocina (minutos)</label>
        <input type="number" class="form-control" id="oDem" name="tiempo_demora_minutos" form="formConfiguracion" min="1" max="240" step="1" required value="<?= e($configuracion['tiempo_demora_minutos'] ?? '15') ?>">
        <div class="invalid-feedback">Entre 1 y 240 minutos.</div>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="oMon">Código de moneda</label>
        <input type="text" class="form-control" id="oMon" name="moneda" form="formConfiguracion" maxlength="3" required value="<?= e($configuracion['moneda'] ?? 'USD') ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-olivo mt-4" form="formConfiguracion">Guardar opciones</button>
  </div>
</div>
</div>
<div class="modal fade" id="modalArea" tabindex="-1" aria-labelledby="tituloArea" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><form class="modal-content" id="formArea">
    <div class="modal-header"><h2 class="modal-title fs-5" id="tituloArea">Nueva área</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
    <div class="modal-body"><div class="mb-3"><label class="form-label fw-semibold" for="aNombre">Nombre</label><input class="form-control" id="aNombre" name="nombre" required maxlength="60"></div><div><label class="form-label fw-semibold" for="aDescripcion">Descripción</label><textarea class="form-control" id="aDescripcion" name="descripcion" rows="3"></textarea></div></div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-olivo" id="btnGuardarArea">Guardar área</button></div>
  </form></div>
</div>
<?php
$contenido = ob_get_clean();
$scripts_extra = '<script>window.AREAS = ' . ($datosAreas ?: '[]') . '; window.MESAS = ' . ($datosMesas ?: '[]') . '; const CSRF = ' . json_encode(Csrf::token()) . ';</script>
<script>
(function () {
  const areas = window.AREAS || [], mesas = window.MESAS || [];
  const tbody = document.getElementById("tbodyAreas");
  const areaModal = new bootstrap.Modal(document.getElementById("modalArea"));
  const formArea = document.getElementById("formArea");
  let editId = null;
  const esc = s => String(s == null ? "" : s).replace(/[&<>"\\u0027]/g, c => {
    if (c === "&") return "&amp;"; if (c === "<") return "&lt;"; if (c === ">") return "&gt;";
    if (c === "\\"") return "&quot;"; return "&#39;";
  });
  function pintarAreas() {
    tbody.innerHTML = areas.length ? areas.map(a => {
      const count = mesas.filter(m => Number(m.area_id) === Number(a.id)).length;
      return `<tr><td class="fw-semibold">${esc(a.nombre)}</td><td>${esc(a.descripcion || "—")}</td><td>${count}</td><td class="text-end text-nowrap"><button class="btn btn-outline-olivo btn-sm" data-editar="${a.id}">Editar</button> <button class="btn btn-outline-danger btn-sm" data-eliminar="${a.id}" ${count ? "disabled title=\\"Asigna las mesas a otra área antes de eliminar.\\"" : ""}>Eliminar</button></td></tr>`;
    }).join("") : `<tr><td colspan="4" class="text-center text-secondary py-4">No hay áreas registradas.</td></tr>`;
  }
  function abrir(a) {
    editId = a ? Number(a.id) : null;
    formArea.reset();
    document.getElementById("tituloArea").textContent = editId ? "Editar área" : "Nueva área";
    if (a) { document.getElementById("aNombre").value = a.nombre || ""; document.getElementById("aDescripcion").value = a.descripcion || ""; }
    areaModal.show();
  }
  document.getElementById("btnNuevaArea").addEventListener("click", () => abrir(null));
  tbody.addEventListener("click", async e => {
    const editar = e.target.closest("[data-editar]"), borrar = e.target.closest("[data-eliminar]");
    if (editar) { const a = areas.find(x => Number(x.id) === Number(editar.dataset.editar)); if (a) abrir(a); }
    if (borrar) {
      try {
        const res = await API.post("/areas/eliminar/" + borrar.dataset.eliminar, {csrf: CSRF});
        if (!res.success) { aviso(res.message || "No se pudo eliminar el área.", "danger"); return; }
        const index = areas.findIndex(a => Number(a.id) === Number(borrar.dataset.eliminar));
        if (index >= 0) areas.splice(index, 1);
        pintarAreas(); aviso(res.message, "success");
      } catch (err) { aviso("Error al eliminar: " + err.message, "danger"); }
    }
  });
  formArea.addEventListener("submit", async e => {
    e.preventDefault();
    const btn = document.getElementById("btnGuardarArea");
    if (!Validar.formulario(formArea)) return;
    const datos = Object.fromEntries(new FormData(formArea).entries()); datos.csrf = CSRF;
    conSpinner(btn);
    try {
      const res = await API.post(editId ? "/areas/actualizar/" + editId : "/areas/crear", datos);
      if (!res.success) { aviso(res.message || "No se pudo guardar el área.", "danger"); return; }
      if (editId) { const a = areas.find(x => Number(x.id) === editId); if (a) Object.assign(a, datos); }
      else areas.push({id: res.area_id, nombre: datos.nombre, descripcion: datos.descripcion});
      pintarAreas(); areaModal.hide(); aviso(res.message, "success");
    } catch (err) { aviso("Error al guardar: " + err.message, "danger"); }
    finally { liberarSpinner(btn); }
  });
  document.getElementById("formConfiguracion").addEventListener("submit", async e => {
    e.preventDefault();
    const btn = document.getElementById("btnGuardarConfig");
    if (!Validar.formulario(e.target)) return;
    const datos = Object.fromEntries(new FormData(e.target).entries()); datos.csrf = CSRF;
    conSpinner(btn);
    try {
      const res = await API.post("/configuracion/actualizar", datos);
      aviso(res.message || (res.success ? "Configuración guardada." : "No se pudo guardar."), res.success ? "success" : "danger");
    } catch (err) { aviso("Error al guardar: " + err.message, "danger"); }
    finally { liberarSpinner(btn); }
  });
  pintarAreas();
})();
</script>';
require_once __DIR__ . '/../layout.php';
render_app_layout($titulo, $pagina, $contenido, $scripts_extra);
