<?php
$titulo = 'Reservas';
$pagina = 'reservas';
$reservas = is_array($reservas ?? null) ? $reservas : [];
ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
  <p class="text-secondary mb-0">Agenda de reservas y asignación de mesas.</p>
  <span class="text-secondary small"><?= count($reservas) ?> reservas</span>
</div>
<section class="tarjeta p-0 overflow-hidden">
  <div class="table-responsive">
    <table class="table tabla-olivo align-middle">
      <thead><tr><th>Fecha y hora</th><th>Cliente</th><th>Mesa</th><th>Personas</th><th>Estado</th></tr></thead>
      <tbody>
      <?php if (!$reservas): ?>
        <tr><td colspan="5" class="text-center text-secondary py-5">No hay reservas registradas.</td></tr>
      <?php else: ?>
        <?php foreach ($reservas as $reserva): ?>
          <?php
          $estado = strtolower((string) ($reserva['estado'] ?? 'pendiente'));
          $claseEstado = in_array($estado, ['confirmada', 'completada', 'cancelada'], true) ? $estado : 'pendiente';
          ?>
          <tr>
            <td><?= e($reserva['fecha_reserva_solo'] ?? '') ?> <?= e($reserva['hora_reserva'] ?? '') ?></td>
            <td>
              <div class="fw-semibold"><?= e($reserva['cliente_nombre'] ?? '') ?></div>
              <?php if (!empty($reserva['cliente_telefono'])): ?><small class="text-secondary"><?= e($reserva['cliente_telefono']) ?></small><?php endif; ?>
            </td>
            <td><?= e($reserva['mesa_numero'] ?? $reserva['mesa'] ?? $reserva['mesa_id'] ?? '—') ?></td>
            <td><?= (int) ($reserva['personas'] ?? 0) ?></td>
            <td><span class="estado estado-<?= e($claseEstado) ?>"><?= e(ucfirst($estado)) ?></span></td>
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
