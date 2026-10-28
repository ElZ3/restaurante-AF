<?php
$titulo = 'Cuentas y pagos';
$pagina = 'cuentas';

// Recibir datos reales del controlador o inicializar vacíos para producción
$cuentas   = is_array($cuentas ?? null) ? $cuentas : [];
$historial = is_array($historial ?? null) ? $historial : [];
$cajero    = $cajero ?? ($_SESSION['usuario_nombre'] ?? 'Administrador');
$descuento = $descuento ?? true;

$datos = [
    'cuentas'   => $cuentas,
    'historial' => $historial,
    'cajero'    => $cajero,
    'descuento' => $descuento,
];

$datos_json = json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

ob_start();
?>
<div class="row g-3 mb-3">
  <div class="col-lg-4">
    <div class="tarjeta">
      <div class="tarjeta-titulo d-flex justify-content-between align-items-center">
        <span>Cuentas abiertas</span>
        <span class="badge bg-secondary rounded-pill" id="cntCuentas">0</span>
      </div>
      <div id="listaCuentas"></div>
    </div>
  </div>
  <div class="col-lg-8" id="detalleCuenta"></div>
</div>

<div class="tarjeta">
  <div class="tarjeta-titulo d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Pagos registrados hoy</span>
    <span class="fw-normal text-secondary">Total cobrado: <strong class="text-dark" id="totalCobrado">$0.00</strong></span>
  </div>
  <div class="table-responsive">
    <table class="table tabla-olivo align-middle mb-0">
      <thead>
        <tr>
          <th>Pago</th>
          <th>Pedido</th>
          <th>Mesa</th>
          <th>Método</th>
          <th>Monto</th>
          <th>Hora</th>
          <th>Cobró</th>
        </tr>
      </thead>
      <tbody id="tbodyPagos"></tbody>
    </table>
  </div>
</div>

<?php
$contenido = ob_get_clean();

