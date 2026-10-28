
<div class="tarjeta mb-3">
  <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <button type="button" class="chip-area activo" data-area="todas">Todas</button>
    <?php foreach (array_keys($areas) as $i => $nombre): ?>
      <button type="button" class="chip-area" data-area="a<?= $i ?>"><?= htmlspecialchars($nombre) ?></button>
    <?php endforeach; ?>
  </div>
  <div class="resumen-mesas">
    <?php foreach ($estados as $clave => $texto): ?>
      <span><span class="estado estado-<?= $clave ?>"><?= $texto ?></span> <strong><?= $conteo[$clave] ?></strong></span>
    <?php endforeach; ?>
  </div>
</div>

<?php $i = 0; foreach ($areas as $nombre_area => $mesas): ?>
  <section class="mb-4 seccion-area" data-area="a<?= $i ?>">
    <h2 class="fs-5 mb-3"><?= htmlspecialchars($nombre_area) ?></h2>
    <div class="row g-3">
      <?php foreach ($mesas as [$num, $cap, $est, $detalle]): ?>
        <div class="col-6 col-md-4 col-xl-3">
          <div class="mesa-card mesa-<?= $est ?>">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="mesa-num">Mesa <?= $num ?></div>
                <div class="small text-secondary"><i class="bi bi-people me-1"></i><?= $cap ?> personas</div>
              </div>
              <div class="dropdown">
                <button class="btn btn-outline-secondary btn-icono" data-bs-toggle="dropdown" aria-label="Acciones de la mesa <?= $num ?>">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <?php foreach ($acciones[$est] as [$texto, $icono, $enlace, $demo]): ?>
                    <li>
                      <?php if ($enlace): ?>
                        <a class="dropdown-item" href="<?= $enlace ?>"><i class="bi <?= $icono ?> me-2"></i><?= $texto ?></a>
                      <?php else: ?>
                        <a class="dropdown-item" href="#" data-demo="<?= htmlspecialchars($demo . ' · Mesa ' . $num) ?>"><i class="bi <?= $icono ?> me-2"></i><?= $texto ?></a>
                      <?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <div class="mt-3"><span class="estado estado-<?= $est ?>"><?= $estados[$est] ?></span></div>
            <div class="mesa-info"><?= htmlspecialchars($detalle) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
<?php $i++; endforeach; ?>

<?php
$scripts_extra = <<<'JS'
<script>
(function () {
  // Filtro por área
  const chips = document.querySelectorAll('.chip-area');
  const secciones = document.querySelectorAll('.seccion-area');
  chips.forEach(chip => chip.addEventListener('click', () => {
    chips.forEach(c => c.classList.toggle('activo', c === chip));
    secciones.forEach(s => s.classList.toggle('d-none', chip.dataset.area !== 'todas' && s.dataset.area !== chip.dataset.area));
  }));

  // Acciones de demostración (BACKEND: aquí se enviará el cambio de estado al servidor)
  document.querySelectorAll('[data-demo]').forEach(a => a.addEventListener('click', e => {
    e.preventDefault();
    aviso(a.dataset.demo + ' (demo)');
  }));
})();
</script>
JS;
