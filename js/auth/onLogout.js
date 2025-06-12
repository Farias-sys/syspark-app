$(document).ready(function () {
  // the anchor inside <aside> has class="logout"
  $('a.logout').on('click', function (event) {
    event.preventDefault(); // stop the browser from following the <a href>

    $.ajax({
      type: 'POST',
      url: '../../server/auth/logout.php', // adjust path if different
      data: {},                            // no body needed
      success: function (response) {
        let data;
        try {
          data = JSON.parse(response);
        } catch (err) {
          console.error('Logout: invalid JSON', err, response);
          // fall through – we’ll still redirect
        }

        // Clear anything you stored in sessionStorage/localStorage
        sessionStorage.removeItem('userName');
        // …add other keys here if needed …

        // If the server confirms success (or we couldn’t parse), go home:
        if (!data || data.status === 'success') {
          window.location.assign('../login.html'); // same URL as your sidebar href
        } else {
          alert('Erro ao sair. Tente novamente.');
        }
      },
      error: function (xhr, status, err) {
        console.error('Logout AJAX error:', status, err);
        alert('Erro ao comunicar com o servidor. Tente novamente.');
      }
    });
  });
});