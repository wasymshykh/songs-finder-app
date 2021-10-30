$('.add-to-playlist').click((e) => {
    const track_id = $(e.target).attr('data-track-id');
    $('#playlistModal').find('input[name="selected-track"]').val(track_id);
    playlistModal.show();
});

    
const playlistModal = new bootstrap.Modal(document.getElementById('playlistModal'), {
    keyboard: false
});

document.getElementById('playlistModal').addEventListener('hidden.bs.modal', function() {
    $('#playlistModal').find('input[name="selected-track"]').val('');
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

const remove_playlist_track_box = (track_id) => {
    $('[data-track-id="'+track_id+'"]').closest('.search-result').parent().remove();
}

$('#delete-playlist-track').click((e) => {
    activate_playlist_select_loader();
    const track_id = $('#playlistModal').find('input[name="selected-track"]').val();
    const playlist_id = $('#playlistModal').find('input[name="selected-playlist"]').val();

    $.ajax({
        url: "api/remove_playlist_track.php",
        data: {'track': track_id, 'playlist': playlist_id},
        method: "GET",
        success: function (data, status) {
            create_success_toast(data.message);
            remove_playlist_track_box(track_id);
            playlistModal.hide();
            deactivate_playlist_select_loader();
        },
        error: function (xhr, status, err) {
            let error_msg = "Unable to send request";
            if (xhr.responseJSON !== undefined) {
                error_msg = xhr.responseJSON.message !== undefined ? xhr.responseJSON.message : 'unable to send request';
            }
            create_error_toast(error_msg);
            setTimeout(() => {
                deactivate_playlist_select_loader();
            }, 1000);
        }
    });

});
