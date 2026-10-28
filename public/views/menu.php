<?php
$titulo = 'Menú';
$pagina = 'menu';
$productos = is_array($productos ?? null) ? $productos : [];
$categorias = is_array($categorias ?? null) ? $categorias : [];
ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
  <p class="text-secondary mb-0">Productos disponibles en el menú del restaurante.</p>
  <span class="text-secondary small"><?= count($productos) ?> productos</span>
</div>
<?php if (!$productos): ?>
  <div class="tarjeta text-center text-secondary py-5">No hay productos disponibles.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($productos as $producto): ?>
      <?php
      $imagenNombre = basename((string) ($producto['imagen'] ?? ''));
      $imagenUrl = $imagenNombre !== '' ? '/img/menu/' . rawurlencode($imagenNombre) : '';
      $categoriaNombre = (string) ($producto['categoria_nombre'] ?? $producto['categoria'] ?? '');
      $disponible = !isset($producto['disponible']) || (bool) $producto['disponible'];
      ?>
      <div class="col-sm-6 col-xl-4">
        <article class="prod-card">
          <div class="prod-img">
            <?php if ($imagenUrl !== ''): ?>
              <div class="prod-media"><img src="<?= e($imagenUrl) ?>" alt="<?= e($producto['nombre'] ?? '') ?>" loading="lazy"></div>
            <?php else: ?>
              <div class="prod-media" aria-hidden="true"><i class="bi bi-egg-fried"></i></div>
            <?php endif; ?>
            <?php if ($categoriaNombre !== ''): ?><span class="prod-cat"><?= e($categoriaNombre) ?></span><?php endif; ?>
            <?php if (!$disponible): ?><span class="prod-agotado">Agotado</span><?php endif; ?>
          </div>
          <div class="prod-body">
            <h2 class="prod-nombre"><?= e($producto['nombre'] ?? 'Producto') ?></h2>
            <p class="prod-desc"><?= e($producto['descripcion'] ?? '') ?></p>
            <div class="prod-precio">$<?= number_format((float) ($producto['precio'] ?? 0), 2) ?></div>
          </div>
          <div class="prod-pie">
            <span class="small text-secondary"><?= e($categoriaNombre ?: 'Sin categoría') ?></span>
            <span class="estado estado-<?= $disponible ? 'activo' : 'inactivo' ?>"><?= $disponible ? 'Disponible' : 'Agotado' ?></span>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$contenido = ob_get_clean();
require_once __DIR__ . '/layout.php';
render_app_layout($titulo, $pagina, $contenido);
