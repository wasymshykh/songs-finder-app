$('.add-to-playlist').click((e) => {
    const track_id = $(e.target).attr('data-track-id');
    const service_id = $(e.target).attr('data-service-id');

    $('#playlistModal').find('input[name="selected-track"]').val(track_id);
    $('#playlistModal').find('input[name="selected-service"]').val(service_id);

    playlistModal.show();
});

const create_error_toast = (message) => {
    $('.toast-error').remove();
    let html = `
        <div class="toast-error">
            <p><strong>Error!</strong></p>
            <p>${message}</p>
        </div>
    `;
    setTimeout(() => {
        $('.toast-error').remove();
    }, 1000);
    $('body').append(html)
}

const create_success_toast = (message) => {
    $('.toast-success').remove();
    let html = `
        <div class="toast-success">
            <p><strong>Success!</strong></p>
            <p>${message}</p>
        </div>
    `;
    setTimeout(() => {
        $('.toast-success').remove();
    }, 1000);
    $('body').append(html)
}

const activate_playlist_select_loader = () => {
    $('.playlist-loader').removeClass('d-none');
}
const deactivate_playlist_select_loader = () => {
    $('.playlist-loader').addClass('d-none');
}

$('.playlist-select').change((e) => {
    activate_playlist_select_loader();

    const playlist_id = e.target.value;
    const track_id = $('#playlistModal').find('input[name="selected-track"]').val();
    const service_id = $('#playlistModal').find('input[name="selected-service"]').val();

    $.ajax({
        url: "api/add_playlist_track.php",
        data: {'playlist': playlist_id, 'track': track_id, 'service': service_id},
        method: "GET",
        success: function (data, status) {
            create_success_toast(data.message);
            playlistModal.hide();
            $('.playlist-select:checked')[0].checked = false;
            deactivate_playlist_select_loader();
        },
        error: function (xhr, status, err) {
            let error_msg = xhr.responseJSON.message;
            create_error_toast(error_msg);
            setTimeout(() => {
                $('.playlist-select:checked')[0].checked = false;
                deactivate_playlist_select_loader();
            }, 1000);
        }
    });

});

const playlistModal = new bootstrap.Modal(document.getElementById('playlistModal'), {
    keyboard: false
});

document.getElementById('playlistModal').addEventListener('hidden.bs.modal', function() {
    $('#playlistModal').find('input[name="selected-track"]').val('');
    $('#playlistModal').find('input[name="selected-service"]').val('');
});
