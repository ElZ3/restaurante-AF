// OLIVO REAL · JavaScript común
// Cliente fetch para comunicación con el backend (endpoints del Front Controller).

// Lee la respuesta JSON también en códigos 4xx/5xx para no perder el mensaje del backend
// (p. ej. "Selecciona una mesa disponible"). Solo lanza error si no hay JSON utilizable.
async function leerRespuestaApi(resp) {
  const datos = await resp.json().catch(() => null);
  if (!datos || typeof datos !== 'object') throw new Error(resp.ok ? 'Respuesta no válida del servidor' : `HTTP ${resp.status}`);
  if (!resp.ok) { datos.success = false; datos.message = datos.message || datos.mensaje || `HTTP ${resp.status}`; }
  return datos;
}

const API = {
  post: async (url, data = {}) => {
    const form = new FormData();
    for (const [key, value] of Object.entries(data)) {
      form.append(key, value);
    }
    const resp = await fetch(url, { method: 'POST', body: form });
    return leerRespuestaApi(resp);
  },
  get: async (url) => {
    const resp = await fetch(url, { method: 'GET' });
    return leerRespuestaApi(resp);
  }
};

// ---------- Validaciones de formularios (deben coincidir con app/core/helpers.php) ----------
// Uso en HTML: data-validar="nombre" | "telefono-sv" | "entero-positivo" | "decimal-positivo"
// Antes de enviar: if (!Validar.formulario(contenedor)) return;
const Validar = (() => {
  const REGEX = {
    telefono: /^\+503\s[267]\d{3}-\d{4}$/,
    nombre: /^\p{L}+(?: \p{L}+)*$/u,
    decimal: /^\d+(\.\d{1,2})?$/,
    entero: /^\d+$/,
  };
  const PREFIJO_TEL = '+503 ';
  const TECLAS_CONTROL = ['Backspace', 'Delete', 'Tab', 'Enter', 'Escape', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];

  // Máscara +503 XXXX-XXXX a partir de los dígitos escritos o pegados.
  function formatearTelefono(valor) {
    let d = String(valor || '').replace(/\D/g, '');
    if (d.startsWith('503')) d = d.slice(3); // el prefijo propio no cuenta como número
    d = d.slice(0, 8);
    return PREFIJO_TEL + d.slice(0, 4) + (d.length > 4 ? '-' + d.slice(4) : '');
  }

  function limpiarNombre(valor) {
    return String(valor || '').replace(/[^\p{L} ]/gu, '').replace(/ {2,}/g, ' ').replace(/^ /, '');
  }

  function etiqueta(el) {
    const lbl = el.id ? document.querySelector('label[for="' + el.id + '"]') : null;
    return lbl ? lbl.textContent.replace(/\(.*?\)/g, '').trim() : (el.name || 'Campo');
  }

  // Devuelve el mensaje de error del campo, o '' si es válido.
  function campo(el) {
    const tipo = el.dataset.validar || '';
    if (tipo === 'telefono-sv' && el.value.replace(/\D/g, '').length > 3) el.value = formatearTelefono(el.value);
    if (tipo === 'nombre') el.value = el.value.replace(/\s+/g, ' ').trim();
    const valor = el.type === 'file' ? (el.files && el.files.length ? 'x' : '') : String(el.value || '').trim();
    const vacio = valor === '' || (tipo === 'telefono-sv' && valor.replace(/\D/g, '').length <= 3);
    const nombre = etiqueta(el);

    if (vacio) return el.required ? 'El campo "' + nombre + '" es obligatorio.' : '';
    if (tipo === 'nombre' && !REGEX.nombre.test(valor)) return '"' + nombre + '" solo puede contener letras y espacios.';
    if (tipo === 'telefono-sv' && !REGEX.telefono.test(valor)) return '"' + nombre + '" debe tener el formato +503 XXXX-XXXX (iniciando en 2, 6 o 7).';
    if (tipo === 'entero-positivo' && (!REGEX.entero.test(valor) || Number(valor) <= 0)) return '"' + nombre + '" debe ser un número entero mayor a 0.';
    if (tipo === 'decimal-positivo' && (!REGEX.decimal.test(valor) || Number(valor) <= 0)) return '"' + nombre + '" debe ser un número mayor a 0 (máximo 2 decimales).';
    if (el.max !== '' && el.type === 'number' && Number(valor) > Number(el.max)) return '"' + nombre + '" no puede ser mayor a ' + el.max + '.';
    if (el.minLength > 0 && valor.length < el.minLength) return '"' + nombre + '" debe tener al menos ' + el.minLength + ' caracteres.';
    if (typeof el.checkValidity === 'function' && !el.checkValidity()) return 'Revisa el formato de "' + nombre + '".';
    return '';
  }

  // Valida todos los campos visibles del contenedor; marca los inválidos y avisa el primero.
  function formulario(contenedor) {
    const raiz = typeof contenedor === 'string' ? document.getElementById(contenedor) : contenedor;
    if (!raiz) return true;
    let primero = null;
    let mensaje = '';
    raiz.querySelectorAll('input, select, textarea').forEach(el => {
      if (el.disabled || el.type === 'hidden' || el.type === 'search' || el.type === 'radio' || el.type === 'checkbox') return;
      const error = campo(el);
      el.classList.toggle('is-invalid', !!error);
      if (error && !primero) { primero = el; mensaje = error; }
    });
    if (primero) {
      primero.focus();
      if (typeof aviso === 'function') aviso(mensaje, 'danger');
      return false;
    }
    return true;
  }

  // Bloqueo de caracteres durante el tipeo (delegado: funciona también en modales).
  document.addEventListener('keydown', e => {
    const el = e.target;
    if (!el || !el.dataset || !el.dataset.validar || e.ctrlKey || e.metaKey || e.altKey || TECLAS_CONTROL.includes(e.key) || e.key.length !== 1) return;
    const tipo = el.dataset.validar;
    if (tipo === 'telefono-sv' && !/\d/.test(e.key)) e.preventDefault();
    if (tipo === 'nombre' && !/[\p{L} ]/u.test(e.key)) e.preventDefault();
    if (tipo === 'entero-positivo' && !/\d/.test(e.key)) e.preventDefault();
    if (tipo === 'decimal-positivo' && !/[\d.]/.test(e.key)) e.preventDefault();
  });

  // Limpieza al escribir o pegar, y se retira la marca de error.
  document.addEventListener('input', e => {
    const el = e.target;
    if (!el || !el.dataset) return;
    const tipo = el.dataset.validar;
    if (tipo === 'telefono-sv') el.value = formatearTelefono(el.value);
    if (tipo === 'nombre') { const v = limpiarNombre(el.value); if (v !== el.value) el.value = v; }
    if (tipo === 'entero-positivo' && /\D/.test(el.value)) el.value = el.value.replace(/\D/g, '');
    if (el.classList && el.classList.contains('is-invalid') && !campo(el)) el.classList.remove('is-invalid');
  });

  // El teléfono muestra +503 por defecto al enfocarlo.
  document.addEventListener('focusin', e => {
    const el = e.target;
    if (el && el.dataset && el.dataset.validar === 'telefono-sv' && el.value.trim() === '') el.value = PREFIJO_TEL;
  });

  // Al abrir un modal se limpian las marcas de error de la vez anterior.
  document.addEventListener('show.bs.modal', e => {
    if (e.target && e.target.querySelectorAll) e.target.querySelectorAll('.is-invalid').forEach(x => x.classList.remove('is-invalid'));
  });

  return { campo, formulario, formatearTelefono, REGEX };
})();

