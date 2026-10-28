
<div class="tarjeta mb-3">
  <div class="barra-filtros">
    <div>
      <label class="form-label" for="fEstado">Estado</label>
      <select id="fEstado" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($estados as $k => $t): ?>
          <option value="<?= $k ?>"><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label" for="fBuscar">Buscar</label>
      <input type="search" id="fBuscar" class="form-control" placeholder="N.º de mesa o de pedido">
    </div>
    <div class="flex-grow-0 ms-auto">
      <button type="button" id="btnNuevo" class="btn btn-olivo"><i class="bi bi-plus-lg me-1"></i>Nuevo pedido</button>
    </div>
  </div>
</div>

<div class="tarjeta">
  <div class="table-responsive">
    <table class="table tabla-olivo">
      <thead>
        <tr><th>Pedido</th><th>Mesa</th><th>Mesero</th><th>Hora</th><th>Estado</th><th>Total</th><th class="text-end">Acciones</th></tr>
      </thead>
      <tbody id="tbodyPedidos"></tbody>
    </table>
  </div>
</div>

<!-- Modal: detalle del pedido -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="detTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-5" id="detTitulo">Pedido</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p class="small mb-3" id="detInfo"></p>
        <div class="table-responsive">
          <table class="table tabla-olivo">
            <thead><tr><th>Producto</th><th class="text-center">Cant.</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody id="detLineas"></tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between border-top pt-3 mt-2">
          <strong>Total</strong><strong class="fs-5" id="detTotal"></strong>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-olivo d-none" id="btnEnviarDet"><i class="bi bi-send me-1"></i>Enviar a cocina</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: nuevo pedido -->
<div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="tituloNuevo" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-5" id="tituloNuevo">Nuevo pedido</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label" for="npMesa">Mesa</label>
            <select id="npMesa" class="form-select">
              <option value="">Elige una mesa</option>
              <?php foreach ($mesas as [$num, $cap]): ?>
                <option value="<?= $num ?>">Mesa <?= $num ?> · <?= $cap ?> personas</option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Elige una mesa.</div>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-md-6">
            <div class="d-flex flex-wrap gap-2 mb-2" id="npCats">
              <button type="button" class="chip-area activo" data-cat="Todas">Todas</button>
              <?php foreach ($categorias as $c): ?>
                <button type="button" class="chip-area" data-cat="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></button>
              <?php endforeach; ?>
            </div>
            <div class="lista-productos" id="npProductos"></div>
          </div>
          <div class="col-md-6">
            <h3 class="fs-6 fw-semibold mb-2">Pedido</h3>
            <div id="npCarrito"></div>
            <div class="d-flex justify-content-between border-top pt-3 mt-3">
              <strong>Total</strong><strong class="fs-5" id="npTotal">$0.00</strong>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-outline-olivo" id="btnBorrador">Guardar borrador</button>
        <button type="button" class="btn btn-olivo" id="btnEnviarNuevo"><i class="bi bi-send me-1"></i>Enviar a cocina</button>
      </div>
    </div>
  </div>
</div>

<script>window.DATOS = <?= json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>

