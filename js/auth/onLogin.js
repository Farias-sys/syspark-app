$(document).ready(
    function () {
        $('#login-form').submit(function (event) {
            event.preventDefault()

            const arr = $(this).serializeArray();
            const obj = Object.fromEntries(
                          arr.map(({name, value}) => [name, value])
                        );
            
            $.ajax({
                type: 'POST',
                url: '../../server/auth/auth.php',
                data: {
                    login: obj.user,
                    password: obj.pwd
                },
                success: function (response) {
                    const data = JSON.parse(response);
                    if (data.status === 'success') {
                        sessionStorage.setItem('userName', data.data.name);
                        window.location.assign('/pages/protected/parked_vehicles.html');
                    } else {
                        const errorMessageHelper = document.getElementById('error-message');
                        errorMessageHelper.innerHTML = 'Usuário ou senha inválidos!';
                    }
                }

            })
        })
    }
)