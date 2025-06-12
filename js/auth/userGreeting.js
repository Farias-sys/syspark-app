(function () {
  const username =
    sessionStorage.getItem('userName') ||
    localStorage.getItem('userName') ||
    'Usuário';

  const nameEl = document.getElementById('user-name');
  if (nameEl) nameEl.textContent = username;
})();