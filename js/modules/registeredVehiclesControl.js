const getRegisteredVehicles = () => {
    return $.ajax({
        type: 'GET',
        url: '../../server/routes/vehicles/read.php',
        dataType: 'json'
    });
};

const postRegisteredVehicle = (vehicle) => {
    return $.ajax({
        type: 'POST',
        url: '../../server/routes/vehicles/create.php',
        data: vehicle,
        dataType: 'json'
    });
};

const updateRegisteredVehicle = (vehicle) => {
    return $.ajax({
        type: 'POST',
        url: '../../server/routes/vehicles/update.php',
        data: vehicle,
        dataType: 'json'
    });
};

const deleteRegisteredVehicle = (id) => {
    return $.ajax({
        type: 'POST',
        url: '../../server/routes/vehicles/delete.php',
        data: { id },
        dataType: 'json'
    });
};

function formatDate(dateStr) {
    const d = new Date(dateStr);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
}

function renderRegisteredTable(vehicles) {
    const $tbody = $('#registeredTableBody').empty();
    vehicles.forEach(v => {
        const $tr = $(`
            <tr>
                <td>${v.plate}</td>
                <td>${v.model}</td>
                <td>${v.color}</td>
                <td>${formatDate(v.date_created)}</td>
                <td class="actions">
                    <button class="btn-action ok"
                                    data-vehicle-id="${v.id}"
                                    data-toggle="modal"
                                    data-target="#edit-vehicle-modal">
                        Editar
                    </button>
                    <button class="btn-action danger"
                                    data-vehicle-id="${v.id}"
                                    data-toggle="modal"
                                    data-target="#delete-vehicle-modal">
                        Deletar
                    </button>
                </td>
            </tr>
        `);
        $tbody.append($tr);
    });
}

// Fetch and display the list
function listRegisteredVehicles() {
    getRegisteredVehicles()
        .done(resp => {
            if (resp.status === 'success') {
                renderRegisteredTable(resp.data);
            } else {
                alert('Erro ao carregar veículos cadastrados!');
            }
        })
        .fail(() => {
            alert('Falha de rede ao buscar veículos cadastrados.');
        });
}

$(document).ready(() => {
    listRegisteredVehicles();

    $('#create-vehicle-modal form').on('submit', function(e) {
        e.preventDefault();

        const arr = $(this).serializeArray();
        const obj = Object.fromEntries(
            arr.map(({ name, value }) => [name, value])
        );

        postRegisteredVehicle(obj)
            .done(res => {
                if (res.status === 'success') {
                    $('#create-vehicle-modal').modal('hide');
                    listRegisteredVehicles();
                } else {
                    alert('Erro ao cadastrar veículo!');
                }
            })
            .fail(() => alert('Erro de rede ao cadastrar veículo.'))
            .always(() => this.reset());
    });

    // Prepare edit form
    $(document).on(
        'click',
        'button[data-target="#edit-vehicle-modal"]',
        function() {
            const id = $(this).data('vehicle-id');
            const $tr = $(this).closest('tr');
            const plate = $tr.find('td').eq(0).text();
            const model = $tr.find('td').eq(1).text();
            const color = $tr.find('td').eq(2).text();

            const $form = $('#edit-vehicle-modal form');
            $form.data('vehicle-id', id);
            $form.find('#edit-license-plate').val(plate);
            $form.find('#edit-model').val(model);
            $form.find('#edit-color').val(color);
        }
    );

    $('#edit-vehicle-modal form').on('submit', function(e) {
        e.preventDefault();
        const id = $(this).data('vehicle-id');

        const arr = $(this).serializeArray();
        const obj = Object.fromEntries(
            arr.map(({ name, value }) => [name, value])
        );

        updateRegisteredVehicle({ id, ...obj })
            .done(res => {
                if (res.status === 'success') {
                    $('#edit-vehicle-modal').modal('hide');
                    listRegisteredVehicles();
                } else {
                    alert('Erro ao salvar alterações!');
                }
            })
            .fail(() => alert('Erro de rede ao salvar alterações.'));
    });

    // Prepare delete
    $(document).on(
        'click',
        'button[data-target="#delete-vehicle-modal"]',
        function() {
            const id = $(this).data('vehicle-id');
            $('#delete-vehicle-modal .btn-danger').data('vehicle-id', id);
        }
    );

    // Confirm delete
    $('#delete-vehicle-modal .btn-danger').on('click', function() {
        const id = $(this).data('vehicle-id');

        deleteRegisteredVehicle(id)
            .done(res => {
                if (res.status === 'success') {
                    listRegisteredVehicles();
                } else {
                    alert('Erro ao deletar veículo!');
                }
            })
            .fail(() => alert('Erro de rede ao deletar.'))
            .always(() => $('#delete-vehicle-modal').modal('hide'));
    });
});
