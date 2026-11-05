<?php
$titulo = 'Inicio';
$pagina = 'dashboard';
$ventas_semana = $ventas_semana ?? [];
$pedidos_estado = $pedidos_estado ?? [];
$reservas_hoy = $reservas_hoy ?? [];
ob_start();
?>
<div class="row g-3 mb-3">
  <?php
  $indicadores = [
      ['Ventas de hoy', '$' . number_format((float) ($ventas_hoy ?? 0), 2), 'Órdenes completadas', 'bi-cash-stack'],
      ['Mesas ocupadas', (int) ($mesas_ocupadas ?? 0) . ' / ' . (int) ($mesas_total ?? 0), 'En servicio', 'bi-grid-3x3-gap'],
      ['Reservas próximas', (int) ($reservas_prox ?? 0), 'Pendientes para hoy', 'bi-calendar-check'],
      ['Pedidos en cocina', (int) ($pedidos_cocina ?? 0), 'Pendientes y en preparación', 'bi-fire'],
  ];
  foreach ($indicadores as [$etiqueta, $valor, $nota, $icono]):
  ?>
    <div class="col-6 col-xl-3">
      <div class="tarjeta">
        <div class="stat">
          <div class="stat-icono"><i class="bi <?= e($icono) ?>" aria-hidden="true"></i></div>
          <div><div class="stat-etiqueta"><?= e($etiqueta) ?></div>
            <div class="stat-valor"><?= e($valor) ?></div>
            <div class="stat-nota"><?= e($nota) ?></div></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<div class="row g-3">
  <div class="col-lg-6">
    <div class="tarjeta">
      <div class="tarjeta-titulo">Ventas últimos 7 días</div>
      <canvas id="graficoVentas" height="200"></canvas>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="tarjeta">
      <div class="tarjeta-titulo">Pedidos por estado</div>
      <ul class="lista-simple">
        <?php foreach ($pedidos_estado as [$nombre, $cantidad, $clase]): ?>
          <li><span class="estado estado-<?= e($clase) ?>"><?= e($nombre) ?></span><strong><?= (int) $cantidad ?></strong></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="tarjeta">
      <div class="tarjeta-titulo">Reservas de hoy</div>
      <ul class="lista-simple">
        <?php foreach ($reservas_hoy as [$hora, $cliente, $mesa, $estado]): ?>
          <li>
            <div><div class="fw-semibold"><?= e($cliente) ?></div>
              <small class="text-secondary"><?= e($hora) ?> · <?= e($mesa) ?></small></div>
            <span class="estado estado-<?= e($estado) ?>"><?= e(ucfirst($estado)) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <a href="/reservas" class="small d-block mt-2" style="color:var(--verde)">Ver todas →</a>
    </div>
  </div>
</div>
<?php
$contenido = ob_get_clean();
$etiquetas = json_encode(array_keys($ventas_semana), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$valores = json_encode(array_values($ventas_semana), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
if ($ventas_semana) {
    $scripts_extra = <<<HTML
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  new Chart(document.getElementById('graficoVentas'), {
    type: 'bar',
    data: { labels: $etiquetas, datasets: [{ label: 'Ventas ($)', data: $valores, backgroundColor: '#183D34', borderRadius: 6 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#EEE9DC' } }, x: { grid: { display: false } } } }
  });
</script>
HTML;
} else {
    $scripts_extra = '';
}
require_once __DIR__ . '/../layout.php';
render_app_layout($titulo, $pagina, $contenido, $scripts_extra);