<?php
$scripts_extra = <<<'JS'
<script>
(function () {
  const D = window.DATOS, PED = D.pedidos, PROD = D.productos;
  const ETQ = { borrador: 'Borrador', pendiente: 'Pendiente', en_preparacion: 'En preparación', lista: 'Lista', entregada: 'Entregada', cancelado: 'Cancelado' };
  const clase = e => e === 'en_preparacion' ? 'preparacion' : e;
  const money = n => '$' + n.toFixed(2);
  const total = items => items.reduce((s, i) => s + i.c * i.p, 0);
  const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const $ = id => document.getElementById(id);

  const tbody = $('tbodyPedidos'), fEstado = $('fEstado'), fBuscar = $('fBuscar');
  const mDet = new bootstrap.Modal($('modalDetalle')), mNuevo = new bootstrap.Modal($('modalNuevo'));
  let detalleId = null;

  /* ---------- Tabla de pedidos (BACKEND: esto se genera en PHP desde la base de datos) ---------- */
  function pintar() {
    const q = fBuscar.value.trim().replace('#', '');
    const lista = PED.filter(p => (!fEstado.value || p.estado === fEstado.value) &&
                                  (!q || String(p.mesa) === q || String(p.id) === q));
    if (!lista.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">No hay pedidos que coincidan.</td></tr>';
      return;
    }
    tbody.innerHTML = lista.map(p => `
      <tr>
        <td class="fw-semibold">#${p.id}</td>
        <td>Mesa ${p.mesa}</td>
        <td>${esc(p.mesero)}</td>
        <td>${p.hora}</td>
        <td><span class="estado estado-${clase(p.estado)}">${ETQ[p.estado]}</span></td>
        <td class="fw-semibold">${money(total(p.items))}</td>
        <td class="text-end text-nowrap">
          <button type="button" class="btn btn-outline-olivo btn-sm" data-ver="${p.id}"><i class="bi bi-eye me-1"></i>Ver</button>
          ${p.estado === 'borrador' ? `<button type="button" class="btn btn-olivo btn-sm ms-1" data-enviar="${p.id}"><i class="bi bi-send me-1"></i>Enviar a cocina</button>` : ''}
        </td>
      </tr>`).join('');
  }
  fEstado.addEventListener('change', pintar);
  fBuscar.addEventListener('input', pintar);
  tbody.addEventListener('click', e => {
    const v = e.target.closest('[data-ver]'), s = e.target.closest('[data-enviar]');
    if (v) ver(+v.dataset.ver);
    if (s) enviar(+s.dataset.enviar);
  });

  /* ---------- Detalle ---------- */
  function ver(id) {
    const p = PED.find(x => x.id === id);
    if (!p) return;
    detalleId = id;
    $('detTitulo').textContent = 'Pedido #' + p.id + ' · Mesa ' + p.mesa;
    $('detInfo').innerHTML = 'Mesero: <strong>' + esc(p.mesero) + '</strong> · ' + p.hora +
      ' · <span class="estado estado-' + clase(p.estado) + '">' + ETQ[p.estado] + '</span>';
    $('detLineas').innerHTML = p.items.map(i => `
      <tr>
        <td>${esc(i.n)}${i.o ? '<br><small class="text-secondary">' + esc(i.o) + '</small>' : ''}</td>
        <td class="text-center">${i.c}</td>
        <td class="text-end">${money(i.p)}</td>
        <td class="text-end">${money(i.c * i.p)}</td>
      </tr>`).join('');
    $('detTotal').textContent = money(total(p.items));
    $('btnEnviarDet').classList.toggle('d-none', p.estado !== 'borrador');
    mDet.show();
  }

  function enviar(id) {
    const p = PED.find(x => x.id === id);
    if (!p || p.estado !== 'borrador') return;
    p.estado = 'pendiente'; // BACKEND: UPDATE pedidos + INSERT en historial_pedido
    pintar();
    mDet.hide();
    aviso('Pedido #' + id + ' enviado a cocina (demo)');
  }
  $('btnEnviarDet').addEventListener('click', () => enviar(detalleId));

  /* ---------- Nuevo pedido ---------- */
  let cart = [], cat = 'Todas';
  const selMesa = $('npMesa'), listaProd = $('npProductos'), listaCart = $('npCarrito');

  function pintarProductos() {
    listaProd.innerHTML = PROD.filter(p => cat === 'Todas' || p.cat === cat).map(p => `
      <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
        <div><div class="fw-semibold small">${esc(p.n)}</div><small class="text-secondary">${money(p.p)}</small></div>
        <button type="button" class="btn btn-outline-olivo btn-icono" data-add="${p.id}" aria-label="Agregar ${esc(p.n)}"><i class="bi bi-plus-lg"></i></button>
      </div>`).join('');
  }

  function pintarCarrito() {
    if (!cart.length) {
      listaCart.innerHTML = '<p class="text-secondary small mb-0">Aún no hay productos. Agrégalos desde la lista.</p>';
    } else {
      listaCart.innerHTML = cart.map((l, i) => `
        <div class="py-2 border-bottom">
          <div class="d-flex justify-content-between align-items-center">
            <span class="fw-semibold small">${esc(l.n)}</span>
            <span class="small">${money(l.c * l.p)}</span>
          </div>
          <div class="d-flex align-items-center gap-2 mt-1">
            <button type="button" class="btn btn-outline-secondary btn-icono" data-op="menos" data-i="${i}" aria-label="Quitar una unidad"><i class="bi bi-dash"></i></button>
            <strong>${l.c}</strong>
            <button type="button" class="btn btn-outline-secondary btn-icono" data-op="mas" data-i="${i}" aria-label="Agregar una unidad"><i class="bi bi-plus"></i></button>
            <input type="text" class="form-control form-control-sm" data-obs="${i}" placeholder="Indicaciones" maxlength="255" value="${esc(l.o)}">
            <button type="button" class="btn btn-outline-danger btn-icono" data-op="quitar" data-i="${i}" aria-label="Quitar producto"><i class="bi bi-trash"></i></button>
          </div>
        </div>`).join('');
    }
    $('npTotal').textContent = money(total(cart));
  }

  listaProd.addEventListener('click', e => {
    const b = e.target.closest('[data-add]');
    if (!b) return;
    const pr = PROD.find(x => x.id === +b.dataset.add);
    const ex = cart.find(l => l.id === pr.id);
    if (ex) ex.c++; else cart.push({ id: pr.id, n: pr.n, p: pr.p, c: 1, o: '' });
    pintarCarrito();
  });
  listaCart.addEventListener('click', e => {
    const b = e.target.closest('[data-op]');
    if (!b) return;
    const i = +b.dataset.i;
    if (b.dataset.op === 'mas') cart[i].c++;
    if (b.dataset.op === 'menos') cart[i].c = Math.max(1, cart[i].c - 1);
    if (b.dataset.op === 'quitar') cart.splice(i, 1);
    pintarCarrito();
  });
  listaCart.addEventListener('input', e => {
    if (e.target.dataset.obs !== undefined) cart[+e.target.dataset.obs].o = e.target.value;
  });
  $('npCats').addEventListener('click', e => {
    const c = e.target.closest('.chip-area');
    if (!c) return;
    cat = c.dataset.cat;
    document.querySelectorAll('#npCats .chip-area').forEach(x => x.classList.toggle('activo', x === c));
    pintarProductos();
  });

  $('btnNuevo').addEventListener('click', () => {
    cart = []; cat = 'Todas';
    selMesa.value = ''; selMesa.classList.remove('is-invalid');
    document.querySelectorAll('#npCats .chip-area').forEach(c => c.classList.toggle('activo', c.dataset.cat === 'Todas'));
    pintarProductos(); pintarCarrito();
    mNuevo.show();
  });

  // BACKEND: validar en PHP, guardar en pedidos + detalle_pedido (con el precio actual) y registrar historial_pedido
  function guardar(estado) {
    if (!selMesa.value) { selMesa.classList.add('is-invalid'); return; }
    selMesa.classList.remove('is-invalid');
    if (!cart.length) { aviso('Agrega al menos un producto al pedido.', 'warning'); return; }
    const id = PED.reduce((m, p) => Math.max(m, p.id), 100) + 1;
    const ahora = new Date();
    const hora = String(ahora.getHours()).padStart(2, '0') + ':' + String(ahora.getMinutes()).padStart(2, '0');
    PED.unshift({ id: id, mesa: +selMesa.value, mesero: D.usuario, hora: hora, estado: estado,
                  items: cart.map(l => ({ n: l.n, c: l.c, p: l.p, o: l.o.trim() })) });
    pintar();
    mNuevo.hide();
    aviso(estado === 'borrador' ? 'Pedido guardado como borrador (demo)' : 'Pedido enviado a cocina (demo)');
  }
  $('btnBorrador').addEventListener('click', () => guardar('borrador'));
  $('btnEnviarNuevo').addEventListener('click', () => guardar('pendiente'));

  pintar();
})();
</script>
JS;