document.addEventListener('DOMContentLoaded', () => {
  const menu = document.getElementById('menuLateral');
  const overlay = document.getElementById('overlayMenu');
  const btn = document.getElementById('btnMenu');
  if (!menu || !btn) return;

  const alternar = (abrir) => menu.classList.toggle('abierto', abrir);
  btn.addEventListener('click', () => alternar(!menu.classList.contains('abierto')));
  overlay.addEventListener('click', () => alternar(false));
});

// Manejo automático de formularios con data-api-form
document.querySelectorAll('form[data-api-form]').forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!Validar.formulario(form)) return;
    const btn = form.querySelector('button[type="submit"]');
    conSpinner(btn);
    try {
      const formData = new FormData(form);
      const datos = Object.fromEntries(formData.entries());
      const resultado = await API.post(form.action, datos);
      if (resultado.ok || resultado.success) {
        aviso(resultado.mensaje || 'Operación exitosa', 'success');
        if (resultado.redirect) {
          window.location.href = resultado.redirect;
        }
      } else {
        aviso(resultado.mensaje || 'Error en la operación', 'danger');
      }
    } catch (err) {
      aviso(err.message || 'Error de conexión', 'danger');
    } finally {
      liberarSpinner(btn);
    }
  });
});

// ---------- Notificaciones toast: animadas, con progreso, interactivas ----------
const CONFIG_AVISO = {
  success: { titulo: 'Éxito', icono: '✓', clase: 'aviso-success', duracion: 4200 },
  danger: { titulo: 'Atención', icono: '✕', clase: 'aviso-danger', duracion: 5600 },
  warning: { titulo: 'Aviso', icono: '⚠', clase: 'aviso-warning', duracion: 5000 },
  info: { titulo: 'Información', icono: 'ℹ', clase: 'aviso-info', duracion: 4200 },
};

