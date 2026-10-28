<?php
$titulo = 'Mesas y áreas';
$pagina = 'mesas';
$mesas = is_array($mesas ?? null) ? $mesas : [];
$estados = ['libre', 'ocupada', 'reservada', 'mantenimiento'];
ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
  <div><p class="text-secondary mb-0">Consulta el estado y la capacidad de cada mesa.</p></div>
  <a href="/mesas/nueva" class="btn btn-olivo"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nueva mesa</a>
</div>
<div class="tarjeta mb-3">
  <div class="resumen-mesas">
    <?php foreach ($estados as $estado): ?>
      <?php $cantidadEstado = count(array_filter($mesas, static function ($mesa) use ($estado) { return strtolower((string) ($mesa['estado'] ?? '')) === $estado; })); ?>
      <span><span class="estado estado-<?= e($estado) ?>"><?= e(ucfirst(str_replace('_', ' ', $estado))) ?></span> <strong><?= $cantidadEstado ?></strong></span>
    <?php endforeach; ?>
  </div>
</div>
<?php if (!$mesas): ?>
  <div class="tarjeta text-center text-secondary py-5">No hay mesas registradas.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($mesas as $mesa): ?>
      <?php
      $estadoMesa = strtolower((string) ($mesa['estado'] ?? 'Libre'));
      if (!in_array($estadoMesa, $estados, true)) $estadoMesa = 'libre';
      ?>
      <div class="col-6 col-md-4 col-xl-3">
        <article class="mesa-card mesa-<?= e($estadoMesa) ?>">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="mesa-num">Mesa <?= e($mesa['numero_mesa'] ?? $mesa['numero'] ?? $mesa['id'] ?? '') ?></div>
              <div class="small text-secondary mt-1"><i class="bi bi-people me-1" aria-hidden="true"></i><?= (int) ($mesa['capacidad'] ?? 0) ?> personas</div>
            </div>
            <a class="btn btn-outline-secondary btn-icono" href="/mesas/editar/<?= (int) ($mesa['id'] ?? 0) ?>" aria-label="Editar mesa"><i class="bi bi-pencil" aria-hidden="true"></i></a>
          </div>
          <div class="mt-3"><span class="estado estado-<?= e($estadoMesa) ?>"><?= e(ucfirst(str_replace('_', ' ', $estadoMesa))) ?></span></div>
          <div class="mesa-info"><?= e($mesa['area_nombre'] ?? 'Área sin asignar') ?></div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$contenido = ob_get_clean();
require_once __DIR__ . '/layout.php';
render_app_layout($titulo, $pagina, $contenido);
