// Click rápido no card para alternar status via AJAX
document.addEventListener('click', async (ev) => {
  const card = ev.target.closest('.sticker-card[data-fig-id]');
  if (!card) return;
  if (ev.target.closest('.rep-btn')) return;
  const id = card.dataset.figId;
  const csrf = document.querySelector('meta[name="csrf"]').content;
  const res = await fetch('index.php?r=toggle', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'figurinha_id=' + id + '&csrf=' + encodeURIComponent(csrf)
  });
  const data = await res.json();
  if (data.ok) {
    card.classList.toggle('obtida', data.quantidade > 0);
    card.classList.toggle('repetida', data.quantidade > 1);
    const badge = card.querySelector('.badge-rep');
    if (data.quantidade > 1) {
      if (badge) badge.textContent = '+' + (data.quantidade - 1);
      else card.insertAdjacentHTML('beforeend', `<span class="badge bg-warning text-dark badge-rep">+${data.quantidade-1}</span>`);
    } else if (badge) badge.remove();
  }
});

// Botões + / - de repetidas
document.addEventListener('click', async (ev) => {
  const btn = ev.target.closest('.rep-btn');
  if (!btn) return;
  ev.stopPropagation();
  const id = btn.dataset.figId;
  const delta = btn.dataset.delta;
  const csrf = document.querySelector('meta[name="csrf"]').content;
  const res = await fetch('index.php?r=ajustar', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'figurinha_id=' + id + '&delta=' + delta + '&csrf=' + encodeURIComponent(csrf)
  });
  const data = await res.json();
  if (data.ok) location.reload();
});