function aviso(texto, tipo, opciones) {
  opciones = opciones || {};
  const cfg = Object.prototype.hasOwnProperty.call(CONFIG_AVISO, tipo) ? CONFIG_AVISO[tipo] : CONFIG_AVISO.success;
  let cont = document.getElementById('avisos');
  if (!cont) {
    cont = document.createElement('div');
    cont.id = 'avisos';
    cont.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    cont.style.zIndex = 2000;
    document.body.appendChild(cont);
  }
  while (cont.children.length >= 3) {
    cont.removeChild(cont.firstElementChild);
  }

  const t = document.createElement('div');
  t.className = 'toast aviso ' + cfg.clase + ' show';
  t.setAttribute('role', tipo === 'danger' ? 'alert' : 'status');

  const icono = document.createElement('div');
  icono.className = 'aviso-icono';
  icono.textContent = cfg.icono;

  const body = document.createElement('div');
  body.className = 'aviso-body';
  const tituloEl = document.createElement('div');
  tituloEl.className = 'aviso-titulo';
  tituloEl.textContent = cfg.titulo;
  const textoEl = document.createElement('div');
  textoEl.className = 'aviso-texto';
  textoEl.textContent = String(texto == null ? '' : texto);
  body.appendChild(tituloEl);
  body.appendChild(textoEl);

  const cerrar = document.createElement('button');
  cerrar.type = 'button';
  cerrar.className = 'aviso-cerrar';
  cerrar.setAttribute('aria-label', 'Cerrar notificación');
  cerrar.innerHTML = '&times;';

  t.appendChild(icono);
  t.appendChild(body);
  t.appendChild(cerrar);

  const pro = document.createElement('div');
  pro.className = 'toast-progreso';
  const barra = document.createElement('span');
  pro.appendChild(barra);
  t.appendChild(pro);

  cont.appendChild(t);

  let saliendo = false;
  let timer = null;
  const duracion = (opciones && opciones.delay) || cfg.duracion;
  barra.style.animationDuration = duracion + 'ms';

  const dismiss = function () {
    if (saliendo) return;
    saliendo = true;
    clearTimeout(timer);
    t.classList.add('saliendo');
    const h = function (e) {
      if (e && e.animationName && e.animationName !== 'toastSalida') return;
      t.removeEventListener('animationend', h);
      t.remove();
    };
    t.addEventListener('animationend', h);
  };

  const programarSalida = function () {
    clearTimeout(timer);
    timer = setTimeout(dismiss, duracion);
  };
  timer = setTimeout(dismiss, duracion);

  cerrar.addEventListener('click', dismiss);
  t.addEventListener('mouseenter', function () {
    clearTimeout(timer);
    barra.style.animationPlayState = 'paused';
  });
  t.addEventListener('mouseleave', function () {
    barra.style.animationPlayState = 'running';
    programarSalida();
  });

  return t;
}

// ---------- Esqueleto de carga (skeleton) ----------
function skeletonHTML(tipo, filas) {
  const n = filas || 5;
  let html = '';
  if (tipo === 'fila') {
    for (let i = 0; i < n; i++) {
      const w1 = 40 + ((i * 17) % 30);
      const w2 = 25 + ((i * 29) % 40);
      html += '<tr class="skeleton-fila"><td colspan="7"><span class="skeleton-line" style="width:' + w1 + '%"></span><span class="skeleton-line" style="width:' + w2 + '%"></span></td></tr>';
    }
  } else if (tipo === 'tarjeta') {
    for (let i = 0; i < n; i++) {
      html += '<div class="skeleton-tarjeta"><span class="skeleton-line" style="width:40%"></span><span class="skeleton-line" style="width:85%"></span><span class="skeleton-line" style="width:60%"></span></div>';
    }
  } else if (tipo === 'grid') {
    for (let i = 0; i < n; i++) {
      html += '<div class="skeleton-card"><div class="skeleton-img"></div><div class="skeleton-card-body"><span class="skeleton-line" style="width:60%"></span><span class="skeleton-line" style="width:40%"></span></div></div>';
    }
  } else {
    for (let i = 0; i < n; i++) {
      const h = 30 + ((i * 22) % 28);
      const w = 45 + ((i * 31) % 40);
      html += '<div class="skeleton-carga" style="height:' + h + 'px"><span class="skeleton-line" style="width:' + w + '%"></span></div>';
    }
  }
  return html;
}

function mostrarSkeleton(id, tipo, filas) {
  const el = document.getElementById(id);
  if (!el) return false;
  el.innerHTML = skeletonHTML(tipo || 'bloques', filas);
  return true;
}

function ocultarSkeleton(id) {
  const el = document.getElementById(id);
  if (el) el.innerHTML = '';
}

// ---------- Spinner en botones ----------
function conSpinner(btn) {
  if (!btn) {
    return function () { };
  }
  const htmlAnterior = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
  const restaurar = function () {
    btn.disabled = false;
    btn.innerHTML = htmlAnterior;
  };
  btn.__restaurar = restaurar;
  return restaurar;
}

function liberarSpinner(btn) {
  if (btn && btn.__restaurar) {
    btn.__restaurar();
    delete btn.__restaurar;
  }
}
