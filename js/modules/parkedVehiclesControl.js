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
        data: { id },
        dataType: 'json'
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

const getPricing = () => {
    return $.ajax({
        type: 'GET',
        url: '../../server/routes/pricing/get.php',
        dataType: 'json'
    });
};

const updatePricing = (price_per_min, fixed_fee) => {
    return $.ajax({
        type: 'POST',
        url: '../../server/routes/pricing/update.php',
        data: { price_per_min, fixed_fee },
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

const formatCurrencyBRL = val => {
    return parseFloat(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

function showExitSummary(data) {
    $('#res-plate').text(data.plate);
    $('#res-model').text(data.model);
    $('#res-color').text(data.color);
    $('#res-start').text(formatTime(data.date_start));
    $('#res-end').text(formatTime(data.date_end));
    $('#res-duration').text(formatDuration(data.date_start, data.date_end));
    $('#res-value').text(formatCurrencyBRL(data.value));
    $('#exit-summary-modal').modal('show');
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
                const label = s.spot_number.toString().padStart(2,'0');
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
                    <td>${v.spot_number}</td>
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

$(document).ready(() => {
    listParkedVehicles();

    $('#park-vehicle-modal').on('show.bs.modal', loadAvailableSpots);

    $('#park-vehicle-form').on('submit', function(event) {
        event.preventDefault();
        const arr = $(this).serializeArray();
        const obj = Object.fromEntries(
            arr.map(({ name, value }) => [name, value])
        );
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
                if (res.status === 'success') {
                    listParkedVehicles();
                    showExitSummary(res.data);
                } else {
                    alert('Erro ao registrar saída!');
                }
            })
            .fail(() => alert('Erro de rede ao registrar saída.'))
            .always(() => $('#register-exit-vehicle-modal').modal('hide'));
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
            .always(() => $('#delete-parked-vehicle-modal').modal('hide'));
    });

    $('#pricingModal').on('show.bs.modal', () => {
        getPricing()
            .done(res => {
                if (res.status === 'success') {
                    $('#pricePerMin').val(res.data.price_per_min);
                    $('#fixedFee').val(res.data.fixed_fee);
                } else {
                    alert('Erro ao obter tarifas!');
                }
            })
            .fail(() => alert('Falha de rede ao buscar tarifas.'));
    });

    $('#pricingForm').on('submit', function(e) {
        e.preventDefault();
        const pricePerMin = $('#pricePerMin').val();
        const fixedFee = $('#fixedFee').val();

        updatePricing(pricePerMin, fixedFee)
            .done(res => {
                if (res.status === 'success') {
                    $('#pricingModal').modal('hide');
                    alert('Tarifas salvas com sucesso!');
                } else {
                    alert('Erro ao salvar tarifas!');
                }
            })
            .fail(() => alert('Falha de rede ao salvar tarifas.'));
    });
});

(() => {
    const $plate = $('#plate');
    const $model = $('#model');
    const $color = $('#color');
    const $list = $('#plateList');

    $plate.on('input', async function() {
        const q = this.value.toUpperCase();
        if (q.length < 2) {
            $list.empty();
            return;
        }
        try {
            const res = await $.getJSON('../../server/routes/parked_vehicles/suggest.php', { q });
            if (res.status !== 'success') return;
            $list.empty();
            res.data.forEach(v =>
                $('<option>')
                    .val(v.plate)
                    .text(`${v.plate} – ${v.model}`)
                    .appendTo($list)
            );
        } catch (err) {
            console.error(err);
        }
    });

    $plate.on('change blur', async function() {
        const plate = this.value.toUpperCase();
        if (!plate) return;
        try {
            const res = await $.getJSON('../../server/routes/parked_vehicles/get.php', { plate });
            if (res.status === 'success' && res.data) {
                $model.val(res.data.model).prop('readonly', true);
                $color.val(res.data.color).prop('readonly', true);
            } else {
                $model.val('').prop('readonly', false);
                $color.val('').prop('readonly', false);
            }
        } catch (err) {
            console.error(err);
        }
    });
})();
