
<div class="tarjeta mb-3">
  <div class="barra-filtros">
    <div>
      <label class="form-label" for="tipo">Tipo de reporte</label>
      <select id="tipo" class="form-select">
        <?php foreach ($tipos as $k => $t): ?><option value="<?= $k ?>"><?= $t ?></option><?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label" for="desde">Desde</label>
      <input type="date" id="desde" class="form-control">
    </div>
    <div>
      <label class="form-label" for="hasta">Hasta</label>
      <input type="date" id="hasta" class="form-control">
    </div>
    <div class="flex-grow-0">
      <button type="button" id="btnGenerar" class="btn btn-olivo"><i class="bi bi-arrow-repeat me-1"></i>Generar</button>
    </div>
    <div class="flex-grow-0 ms-auto">
      <button type="button" id="btnCsv" class="btn btn-outline-olivo"><i class="bi bi-download me-1"></i>Exportar CSV</button>
    </div>
  </div>
</div>

<div id="resultado"></div>

<script>window.DATOS = <?= json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>

<?php
$scripts_extra = <<<'JS'
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  const D = window.DATOS, DIAS = D.dias, ULT = DIAS.length - 1;
  const PROD = D.productos.map(p => ({ id: p[0], n: p[1], c: Math.round(p[2] * 100) }));
  const MET = ['efectivo', 'tarjeta', 'tarjeta', 'transferencia', 'efectivo'];
  const MN = { efectivo: 'Efectivo', tarjeta: 'Tarjeta', transferencia: 'Transferencia' };
  const $ = id => document.getElementById(id);
  const money = c => '$' + (c / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const fmt = f => f.split('-').reverse().join('/');
  const suma = (arr, fn) => arr.reduce((s, x) => s + fn(x), 0);
  let grafico = null, actual = null;

  /* ---------- Datos de demostración (BACKEND: reemplazar por consultas SQL) ---------- */
  const uds = (d, p) => ((d * 7 + p * 13 + (d * p) % 5) % 9) + 1;
  function dia(i) {
    const lin = PROD.map(p => ({ p: p, u: uds(i, p.id) }));
    const venta = suma(lin, l => l.u * l.p.c);
    const n = Math.ceil(suma(lin, l => l.u) / 3);
    const base = Math.floor(venta / n), mov = [];
    for (let k = 0; k < n; k++) {
      mov.push({ id: 1000 + i * 30 + k, mesa: ((k * 5 + i) % 12) + 1, metodo: MET[(k + i) % 5],
                 monto: k === n - 1 ? venta - base * (n - 1) : base, f: DIAS[i],
                 hora: String(11 + (k % 11)).padStart(2, '0') + ':' + String((k * 37 + i * 11) % 60).padStart(2, '0') });
    }
    return { lin: lin, venta: venta, n: n, mov: mov };
  }

  /* ---------- Piezas de la interfaz ---------- */
  const kpi = (icono, etiqueta, valor) => `<div class="col-6 col-xl-3"><div class="tarjeta"><div class="stat">
      <div class="stat-icono"><i class="bi ${icono}"></i></div>
      <div><div class="stat-etiqueta">${etiqueta}</div><div class="stat-valor" style="font-size:1.4rem">${valor}</div></div>
    </div></div></div>`;
  const tabla = (cab, filas) => '<div class="table-responsive"><table class="table tabla-olivo"><thead><tr>' +
    cab.map(c => '<th>' + c + '</th>').join('') + '</tr></thead><tbody>' +
    filas.map(f => '<tr>' + f.map(c => '<td>' + c + '</td>').join('') + '</tr>').join('') + '</tbody></table></div>';
  const panel = (titulo, cuerpo) => '<div class="tarjeta mb-3"><div class="tarjeta-titulo">' + titulo + '</div>' + cuerpo + '</div>';
  const kpis = items => '<div class="row g-3 mb-3">' + items.join('') + '</div>';
  const cent = c => (c / 100).toFixed(2);

  /* ---------- Reportes ---------- */
  function rVentas(ix) {
    const ds = ix.map(i => Object.assign({ f: DIAS[i] }, dia(i)));
    const total = suma(ds, d => d.venta), cuentas = suma(ds, d => d.n);
    const html = kpis([kpi('bi-cash-stack', 'Total cobrado', money(total)), kpi('bi-receipt', 'Cuentas pagadas', cuentas),
                       kpi('bi-calculator', 'Ticket promedio', money(Math.round(total / cuentas))), kpi('bi-calendar3', 'Días del período', ds.length)]) +
      panel('Ventas por día', '<canvas id="grafico" height="110"></canvas>') +
      panel('Detalle diario', tabla(['Fecha', 'Cuentas pagadas', 'Total cobrado'],
        ds.map(d => [fmt(d.f), d.n, '<strong>' + money(d.venta) + '</strong>'])));
    return { html: html,
      csv: [['Fecha', 'Cuentas pagadas', 'Total cobrado']].concat(ds.map(d => [fmt(d.f), d.n, cent(d.venta)])),
      after: () => { grafico = new Chart($('grafico'), { type: 'bar',
        data: { labels: ds.map(d => fmt(d.f).slice(0, 5)), datasets: [{ label: 'Ventas ($)', data: ds.map(d => d.venta / 100), backgroundColor: '#183D34', borderRadius: 6 }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#EEE9DC' } }, x: { grid: { display: false } } } } }); } };
  }

  function rProductos(ix) {
    const ag = PROD.map(p => { const u = suma(ix, i => uds(i, p.id)); return { n: p.n, u: u, m: u * p.c }; })
                   .sort((a, b) => b.u - a.u);
    const max = ag[0].u;
    return { html: panel('Productos más vendidos', tabla(['#', 'Producto', 'Unidades', 'Monto vendido', 'Participación'],
        ag.map((a, k) => [k + 1, a.n, '<strong>' + a.u + '</strong>', money(a.m),
          '<div class="barra-h"><span style="width:' + Math.round(a.u / max * 100) + '%"></span></div>']))),
      csv: [['Producto', 'Unidades', 'Monto vendido']].concat(ag.map(a => [a.n, a.u, cent(a.m)])) };
  }

  function rPedidos(ix) {
    const hoy = ix.includes(ULT);
    const ent = suma(ix, i => dia(i).n), can = suma(ix, i => (i % 5 === 0 ? 1 : 0));
    const prep = hoy ? 2 : 0, pend = hoy ? 2 : 0;
    const tot = ent + can + prep + pend, tiempo = Math.round(suma(ix, i => 18 + (i % 7)) / ix.length);
    const filas = [['Entregados', ent], ['En preparación', prep], ['Pendientes', pend], ['Cancelados', can]];
    return { html: kpis([kpi('bi-bag-check', 'Pedidos en total', tot), kpi('bi-check2-circle', 'Entregados', ent),
                         kpi('bi-x-circle', 'Cancelados', can), kpi('bi-stopwatch', 'Tiempo de atención', tiempo + ' min')]) +
      panel('Pedidos por estado', tabla(['Estado', 'Cantidad', 'Porcentaje'], filas.map(f => [f[0], f[1], tot ? Math.round(f[1] / tot * 100) + '%' : '0%']))),
      csv: [['Estado', 'Cantidad']].concat(filas) };
  }

  function rReservas(ix) {
    const hoy = ix.includes(ULT);
    const at = suma(ix, i => 3 + (i % 4)), ca = suma(ix, i => (i % 3 === 0 ? 1 : 0)), co = hoy ? 3 : 0;
    const filas = [['Atendidas', at], ['Confirmadas (por atender)', co], ['Canceladas', ca]];
    const tot = at + ca + co;
    return { html: kpis([kpi('bi-calendar-check', 'Reservas en total', tot), kpi('bi-check-all', 'Atendidas', at),
                         kpi('bi-calendar2-event', 'Confirmadas', co), kpi('bi-x-circle', 'Canceladas', ca)]) +
      panel('Reservas por estado', tabla(['Estado', 'Cantidad', 'Porcentaje'], filas.map(f => [f[0], f[1], tot ? Math.round(f[1] / tot * 100) + '%' : '0%']))),
      csv: [['Estado', 'Cantidad']].concat(filas) };
  }

  function rPagos(ix) {
    const mov = [].concat.apply([], ix.map(i => dia(i).mov));
    const por = {}; mov.forEach(m => { por[m.metodo] = por[m.metodo] || { n: 0, t: 0 }; por[m.metodo].n++; por[m.metodo].t += m.monto; });
    const total = suma(mov, m => m.monto);
    const recientes = mov.slice().reverse().slice(0, 20);
    return { html: kpis([kpi('bi-cash-coin', 'Total cobrado', money(total)), kpi('bi-list-check', 'Movimientos', mov.length)]) +
      panel('Por método de pago', tabla(['Método', 'Pagos', 'Monto'], Object.keys(por).map(k => [MN[k], por[k].n, '<strong>' + money(por[k].t) + '</strong>']))) +
      panel('Últimos movimientos (hasta 20)', tabla(['Pago', 'Fecha', 'Hora', 'Mesa', 'Método', 'Monto'],
        recientes.map(m => ['#' + m.id, fmt(m.f), m.hora, 'Mesa ' + m.mesa, MN[m.metodo], money(m.monto)]))),
      csv: [['Pago', 'Fecha', 'Hora', 'Mesa', 'Método', 'Monto']].concat(mov.map(m => [m.id, fmt(m.f), m.hora, m.mesa, MN[m.metodo], cent(m.monto)])) };
  }

  /* ---------- Generar y exportar ---------- */
  function generar() {
    const d = $('desde').value, h = $('hasta').value, caja = $('resultado');
    if (grafico) { grafico.destroy(); grafico = null; }
    actual = null;
    const aviso1 = t => caja.innerHTML = '<div class="alert alert-warning mb-0">' + t + '</div>';
    if (!d || !h) return aviso1('Elige las fechas "desde" y "hasta".');
    if (d > h) return aviso1('La fecha "desde" no puede ser posterior a "hasta".');
    const ix = []; DIAS.forEach((f, i) => { if (f >= d && f <= h) ix.push(i); });
    if (!ix.length) return aviso1('No hay datos en ese rango. Elige fechas entre ' + fmt(DIAS[0]) + ' y ' + fmt(DIAS[ULT]) + '.');
    const t = $('tipo').value;
    const out = { ventas: rVentas, productos: rProductos, pedidos: rPedidos, reservas: rReservas, pagos: rPagos }[t](ix);
    caja.innerHTML = out.html;
    if (out.after) out.after();
    actual = { csv: out.csv, nombre: 'reporte_' + t + '_' + d + '_' + h + '.csv' };
  }

  function descargar() {
    if (!actual) { aviso('Primero genera un reporte con datos.', 'warning'); return; }
    const txt = '\ufeff' + actual.csv.map(f => f.map(c => '"' + String(c).replace(/"/g, '""') + '"').join(',')).join('\r\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([txt], { type: 'text/csv;charset=utf-8' }));
    a.download = actual.nombre;
    a.click();
    URL.revokeObjectURL(a.href);
  }

  $('desde').min = $('hasta').min = DIAS[0];
  $('desde').max = $('hasta').max = DIAS[ULT];
  $('hasta').value = DIAS[ULT];
  $('desde').value = DIAS[ULT - 6];
  $('btnGenerar').addEventListener('click', generar);
  $('btnCsv').addEventListener('click', descargar);
  generar();
})();
</script>
JS;
