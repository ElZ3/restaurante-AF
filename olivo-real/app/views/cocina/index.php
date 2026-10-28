
<p class="text-secondary small mb-3">
  Vista de cocina: solo muestra órdenes. Las que llevan más de 15 minutos sin terminar se marcan con <strong>Demora</strong>.
</p>

<div class="row g-3">
  <?php foreach ($columnas as $estado => [$nombre, $sub]): ?>
    <div class="col-md-6 col-xl-3">
      <div class="col-cocina col-<?= $estado ?>">
        <div class="col-titulo">
          <span class="fw-semibold"><?= $nombre ?></span>
          <span class="conteo">0</span>
        </div>
        <div class="small text-secondary mb-3"><?= $sub ?></div>

        <div data-col="<?= $estado ?>">
          <p class="vacio text-secondary small text-center mb-0">Sin órdenes</p>
          <?php foreach ($ordenes as [$id, $mesa, $hora, $min, $est, $items]): ?>
            <?php if ($est !== $estado) continue; ?>
            <div class="orden-card" data-id="<?= $id ?>" data-estado="<?= $est ?>" data-min="<?= $min ?>">
              <div class="d-flex justify-content-between align-items-start">
                <strong>Mesa <?= $mesa ?></strong>
                <span class="small text-secondary">#<?= $id ?> · <?= $hora ?></span>
              </div>
              <div class="small text-secondary mt-1">
                <i class="bi bi-clock me-1"></i>Hace <span class="min"><?= $min ?></span> min
                <span class="badge text-bg-danger ms-1 demora d-none"><i class="bi bi-exclamation-triangle me-1"></i>Demora</span>
              </div>
              <ul class="orden-items">
                <?php foreach ($items as [$prod, $cant, $obs]): ?>
                  <li><span class="cant"><?= $cant ?>×</span><?= htmlspecialchars($prod) ?>
                    <?php if ($obs): ?><small><?= htmlspecialchars($obs) ?></small><?php endif; ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="btn btn-olivo btn-sm w-100 btn-avanzar"></button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php
$scripts_extra = <<<'JS'
<script>
(function () {
  // [siguiente estado, texto del botón, icono]
  const SIG = {
    pendiente:      ['en_preparacion', 'Iniciar preparación', 'bi-fire'],
    en_preparacion: ['lista',          'Marcar lista',        'bi-check2-circle'],
    lista:          ['entregada',      'Marcar entregada',    'bi-bag-check']
  };
  const ETQ = { pendiente: 'Pendiente', en_preparacion: 'En preparación', lista: 'Lista', entregada: 'Entregada' };
  const cols = {};
  document.querySelectorAll('[data-col]').forEach(c => cols[c.dataset.col] = c);

  function refrescar(card) {
    const e = card.dataset.estado, min = +card.dataset.min;
    card.querySelector('.min').textContent = min;
    const activa = e === 'pendiente' || e === 'en_preparacion';
    card.querySelector('.demora').classList.toggle('d-none', !(activa && min >= 15));
    const btn = card.querySelector('.btn-avanzar');
    if (SIG[e]) {
      btn.innerHTML = '<i class="bi ' + SIG[e][2] + ' me-1"></i>' + SIG[e][1];
      btn.classList.remove('d-none');
    } else {
      btn.classList.add('d-none');
    }
  }

  function conteos() {
    Object.keys(cols).forEach(k => {
      const n = cols[k].querySelectorAll('.orden-card').length;
      cols[k].closest('.col-cocina').querySelector('.conteo').textContent = n;
      cols[k].querySelector('.vacio').classList.toggle('d-none', n > 0);
    });
  }

  // BACKEND: aquí se enviará el cambio de estado al servidor (UPDATE pedidos + historial_pedido)
  document.addEventListener('click', e => {
    const b = e.target.closest('.btn-avanzar');
    if (!b) return;
    const card = b.closest('.orden-card'), sig = SIG[card.dataset.estado];
    if (!sig) return;
    card.dataset.estado = sig[0];
    cols[sig[0]].appendChild(card);
    refrescar(card);
    conteos();
    aviso('Pedido #' + card.dataset.id + ': ' + ETQ[sig[0]].toLowerCase() + ' (demo)');
  });

  document.querySelectorAll('.orden-card').forEach(refrescar);
  conteos();

  // El tiempo avanza cada minuto en las órdenes que siguen activas
  setInterval(() => {
    document.querySelectorAll('.orden-card').forEach(c => {
      if (c.dataset.estado !== 'entregada') { c.dataset.min = +c.dataset.min + 1; refrescar(c); }
    });
  }, 60000);
})();
</script>
JS;
