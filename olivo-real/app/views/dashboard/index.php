
<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3">
    <div class="tarjeta"><div class="stat">
      <div class="stat-icono"><i class="bi bi-cash-stack"></i></div>
      <div><div class="stat-etiqueta">Ventas de hoy</div>
        <div class="stat-valor">$<?= number_format($ventas_hoy, 2) ?></div>
        <div class="stat-nota">Total cobrado</div></div>
    </div></div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="tarjeta"><div class="stat">
      <div class="stat-icono"><i class="bi bi-grid-3x3-gap"></i></div>
      <div><div class="stat-etiqueta">Mesas ocupadas</div>
        <div class="stat-valor"><?= $mesas_ocupadas ?> / <?= $mesas_total ?></div>
        <div class="stat-nota"><?= round($mesas_ocupadas / $mesas_total * 100) ?>% de ocupación</div></div>
    </div></div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="tarjeta"><div class="stat">
      <div class="stat-icono"><i class="bi bi-calendar-check"></i></div>
      <div><div class="stat-etiqueta">Reservas próximas</div>
        <div class="stat-valor"><?= $reservas_prox ?></div>
        <div class="stat-nota">Reservas del día</div></div>
    </div></div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="tarjeta"><div class="stat">
      <div class="stat-icono"><i class="bi bi-fire"></i></div>
      <div><div class="stat-etiqueta">Pedidos en cocina</div>
        <div class="stat-valor"><?= $pedidos_cocina ?></div>
        <div class="stat-nota">Pendientes y en curso</div></div>
    </div></div>
  </div>
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
          <li><span class="estado estado-<?= $clase ?>"><?= $nombre ?></span><strong><?= $cantidad ?></strong></li>
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
            <div><div class="fw-semibold"><?= htmlspecialchars($cliente) ?></div>
              <small class="text-secondary"><?= $hora ?> · <?= $mesa ?></small></div>
            <span class="estado estado-<?= $estado ?>"><?= ucfirst($estado) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <a href="reservas.php" class="small d-block mt-2" style="color:var(--verde)">Ver todas →</a>
    </div>
  </div>
</div>

<?php
$etiquetas = json_encode(array_keys($ventas_semana));
$valores   = json_encode(array_values($ventas_semana));
$scripts_extra = <<<HTML
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  new Chart(document.getElementById('graficoVentas'), {
    type: 'bar',
    data: { labels: $etiquetas, datasets: [{ label: 'Ventas ($)', data: $valores,
      backgroundColor: '#183D34', borderRadius: 6 }] },
    options: { plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, grid: { color: '#EEE9DC' } }, x: { grid: { display: false } } } }
  });
</script>
HTML;
