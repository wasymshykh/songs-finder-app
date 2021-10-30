
<?php if(isset($success) && !empty($success)): ?>
    <div class="alert alert-success">
        <?=$success?>
    </div>
<?php endif; ?>

<?php if(isset($error) && !empty($error)): ?>
    <div class="alert alert-danger">
        <?=$error?>
    </div>
<?php endif; ?>

<div class="container mt-4">
    <div class="row">

        <div class="col-lg-12">
            <div class="card" style="border-bottom: 0;">
                <div class="card-header playlist-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0"><span class="badge rounded-pill bg-primary">Playlist</span> <?=$playlist['playlist_name']?></h5>
                        </div>
                        <div class="col-auto">
                            <a href="<?=href('playlists')?>" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Go Back</a>
                            <a href="<?=href('playlists.php?d='.$playlist['playlist_id'], false)?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                </div>
                <div class="card-body row text-center playlist-box">
                    <div class="col-lg-4">
                        <h5><?=count($playlist_tracks)?></h5>
                        <p><i class="fas fa-music me-2"></i> Tracks</p>
                    </div>
                    <div class="col-lg-4">
                        <h5><?=count($artists)?></h5>
                        <p><i class="fas fa-users me-2"></i> Artists</p>
                    </div>
                    <div class="col-lg-4">
                        <p class="mb-0"><i class="fas fa-calendar-alt me-2"></i>  created on</p>
                        <h5><?=normal_date($playlist['playlist_created'], 'dS F')?></h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 mt-0">
            <div class="card" style="border-top: 0;">
                <div class="card-header playlist-header">
                    <div class="row align-items-center">
                        <div class="col">
                            Tracks
                        </div>
                        <div class="col-auto">
                            <small>
                                <i class="fas fa-file-export"></i>
                                <span class="ms-2">Export to</span>
                            </small>

                            <a href="<?=href('export_spotify.php?s=Spotify&i='.$playlist['playlist_id'], false)?>" class="btn btn-sm btn-spotify ms-2">Spotify</a>
                            <a href="<?=href('export_youtube.php?s=Youtube&i='.$playlist['playlist_id'], false)?>" class="btn btn-sm btn-youtube ms-2">Youtube</a>
                            <a href="<?=href('export_deezer.php?s=Deezer&i='.$playlist['playlist_id'], false)?>" class="btn btn-sm btn-deezer ms-2">Deezer</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    
                    <div class="row">
                        <?php foreach ($playlist_tracks as $track): ?>
                        <div class="col-md-3">
                            <div class="row px-2 py-2 m-0 gy-0 gx-2 search-result">
                                <div class="col-sm-3">
                                    <div class="search-result-img<?=$track['mservice_name']==="Youtube"?'-no':''?>" data-name="<?=$track['track_name']?>" data-artist="<?=$track['artist_name']?>" data-preview="<?=$track['track_preview']?>" data-image="<?=$track['album_image']?>">
                                        <img src="<?=$track['album_image']?>" alt="<?=$track['track_name']?>">
                                        <div class="search-result-img-duration">
                                            <?=$track['track_duration']?>
                                        </div>
                                        <div class="search-result-img-play">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-9 search-result-text">
                                    <h5><?=$track['track_name']?></h5>
                                    <p><?=$track['artist_name']?></p>
                                    <?php if (!empty($track['album_name'])): ?>
                                        <p class="search-result-album"><strong>album</strong> <?=$track['album_name']?></p>
                                    <?php endif; ?>

                                    <div class="search-result-source">
                                        <div class="search-result-source-icon">
                                            <img src="<?=$track['mservice_icon']?>" alt="<?=$track['mservice_name']?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?=$track['mservice_name']?>">
                                        </div>

                                        <div class="search-result-source-add">
                                            <button class="add-to-playlist btn btn-sm btn-primary" data-track-id="<?=$track['ptrack_id']?>"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
        </div>
    
    </div>
</div>

<div class="modal fade" id="playlistModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <p class="m-0 p-0" style="line-height: 1">Are you sure?</p>
                <button type="button" class="btn-sm btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="playlist-select-radio-boxes">
                    <input type="hidden" name="selected-track" value="">
                    <input type="hidden" name="selected-playlist" value="<?=$playlist['playlist_id']?>">
                </div>
                <button class="btn btn-primary" id="delete-playlist-track">Yes, delete</button>
                <div class="playlist-loader d-none"><i class="fas fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>
</div>


<div class="page-audio-player">
</div>


<?=js_link('calamansi.min', true);?>

<div class="modal fade" id="playlistModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <p class="m-0 p-0" style="line-height: 1">select playlist</p>
                <button type="button" class="btn-sm btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="playlist-select-radio-boxes">
                    <input type="hidden" name="selected-track" value="">
                    <input type="hidden" name="selected-service" value="">

                    <?php foreach ($playlists as $playlist): ?>
                    <div class="playlist-select-radio">
                        <input type="radio" name="playlist-select" class="playlist-select" id="p-s-<?=$playlist['playlist_id']?>" value="<?=$playlist['playlist_id']?>">
                        <label for="p-s-<?=$playlist['playlist_id']?>">
                            <div class="playlist-select-checked"><i class="fas fa-check-circle"></i></div>
                            <div class="playlist-select-unchecked"><i class="fa fa-circle-o"></i></div>
                            <div class="playlist-select-name">
                                <?=$playlist['playlist_name']?>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="playlist-loader d-none"><i class="fas fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>
</div>

<script>

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
    
    $('.search-result-img').each((i, el) => {
        el.addEventListener('click', (e) => {
            var song_name = e.target.getAttribute('data-name').trim();
            var artist = e.target.getAttribute('data-artist').trim();
            var file_url = e.target.getAttribute('data-preview').trim();
            var album_image = e.target.getAttribute('data-image').trim();

            play_song(file_url, song_name, artist, album_image)
        })
    });

</script>
