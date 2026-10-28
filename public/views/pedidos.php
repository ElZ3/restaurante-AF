<?php
$titulo = 'Pedidos';
$pagina = 'pedidos';

// Datos recibidos desde la BD / Controlador
$pedidos    = $pedidos ?? $datos['pedidos'] ?? [];
$productos  = $productos ?? $datos['productos'] ?? [];
$mesas      = $mesas ?? [];
$categorias = $categorias ?? [];
$usuario    = $_SESSION['usuario_nombre'] ?? 'Mesero';

$estados = $estados ?? [
    'borrador'      => 'Borrador',
    'pendiente'     => 'Pendiente',
    'en_preparacion' => 'En preparación',
    'lista'         => 'Lista',
    'entregada'     => 'Entregada',
    'cancelado'     => 'Cancelado'
];

$datos_pantalla = [
    'pedidos'   => is_array($pedidos) ? $pedidos : [],
    'productos' => is_array($productos) ? $productos : [],
    'usuario'   => $usuario
];

$datos_json = json_encode($datos_pantalla, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

ob_start();
?>
<!-- Barra de Filtros -->
<div class="tarjeta mb-3">
  <div class="barra-filtros d-flex flex-wrap align-items-end gap-3">
    <div>
      <label class="form-label mb-1 fw-semibold" for="fEstado">Estado</label>
      <select id="fEstado" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($estados as $k => $t): ?>
          <option value="<?= $k ?>"><?= htmlspecialchars($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label mb-1 fw-semibold" for="fBuscar">Buscar</label>
      <input type="search" id="fBuscar" class="form-control" placeholder="N.º de mesa o de pedido">
    </div>
    <div class="flex-grow-0 ms-auto">
      <button type="button" id="btnNuevo" class="btn btn-olivo d-inline-flex align-items-center gap-1">
        <i class="bi bi-plus-lg"></i> Nuevo pedido
      </button>
    </div>
  </div>
</div>

<!-- Tabla de Pedidos -->
<div class="tarjeta">
  <div class="table-responsive">
    <table class="table tabla-olivo align-middle mb-0">
      <thead>
        <tr>
          <th>Pedido</th>
          <th>Mesa</th>
          <th>Mesero</th>
          <th>Hora</th>
          <th>Estado</th>
          <th>Total</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody id="tbodyPedidos"></tbody>
    </table>
  </div>
</div>

<!-- Modal: Detalle del pedido -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="detTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-5" id="detTitulo">Pedido</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p class="small mb-3 text-secondary" id="detInfo"></p>
        <div class="table-responsive">
          <table class="table tabla-olivo align-middle mb-0">
            <thead>
              <tr>
                <th>Producto</th>
                <th class="text-center">Cant.</th>
                <th class="text-end">Precio</th>
                <th class="text-end">Subtotal</th>
              </tr>
            </thead>
            <tbody id="detLineas"></tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between border-top pt-3 mt-2">
          <strong>Total</strong><strong class="fs-5" id="detTotal">$0.00</strong>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-olivo d-none" id="btnEnviarDet"><i class="bi bi-send me-1"></i>Enviar a cocina</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Nuevo pedido -->
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
            <label class="form-label fw-semibold" for="npMesa">Mesa</label>
            <select id="npMesa" class="form-select">
              <option value="">Elige una mesa</option>
              <?php foreach ($mesas as $m): ?>
                <?php 
                  $num = is_array($m) ? ($m['numero_mesa'] ?? $m[0] ?? $m['id']) : $m;
                  $cap = is_array($m) ? ($m['capacidad'] ?? $m[1] ?? '') : '';
                ?>
                <option value="<?= htmlspecialchars($num) ?>">
                  Mesa <?= htmlspecialchars($num) ?><?= $cap ? " · {$cap} personas" : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Elige una mesa para el pedido.</div>
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
            <div class="lista-productos border rounded p-2" id="npProductos" style="max-height: 300px; overflow-y: auto;"></div>
          </div>
          <div class="col-md-6">
            <h3 class="fs-6 fw-semibold mb-2">Resumen del pedido</h3>
            <div id="npCarrito" style="max-height: 250px; overflow-y: auto;"></div>
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

<?php
$contenido = ob_get_clean();

$scripts_extra = '<script>window.DATOS = ' . $datos_json . ';</script>' . "\n";
$scripts_extra .= <<<'JS'
<script>
(function () {
  const D = window.DATOS || {}, PED = D.pedidos || [], PROD = D.productos || [];
  const ETQ = {
    borrador: 'Borrador',
    pendiente: 'Pendiente',
    Abierta: 'Pendiente',
    en_preparacion: 'En preparación',
    'En Preparacion': 'En preparación',
    lista: 'Lista',
    Servida: 'Lista',
    entregada: 'Entregada',
    Pagada: 'Entregada',
    cancelado: 'Cancelado',
    Cancelada: 'Cancelado'
  };
  const clase = e => e === 'en_preparacion' || e === 'En Preparacion' ? 'preparacion' : (e === 'Servida' ? 'lista' : (e === 'Pagada' ? 'entregada' : (e === 'Abierta' ? 'pendiente' : e)));
  const money = n => '$' + (parseFloat(n) || 0).toFixed(2);
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const $ = id => document.getElementById(id);

  const tbody = $('tbodyPedidos'), fEstado = $('fEstado'), fBuscar = $('fBuscar');
  const mDet = new bootstrap.Modal($('modalDetalle')), mNuevo = new bootstrap.Modal($('modalNuevo'));
  let detalleId = null;

  function total(items) {
    if (!Array.isArray(items)) return 0;
    return items.reduce((s, i) => s + ((parseFloat(i.c || i.cantidad) || 1) * (parseFloat(i.p || i.precio || i.precio_unitario) || 0)), 0);
  }

  /* ---------- Rendereado de la tabla de pedidos ---------- */
  function pintar() {
    const q = fBuscar.value.trim().toLowerCase().replace('#', '');
    const estSel = fEstado.value;

    const lista = PED.filter(p => {
      const matchEstado = !estSel || p.estado === estSel || (clase(p.estado) === clase(estSel));
      const mesa = String(p.mesa || p.mesa_numero || '').toLowerCase();
      const id = String(p.id || '').toLowerCase();
      const matchBusqueda = !q || mesa.includes(q) || id.includes(q);
      return matchEstado && matchBusqueda;
    });

    if (!lista.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">No hay pedidos disponibles.</td></tr>';
      return;
    }

    tbody.innerHTML = lista.map(p => {
      const totalPedido = p.total ? parseFloat(p.total) : total(p.items || []);
      const mesaNum = p.mesa || p.mesa_numero || '—';
      const meseroNom = p.mesero || p.usuario_nombre || 'Sistema';
      const horaStr = p.hora || (p.creado_en ? p.creado_en.slice(11, 16) : '—');
      const stClase = clase(p.estado);
      const stNombre = ETQ[p.estado] || p.estado;

      return `
      <tr>
        <td class="fw-semibold">#${p.id}</td>
        <td>Mesa ${esc(mesaNum)}</td>
        <td>${esc(meseroNom)}</td>
        <td>${esc(horaStr)}</td>
        <td><span class="estado estado-${stClase}">${esc(stNombre)}</span></td>
        <td class="fw-semibold">${money(totalPedido)}</td>
        <td class="text-end text-nowrap">
          <button type="button" class="btn btn-outline-olivo btn-sm" data-ver="${p.id}"><i class="bi bi-eye me-1"></i>Ver</button>
          ${p.estado === 'borrador' ? `<button type="button" class="btn btn-olivo btn-sm ms-1" data-enviar="${p.id}"><i class="bi bi-send me-1"></i>Enviar a cocina</button>` : ''}
        </td>
      </tr>`;
    }).join('');
  }

  fEstado.addEventListener('change', pintar);
  fBuscar.addEventListener('input', pintar);
  tbody.addEventListener('click', e => {
    const v = e.target.closest('[data-ver]'), s = e.target.closest('[data-enviar]');
    if (v) ver(+v.dataset.ver);
    if (s) enviar(+s.dataset.enviar);
  });

  /* ---------- Modal Detalle ---------- */
  function ver(id) {
    const p = PED.find(x => x.id === id);
    if (!p) return;
    detalleId = id;
    const items = p.items || [];
    const totalPedido = p.total ? parseFloat(p.total) : total(items);
    const mesaNum = p.mesa || p.mesa_numero || '—';
    const meseroNom = p.mesero || p.usuario_nombre || 'Sistema';
    const horaStr = p.hora || (p.creado_en ? p.creado_en.slice(11, 16) : '—');

    $('detTitulo').textContent = 'Pedido #' + p.id + ' · Mesa ' + mesaNum;
    $('detInfo').innerHTML = 'Mesero: <strong>' + esc(meseroNom) + '</strong> · ' + esc(horaStr) +
      ' · <span class="estado estado-' + clase(p.estado) + '">' + (ETQ[p.estado] || esc(p.estado)) + '</span>';

    $('detLineas').innerHTML = items.length ? items.map(i => {
      const cant = i.c || i.cantidad || 1;
      const prec = i.p || i.precio || i.precio_unitario || 0;
      const nom = i.n || i.nombre || i.producto_nombre || 'Producto';
      const obs = i.o || i.notas || '';
      return `
      <tr>
        <td>${esc(nom)}${obs ? '<br><small class="text-secondary">' + esc(obs) + '</small>' : ''}</td>
        <td class="text-center">${cant}</td>
        <td class="text-end">${money(prec)}</td>
        <td class="text-end">${money(cant * prec)}</td>
      </tr>`;
    }).join('') : '<tr><td colspan="4" class="text-center text-secondary py-3">Sin ítems en el pedido.</td></tr>';

    $('detTotal').textContent = money(totalPedido);
    $('btnEnviarDet').classList.toggle('d-none', p.estado !== 'borrador');
    mDet.show();
  }

  function enviar(id) {
    const p = PED.find(x => x.id === id);
    if (!p || p.estado !== 'borrador') return;
    p.estado = 'pendiente';
    pintar();
    mDet.hide();
  }
  $('btnEnviarDet').addEventListener('click', () => enviar(detalleId));

  /* ---------- Modal Nuevo Pedido ---------- */
  let cart = [], cat = 'Todas';
  const selMesa = $('npMesa'), listaProd = $('npProductos'), listaCart = $('npCarrito');

  function pintarProductos() {
    const prods = PROD.filter(p => cat === 'Todas' || p.cat === cat || p.categoria_nombre === cat);
    if (!prods.length) {
      listaProd.innerHTML = '<p class="text-secondary small mb-0 py-3 text-center">No hay productos en esta categoría.</p>';
      return;
    }
    listaProd.innerHTML = prods.map(p => `
      <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
        <div><div class="fw-semibold small">${esc(p.n || p.nombre)}</div><small class="text-secondary">${money(p.p || p.precio)}</small></div>
        <button type="button" class="btn btn-outline-olivo btn-sm btn-icono" data-add="${p.id}" aria-label="Agregar"><i class="bi bi-plus-lg"></i></button>
      </div>`).join('');
  }

  function pintarCarrito() {
    if (!cart.length) {
      listaCart.innerHTML = '<p class="text-secondary small mb-0 py-3 text-center">Aún no hay productos. Agrégalos desde la lista.</p>';
    } else {
      listaCart.innerHTML = cart.map((l, i) => `
        <div class="py-2 border-bottom">
          <div class="d-flex justify-content-between align-items-center">
            <span class="fw-semibold small">${esc(l.n)}</span>
            <span class="small">${money(l.c * l.p)}</span>
          </div>
          <div class="d-flex align-items-center gap-2 mt-1">
            <button type="button" class="btn btn-outline-secondary btn-sm btn-icono" data-op="menos" data-i="${i}"><i class="bi bi-dash"></i></button>
            <strong>${l.c}</strong>
            <button type="button" class="btn btn-outline-secondary btn-sm btn-icono" data-op="mas" data-i="${i}"><i class="bi bi-plus"></i></button>
            <input type="text" class="form-control form-control-sm" data-obs="${i}" placeholder="Indicaciones" maxlength="255" value="${esc(l.o)}">
            <button type="button" class="btn btn-outline-danger btn-sm btn-icono" data-op="quitar" data-i="${i}"><i class="bi bi-trash"></i></button>
          </div>
        </div>`).join('');
    }
    $('npTotal').textContent = money(total(cart));
  }

  listaProd.addEventListener('click', e => {
    const b = e.target.closest('[data-add]');
    if (!b) return;
    const pr = PROD.find(x => x.id === +b.dataset.add);
    if (!pr) return;
    const idProd = pr.id;
    const nomProd = pr.n || pr.nombre;
    const precProd = parseFloat(pr.p || pr.precio) || 0;

    const ex = cart.find(l => l.id === idProd);
    if (ex) ex.c++; else cart.push({ id: idProd, n: nomProd, p: precProd, c: 1, o: '' });
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
    pintarProductos();
    pintarCarrito();
    mNuevo.show();
  });

  function guardar(estado) {
    if (!selMesa.value) { selMesa.classList.add('is-invalid'); return; }
    selMesa.classList.remove('is-invalid');
    if (!cart.length) return;
    
    const id = PED.reduce((m, p) => Math.max(m, p.id || 0), 100) + 1;
    const ahora = new Date();
    const hora = String(ahora.getHours()).padStart(2, '0') + ':' + String(ahora.getMinutes()).padStart(2, '0');
    
    PED.unshift({
      id: id,
      mesa: selMesa.value,
      mesero: D.usuario || 'Mesero',
      hora: hora,
      estado: estado,
      total: total(cart),
      items: cart.map(l => ({ n: l.n, c: l.c, p: l.p, o: l.o.trim() }))
    });
    
    pintar();
    mNuevo.hide();
  }

  $('btnBorrador').addEventListener('click', () => guardar('borrador'));
  $('btnEnviarNuevo').addEventListener('click', () => guardar('pendiente'));

  pintar();
})();
</script>
JS;

require_once __DIR__ . '/layout.php';
render_app_layout($titulo, $pagina, $contenido, $scripts_extra);