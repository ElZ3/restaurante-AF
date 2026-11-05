<?php
$titulo = 'Mesas y áreas';
$pagina = 'mesas';
$mesas = is_array($mesas ?? null) ? $mesas : [];
$areas = is_array($areas ?? null) ? $areas : [];
$estados = ['disponible', 'reservada', 'ocupada', 'fuera_servicio'];

$datos_meses = [];
foreach ($mesas as $m) {
    $datos_meses[] = [
        'id' => (int) ($m['id'] ?? 0),
        'numero' => (int) ($m['numero_mesa'] ?? $m['numero'] ?? $m['id'] ?? 0),
        'capacidad' => (int) ($m['capacidad'] ?? 0),
        'area' => (string) ($m['area_nombre'] ?? ''),
        'estado' => strtolower((string) ($m['estado'] ?? 'disponible')),
        'area_id' => (int) ($m['area_id'] ?? 0),
    ];
}
$datos_json = json_encode($datos_meses, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

ob_start();
?>
<div class="tarjeta mb-3">
  <div class="d-flex flex-wrap gap-2 align-items-center mb-3" id="filtroAreas">
    <button type="button" class="chip-area activo" data-area="">Todas</button>
    <?php foreach ($areas as $area): ?>
      <button type="button" class="chip-area" data-area="<?= (int) ($area['id'] ?? 0) ?>"><?= e($area['nombre'] ?? '') ?></button>
    <?php endforeach; ?>
    <button type="button" class="btn btn-olivo ms-auto" id="btnAbrirMesa"><i class="bi bi-plus-lg me-1"></i>Nueva mesa</button>
  </div>
  <div class="resumen-mesas" id="resumenMesas"></div>
</div>
<div id="gridMesas"></div>

<!-- Modal: Mesa (crear / editar) -->
<div class="modal fade" id="modalMesa" tabindex="-1" aria-labelledby="tituloMesa" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-5" id="tituloMesa">Mesa</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="mMesaNumero">Número de Mesa</label>
            <input type="number" class="form-control" id="mMesaNumero" min="1" step="1" required inputmode="numeric" data-validar="entero-positivo">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="mMesaCapacidad">Capacidad (Personas)</label>
            <input type="number" class="form-control" id="mMesaCapacidad" min="1" max="255" step="1" value="4" required inputmode="numeric" data-validar="entero-positivo">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="mMesaArea">Área</label>
            <select class="form-select" id="mMesaArea" required></select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="mMesaEstado">Estado</label>
            <select class="form-select" id="mMesaEstado">
              <option value="disponible">Disponible</option>
              <option value="ocupada">Ocupada</option>
              <option value="reservada">Reservada</option>
              <option value="fuera_servicio">Fuera de Servicio</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-olivo" id="btnGuardarMesa"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Guardar mesa</button>
      </div>
    </div>
  </div>
</div>
<?php
$contenido = ob_get_clean();

$scripts_extra = '<script>window.DATOS_MESAS = ' . $datos_json . ';</script>' . "\n";
$scripts_extra .= '<script>window.DATAS_AREAS = ' . json_encode($areas) . ';</script>' . "\n";
$scripts_extra .= '<script>
  const CSRF = ' . json_encode(Csrf::token()) . ';
</script>' . "\n";
$scripts_extra .= <<<'JS'
<script>
(function () {
  const MESAS = window.DATOS_MESAS || [];
  const AREAS = window.DATAS_AREAS || [];
  const ESTADOS_MESA = ['disponible', 'reservada', 'ocupada', 'fuera_servicio'];
  const NOMBRES_ESTADO = {disponible: 'Disponible', reservada: 'Reservada', ocupada: 'Ocupada', fuera_servicio: 'Fuera de servicio'};
  const mMesa = new bootstrap.Modal(document.getElementById('modalMesa'));
  let mesaEditId = null;
  let areaActiva = '';

  function filtrarArea() {
    document.querySelectorAll('.seccion-area').forEach(seccion => {
      seccion.classList.toggle('d-none', areaActiva !== '' && seccion.dataset.area !== areaActiva);
    });
  }

  function esc(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }

  function poblarAreas() {
    const sel = document.getElementById('mMesaArea');
    sel.innerHTML = '<option value="">Selecciona un área</option>';
    AREAS.forEach(a => {
      const opt = document.createElement('option');
      opt.value = a.id;
      opt.textContent = a.nombre || ('Área ' + a.id);
      sel.appendChild(opt);
    });
  }

  function abrirMesa(id) {
    const m = MESAS.find(x => Number(x.id) === Number(id));
    if (!m) return;
    mesaEditId = id;
    document.getElementById('mMesaNumero').value = m.numero || '';
    document.getElementById('mMesaCapacidad').value = m.capacidad || 0;
    document.getElementById('mMesaArea').value = m.area_id || '';
    document.getElementById('mMesaEstado').value = m.estado || 'disponible';
    mMesa.show();
  }

  function pintarMesas() {
    const resumen = document.getElementById('resumenMesas');
    resumen.innerHTML = ESTADOS_MESA.map(estado => {
      const count = MESAS.filter(m => m.estado === estado).length;
      return '<span><span class="estado estado-' + estado + '">' + NOMBRES_ESTADO[estado] + '</span> <strong>' + count + '</strong></span>';
    }).join('');
    const grid = document.getElementById('gridMesas');
    grid.innerHTML = AREAS.map(area => {
      const mesasArea = MESAS.filter(m => Number(m.area_id) === Number(area.id));
      const tarjetas = mesasArea.map(m => {
        const estado = ESTADOS_MESA.includes(m.estado) ? m.estado : 'disponible';
        return '<div class="col-6 col-md-4 col-xl-3"><div class="mesa-card mesa-' + estado + '" data-mesa-id="' + m.id + '">' +
          '<div class="d-flex justify-content-between align-items-start"><div><div class="mesa-num">Mesa ' + esc(m.numero) + '</div>' +
          '<div class="small text-secondary"><i class="bi bi-people me-1"></i>' + Number(m.capacidad) + ' personas</div></div>' +
          '<div class="dropdown"><button type="button" class="btn btn-outline-secondary btn-icono" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-display="static" aria-label="Acciones de la mesa ' + esc(m.numero) + '">' +
          '<i class="bi bi-three-dots-vertical"></i></button><ul class="dropdown-menu dropdown-menu-end">' +
          '<li><a class="dropdown-item" href="#" data-editar="' + m.id + '"><i class="bi bi-pencil me-2"></i>Editar mesa</a></li></ul></div></div>' +
          '<div class="mt-3"><span class="estado estado-' + estado + '">' + NOMBRES_ESTADO[estado] + '</span></div>' +
          '<div class="mesa-info">' + esc(m.area || 'Área sin asignar') + '</div></div></div>';
      }).join('');
      return '<section class="mb-4 seccion-area" data-area="' + area.id + '"><h2 class="fs-5 mb-3">' + esc(area.nombre) + '</h2><div class="row g-3">' + tarjetas + '</div></section>';
    }).join('') || '<div class="row g-3"></div>';
    filtrarArea();
  }

  document.getElementById('btnAbrirMesa').addEventListener('click', () => {
    mesaEditId = null;
    document.getElementById('mMesaNumero').value = '';
    document.getElementById('mMesaCapacidad').value = 4;
    document.getElementById('mMesaArea').value = '';
    document.getElementById('mMesaEstado').value = 'disponible';
    poblarAreas();
    mMesa.show();
  });

  document.getElementById('btnGuardarMesa').addEventListener('click', async () => {
    const btn = document.getElementById('btnGuardarMesa');
    if (!Validar.formulario('modalMesa')) return;
    conSpinner(btn);
    const payload = {
      numero: document.getElementById('mMesaNumero').value,
      capacidad: parseInt(document.getElementById('mMesaCapacidad').value, 10) || 0,
      area_id: document.getElementById('mMesaArea').value || 0,
      estado: document.getElementById('mMesaEstado').value,
    };
    try {
      const res = mesaEditId
        ? await API.post('/mesas/actualizar/' + mesaEditId, Object.assign({ csrf: CSRF }, payload))
        : await API.post('/mesas/crear', Object.assign({ csrf: CSRF }, payload));
      if (!res.success) { aviso(res.message || 'Error al guardar mesa', 'danger'); return; }
      const area = AREAS.find(a => Number(a.id) === Number(payload.area_id));
      const values = {
        id: Number(res.mesa_id || mesaEditId),
        numero: Number(payload.numero),
        capacidad: payload.capacidad,
        area_id: Number(payload.area_id),
        area: area ? area.nombre : '',
        estado: payload.estado
      };
      const index = MESAS.findIndex(m => Number(m.id) === Number(values.id));
      if (index >= 0) MESAS.splice(index, 1, values); else MESAS.push(values);
      pintarMesas();
      aviso(mesaEditId ? 'Mesa actualizada correctamente' : 'Mesa creada correctamente', 'success');
      mMesa.hide();
    } catch (err) {
      aviso('No se pudo guardar la mesa: ' + err.message, 'danger');
    } finally {
      liberarSpinner(btn);
    }
  });

  document.getElementById('gridMesas').addEventListener('click', e => {
    const edit = e.target.closest('[data-editar]');
    if (!edit) return;
    e.preventDefault();
    abrirMesa(Number(edit.dataset.editar));
  });

  document.addEventListener('DOMContentLoaded', () => {
    poblarAreas();
    pintarMesas();
  });
  document.getElementById('filtroAreas').addEventListener('click', e => {
    const tab = e.target.closest('[data-area]');
    if (!tab) return;
    areaActiva = tab.dataset.area || '';
    document.querySelectorAll('#filtroAreas [data-area]').forEach(x => x.classList.toggle('activo', x === tab));
    filtrarArea();
  });
})();
</script>
JS;

require_once __DIR__ . '/../layout.php';
render_app_layout($titulo, $pagina, $contenido, $scripts_extra);
