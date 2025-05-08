const getParkedVehicles = () => {
    return $.ajax({
        type: 'GET',
        url: '../../server/routes/parked_vehicles/read.php',
        dataType: 'json'
    });
};

const postParkedVehicle = (vehicle) => {
    return $.ajax({
        type: 'POST',
        url: '../../server/routes/parked_vehicles/create.php',
        data: vehicle
    });
};

const registerExitVehicle = (id) => {
    return $.ajax({
        type: 'POST',
        url: '../../server/routes/parked_vehicles/register_vehicle_exit.php',
        data: { id }
    });
};

const deleteParkedVehicle = (id) => {
    return $.ajax({
        type: 'POST',
        url: '../../server/routes/parked_vehicles/delete.php',
        data: { id }
    });
};

const getParkingSpots = () => {
    return $.ajax({
        type: 'GET',
        url: '../../server/routes/parking_spots/read.php',
        dataType: 'json'
    });
};

function formatTime(ts) {
    const d = new Date(ts);
    return d.getHours().toString().padStart(2, '0') +
        ':' + d.getMinutes().toString().padStart(2, '0');
}

function formatDuration(startTs, endTs) {
    const diffMs = new Date(endTs) - new Date(startTs);
    const minsTotal = Math.floor(diffMs / 60000);
    const h = Math.floor(minsTotal / 60);
    const m = minsTotal % 60;
    return `${h} h ${m} min`;
}

function loadAvailableSpots() {
    getParkingSpots()
        .done(resp => {
            if (resp.status !== 'success') return alert('Erro ao carregar vagas!');
            const freeSpots = resp.data.filter(s => s.status === 'available');
            const $sel = $('#spot')
                .empty()
                .append('<option value="" disabled selected>Selecione uma vaga</option>');
            freeSpots.forEach(s => {
                // pad to two digits if you like:
                const label = s.id.toString().padStart(2, '0');
                $sel.append(`<option value="${s.id}">${label}</option>`);
            });
        })
        .fail(() => alert('Falha de rede ao buscar vagas.'));
}
  

function renderTables(vehicles) {
    const $current = $('#currentTableBody').empty();
    const $history = $('#historyTableBody').empty();

    vehicles.forEach(v => {
        if (!v.date_end) {
            const $tr = $(`
                <tr>
                    <td>${v.plate}</td>
                    <td>${v.model}</td>
                    <td>${v.color}</td>
                    <td>${v.parking_spot}</td>
                    <td>${formatTime(v.date_start)}</td>
                    <td class="actions">
                        <button class="btn-action ok"
                                        data-vehicle-id="${v.id}"
                                        data-toggle="modal"
                                        data-target="#register-exit-vehicle-modal">
                            Registrar saída
                        </button>
                        <button class="btn-action danger"
                                        data-vehicle-id="${v.id}"
                                        data-toggle="modal"
                                        data-target="#delete-parked-vehicle-modal">
                            Deletar
                        </button>
                    </td>
                </tr>
            `);
            $current.append($tr);
        } else {
            const duration = formatDuration(v.date_start, v.date_end);
            const value = parseFloat(v.value).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
            const $tr = $(`
                <tr>
                    <td>${v.plate}</td>
                    <td>${v.model}</td>
                    <td>${v.color}</td>
                    <td>${formatTime(v.date_start)}</td>
                    <td>${formatTime(v.date_end)}</td>
                    <td>${duration}</td>
                    <td>${value}</td>
                </tr>
            `);
            $history.append($tr);
        }
    });
}

function listParkedVehicles() {
    getParkedVehicles()
        .done(response => {
            if (response.status === 'success') {
                renderTables(response.data);
            } else {
                alert('Erro ao carregar veículos estacionados!');
            }
        })
        .fail(() => {
            alert('Falha de rede ao buscar veículos.');
        });
}

$(document).ready(
    () => {
        listParkedVehicles();

        $('#park-vehicle-modal').on('show.bs.modal', loadAvailableSpots);

        $('#park-vehicle-form').on('submit', function(event) {
            event.preventDefault();
            const arr = $(this).serializeArray();
            const obj = Object.fromEntries(
                arr.map(({ name, value }) => [name, value])
            );
            console.log(arr);
            postParkedVehicle(obj)
                .done(resp => {
                    const data = JSON.parse(resp);
                    if (data.status === 'success') {
                        $('#park-vehicle-modal').modal('hide');
                        listParkedVehicles();
                    } else {
                        alert('Erro ao estacionar veículo!');
                    }
                })
                .fail(() => alert('Erro de rede ao estacionar.'))
                .always(() => this.reset());
        });

        $(document).on(
            'click',
            'button[data-target="#register-exit-vehicle-modal"]',
            function() {
                const id = $(this).data('vehicle-id');
                $('#register-exit-vehicle-modal .btn-success').data('vehicle-id', id);
            }
        );
        $('#register-exit-vehicle-modal .btn-success').on('click', function() {
            const id = $(this).data('vehicle-id');
            registerExitVehicle(id)
                .done(res => {
                    res = JSON.parse(res);
                    if (res.status === 'success') {
                        listParkedVehicles();
                    } else {
                        alert('Erro ao registrar saída!');
                    }
                })
                .fail(() => alert('Erro de rede ao registrar saída.'))
                .always(() =>
                    $('#register-exit-vehicle-modal').modal('hide')
                );
        });

        $(document).on(
            'click',
            'button[data-target="#delete-parked-vehicle-modal"]',
            function() {
                const id = $(this).data('vehicle-id');
                $('#delete-parked-vehicle-modal .btn-danger').data('vehicle-id', id);
            }
        );
        $('#delete-parked-vehicle-modal .btn-danger').on('click', function() {
            const id = $(this).data('vehicle-id');
            deleteParkedVehicle(id)
                .done(res => {
                    res = JSON.parse(res);
                    if (res.status === 'success') {
                        listParkedVehicles();
                    } else {
                        alert('Erro ao deletar veículo!');
                    }
                })
                .fail(() => alert('Erro de rede ao deletar.'))
                .always(() =>
                    $('#delete-parked-vehicle-modal').modal('hide')
                );
        });
    }
);
