<div class="tarjeta text-center py-5">
  <i class="bi bi-shield-lock fs-1 d-block mb-2 text-secondary"></i>
  <h2 class="fs-5">No tienes permiso para ver esta página</h2>
  <p class="text-secondary mb-3">Tu rol (<?= e(Auth::ROLES[Auth::usuario()['rol']] ?? '') ?>) no tiene acceso a este módulo.</p>
  <a href="dashboard.php" class="btn btn-olivo">Volver al inicio</a>
</div>
