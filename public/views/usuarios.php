<?php
$titulo = 'Usuarios';
$pagina = 'usuarios';
$usuarios = is_array($usuarios ?? null) ? $usuarios : [];
ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
  <p class="text-secondary mb-0">Administra las cuentas y roles de acceso al sistema.</p>
  <a href="/usuarios/nuevo" class="btn btn-olivo"><i class="bi bi-person-plus me-1" aria-hidden="true"></i>Nuevo usuario</a>
</div>
<section class="tarjeta p-0 overflow-hidden">
  <div class="table-responsive">
    <table class="table tabla-olivo align-middle">
      <thead><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th><span class="visually-hidden">Acciones</span></th></tr></thead>
      <tbody>
      <?php if (!$usuarios): ?>
        <tr><td colspan="6" class="text-center text-secondary py-5">No hay usuarios registrados.</td></tr>
      <?php else: ?>
        <?php foreach ($usuarios as $usuario): ?>
          <?php
          $activo = !in_array(strtolower((string) ($usuario['estado'] ?? 'activo')), ['inactivo', '0'], true);
          $estadoClase = $activo ? 'activo' : 'inactivo';
          ?>
          <tr>
            <td><?= (int) ($usuario['id'] ?? 0) ?></td>
            <td class="fw-semibold"><?= e($usuario['nombre'] ?? '') ?></td>
            <td><?= e($usuario['email'] ?? $usuario['usuario'] ?? '') ?></td>
            <td><span class="rol <?= strtolower((string) ($usuario['rol'] ?? '')) === 'admin' ? 'rol-admin' : '' ?>"><?= e($usuario['rol_nombre'] ?? $usuario['rol'] ?? $usuario['rol_id'] ?? '') ?></span></td>
            <td><span class="estado estado-<?= e($estadoClase) ?>"><?= $activo ? 'Activo' : 'Inactivo' ?></span></td>
            <td class="text-end"><a href="/usuarios/editar/<?= (int) ($usuario['id'] ?? 0) ?>" class="btn btn-outline-olivo btn-sm">Editar</a></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php
$contenido = ob_get_clean();
require_once __DIR__ . '/layout.php';
render_app_layout($titulo, $pagina, $contenido);
