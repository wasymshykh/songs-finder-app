<div class="container mt-4">
    <div class="row">

        <div class="col-lg-10 offset-lg-1">

            <div class="artist-row">
                <div class="artist-image">
                    <img src="<?=$artist['artist_image']?>" alt="<?=$artist['artist_name']?>">
                </div>
                <div class="artist-info">
                    <h1><?=$artist['artist_name']?></h1>
                    <p>Albums <span class="badge"><?=count($albums)?></span></p>
                </div>
            </div>

        </div>

        <div class="col-lg-10 offset-lg-1">
            
            <div class="artist-albums">
                <div class="artist-albums-title">
                    <h4><i class="fas fa-music"></i> Albums</h4>
                </div>

                <div class="row">
                    <?php foreach ($albums as $album): ?>
                    <div class="col-lg-4 my-2">
                        <div class="album-box">
                            <div class="album-box-content" style="background-image: radial-gradient(farthest-corner at 40px 40px, rgba(9, 27, 117, 0.8) 0%, rgba(16, 13, 43, 0.5) 100%), url(<?=$album['album_image']?>);">
                                <div class="album-box-img">
                                    <img src="<?=$album['album_image']?>">
                                </div>
                                <div class="album-content">
                                    <h4><?=$album['album_name']?></h4>
                                    <p>Release <span class="badge bg-primary"><?=normal_date($album['album_release_date'], 'M d, Y')?></span></p>
                                </div>
                                <div class="album-tracks">
                                    <div class="submit-loader-btn">
                                        <div class="loader-icon">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </div>
                                        <button type="submit" class="loader-btn btn btn-primary browse-track" data-album-id="<?=$album['album_id']?>" data-artist="<?=$artist['artist_name']?>" data-album-image="<?=$album['album_image']?>">Browse Tracks</button>
                                    </div>
                                </div>
                            </div>

                            <div class="album-tracks-list d-none">
                                <h5>Tracks</h5>

                                <ul class="album-tracks-list-tracks"></ul>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

    </div>
</div>


<div class="page-audio-player"></div>

<?=js_link('calamansi.min', true);?>

<script>
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

    function active_loader (button) {
        button.blur().parent().addClass('active');
    }

    function deactive_loader (button) {
        button.blur().parent().removeClass('active');
    }

    const get_player_volume = () => {
        if (localStorage.getItem("app_player_volume") === null || localStorage.getItem("app_player_volume") === undefined) {
            localStorage.setItem("app_player_volume", "50");
        }
        return localStorage.getItem("app_player_volume")
    }

    const store_player_volume = (v) => {
        localStorage.setItem("app_player_volume", v);
    }

    const page_state = {
        'player': {
            'exists': false,
            'object': null,
            'skin': '<?=URL?>/assets/skins',
            'volume': get_player_volume()
        }
    }

    const create_player_container = () => {
        $('.page-audio-player').html($(`<div class="page-audio-player-container" data-skin="ayon"></div>`));
    }

    const create_player = (playlist, album_image) => {
        create_player_container();

        if (page_state.player.exists) {
            page_state.player.object.destroy();
        }

        page_state.player.object = new Calamansi(document.querySelector('.page-audio-player-container'), {
            skin: page_state.player.skin,
            playlists: playlist,
            defaultAlbumCover: album_image,
            loadTrackInfoOnPlay: false,
            volume: page_state.player.volume
        });

        page_state.player.exists = true;

        page_state.player.object.on('trackEnded', (e) => {
            page_state.player.object.destroy();
            page_state.player.exists = false;
        });

        page_state.player.object.on('canplaythrough', (e) => {
            page_state.player.object.audio.play();
        })

        page_state.player.object.on('volumechange', (e) => {
            store_player_volume((page_state.player.object.audio.volume*100).toFixed());
        })
    }

    const make_playlist_format = (file_url, song_name, artist) => {
        return {
            'Default': [
                {
                    source: file_url,
                    info: {
                        title: song_name,
                        artist: artist
                    }
                }
            ]
        };
    }

    const play_song = (file_url, song_name, artist, album_image) => {
        create_player(make_playlist_format(file_url, song_name, artist), album_image);
    }


    function play_track (target) {
        let track_div = $(target).parent();

        let file_url = track_div.attr('data-track-preview');
        let song_name = track_div.attr('data-track-name');
        let artist = track_div.attr('data-track-artist');
        let album_image = track_div.attr('data-album-image');

        play_song(file_url, song_name, artist, album_image);
    }

    function generate_album_track_div (track, artist_name, album_image) {
        let track_li = $(`<li class="album-tracks-track" data-track-id="${track.track_id}" data-track-name="${track.track_name}" data-track-preview="${track.track_preview}" data-track-artist="${artist_name}" data-album-image="${album_image}">
                            <span class="album-tracks-play"><i class="fas fa-play"></i></span> 
                            <span class="album-tracks-duration">${track.track_duration}</span>
                            <span class="album-tracks-name">${track.track_name}</span> 
                            <span class="album-tracks-playlist"><i class="fas fa-plus"></i></span>
                        </li>`);

        track_li.find('.album-tracks-play').click(e => { play_track (e.target); })
        return track_li;
    }

    function populate_tracks_in_album(target, tracks, artist_name, album_image) {
        let t_p = target.closest('.album-box').find('.album-tracks-list');

        let t = t_p.find('.album-tracks-list-tracks');
        t.html('');

        tracks.forEach(track => {
            t.append(generate_album_track_div(track, artist_name, album_image));
        });

        t_p.removeClass('d-none');
    }

    $('.browse-track').click(function (e) {
        e.preventDefault();
        
        var t = $(e.target);
        active_loader(t);

        var album_id = t.attr('data-album-id');
        var artist_name = t.attr('data-artist');
        var album_image = t.attr('data-album-image');
        var service = "<?=$artist['mservice_name']?>";
        
        setTimeout(function () { 

            $.ajax({
                url: "api/album_tracks.php",
                data: {'album': album_id, 'service': service},
                method: "GET",
                success: function (data, status) {
                    create_success_toast("Successfully listed tracks");

                    populate_tracks_in_album(t, data.message.tracks, artist_name, album_image);

                    deactive_loader(t);
                },
                error: function (xhr, status, err) {
                    let error_msg = xhr.responseJSON.message;
                    create_error_toast(error_msg);
                    setTimeout(() => {
                        deactive_loader(t);
                    }, 1000);

                }
            });

        }, <?=$settings->fetch('login_submit_timeout')?>);
    });

</script>
