
<div class="tarjeta mb-3">
  <div class="barra-filtros">
    <div>
      <label class="form-label" for="fBuscar">Buscar</label>
      <input type="search" id="fBuscar" class="form-control" placeholder="Cliente o teléfono">
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
          <option value="<?= $clave ?>"><?= $texto ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label" for="fPersonas">Personas</label>
      <select id="fPersonas" class="form-select">
        <option value="">Cualquiera</option>
        <option value="1-2">1 a 2</option>
        <option value="3-4">3 a 4</option>
        <option value="5+">5 o más</option>
      </select>
    </div>
    <div class="flex-grow-0">
      <button type="button" id="btnLimpiar" class="btn btn-outline-olivo">Limpiar</button>
    </div>
    <div class="flex-grow-0 ms-auto">
      <button type="button" id="btnNueva" class="btn btn-olivo"><i class="bi bi-plus-lg me-1"></i>Nueva reserva</button>
    </div>
  </div>
</div>

<div class="tarjeta">
  <div class="table-responsive">
    <table class="table tabla-olivo" id="tablaReservas">
      <thead>
        <tr><th>Cliente</th><th>Fecha y hora</th><th>Personas</th><th>Mesa</th><th>Estado</th><th class="text-end">Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($reservas as [$id, $cli, $con, $fec, $hor, $per, $mes, $est, $obs]): ?>
        <tr data-id="<?= $id ?>" data-cliente="<?= htmlspecialchars($cli) ?>" data-contacto="<?= htmlspecialchars($con) ?>"
            data-fecha="<?= $fec ?>" data-hora="<?= $hor ?>" data-personas="<?= $per ?>" data-mesa="<?= $mes ?>"
            data-estado="<?= $est ?>" data-obs="<?= htmlspecialchars($obs) ?>">
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($cli) ?></div>
            <small class="text-secondary"><?= htmlspecialchars($con) ?></small>
          </td>
          <td><?= date('d/m/Y', strtotime($fec)) ?><br><small class="text-secondary"><?= $hor ?></small></td>
          <td><?= $per ?></td>
          <td><?= $nombre_mesa[$mes] ?? 'Sin asignar' ?></td>
          <td><span class="estado estado-<?= $est ?>" data-badge><?= $estados[$est] ?></span></td>
          <td class="text-end text-nowrap">
            <button type="button" class="btn btn-outline-olivo btn-icono btn-editar" title="Editar" aria-label="Editar reserva">
              <i class="bi bi-pencil"></i>
            </button>
            <div class="dropdown d-inline-block">
              <button class="btn btn-outline-secondary btn-icono" data-bs-toggle="dropdown" aria-label="Cambiar estado">
                <i class="bi bi-three-dots-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" data-accion="confirmada"><i class="bi bi-check2-circle me-2"></i>Confirmar</a></li>
                <li><a class="dropdown-item" href="#" data-accion="llego"><i class="bi bi-person-check me-2"></i>Marcar llegada</a></li>
                <li><a class="dropdown-item" href="#" data-accion="atendida"><i class="bi bi-check-all me-2"></i>Marcar atendida</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" data-accion="cancelada"><i class="bi bi-x-circle me-2"></i>Cancelar</a></li>
              </ul>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <tr id="filaVacia" class="d-none"><td colspan="6" class="text-center text-secondary py-4">No hay reservas que coincidan con los filtros.</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: crear / editar reserva -->
<div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="tituloModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formReserva" method="post" action="reservas.php" novalidate>
        <div class="modal-header">
          <h2 class="modal-title fs-5" id="tituloModal">Nueva reserva</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="reserva_id" value="">
          <div class="alert alert-warning py-2 small d-none" id="avisoConflicto" role="alert"></div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="rCliente">Nombre del cliente</label>
              <input type="text" class="form-control" id="rCliente" name="cliente" maxlength="100" required>
              <div class="invalid-feedback">Escribe el nombre del cliente.</div>
            </div>
            <div class="col-12">
              <label class="form-label" for="rContacto">Teléfono o contacto</label>
              <input type="text" class="form-control" id="rContacto" name="contacto" maxlength="80" required>
              <div class="invalid-feedback">Indica un teléfono o contacto.</div>
            </div>
            <div class="col-6">
              <label class="form-label" for="rFecha">Fecha</label>
              <input type="date" class="form-control" id="rFecha" name="fecha" required>
              <div class="invalid-feedback">Elige la fecha.</div>
            </div>
            <div class="col-6">
              <label class="form-label" for="rHora">Hora</label>
              <input type="time" class="form-control" id="rHora" name="hora" required>
              <div class="invalid-feedback">Elige la hora.</div>
            </div>
            <div class="col-6">
              <label class="form-label" for="rPersonas">Personas</label>
              <input type="number" class="form-control" id="rPersonas" name="personas" min="1" max="20" value="2" required>
              <div class="invalid-feedback">Entre 1 y 20 personas.</div>
            </div>
            <div class="col-6">
              <label class="form-label" for="rMesa">Mesa (opcional)</label>
              <select class="form-select" id="rMesa" name="mesa_id">
                <option value="">Sin asignar</option>
                <?php foreach ($mesas as [$mid, $mnom, $mcap, $marea]): ?>
                  <option value="<?= $mid ?>" data-cap="<?= $mcap ?>" data-nombre="<?= $mnom ?>"><?= $mnom ?> · <?= $mcap ?> pers. · <?= $marea ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label" for="rEstado">Estado</label>
              <select class="form-select" id="rEstado" name="estado">
                <?php foreach ($estados as $clave => $texto): ?>
                  <option value="<?= $clave ?>"><?= $texto ?></option>
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
          <button type="submit" class="btn btn-olivo">Guardar reserva</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$scripts_extra = <<<'JS'
