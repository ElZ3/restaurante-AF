<?php
$titulo = 'Reportes';
$pagina = 'reportes';

// Datos reales recibidos del controlador/PDO
$reporte = is_array($reporte ?? null) ? $reporte : [];
$ventas_semana = is_array($ventas_semana ?? null) ? $ventas_semana : [];

$tipos = [
    'ventas'    => 'Ventas por período',
    'productos' => 'Productos más vendidos',
    'pedidos'   => 'Pedidos por estado',
    'reservas'  => 'Reservas por estado',
    'pagos'     => 'Por método de pago'
];

$ventas_total    = (float) ($reporte['ventas'] ?? 0);
$cuentas_total   = (int) ($reporte['pedidos'] ?? $reporte['cuentas'] ?? 0);
$ticket_promedio = $cuentas_total > 0 ? ($ventas_total / $cuentas_total) : 0;

$desde_val = $_GET['desde'] ?? date('Y-m-d', strtotime('-6 days'));
$hasta_val = $_GET['hasta'] ?? date('Y-m-d');
$dias_periodo = max(1, (int) round((strtotime($hasta_val) - strtotime($desde_val)) / 86400) + 1);

ob_start();
?>
<div class="tarjeta mb-3">
  <form method="GET" action="/reportes" class="barra-filtros d-flex flex-wrap align-items-end gap-3">
    <div>
      <label class="form-label mb-1 fw-semibold" for="tipo">Tipo de reporte</label>
      <select id="tipo" name="tipo" class="form-select">
        <?php foreach ($tipos as $k => $t): ?>
          <option value="<?= $k ?>" <?= (($_GET['tipo'] ?? '') === $k) ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label mb-1 fw-semibold" for="desde">Desde</label>
      <input type="date" id="desde" name="desde" class="form-control" value="<?= htmlspecialchars($desde_val) ?>">
    </div>
    <div>
      <label class="form-label mb-1 fw-semibold" for="hasta">Hasta</label>
      <input type="date" id="hasta" name="hasta" class="form-control" value="<?= htmlspecialchars($hasta_val) ?>">
    </div>
    <div class="flex-grow-0">
      <button type="submit" id="btnGenerar" class="btn btn-olivo d-inline-flex align-items-center gap-1">
        <i class="bi bi-arrow-repeat"></i> Generar
      </button>
    </div>
    <div class="flex-grow-0 ms-auto">
      <button type="button" id="btnCsv" class="btn btn-outline-olivo d-inline-flex align-items-center gap-1">
        <i class="bi bi-download"></i> Exportar CSV
      </button>
    </div>
  </form>
</div>

<!-- Tarjetas KPI -->
<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3">
    <div class="tarjeta">
      <div class="stat">
        <div class="stat-icono"><i class="bi bi-cash-stack"></i></div>
        <div>
          <div class="stat-etiqueta">Total cobrado</div>
          <div class="stat-valor" style="font-size:1.4rem">$<?= number_format($ventas_total, 2) ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6 col-xl-3">
    <div class="tarjeta">
      <div class="stat">
        <div class="stat-icono"><i class="bi bi-receipt"></i></div>
        <div>
          <div class="stat-etiqueta">Cuentas pagadas</div>
          <div class="stat-valor" style="font-size:1.4rem"><?= $cuentas_total ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6 col-xl-3">
    <div class="tarjeta">
      <div class="stat">
        <div class="stat-icono"><i class="bi bi-calculator"></i></div>
        <div>
          <div class="stat-etiqueta">Ticket promedio</div>
          <div class="stat-valor" style="font-size:1.4rem">$<?= number_format($ticket_promedio, 2) ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6 col-xl-3">
    <div class="tarjeta">
      <div class="stat">
        <div class="stat-icono"><i class="bi bi-calendar3"></i></div>
        <div>
          <div class="stat-etiqueta">Días del período</div>
          <div class="stat-valor" style="font-size:1.4rem"><?= $dias_periodo ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Contenedor del Gráfico de Ventas -->
<div class="tarjeta mb-3">
  <div class="tarjeta-titulo">Ventas por día</div>
  <?php if (!empty($ventas_semana)): ?>
    <canvas id="graficoReporte" height="110"></canvas>
  <?php else: ?>
    <div class="py-4 text-center text-secondary">
      <i class="bi bi-bar-chart fs-1 d-block mb-2 text-muted"></i>
      No hay registros de ventas para el período seleccionado.
    </div>
  <?php endif; ?>
</div>

<?php
$contenido = ob_get_clean();

$etiquetas = json_encode(array_keys($ventas_semana), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$valores   = json_encode(array_values($ventas_semana), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

if (!empty($ventas_semana)) {
    $scripts_extra = <<<HTML
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  new Chart(document.getElementById('graficoReporte'), {
    type: 'bar',
    data: {
      labels: {$etiquetas},
      datasets: [{
        label: 'Ventas ($)',
        data: {$valores},
        backgroundColor: '#183D34',
        borderRadius: 6
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#EEE9DC' } },
        x: { grid: { display: false } }
      }
    }
  });
</script>
HTML;
} else {
    $scripts_extra = '';
}

require_once __DIR__ . '/layout.php';
render_app_layout($titulo, $pagina, $contenido, $scripts_extra);