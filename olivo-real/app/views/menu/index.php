
<div class="tarjeta mb-3">
  <div class="d-flex flex-wrap gap-2 mb-3" id="chipsCat">
    <button type="button" class="chip-area activo" data-cat="Todas">Todas</button>
    <?php foreach ($categorias as $c): ?>
      <button type="button" class="chip-area" data-cat="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></button>
    <?php endforeach; ?>
  </div>
  <div class="barra-filtros">
    <div>
      <label class="form-label" for="fBuscar">Buscar plato</label>
      <input type="search" id="fBuscar" class="form-control" placeholder="Nombre o descripción">
    </div>
    <div>
      <label class="form-label" for="fDisp">Disponibilidad</label>
      <select id="fDisp" class="form-select">
        <option value="">Todos</option>
        <option value="1">Disponibles</option>
        <option value="0">No disponibles</option>
      </select>
    </div>
    <?php if ($puede_editar): ?>
      <div class="flex-grow-0 ms-auto">
        <button type="button" id="btnNuevo" class="btn btn-olivo"><i class="bi bi-plus-lg me-1"></i>Nuevo producto</button>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3" id="gridMenu"></div>
<p class="small text-secondary mt-3 mb-0">
  <span id="contador"></span> Los productos no se borran: se desactivan, para conservar el historial de ventas.
</p>

<?php if ($puede_editar): ?>
<!-- Modal: crear / editar producto -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="tituloProd" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formProducto" novalidate>
        <div class="modal-header">
          <h2 class="modal-title fs-5" id="tituloProd">Nuevo producto</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="producto_id" value="">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="pNombre">Nombre</label>
              <input type="text" class="form-control" id="pNombre" name="nombre" maxlength="100" required>
              <div class="invalid-feedback">Escribe el nombre del producto.</div>
            </div>
            <div class="col-12">
              <label class="form-label" for="pDesc">Descripción</label>
              <textarea class="form-control" id="pDesc" name="descripcion" rows="2" maxlength="255"></textarea>
            </div>
            <div class="col-7">
              <label class="form-label" for="pCat">Categoría</label>
              <select class="form-select" id="pCat" name="categoria" required>
                <?php foreach ($categorias as $c): ?>
                  <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-5">
              <label class="form-label" for="pPrecio">Precio ($)</label>
              <input type="number" class="form-control" id="pPrecio" name="precio" min="0" step="0.01" required>
              <div class="invalid-feedback">Precio válido (0 o más).</div>
            </div>
            <div class="col-12">
              <label class="form-label" for="pImagen">Imagen (opcional)</label>
              <div class="d-flex align-items-center gap-3">
                <div class="prod-img rounded" style="width:96px;height:96px;flex-shrink:0" id="previa"></div>
                <div class="flex-grow-1">
                  <input type="file" class="form-control" id="pImagen" name="imagen" accept="image/jpeg,image/png,image/webp">
                  <div class="small text-danger mt-1 d-none" id="errImg">Usa una imagen JPG, PNG o WebP de máximo 2 MB.</div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="pDisp" name="disponible" checked>
                <label class="form-check-label" for="pDisp">Disponible para pedidos</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-olivo">Guardar producto</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>window.DATOS = <?= json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>

