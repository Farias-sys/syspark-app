const getParkingSpots = () => {
    return $.ajax({
        type: 'GET',
        url: '../../server/routes/parking_spots/read.php',
        dataType: 'json'
    })
};

const postParkingSpots = (numberOfSpots) => {
    return $.ajax({
        type: 'POST',
        url: '../../server/routes/parking_spots/create.php',
        data: { count: numberOfSpots }
    })
};

const toggleParkingSpot = (id) => {
    return $.ajax({
        type: 'POST',
        url: '../../server/routes/parking_spots/toggle_parking_spot.php',
        data: { id: id }
    })
}

const renderParkingSpotsTable = (parkingSpots) => {
    const $tbody = $('#spotsTableBody').empty();

    parkingSpots.forEach(spot => {

        let badgeClass, badgeText;
      if (spot.status === 'available') {
        badgeClass = 'free';    badgeText = 'Livre';
      } else if (spot.status === 'busy') {
        badgeClass = 'busy';    badgeText = 'Ocupada';
      } else {
        badgeClass = 'maintenance'; badgeText = 'Manutenção';
      }

      const plateCell = spot.status === 'busy'
        ? spot.in_use_by
        : '—';

      let actionBtn;
      if (spot.status === 'innoperant') {
        actionBtn = `<button class="btn-action ok"
                               data-spot-id="${spot.id}"
                               data-toggle="modal"
                               data-target="#activate-parking-spot-modal">
                       Ativar vaga
                     </button>`;
      } else {
        actionBtn = `<button class="btn-action danger"
                               data-spot-id="${spot.id}"
                               data-toggle="modal"
                               data-target="#deactivate-parking-spot-modal">
                       Desativar vaga
                     </button>`;
      }

      const $tr = $(`
        <tr>
          <td>${spot.id.toString().padStart(2,'0')}</td>
          <td><span class="badge ${badgeClass}">${badgeText}</span></td>
          <td>${plateCell}</td>
          <td class="actions">${actionBtn}</td>
        </tr>
      `);

      $tbody.append($tr);
    })
}

const listParkingSpots = () => {
    getParkingSpots().done((response) => {
        if (response.status === 'success') {
            renderParkingSpotsTable(response.data);
        } else {
            alert('Erro ao carregar os pontos de estacionamento!')
        }
    })
}

$(document).ready(
    () => {

        listParkingSpots();

        $('#create-parking-spot-form').submit(function (event) {
            event.preventDefault()

            const arr = $(this).serializeArray();
            const obj = Object.fromEntries(
                arr.map(({ name, value }) => [name, value])
            );

            postParkingSpots(obj.spotNumber).done((response) => {
                const data = JSON.parse(response);
                if (data.status === 'success') {
                    $('#create-parking-spot-modal').modal('hide');
                    listParkingSpots();
                } else {
                    alert('Erro ao criar os pontos de estacionamento!')
                }
            })
        });

        $(document).on('click', 'button[data-target="#deactivate-parking-spot-modal"]', function() {
            const id = $(this).data('spot-id');
            $('#deactivate-parking-spot-modal .btn-danger').data('spot-id', id);
          });
          
        $('#deactivate-parking-spot-modal .btn-danger').on('click', function() {
            const id = $(this).data('spot-id');
            
            toggleParkingSpot(id)
                .done(response => {
                    response = JSON.parse(response);
                    if (response.status === 'success') {
                        listParkingSpots();
                    } else {
                        alert('Erro ao desativar a vaga!');
                    }
                })
                .always(() => {
                    $('#deactivate-parking-spot-modal').modal('hide');
                });
        });

        $(document).on('click', 'button[data-target="#activate-parking-spot-modal"]', function() {
            const id = $(this).data('spot-id');
            $('#activate-parking-spot-modal .btn-success').data('spot-id', id);
        });

        $('#activate-parking-spot-modal .btn-success').on('click', function() {
            const id = $(this).data('spot-id');
            
            toggleParkingSpot(id)
                .done(response => {
                    response = JSON.parse(response);
                    if (response.status === 'success') {
                        listParkingSpots();
                    } else {
                        alert('Erro ao ativar a vaga!');
                    }
                })
                .always(() => {
                    $('#activate-parking-spot-modal').modal('hide');
                });
        });

    }
)