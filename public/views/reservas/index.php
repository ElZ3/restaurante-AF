<?php
$titulo = 'Reservas';
$pagina = 'reservas';
$reservas = is_array($reservas ?? null) ? $reservas : [];
$mesas = is_array($mesas ?? null) ? $mesas : [];

$estados = [
    'pendiente' => 'Pendiente',
    'confirmada' => 'Confirmada',
    'llego' => 'Llegó',
    'completada' => 'Completada',
    'cancelada' => 'Cancelada',
];

$datos_reservas = [];
foreach ($reservas as $r) {
    $datos_reservas[] = [
        'id' => (int) ($r['id'] ?? 0),
        'mesa_id' => (int) ($r['mesa_id'] ?? 0),
        'mesa' => $r['mesa_numero'] ?? null,
        'cliente' => $r['nombre_cliente'] ?? '',
        'telefono' => $r['telefono_cliente'] ?? '',
        'fecha' => $r['fecha_reserva'] ?? '',
        'hora' => $r['hora'] ?? '',
        'personas' => (int) ($r['personas'] ?? 0),
        'estado' => strtolower((string) ($r['estado'] ?? 'confirmada')),
        'observaciones' => $r['observaciones'] ?? '',
    ];
}
$datos_json = json_encode($datos_reservas, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

ob_start();
?>
<div class="tarjeta mb-3">
  <div class="barra-filtros">
    <div>
      <label class="form-label" for="fBusqueda">Buscar</label>
      <input type="search" id="fBusqueda" class="form-control" placeholder="Cliente o teléfono">
    </div>
    <div>
      <label class="form-label" for="fFecha">Fecha</label>
      <input type="date" id="fFecha" class="form-control">
    </div>
    <div>
      <label class="form-label" for="fEstado">Estado</label>
      <select id="fEstado" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($estados as $clave => $texto): ?>
          <option value="<?= e($clave) ?>"><?= e($texto) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label" for="fPersonas">Personas</label>
      <select id="fPersonas" class="form-select">
        <option value="">Cualquiera</option>
        <option value="1-2">1–2 personas</option>
        <option value="3-4">3–4 personas</option>
        <option value="5+">5 o más</option>
      </select>
    </div>
    <div class="flex-grow-0">
      <button type="button" class="btn btn-outline-olivo" id="btnLimpiar">Limpiar</button>
    </div>
    <div class="flex-grow-0 ms-auto">
      <button type="button" class="btn btn-olivo" id="btnAbrirReserva"><i class="bi bi-plus-lg me-1"></i>Nueva reserva</button>
    </div>
  </div>
</div>

<div class="tarjeta">
  <div class="table-responsive">
    <table class="table tabla-olivo" id="tablaReservas">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Fecha y hora</th>
          <th>Personas</th>
          <th>Mesa</th>
          <th>Estado</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody id="tbodyReservas"></tbody>
    </table>
  </div>
</div>

<!-- Modal: Reserva (crear / editar) -->
<div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="tituloReserva" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formReserva" method="post" action="/reservas" novalidate>
    <div class="modal-header">
      <h2 class="modal-title fs-5" id="tituloReserva">Nueva reserva</h2>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
    </div>
    <div class="modal-body">
      <input type="hidden" name="reserva_id" value="">
      <div class="alert alert-warning py-2 small d-none" id="avisoConflicto" role="alert"></div>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label" for="rCliente">Nombre del cliente</label>
          <input type="text" class="form-control" id="rCliente" name="cliente" placeholder="Nombre del cliente" required maxlength="100" data-validar="nombre" autocomplete="off">
        </div>
        <div class="col-12">
          <label class="form-label" for="rTelefono">Teléfono o contacto</label>
          <input type="tel" class="form-control" id="rTelefono" name="telefono" placeholder="+503 XXXX-XXXX" required maxlength="80" inputmode="numeric" data-validar="telefono-sv" autocomplete="off">
        </div>
        <div class="col-6">
          <label class="form-label" for="rFecha">Fecha</label>
          <input type="date" class="form-control" id="rFecha" name="fecha" required>
        </div>
        <div class="col-6">
          <label class="form-label" for="rHora">Hora</label>
          <input type="time" class="form-control" id="rHora" name="hora" required>
        </div>
        <div class="col-6">
          <label class="form-label" for="rPersonas">Personas</label>
          <input type="number" class="form-control" id="rPersonas" name="personas" min="1" max="20" value="2" required>
        </div>
        <div class="col-6">
          <label class="form-label" for="rMesa">Mesa (opcional)</label>
          <select class="form-select" id="rMesa" name="mesa_id"></select>
        </div>
        <div class="col-12">
          <label class="form-label" for="rEstado">Estado</label>
          <select class="form-select" id="rEstado" name="estado">
            <?php foreach ($estados as $clave => $texto): ?>
              <option value="<?= e($clave) ?>"><?= e($texto) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label" for="rObs">Observaciones</label>
          <textarea class="form-control" id="rObs" name="observaciones" rows="2" maxlength="255"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
      <button type="button" class="btn btn-olivo" id="btnGuardarReserva"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Guardar reserva</button>
    </div>
      </form>
    </div>
  </div>
</div>
<?php
$contenido = ob_get_clean();

$scripts_extra = '<script>window.DATOS_RESERVAS = ' . $datos_json . ';</script>' . "\n";
$scripts_extra .= '<script>window.DATAS_MESAS = ' . json_encode($mesas) . ';</script>' . "\n";
$scripts_extra .= '<script>
  const CSRF = ' . json_encode(Csrf::token()) . ';
</script>' . "\n";
$scripts_extra .= <<<'JS'
<script>
(function () {
  const RES = window.DATOS_RESERVAS || [];
  const MESAS = window.DATAS_MESAS || [];
  const ESTADOS = { pendiente: 'Pendiente', confirmada: 'Confirmada', llego: 'Llegó', completada: 'Atendida', cancelada: 'Cancelada' };
  const ESTADO_CLASE = { pendiente: 'estado-pendiente', confirmada: 'estado-confirmada', llego: 'estado-activo', completada: 'estado-completada', cancelada: 'estado-cancelada' };
  const tbody = document.getElementById('tbodyReservas');
  const fBusqueda = document.getElementById('fBusqueda');
  const fFecha = document.getElementById('fFecha');
  const fEstado = document.getElementById('fEstado');
  const fPersonas = document.getElementById('fPersonas');
  const mReserva = new bootstrap.Modal(document.getElementById('modalReserva'));
  let reservaEditId = null;

  function esc(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }

  function pintar() {
    const q = (fBusqueda.value || '').trim().toLowerCase();
    const lista = RES.filter(r => {
      const okEstado = !fEstado.value || r.estado === fEstado.value;
      const okFecha = !fFecha.value || String(r.fecha || '').slice(0, 10) === fFecha.value;
      const okBus = !q || String(r.cliente || '').toLowerCase().includes(q) || String(r.telefono || '').toLowerCase().includes(q);
      const rango = fPersonas.value;
      const okPers = !rango || (rango === '5+' ? r.personas >= 5 : r.personas >= Number(rango.split('-')[0]) && r.personas <= Number(rango.split('-')[1]));
      return okEstado && okFecha && okBus && okPers;
    });

    if (!lista.length) {
      tbody.innerHTML = '<tr>      <td colspan="6" class="text-center text-secondary py-4">No hay reservas que coincidan con los filtros.</td></tr>';
      return;
    }

    tbody.innerHTML = lista.map(r => {
      const acciones = `
        <button type="button" class="btn btn-outline-olivo btn-icono btn-editar" title="Editar" aria-label="Editar reserva" data-accion="editar" data-id="${r.id}">
          <i class="bi bi-pencil"></i>
        </button>
        <div class="dropdown d-inline-block">
          <button type="button" class="btn btn-outline-secondary btn-icono" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-display="static" aria-label="Cambiar estado">
            <i class="bi bi-three-dots-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#" data-accion="confirmada" data-id="${r.id}"><i class="bi bi-check2-circle me-2"></i>Confirmar</a></li>
            <li><a class="dropdown-item" href="#" data-accion="llego" data-id="${r.id}"><i class="bi bi-person-check me-2"></i>Marcar llegada</a></li>
            <li><a class="dropdown-item" href="#" data-accion="completada" data-id="${r.id}"><i class="bi bi-check-all me-2"></i>Marcar atendida</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#" data-accion="cancelada" data-id="${r.id}"><i class="bi bi-x-circle me-2"></i>Cancelar</a></li>
            <li><a class="dropdown-item text-danger" href="#" data-accion="eliminar" data-id="${r.id}"><i class="bi bi-trash me-2"></i>Eliminar</a></li>
          </ul>
        </div>`;
      return `
      <tr>
        <td><div class="fw-semibold">${esc(r.cliente)}</div><small class="text-secondary">${esc(r.telefono)}</small></td>
        <td>${String(r.fecha || '').slice(0, 10)}<br><small class="text-secondary">${esc(r.hora || '')}</small></td>
        <td>${parseInt(r.personas) || 0}</td>
        <td>${r.mesa ? 'Mesa ' + esc(r.mesa) : 'Sin asignar'}</td>
        <td><span class="estado estado-${r.estado}" data-badge>${esc(ESTADOS[r.estado] || r.estado)}</span></td>
        <td class="text-end text-nowrap">${acciones}</td>
      </tr>`;
    }).join('');
  }

  fBusqueda.addEventListener('input', pintar);
  fFecha.addEventListener('change', pintar);
  fEstado.addEventListener('change', pintar);
  fPersonas.addEventListener('change', pintar);
  document.getElementById('btnLimpiar').addEventListener('click', () => {
    fBusqueda.value = ''; fFecha.value = ''; fEstado.value = ''; fPersonas.value = '';
    pintar();
  });
  document.getElementById('btnAbrirReserva').addEventListener('click', abrirCrear);
  tbody.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-accion]');
    if (!btn) return;
    const id = +btn.dataset.id;
    const accion = btn.dataset.accion;
    if (accion === 'editar') return abrirEditar(id);
    if (accion === 'eliminar') return await eliminarReserva(id);
    e.preventDefault();
    return await cambiarEstado(id, accion, btn);
  });

  async function cambiarEstado(id, estado, btn) {
    conSpinner(btn);
    try {
      const res = await API.post('/reservas/actualizar-estado/' + id, { estado: estado, csrf: CSRF });
      if (!res.success) { aviso(res.message || 'Error al actualizar estado', 'danger'); return; }
      const r = RES.find(x => x.id === id);
      if (r) r.estado = estado;
      pintar();
      aviso('Reserva actualizada correctamente', 'success');
    } catch (err) {
      aviso('Error al actualizar la reserva: ' + err.message, 'danger');
    } finally {
      liberarSpinner(btn);
    }
  }

  async function eliminarReserva(id) {
    try {
      const res = await API.post('/reservas/eliminar/' + id, { csrf: CSRF });
      if (!res.success) { aviso(res.message || 'Error al eliminar reserva', 'danger'); return; }
      RES.splice(RES.findIndex(x => x.id === id), 1);
      pintar();
      aviso('Reserva eliminada.', 'success');
    } catch (err) {
      aviso('Error al eliminar la reserva: ' + err.message, 'danger');
    }
  }

  let revisionMesas = 0;
  async function poblarMesas(mesaSeleccionada) {
    const sel = document.getElementById('rMesa');
    const revision = ++revisionMesas;
    const fecha = document.getElementById('rFecha').value;
    const hora = document.getElementById('rHora').value;
    const personas = document.getElementById('rPersonas').value;
    sel.innerHTML = '<option value="">Selecciona fecha, hora y personas</option>';
    if (!fecha || !hora || !personas) return;
    sel.disabled = true;
    try {
      const params = new URLSearchParams({
        fecha, hora, personas,
        reserva_id: String(reservaEditId || 0)
      });
      const respuesta = await API.get('/reservas/mesas-disponibles?' + params.toString());
      if (revision !== revisionMesas) return;
      const mesasDisponibles = respuesta.mesas || [];
      sel.innerHTML = '<option value="">Selecciona una mesa</option>';
      mesasDisponibles.forEach(m => {
      const num = m.numero_mesa ?? m.numero ?? m.id;
      const cap = m.capacidad ? ' · ' + m.capacidad + ' pers.' : '';
      const opt = document.createElement('option');
      opt.value = m.id;
      opt.textContent = 'Mesa ' + num + (m.area_nombre ? ' · ' + m.area_nombre : '') + cap;
      sel.appendChild(opt);
      });
      sel.value = mesaSeleccionada || '';
      if (!mesasDisponibles.length) sel.innerHTML = '<option value="">No hay mesas disponibles para ese horario</option>';
    } catch (err) {
      if (revision === revisionMesas) {
        sel.innerHTML = '<option value="">No se pudieron cargar las mesas</option>';
        aviso('No se pudieron consultar las mesas disponibles: ' + err.message, 'danger');
      }
    } finally {
      if (revision === revisionMesas) sel.disabled = false;
    }
  }

  function abrirCrear() {
    reservaEditId = null;
    document.getElementById('tituloReserva').textContent = 'Nueva reserva';
    document.getElementById('rCliente').value = '';
    document.getElementById('rTelefono').value = '';
    document.getElementById('rFecha').value = '';
    document.getElementById('rHora').value = '';
    document.getElementById('rPersonas').value = 2;
    document.getElementById('rEstado').value = 'confirmada';
    document.getElementById('rObs').value = '';
    void poblarMesas('');
    mReserva.show();
  }

  function abrirEditar(id) {
    const r = RES.find(x => x.id === id);
    if (!r) return;
    reservaEditId = id;
    document.getElementById('tituloReserva').textContent = 'Editar reserva';
    document.getElementById('rCliente').value = r.cliente || '';
    document.getElementById('rTelefono').value = r.telefono ? Validar.formatearTelefono(r.telefono) : '';
    document.getElementById('rFecha').value = String(r.fecha || '').slice(0, 10);
    document.getElementById('rHora').value = r.hora || '';
    document.getElementById('rPersonas').value = r.personas || 2;
    document.getElementById('rEstado').value = r.estado || 'confirmada';
    document.getElementById('rObs').value = r.observaciones || '';
    void poblarMesas(r.mesa_id || '');
    mReserva.show();
  }

  ['rFecha', 'rHora', 'rPersonas'].forEach(id => {
    document.getElementById(id).addEventListener('change', () => void poblarMesas(document.getElementById('rMesa').value));
  });

  document.getElementById('btnGuardarReserva').addEventListener('click', async () => {
    const btn = document.getElementById('btnGuardarReserva');
    // Validación previa: no se envía nada si hay campos vacíos o con formato inválido.
    if (!Validar.formulario('modalReserva')) return;
    conSpinner(btn);
    const payload = {
      cliente: document.getElementById('rCliente').value,
      cliente_telefono: document.getElementById('rTelefono').value,
      fecha: document.getElementById('rFecha').value,
      hora: document.getElementById('rHora').value,
      mesa_id: document.getElementById('rMesa').value || 0,
      personas: parseInt(document.getElementById('rPersonas').value, 10) || 0,
      estado: document.getElementById('rEstado').value,
      observaciones: document.getElementById('rObs').value,
    };
    try {
      const res = reservaEditId
        ? await API.post('/reservas/actualizar/' + reservaEditId, Object.assign({ csrf: CSRF }, payload))
        : await API.post('/reservas/crear', Object.assign({ csrf: CSRF }, payload));
      if (!res.success) { aviso(res.message || 'Error al guardar reserva', 'danger'); return; }
      const id = Number(res.reserva_id);
      const mesaSeleccionada = MESAS.find(m => Number(m.id) === Number(payload.mesa_id));
      const actualizada = {
        id, mesa_id: Number(payload.mesa_id), mesa: mesaSeleccionada ? (mesaSeleccionada.numero_mesa || mesaSeleccionada.numero) : null,
        cliente: payload.cliente, telefono: payload.cliente_telefono, fecha: payload.fecha, hora: payload.hora,
        personas: payload.personas, estado: payload.estado, observaciones: payload.observaciones
      };
      const idx = RES.findIndex(x => Number(x.id) === id);
      if (idx >= 0) RES.splice(idx, 1, actualizada); else RES.push(actualizada);
      pintar();
      mReserva.hide();
      aviso('Reserva guardada correctamente', 'success');
    } catch (err) {
      aviso('No se pudo guardar la reserva: ' + err.message, 'danger');
    } finally {
      liberarSpinner(btn);
    }
  });

  document.addEventListener('DOMContentLoaded', () => {
    pintar();
  });
})();
</script>
JS;

require_once __DIR__ . '/../layout.php';
render_app_layout($titulo, $pagina, $contenido, $scripts_extra);
