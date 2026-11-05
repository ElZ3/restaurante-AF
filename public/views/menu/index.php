<?php
$titulo = 'Menú';
$pagina = 'menu';
$productos = is_array($productos ?? null) ? $productos : [];
$categorias = is_array($categorias ?? null) ? $categorias : [];
$datosMenu = json_encode($productos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$datosCategorias = json_encode($categorias, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
ob_start();
?>
<div class="tarjeta mb-3">
  <div class="d-flex flex-wrap gap-2 mb-3" id="fCategorias">
    <button type="button" class="chip-area activo" data-categoria-id="">Todas</button>
    <?php foreach ($categorias as $categoria): ?>
      <button type="button" class="chip-area" data-categoria-id="<?= (int) ($categoria['id'] ?? 0) ?>"><?= e($categoria['nombre'] ?? '') ?></button>
    <?php endforeach; ?>
  </div>
  <div class="barra-filtros">
    <div>
      <label class="form-label" for="fProducto">Buscar plato</label>
      <input id="fProducto" type="search" class="form-control" placeholder="Nombre o descripción">
    </div>
    <div>
      <label class="form-label" for="fDisponibilidad">Disponibilidad</label>
      <select id="fDisponibilidad" class="form-select"><option value="">Todos</option><option value="1">Disponibles</option><option value="0">No disponibles</option></select>
    </div>
    <div class="flex-grow-0 ms-auto">
      <button type="button" class="btn btn-olivo" id="btnNuevoProducto"><i class="bi bi-plus-lg me-1"></i>Nuevo producto</button>
    </div>
  </div>
</div>
<div class="row g-3" id="gridProductos"></div>
<p class="small text-secondary mt-3 mb-0">
  <span id="contador"></span> Los productos no se borran: se desactivan, para conservar el historial de ventas.
</p>
<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="tituloProducto" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><form class="modal-content" id="formProducto">
    <div class="modal-header"><h2 class="modal-title fs-5" id="tituloProducto">Nuevo producto</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
    <div class="modal-body">
      <div class="mb-3"><label class="form-label fw-semibold" for="pNombre">Nombre</label><input class="form-control" id="pNombre" name="nombre" required maxlength="100"></div>
      <div class="mb-3"><label class="form-label fw-semibold" for="pDescripcion">Descripción</label><textarea class="form-control" id="pDescripcion" name="descripcion" rows="3"></textarea></div>
      <div class="row g-3">
        <div class="col-sm-6"><label class="form-label fw-semibold" for="pCategoria">Categoría</label><select class="form-select" id="pCategoria" name="categoria_id" required></select></div>
        <div class="col-sm-6"><label class="form-label fw-semibold" for="pPrecio">Precio ($)</label><input class="form-control" id="pPrecio" name="precio" type="number" min="0.01" step="0.01" required inputmode="decimal" data-validar="decimal-positivo"></div>
      </div>
      <div class="mt-3"><label class="form-label fw-semibold" for="pImagen">Ruta de imagen</label><input class="form-control" id="pImagen" name="imagen" placeholder="platillo.jpg"><div class="form-text">Usa una imagen guardada dentro de /img/menu/.</div></div>
      <div class="mt-3"><label class="form-label fw-semibold" for="pArchivo">Subir imagen</label><input class="form-control" id="pArchivo" name="imagen" type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp"><div class="form-text">JPG, JPEG, PNG o WEBP (máx. 5 MB). Si eliges un archivo, reemplaza la ruta.</div></div>
      <div class="form-check form-switch mt-3"><input class="form-check-input" id="pDisponible" name="disponible" type="checkbox" value="1" checked><label class="form-check-label" for="pDisponible">Disponible</label></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-olivo" id="btnGuardarProducto">Guardar producto</button></div>
  </form></div>
</div>
<?php
$contenido = ob_get_clean();
$scripts_extra = '<script>window.PRODUCTOS = ' . ($datosMenu ?: '[]') . '; window.CATEGORIAS = ' . ($datosCategorias ?: '[]') . '; const CSRF = ' . json_encode(Csrf::token()) . ';</script>
<script>
(function () {
  const productos = window.PRODUCTOS || [];
  const categorias = window.CATEGORIAS || [];
  const grid = document.getElementById("gridProductos");
  const modal = new bootstrap.Modal(document.getElementById("modalProducto"));
  const form = document.getElementById("formProducto");
  let categoriaActiva = "";
  let editId = null;
  const esc = s => String(s == null ? "" : s).replace(/[&<>"\\u0027]/g, c => {
    if (c === "&") return "&amp;"; if (c === "<") return "&lt;"; if (c === ">") return "&gt;";
    if (c === "\\"") return "&quot;"; return "&#39;";
  });
  function poblarCategorias() {
    const select = document.getElementById("pCategoria");
    select.innerHTML = "";
    categorias.forEach(c => { const op = document.createElement("option"); op.value = c.id; op.textContent = c.nombre; select.appendChild(op); });
  }
  function pintar() {
    const q = document.getElementById("fProducto").value.trim().toLowerCase();
    const disponibilidad = document.getElementById("fDisponibilidad").value;
    const lista = productos.filter(p => (!categoriaActiva || String(p.categoria_id) === categoriaActiva)
      && (disponibilidad === "" || String(Number(p.disponible)) === disponibilidad)
      && (!q || String(p.nombre || "").toLowerCase().includes(q) || String(p.descripcion || "").toLowerCase().includes(q)));
    grid.innerHTML = lista.length ? lista.map(p => {
      const disponible = Number(p.disponible) === 1;
      const imagenUrl = p.imagen ? (String(p.imagen).startsWith("/img/menu/") ? p.imagen : "/img/menu/" + encodeURIComponent(p.imagen)) : "";
      const imagen = imagenUrl ? `<div class="prod-media"><img src="${esc(imagenUrl)}" alt="${esc(p.nombre)}" loading="lazy"></div>` : `<div class="prod-media" aria-hidden="true"><i class="bi bi-egg-fried"></i></div>`;
      return `<div class="col-sm-6 col-lg-4 col-xxl-3"><article class="prod-card ${disponible ? "" : "agotado"}">
        <div class="prod-img">${imagen}<span class="prod-cat">${esc(p.categoria_nombre || "Sin categoría")}</span>${disponible ? "" : `<span class="prod-agotado">No disponible</span>`}</div>
        <div class="prod-body"><div class="prod-nombre">${esc(p.nombre)}</div><div class="prod-desc">${esc(p.descripcion || "") || "&nbsp;"}</div><div class="prod-precio">$${Number(p.precio || 0).toFixed(2)}</div></div>
        <div class="prod-pie"><div class="form-check form-switch m-0">
          <input class="form-check-input" type="checkbox" role="switch" id="sw${p.id}" data-toggle="${p.id}" data-disponible="${disponible ? "1" : "0"}" ${disponible ? "checked" : ""}>
          <label class="form-check-label small" for="sw${p.id}">${disponible ? "Disponible" : "Agotado"}</label>
        </div><button type="button" class="btn btn-outline-olivo btn-icono" data-edit="${p.id}" aria-label="Editar ${esc(p.nombre)}"><i class="bi bi-pencil"></i></button>
        <button type="button" class="btn btn-outline-danger btn-icono" data-delete="${p.id}" aria-label="Eliminar ${esc(p.nombre)}"><i class="bi bi-trash"></i></button></div></article></div>`;
    }).join("") : `<div class="col-12"><div class="tarjeta text-center text-secondary py-5">No hay productos que coincidan con los filtros.</div></div>`;
    document.getElementById("contador").textContent = `${lista.length} de ${productos.length} productos.`;
  }
  function abrir(p) {
    editId = p ? Number(p.id) : null;
    form.reset();
    document.getElementById("tituloProducto").textContent = editId ? "Editar producto" : "Nuevo producto";
    if (p) {
      document.getElementById("pNombre").value = p.nombre || "";
      document.getElementById("pDescripcion").value = p.descripcion || "";
      document.getElementById("pCategoria").value = p.categoria_id || "";
      document.getElementById("pPrecio").value = p.precio || "";
      document.getElementById("pImagen").value = p.imagen || "";
      document.getElementById("pDisponible").checked = Number(p.disponible) === 1;
    } else document.getElementById("pDisponible").checked = true;
    modal.show();
  }
  document.getElementById("btnNuevoProducto").addEventListener("click", () => abrir(null));
  document.getElementById("fProducto").addEventListener("input", pintar);
  document.getElementById("fDisponibilidad").addEventListener("change", pintar);
  document.getElementById("fCategorias").addEventListener("click", async e => {
    const btn = e.target.closest("[data-categoria-id]"); if (!btn) return;
    categoriaActiva = btn.dataset.categoriaId || "";
    const categoriaSeleccionada = categoriaActiva;
    document.querySelectorAll("#fCategorias [data-categoria-id]").forEach(x => x.classList.toggle("activo", x === btn));
    try {
      const query = categoriaSeleccionada ? "?categoria_id=" + encodeURIComponent(categoriaSeleccionada) : "";
      const res = await API.get("/productos/listar" + query);
      if (categoriaActiva !== categoriaSeleccionada) return;
      if (!res.success) throw new Error(res.message || "No se pudieron cargar los productos.");
      productos.splice(0, productos.length, ...(res.productos || []));
      pintar();
    } catch (err) {
      if (categoriaActiva === categoriaSeleccionada) aviso("Error al filtrar productos: " + err.message, "danger");
    }
  });
  grid.addEventListener("click", async e => {
    const edit = e.target.closest("[data-edit]");
    const toggle = e.target.closest("[data-toggle]");
    const remove = e.target.closest("[data-delete]");
    if (edit) { const p = productos.find(x => Number(x.id) === Number(edit.dataset.edit)); if (p) abrir(p); }
    if (remove && window.confirm("¿Eliminar este producto?")) {
      try {
        const res = await API.post("/productos/eliminar/" + remove.dataset.delete, {csrf: CSRF});
        if (!res.success) { aviso(res.message || "No se pudo eliminar el producto.", "danger"); return; }
        const index = productos.findIndex(x => Number(x.id) === Number(remove.dataset.delete));
        if (index >= 0) productos.splice(index, 1);
        pintar(); aviso(res.message, "success");
      } catch (err) { aviso("Error al eliminar: " + err.message, "danger"); }
    }
    if (toggle) {
      const disponible = toggle.dataset.disponible === "1" ? "0" : "1";
      toggle.disabled = true;
      try {
        const res = await API.post("/productos/toggle/" + toggle.dataset.toggle, {disponible, csrf: CSRF});
        if (!res.success) { toggle.checked = toggle.dataset.disponible === "1"; aviso(res.message || "No se pudo actualizar disponibilidad.", "danger"); return; }
        const p = productos.find(x => Number(x.id) === Number(toggle.dataset.toggle));
        if (p) p.disponible = Number(disponible);
        pintar(); aviso(res.message, "success");
      } catch (err) { toggle.checked = toggle.dataset.disponible === "1"; aviso("Error al actualizar: " + err.message, "danger"); }
      finally { toggle.disabled = false; }
    }
  });
  form.addEventListener("submit", async e => {
    e.preventDefault();
    const btn = document.getElementById("btnGuardarProducto");
    if (!Validar.formulario(form)) return;
    const archivo = document.getElementById("pArchivo").files[0] || null;
    if (archivo) {
      const ext = (archivo.name.split(".").pop() || "").toLowerCase();
      if (!["jpg", "jpeg", "png", "webp"].includes(ext)) { document.getElementById("pArchivo").classList.add("is-invalid"); aviso("Formato no permitido. Usa .jpg, .jpeg, .png o .webp.", "danger"); return; }
      if (archivo.size > 5 * 1024 * 1024) { document.getElementById("pArchivo").classList.add("is-invalid"); aviso("La imagen supera el tamaño permitido (5 MB).", "danger"); return; }
    }
    const datos = Object.fromEntries(new FormData(form).entries());
    // Si se eligió un archivo se envía como imagen; si no, se envía la ruta de texto.
    datos.imagen = archivo ? archivo : document.getElementById("pImagen").value.trim();
    datos.disponible = document.getElementById("pDisponible").checked ? "1" : "0";
    datos.csrf = CSRF;
    conSpinner(btn);
    try {
      const res = await API.post(editId ? "/productos/actualizar/" + editId : "/productos/crear", datos);
      if (!res.success) { aviso(res.message || "No se pudo guardar el producto.", "danger"); return; }
      const id = Number(res.producto_id || editId);
      const p = {id, nombre: datos.nombre, descripcion: datos.descripcion, categoria_id: Number(datos.categoria_id), categoria_nombre: (categorias.find(c => Number(c.id) === Number(datos.categoria_id)) || {}).nombre || "", precio: Number(datos.precio), imagen: res.imagen !== undefined ? res.imagen : (archivo ? "" : datos.imagen), disponible: Number(datos.disponible)};
      const idx = productos.findIndex(x => Number(x.id) === id);
      if (idx >= 0) productos.splice(idx, 1, p); else productos.unshift(p);
      pintar(); modal.hide(); aviso(res.message, "success");
    } catch (err) { aviso("Error al guardar: " + err.message, "danger"); }
    finally { liberarSpinner(btn); }
  });
  poblarCategorias();
  pintar();
})();
</script>';
require_once __DIR__ . '/../layout.php';
render_app_layout($titulo, $pagina, $contenido, $scripts_extra);