<?php
$scripts_extra = <<<'JS'
<script>
(function () {
  const D = window.DATOS, P = D.productos;
  const ICONOS = {
    'Desayunos': 'bi-sunrise', 'Entradas': 'bi-egg-fried', 'Sopas': 'bi-droplet-half', 'Platos fuertes': 'bi-fire',
    'Cenas': 'bi-moon-stars', 'Postres': 'bi-cake2', 'Bebidas calientes': 'bi-cup-hot', 'Bebidas frías': 'bi-cup-straw'
  };
  const $ = id => document.getElementById(id);
  const money = n => '$' + Number(n).toFixed(2);
  const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const grid = $('gridMenu'), fBuscar = $('fBuscar'), fDisp = $('fDisp');
  let cat = 'Todas';

  // Siempre dibuja el icono de la categoría; la foto va encima y, si no carga, se oculta y queda el icono.
  const media = p => `<i class="bi ${ICONOS[p.cat] || 'bi-image'}"></i>` + (p.img
    ? `<img src="${esc(p.img)}" alt="${esc(p.n || '')}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover" onerror="this.style.display='none'">`
    : '');

  /* ---------- Carta (BACKEND: se generará en PHP desde la base de datos) ---------- */
  function pintar() {
    const q = fBuscar.value.trim().toLowerCase();
    const lista = P.filter(p => (cat === 'Todas' || p.cat === cat) &&
      (!q || (p.n + ' ' + p.d).toLowerCase().includes(q)) &&
      (fDisp.value === '' || String(p.disp) === fDisp.value));
    $('contador').textContent = lista.length + ' de ' + P.length + ' productos.';
    if (!lista.length) {
      grid.innerHTML = '<div class="col-12"><div class="tarjeta text-center text-secondary py-4">No hay productos que coincidan.</div></div>';
      return;
    }
    grid.innerHTML = lista.map(p => `
      <div class="col-sm-6 col-lg-4 col-xxl-3">
        <article class="prod-card ${p.disp ? '' : 'agotado'}">
          <div class="prod-img">
            <div class="prod-media">${media(p)}</div>
            <span class="prod-cat">${esc(p.cat)}</span>
            ${p.disp ? '' : '<span class="prod-agotado">No disponible</span>'}
          </div>
          <div class="prod-body">
            <div class="prod-nombre">${esc(p.n)}</div>
            <div class="prod-desc">${esc(p.d) || '&nbsp;'}</div>
            <div class="prod-precio">${money(p.p)}</div>
          </div>
          <div class="prod-pie">
            ${D.editar
              ? `<div class="form-check form-switch m-0">
                   <input class="form-check-input" type="checkbox" role="switch" id="sw${p.id}" data-disp="${p.id}" ${p.disp ? 'checked' : ''}>
                   <label class="form-check-label small" for="sw${p.id}">${p.disp ? 'Disponible' : 'Agotado'}</label>
                 </div>
                 <button type="button" class="btn btn-outline-olivo btn-icono" data-editar="${p.id}" aria-label="Editar ${esc(p.n)}"><i class="bi bi-pencil"></i></button>`
              : `<span class="estado ${p.disp ? 'estado-disponible' : 'estado-inactivo'}">${p.disp ? 'Disponible' : 'No disponible'}</span>`}
          </div>
        </article>
      </div>`).join('');
  }

  fBuscar.addEventListener('input', pintar);
  fDisp.addEventListener('change', pintar);
  $('chipsCat').addEventListener('click', e => {
    const c = e.target.closest('.chip-area');
    if (!c) return;
    cat = c.dataset.cat;
    document.querySelectorAll('#chipsCat .chip-area').forEach(x => x.classList.toggle('activo', x === c));
    pintar();
  });

  if (!D.editar) { pintar(); return; }

  /* ---------- Activar / desactivar (BACKEND: UPDATE productos SET disponible) ---------- */
  grid.addEventListener('change', e => {
    const s = e.target.closest('[data-disp]');
    if (!s) return;
    const p = P.find(x => x.id === +s.dataset.disp);
    p.disp = s.checked ? 1 : 0;
    pintar();
    aviso(p.n + (p.disp ? ' ahora está disponible' : ' se marcó como agotado') + ' (demo)');
  });
  grid.addEventListener('click', e => {
    const b = e.target.closest('[data-editar]');
    if (b) abrir(P.find(x => x.id === +b.dataset.editar));
  });

  /* ---------- Modal ---------- */
  const modal = new bootstrap.Modal($('modalProducto'));
  const form = $('formProducto'), el = form.elements;
  let imgActual = null, catActual = null;

  function previa() {
    $('previa').innerHTML = '<div class="prod-media">' + media({ img: imgActual, cat: catActual }) + '</div>';
  }

  function abrir(p) {
    form.reset();
    form.classList.remove('was-validated');
    $('errImg').classList.add('d-none');
    $('tituloProd').textContent = p ? 'Editar producto' : 'Nuevo producto';
    el.producto_id.value = p ? p.id : '';
    if (p) {
      el.nombre.value = p.n; el.descripcion.value = p.d; el.categoria.value = p.cat;
      el.precio.value = p.p; el.disponible.checked = !!p.disp;
    } else {
      el.disponible.checked = true;
    }
    imgActual = p ? p.img : null;
    catActual = el.categoria.value;
    previa();
    modal.show();
  }
  el.categoria.addEventListener('change', () => { catActual = el.categoria.value; previa(); });
  $('btnNuevo').addEventListener('click', () => abrir(null));

  // BACKEND: validar tipo y tamaño también en PHP y guardar la imagen en la carpeta img/menu/
  el.imagen.addEventListener('change', () => {
    const f = el.imagen.files[0];
    if (!f) return;
    if (!/^image\/(jpeg|png|webp)$/.test(f.type) || f.size > 2 * 1024 * 1024) {
      $('errImg').classList.remove('d-none');
      el.imagen.value = '';
      return;
    }
    $('errImg').classList.add('d-none');
    imgActual = URL.createObjectURL(f);
    previa();
  });

  form.addEventListener('submit', e => {
    e.preventDefault();
    form.classList.add('was-validated');
    if (!form.checkValidity()) return;
    const datos = { n: el.nombre.value.trim(), d: el.descripcion.value.trim(), cat: el.categoria.value,
                    p: parseFloat(el.precio.value), img: imgActual, disp: el.disponible.checked ? 1 : 0 };
    if (!datos.n) { el.nombre.value = ''; return; }
    if (el.producto_id.value) {
      Object.assign(P.find(x => x.id === +el.producto_id.value), datos);
    } else {
      datos.id = P.reduce((m, x) => Math.max(m, x.id), 0) + 1;
      P.push(datos);
    }
    pintar();
    modal.hide();
    aviso('Producto guardado (demo: todavía no se almacena en la base de datos)');
  });

  pintar();
})();
</script>
JS;
