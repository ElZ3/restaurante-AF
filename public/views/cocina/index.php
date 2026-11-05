<?php
$titulo = 'Cocina';
$pagina = 'cocina';
$minutosDemora = max(1, (int) ($minutosDemora ?? 15));
$pedidos = $pedidos ?? $ordenes ?? [];
$pedidos = is_array($pedidos) ? $pedidos : [];
$columnas = [
    'pendiente' => ['Pendientes', 'Nuevos pedidos por preparar', 'col-pendiente'],
    'en_preparacion' => ['En preparación', 'Pedidos actualmente en cocina', 'col-en_preparacion'],
    'lista' => ['Listos', 'Pedidos listos para entregar', 'col-lista'],
    'entregada' => ['Entregadas', 'Pedidos entregados', 'col-entregada'],
];
$normalizar = static function ($estado) {
    return strtolower(str_replace([' ', 'á'], ['_', 'a'], (string) $estado));
};
$estadoCocina = [
    'pendiente' => 'pendiente',
    'en_preparacion' => 'en_preparacion',
    'lista' => 'lista',
    'entregada' => 'entregada',
];
ob_start();
?>
<p class="text-secondary small mb-3">
  Vista de cocina: solo muestra órdenes. Las que llevan más de <?= $minutosDemora ?> minutos sin terminar se marcan con <strong>Demora</strong>.
</p>
<div class="row g-3">
  <?php foreach ($columnas as $estadoColumna => [$nombre, $descripcion, $clase]): ?>
    <?php $ordenesColumna = array_filter($pedidos, static function ($pedido) use ($estadoColumna, $normalizar, $estadoCocina) { return ($estadoCocina[$normalizar($pedido['estado'] ?? '')] ?? '') === $estadoColumna; }); ?>
    <div class="col-md-6 col-xl-3">
      <div class="col-cocina <?= e($clase) ?>">
        <div class="col-titulo">
          <span class="fw-semibold"><?= e($nombre) ?></span>
          <span class="conteo">0</span>
        </div>
        <div class="small text-secondary mb-3"><?= e($descripcion) ?></div>
        <div data-col="<?= e($estadoColumna) ?>">
          <p class="vacio text-secondary small text-center mb-0">Sin órdenes</p>
          <?php foreach ($ordenesColumna as $orden): ?>
            <?php
              $estadoOrden = strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú'], ['_', 'a', 'e', 'i', 'o', 'u'], trim((string) ($orden['estado'] ?? 'pendiente'))));
              $minutosTranscurridos = 0;
              if (!empty($orden['fecha_hora'])) {
                  $ts = strtotime((string) $orden['fecha_hora']);
                  if ($ts !== false) { $minutosTranscurridos = (int) floor((time() - $ts) / 60); }
              }
            ?>
            <div class="orden-card" data-id="<?= (int) ($orden['id'] ?? 0) ?>" data-pedido-id="<?= (int) ($orden['id'] ?? 0) ?>" data-estado="<?= e($estadoOrden) ?>" data-min="<?= $minutosTranscurridos ?>" data-hora="<?= e((string) ($orden['fecha_hora'] ?? '')) ?>">
              <div class="d-flex justify-content-between align-items-start">
                <strong><?= e($orden['mesa_numero'] ?? $orden['mesa'] ?? 'Mesa sin asignar') ?></strong>
                <span class="small text-secondary">#<?= e($orden['id'] ?? '') ?></span>
              </div>
              <div class="small text-secondary mt-1">
                <i class="bi bi-clock me-1"></i>Hace <span class="min"><?= $minutosTranscurridos ?></span> min
                <span class="badge text-bg-danger ms-1 demora d-none"><i class="bi bi-exclamation-triangle me-1"></i>Demora</span>
              </div>
              <?php if (!empty($orden['items']) && is_array($orden['items'])): ?>
                <ul class="orden-items">
                  <?php foreach ($orden['items'] as $item): ?><li><span class="cant"><?= (int) ($item['cantidad'] ?? 1) ?>×</span><?= e($item['nombre'] ?? 'Producto') ?></li><?php endforeach; ?>
                </ul>
              <?php endif; ?>
              <?php if ($estadoOrden === 'pendiente'): ?>
                <button type="button" class="btn btn-olivo btn-sm w-100 btn-avanzar" data-accion="en_preparacion"><i class="bi bi-fire me-1"></i>Iniciar preparación</button>
              <?php elseif ($estadoOrden === 'en_preparacion'): ?>
                <button type="button" class="btn btn-olivo btn-sm w-100 btn-avanzar" data-accion="lista"><i class="bi bi-check2-circle me-1"></i>Marcar lista</button>
              <?php elseif ($estadoOrden === 'lista'): ?>
                <button type="button" class="btn btn-olivo btn-sm w-100 btn-avanzar" data-accion="entregada"><i class="bi bi-bag-check me-1"></i>Marcar entregada</button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php
