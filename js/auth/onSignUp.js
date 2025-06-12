$(document).ready(function () {
  $('#signup-form').submit(function (event) {
    event.preventDefault();

    const data = Object.fromEntries(
      $(this).serializeArray().map(({ name, value }) => [name, value.trim()])
    );

    $.ajax({
      type: 'POST',
      url: '../../server/auth/signup.php',
      data,

      success: function (raw) {
        const res = JSON.parse(raw);
        if (res.status === 'success') {
          alert('Conta criada com sucesso! Faça login.');
          window.location.assign('login.html');
        } else {
          $('#signup-error').text(res.message || 'Erro ao criar conta.');
        }
      },

      error: function () {
        $('#signup-error').text('Falha na tentativa de cadastro. Tente novamente.');
      },
    });
  });
});
