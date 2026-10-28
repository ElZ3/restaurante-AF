// OLIVO REAL · JavaScript común
document.addEventListener('DOMContentLoaded', () => {
  const menu = document.getElementById('menuLateral');
  const overlay = document.getElementById('overlayMenu');
  const btn = document.getElementById('btnMenu');
  if (!menu || !btn) return;

  const alternar = (abrir) => menu.classList.toggle('abierto', abrir);
  btn.addEventListener('click', () => alternar(!menu.classList.contains('abierto')));
  overlay.addEventListener('click', () => alternar(false));
});

function aviso(texto, tipo) {
  tipo = tipo || 'success';
  let cont = document.getElementById('avisos');
  if (!cont) {
    cont = document.createElement('div');
    cont.id = 'avisos';
    cont.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    cont.style.zIndex = 2000;
    document.body.appendChild(cont);
  }
  const t = document.createElement('div');
  t.className = 'toast align-items-center text-bg-' + tipo + ' border-0';
  t.setAttribute('role', 'status');
  t.innerHTML = '<div class="d-flex"><div class="toast-body"></div>' +
    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button></div>';
  t.querySelector('.toast-body').textContent = texto;
  cont.appendChild(t);
  t.addEventListener('hidden.bs.toast', () => t.remove());
  new bootstrap.Toast(t, { delay: 3200 }).show();
}