$scripts_extra = '<script>window.DATOS = ' . $datos_json . ';</script>' . "\n";
$scripts_extra .= <<<'JS'
<script>
(function () {
  const D = window.DATOS || {}, CU = D.cuentas || [], HIS = D.historial || [];
  const ETQ = { pendiente: 'Pendiente', en_preparacion: 'En preparación', lista: 'Lista', entregada: 'Entregada', pagada: 'Pagada', Servida: 'Servida', Abierta: 'Abierta' };
  const METODOS = { efectivo: 'Efectivo', tarjeta: 'Tarjeta', transferencia: 'Transferencia' };
  const $ = id => document.getElementById(id);
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const clase = e => e === 'en_preparacion' ? 'preparacion' : e;

  const cents = n => Math.round((parseFloat(n) || 0) * 100);
  const money = c => '$' + (c / 100).toFixed(2);
  const sub = c => (c.items || []).reduce((s, i) => s + cents((i.c || i.cantidad || 0) * (i.p || i.precio || 0)), 0);
  const desc = c => Math.round(sub(c) * (c.desc || 0) / 100);
  const tot = c => sub(c) - desc(c);
  let sel = null;

  /* ---------- Lista de cuentas abiertas ---------- */
  function pintarLista() {
    const abiertas = CU.filter(c => !c.cerrada);
    $('cntCuentas').textContent = abiertas.length;
    $('listaCuentas').innerHTML = abiertas.length ? abiertas.map(c => `
      <button type="button" class="cuenta-item w-100 text-start p-2 mb-2 border rounded bg-light ${sel && sel.id === c.id ? 'activa border-success' : ''}" data-id="${c.id}">
        <div class="d-flex justify-content-between"><strong>Mesa ${esc(c.mesa || c.mesa_numero)}</strong><strong>${money(tot(c))}</strong></div>
        <div class="d-flex justify-content-between align-items-center mt-1">
          <small class="text-secondary">Pedido #${c.id}</small>
          ${c.pagada ? '<span class="estado estado-confirmada">Pagada</span>'
                     : '<span class="estado estado-' + clase(c.estado) + '">' + (ETQ[c.estado] || esc(c.estado)) + '</span>'}
        </div>
      </button>`).join('') : '<p class="text-secondary small mb-0 py-3 text-center">No hay cuentas abiertas.</p>';
  }

  $('listaCuentas').addEventListener('click', e => {
    const b = e.target.closest('[data-id]');
    if (!b) return;
    sel = CU.find(c => c.id === +b.dataset.id);
    pintarLista();
    pintarDetalle();
  });

  /* ---------- Historial de pagos ---------- */
  function pintarHistorial() {
    if (!HIS.length) {
      $('tbodyPagos').innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-3">No hay pagos registrados hoy.</td></tr>';
      $('totalCobrado').textContent = '$0.00';
      return;
    }
    $('tbodyPagos').innerHTML = HIS.map(h => `
      <tr>
        <td>#${esc(h.id)}</td>
        <td>#${esc(h.pedido || h.orden_id)}</td>
        <td>Mesa ${esc(h.mesa || h.mesa_numero)}</td>
        <td>${METODOS[h.metodo] || esc(h.metodo)}</td>
        <td class="fw-semibold">${money(cents(h.monto || h.total))}</td>
        <td>${esc(h.hora)}</td>
        <td>${esc(h.cajero)}</td>
      </tr>`).join('');
    $('totalCobrado').textContent = money(HIS.reduce((s, h) => s + cents(h.monto || h.total), 0));
  }

  /* ---------- Detalle de la cuenta ---------- */
  function pintarDetalle() {
    const box = $('detalleCuenta');
    if (!sel) {
      box.innerHTML = '<div class="tarjeta text-center text-secondary py-5"><i class="bi bi-receipt fs-1 d-block mb-2"></i>Elige una cuenta de la lista para ver su detalle y cobrar.</div>';
      return;
    }
    const c = sel, cobrable = (c.estado === 'entregada' || c.estado === 'Servida' || c.estado === 'abierta') && !c.pagada;
    const lineas = (c.items || []).map(i => {
      const cant = i.c || i.cantidad || 0;
      const prec = i.p || i.precio || 0;
      return `<tr><td>${esc(i.n || i.nombre)}</td><td class="text-center">${cant}</td>
          <td class="text-end">${money(cents(prec))}</td><td class="text-end">${money(cents(cant * prec))}</td></tr>`;
    }).join('');

    let filaDesc = '';
    if (cobrable && D.descuento) {
      filaDesc = `<div class="d-flex justify-content-between align-items-center mb-2">
        <label class="small text-secondary" for="descuento">Descuento (%)</label>
        <input type="number" id="descuento" class="form-control form-control-sm" style="width:90px" min="0" max="100" step="1" value="${c.desc || 0}">
      </div>`;
    } else if (c.desc) {
      filaDesc = `<div class="d-flex justify-content-between small mb-2"><span class="text-secondary">Descuento (${c.desc}%)</span><span>-${money(desc(c))}</span></div>`;
    }

    let pago;
    if (c.pagada) {
      pago = `<div class="alert alert-success small">Pago registrado: <strong>${METODOS[c.pago.metodo] || esc(c.pago.metodo)}</strong> · ${money(c.pago.recibido)}${c.pago.cambio ? ' · cambio ' + money(c.pago.cambio) : ''}</div>
              <button type="button" class="btn btn-olivo w-100" id="btnCerrar"><i class="bi bi-lock me-1"></i>Cerrar cuenta y liberar mesa</button>`;
    } else if (!cobrable) {
      pago = `<div class="alert alert-warning small mb-0">El pedido está <strong>${(ETQ[c.estado] || c.estado).toLowerCase()}</strong>. La cuenta solo se puede cobrar cuando el pedido esté listo o entregado.</div>`;
    } else {
      pago = `
        <label class="form-label">Método de pago</label>
        <div class="d-flex flex-wrap gap-2 mb-3">
          ${Object.keys(METODOS).map((k, n) => `<input type="radio" class="btn-check" name="metodo" id="m_${k}" value="${k}" ${n === 0 ? 'checked' : ''}><label class="btn btn-outline-olivo" for="m_${k}">${METODOS[k]}</label>`).join('')}
        </div>
        <div class="mb-2">
          <label class="form-label" for="monto">Monto recibido ($)</label>
          <input type="number" class="form-control" id="monto" step="0.01" min="0" inputmode="decimal">
          <div class="invalid-feedback" id="errMonto"></div>
        </div>
        <div class="d-flex justify-content-between mb-3"><span class="text-secondary">Cambio</span><strong id="cambio">$0.00</strong></div>
        <button type="button" class="btn btn-olivo w-100" id="btnPagar"><i class="bi bi-cash-coin me-1"></i>Registrar pago</button>`;
    }

    box.innerHTML = `
      <div class="row g-3">
        <div class="col-md-7"><div class="tarjeta">
          <div class="tarjeta-titulo">Mesa ${esc(c.mesa || c.mesa_numero)} · Pedido #${c.id}</div>
          <p class="small text-secondary">Mesero: ${esc(c.mesero || 'Sistema')} · ${esc(c.hora || '')}</p>
          <div class="table-responsive">
            <table class="table tabla-olivo"><thead><tr><th>Producto</th><th class="text-center">Cant.</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody>${lineas || '<tr><td colspan="4" class="text-center text-secondary">Sin ítems</td></tr>'}</tbody></table>
          </div>
          <div class="border-top pt-3 mt-2">
            <div class="d-flex justify-content-between small mb-2"><span class="text-secondary">Subtotal</span><span>${money(sub(c))}</span></div>
            ${filaDesc}
            <div class="d-flex justify-content-between"><strong>Total</strong><strong class="fs-5" id="lblTotal">${money(tot(c))}</strong></div>
          </div>
        </div></div>
        <div class="col-md-5"><div class="tarjeta">
          <div class="tarjeta-titulo">Registro de pago</div>
          ${pago}
        </div></div>
      </div>`;
    if (cobrable) actualizarPago(true);
  }

  const metodoSel = () => { const r = document.querySelector('input[name="metodo"]:checked'); return r ? r.value : null; };

  function actualizarPago(cambioMetodo) {
    const m = $('monto');
    if (!m || !sel) return;
    const t = tot(sel);
    if (metodoSel() !== 'efectivo') { m.value = (t / 100).toFixed(2); m.readOnly = true; }
    else { m.readOnly = false; if (cambioMetodo) m.value = ''; }
    $('cambio').textContent = money(Math.max(0, cents(parseFloat(m.value) || 0) - t));
    m.classList.remove('is-invalid');
  }

  const caja = $('detalleCuenta');
  caja.addEventListener('input', e => {
    if (e.target.id === 'descuento') {
      sel.desc = Math.min(100, Math.max(0, parseFloat(e.target.value) || 0));
      $('lblTotal').textContent = money(tot(sel));
      actualizarPago(false);
      pintarLista();
    }
    if (e.target.id === 'monto') actualizarPago(false);
  });
  caja.addEventListener('change', e => { if (e.target.name === 'metodo') actualizarPago(true); });
  caja.addEventListener('click', e => {
    if (e.target.closest('#btnPagar')) pagar();
    if (e.target.closest('#btnCerrar')) cerrar();
  });

  function pagar() {
    const c = sel, t = tot(c), m = $('monto');
    const recibido = cents(parseFloat(m.value) || 0);
    if (t <= 0) { m.classList.add('is-invalid'); $('errMonto').textContent = 'El total debe ser mayor que cero.'; return; }
    if (recibido < t) { m.classList.add('is-invalid'); $('errMonto').textContent = 'El monto recibido no cubre el total (' + money(t) + ').'; return; }
    $('btnPagar').disabled = true;
    const metodo = metodoSel();
    c.pagada = true;
    c.pago = { metodo: metodo, recibido: recibido, cambio: recibido - t };
    HIS.unshift({
      id: HIS.reduce((n, h) => Math.max(n, h.id || 0), 0) + 1,
      pedido: c.id,
      mesa: c.mesa || c.mesa_numero,
      metodo: metodo,
      monto: t / 100,
      hora: new Date().toTimeString().slice(0, 5),
      cajero: D.cajero || 'Cajero'
    });
    pintarLista();
    pintarDetalle();
    pintarHistorial();
  }

  function cerrar() {
    const c = sel;
    c.cerrada = true;
    sel = null;
    pintarLista();
    pintarDetalle();
  }

  pintarLista();
  pintarDetalle();
  pintarHistorial();
})();
</script>
JS;

require_once __DIR__ . '/layout.php';
render_app_layout($titulo, $pagina, $contenido, $scripts_extra);