<script>
(function () {
  const ESTADOS = { pendiente: 'Pendiente', confirmada: 'Confirmada', llego: 'Llegó', atendida: 'Atendida', cancelada: 'Cancelada' };
  const filas = [...document.querySelectorAll('#tablaReservas tbody tr[data-id]')];
  const vacia = document.getElementById('filaVacia');
  const fB = document.getElementById('fBuscar'), fF = document.getElementById('fFecha'),
        fE = document.getElementById('fEstado'), fP = document.getElementById('fPersonas');

  /* ---------- Filtros ---------- */
  function filtrar() {
    const q = fB.value.trim().toLowerCase();
    let visibles = 0;
    filas.forEach(tr => {
      const d = tr.dataset, p = +d.personas;
      let ok = true;
      if (q && !(d.cliente + ' ' + d.contacto).toLowerCase().includes(q)) ok = false;
      if (fF.value && d.fecha !== fF.value) ok = false;
      if (fE.value && d.estado !== fE.value) ok = false;
      if (fP.value === '1-2' && p > 2) ok = false;
      if (fP.value === '3-4' && (p < 3 || p > 4)) ok = false;
      if (fP.value === '5+' && p < 5) ok = false;
      tr.classList.toggle('d-none', !ok);
      if (ok) visibles++;
    });
    vacia.classList.toggle('d-none', visibles > 0);
  }
  [fB, fF, fE, fP].forEach(el => { el.addEventListener('input', filtrar); el.addEventListener('change', filtrar); });
  document.getElementById('btnLimpiar').addEventListener('click', () => { [fB, fF, fE, fP].forEach(el => el.value = ''); filtrar(); });

  /* ---------- Cambiar estado (solo visual; BACKEND: UPDATE reservas SET estado) ---------- */
  document.querySelectorAll('[data-accion]').forEach(a => a.addEventListener('click', e => {
    e.preventDefault();
    const tr = a.closest('tr'), nuevo = a.dataset.accion;
    if (nuevo === 'cancelada' && !confirm('¿Cancelar la reserva de ' + tr.dataset.cliente + '?')) return;
    tr.dataset.estado = nuevo;
    const b = tr.querySelector('[data-badge]');
    b.className = 'estado estado-' + nuevo;
    b.textContent = ESTADOS[nuevo];
    aviso('Reserva marcada como ' + ESTADOS[nuevo].toLowerCase() + ' (demo)');
    filtrar();
  }));

  /* ---------- Modal ---------- */
  const modal = new bootstrap.Modal(document.getElementById('modalReserva'));
  const form = document.getElementById('formReserva');
  const avisoC = document.getElementById('avisoConflicto');
  const el = form.elements;

  function abrir(tr) {
    form.reset();
    form.classList.remove('was-validated');
    avisoC.classList.add('d-none');
    document.getElementById('tituloModal').textContent = tr ? 'Editar reserva' : 'Nueva reserva';
    if (tr) {
      const d = tr.dataset;
      el.reserva_id.value = d.id; el.cliente.value = d.cliente; el.contacto.value = d.contacto;
      el.fecha.value = d.fecha; el.hora.value = d.hora; el.personas.value = d.personas;
      el.mesa_id.value = d.mesa; el.estado.value = d.estado; el.observaciones.value = d.obs;
    } else {
      el.fecha.value = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10);
    }
    modal.show();
  }
  document.getElementById('btnNueva').addEventListener('click', () => abrir(null));
  document.querySelectorAll('.btn-editar').forEach(b => b.addEventListener('click', () => abrir(b.closest('tr'))));

  /* ---------- Validación y conflictos (BACKEND: repetir esta validación en PHP) ---------- */
  const minutos = h => { const p = h.split(':'); return (+p[0]) * 60 + (+p[1]); };

  function conflicto(d) {
    if (!d.mesa_id) return '';
    const opt = el.mesa_id.selectedOptions[0];
    if (+d.personas > +opt.dataset.cap) {
      return opt.dataset.nombre + ' tiene capacidad para ' + opt.dataset.cap + ' personas y la reserva es para ' + d.personas + '.';
    }
    for (const tr of filas) {
      const t = tr.dataset;
      if (t.id === d.reserva_id || t.mesa !== d.mesa_id || t.fecha !== d.fecha || t.estado === 'cancelada') continue;
      if (Math.abs(minutos(t.hora) - minutos(d.hora)) < 120) {
        return opt.dataset.nombre + ' ya está reservada para ' + t.cliente + ' a las ' + t.hora.slice(0, 5) +
               '. Deja al menos 2 horas entre reservas o elige otra mesa.';
      }
    }
    return '';
  }

  form.addEventListener('submit', e => {
    e.preventDefault();
    form.classList.add('was-validated');
    if (!form.checkValidity()) return;
    const msg = conflicto(Object.fromEntries(new FormData(form)));
    avisoC.textContent = msg;
    avisoC.classList.toggle('d-none', !msg);
    if (msg) return;
    modal.hide();
    aviso('Reserva guardada (demo: todavía no se almacena en la base de datos)');
  });
})();
</script>
JS;
