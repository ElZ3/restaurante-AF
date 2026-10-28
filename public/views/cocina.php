<?php
$titulo = 'Cocina';
$pagina = 'cocina';
$pedidos = $pedidos ?? $ordenes ?? [];
$pedidos = is_array($pedidos) ? $pedidos : [];
$columnas = [
    'pendiente' => ['Pendientes', 'Nuevos pedidos por preparar', 'col-pendiente'],
    'en_preparacion' => ['En preparación', 'Pedidos actualmente en cocina', 'col-en_preparacion'],
    'lista' => ['Listos', 'Pedidos listos para entregar', 'col-lista'],
];
$normalizar = static function ($estado) {
    return strtolower(str_replace([' ', 'á'], ['_', 'a'], (string) $estado));
};
$estadoCocina = [
    'abierta' => 'pendiente',
    'en_preparacion' => 'en_preparacion',
    'servida' => 'lista',
];
ob_start();
?>
<p class="text-secondary small mb-3">Órdenes activas organizadas por estado de preparación.</p>
<div class="row g-3">
  <?php foreach ($columnas as $estadoColumna => [$nombre, $descripcion, $clase]): ?>
    <?php $ordenesColumna = array_filter($pedidos, static function ($pedido) use ($estadoColumna, $normalizar, $estadoCocina) { return ($estadoCocina[$normalizar($pedido['estado'] ?? '')] ?? '') === $estadoColumna; }); ?>
    <div class="col-md-6 col-xl-4">
      <section class="col-cocina <?= e($clase) ?>">
        <div class="col-titulo"><strong><?= e($nombre) ?></strong><span class="conteo"><?= count($ordenesColumna) ?></span></div>
        <div class="small text-secondary mb-3"><?= e($descripcion) ?></div>
        <?php if (!$ordenesColumna): ?>
          <p class="vacio text-secondary small text-center mb-0">Sin órdenes</p>
        <?php else: ?>
          <?php foreach ($ordenesColumna as $orden): ?>
            <article class="orden-card">
              <div class="d-flex justify-content-between gap-2">
                <strong><?= e($orden['mesa_numero'] ?? $orden['mesa'] ?? 'Mesa sin asignar') ?></strong>
                <span class="small text-secondary">#<?= e($orden['id'] ?? '') ?></span>
              </div>
              <?php if (!empty($orden['fecha_hora'])): ?><div class="small text-secondary mt-1"><?= e($orden['fecha_hora']) ?></div><?php endif; ?>
              <?php if (!empty($orden['items']) && is_array($orden['items'])): ?>
                <ul class="orden-items mb-0">
                  <?php foreach ($orden['items'] as $item): ?><li><span class="cant"><?= (int) ($item['cantidad'] ?? 1) ?>×</span><?= e($item['nombre'] ?? 'Producto') ?></li><?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>
    </div>
  <?php endforeach; ?>
</div>
<?php
$contenido = ob_get_clean();
require_once __DIR__ . '/layout.php';
render_app_layout($titulo, $pagina, $contenido);
