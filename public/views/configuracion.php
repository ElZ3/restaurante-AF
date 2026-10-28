<?php
$titulo = 'Configuración';
$pagina = 'config';
$areas = is_array($areas ?? null) ? $areas : [];
$mesas = is_array($mesas ?? null) ? $mesas : [];
$estados = ['disponible', 'reservada', 'ocupada', 'fuera_servicio'];
$mesasPorArea = [];
foreach ($mesas as $mesa) {
    $areaId = (string) ($mesa['area_id'] ?? '');
    $mesasPorArea[$areaId] = ($mesasPorArea[$areaId] ?? 0) + 1;
}
ob_start();
?>
<p class="text-secondary mb-3">Áreas del restaurante y distribución de las mesas.</p>
<div class="row g-3 mb-3">
  <?php foreach ($estados as $estado): ?>
    <?php $cantidadEstado = count(array_filter($mesas, static function ($mesa) use ($estado) { return strtolower((string) ($mesa['estado'] ?? '')) === $estado; })); ?>
    <div class="col-6 col-xl-3">
      <section class="tarjeta">
        <div class="stat">
          <div class="stat-icono"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i></div>
          <div><div class="stat-etiqueta"><?= e(ucfirst(str_replace('_', ' ', $estado))) ?></div><div class="stat-valor"><?= $cantidadEstado ?></div></div>
        </div>
      </section>
    </div>
  <?php endforeach; ?>
</div>
<section class="tarjeta">
  <h2 class="tarjeta-titulo">Áreas</h2>
  <?php if (!$areas): ?>
    <p class="text-secondary mb-0">No hay áreas registradas.</p>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table tabla-olivo align-middle">
        <thead><tr><th>Área</th><th>Descripción</th><th>Mesas asignadas</th></tr></thead>
        <tbody>
        <?php foreach ($areas as $area): ?>
          <tr>
            <td class="fw-semibold"><?= e($area['nombre'] ?? '') ?></td>
            <td><?= e($area['descripcion'] ?? '—') ?></td>
            <td><?= (int) ($mesasPorArea[(string) ($area['id'] ?? '')] ?? 0) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
<?php
$contenido = ob_get_clean();
require_once __DIR__ . '/layout.php';
render_app_layout($titulo, $pagina, $contenido);
