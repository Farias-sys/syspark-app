$(document).ready(
    function () {
        $('#login-form').submit(function (event) {
            event.preventDefault()

            const arr = $(this).serializeArray();
            const obj = Object.fromEntries(
                          arr.map(({name, value}) => [name, value])
                        );

            window.location.assign('/pages/protected/parked_vehicles.html')
        })
    }
)