$contenido = ob_get_clean();
$scripts_extra = '<script>
  const CSRF = ' . json_encode(Csrf::token()) . ';
  const LIMITE_DEMORA = ' . $minutosDemora . ';
  const siguiente = {
    pendiente: "en_preparacion",
    en_preparacion: "lista",
    lista: "entregada"
  };
  const columnas = { en_preparacion: "col-en_preparacion", lista: "col-lista", entregada: "col-entregada" };
  function actualizarConteos() {
    document.querySelectorAll(".col-cocina").forEach(columna => {
      const badge = columna.querySelector(".conteo");
      const cantidad = columna.querySelectorAll(".orden-card").length;
      if (badge) badge.textContent = cantidad;
      const vacio = columna.querySelector(".vacio");
      if (vacio) vacio.classList.toggle("d-none", cantidad > 0);
    });
  }
  function revisarTiempos() {
    document.querySelectorAll(".orden-card").forEach(card => {
      const fecha = Date.parse((card.dataset.hora || "").replace(" ", "T"));
      if (!Number.isFinite(fecha)) return;
      const minutos = Math.max(0, Math.floor((Date.now() - fecha) / 60000));
      card.dataset.min = minutos;
      const min = card.querySelector(".min"), demora = card.querySelector(".demora");
      if (min) min.textContent = minutos;
      if (demora) demora.classList.toggle("d-none", !(["pendiente", "en_preparacion"].includes(card.dataset.estado) && minutos >= LIMITE_DEMORA));
    });
  }
  document.querySelector(".row.g-3").addEventListener("click", async e => {
    const btn = e.target.closest("[data-accion]");
    if (!btn) return;
    const card = btn.closest(".orden-card");
    const accion = siguiente[card.dataset.estado];
    if (!accion) return;
    conSpinner(btn);
    try {
      const response = await fetch("/pedidos/actualizar-estado/" + card.dataset.pedidoId, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded", "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
        body: new URLSearchParams({ estado: accion, csrf: CSRF })
      });
      const res = await response.json();
      if (!res.success) { aviso(res.message || "Error al actualizar estado", "danger"); return; }
      const columna = document.querySelector("." + columnas[accion] + " [data-col]");
      if (!columna) throw new Error("No se encontró la columna de destino.");
      columna.appendChild(card);
      card.dataset.estado = accion;
      const botonSiguiente = card.querySelector("[data-accion]");
      if (accion === "entregada") botonSiguiente.remove();
      else {
        botonSiguiente.dataset.accion = accion === "en_preparacion" ? "lista" : "entregada";
        botonSiguiente.innerHTML = accion === "en_preparacion" ? \'<i class="bi bi-check2-circle me-1"></i>Marcar lista\' : \'<i class="bi bi-bag-check me-1"></i>Marcar entregada\';
      }
      actualizarConteos();
      aviso("Pedido actualizado.", "success");
    } catch (err) {
      aviso("Error de conexión: " + err.message, "danger");
    } finally {
      liberarSpinner(btn);
    }
  });
  actualizarConteos();
  revisarTiempos();
  window.setInterval(revisarTiempos, 60000);
</script>';
require_once __DIR__ . '/../layout.php';
render_app_layout($titulo, $pagina, $contenido, $scripts_extra);
