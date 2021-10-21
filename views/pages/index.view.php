
<div class="container mt-4">
    <div class="row">
        <div class="col-lg-6 offset-lg-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0 text-center font-weight-bold">Search</h5>
                </div>
                <div class="card-body row">
                
                    <div class="col-md-12">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach($errors as $error):?>
                                <li><?=$error?></li>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?=href('index')?>" class="row" method="post">                            
                            <div class="col-md-12">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" name="search" id="search" placeholder="Search" class="form-control" value="<?=$_POST['search']??''?>" required>
                            </div>
                            <div class="col-md-12 mt-4">
                                <div class="submit-loader-btn">
                                    <div class="loader-icon">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                    <button type="submit" class="loader-btn btn btn-primary"><i class="fas fa-search"></i> Search</button>
                                </div>
                            </div>
                        </form>

                        <?php if (!empty($songs)): ?>
                        <div class="row g-2">
                            <div class="col-sm-12 mt-4">
                                <hr>
                                <h4><i class="fas fa-music"></i> Songs</h4>
                            </div>

                            <?php foreach ($songs as $song): ?>
                            <div class="col-md-4">
                                <div class="row px-0 py-1 m-0 gy-0 gx-2 search-result">
                                    <div class="col-sm-4">
                                        <div class="search-result-img" data-name="<?=$song['song_name']?>" data-artist="<?=$song['artist_name']?>" data-preview="<?=$song['song_preview']?>" data-image="<?=$song['album_image']?>">
                                            <img src="<?=$song['album_image']?>" alt="<?=$song['song_name']?>">
                                            <div class="search-result-img-duration">
                                                <?=$song['song_duration']?>
                                            </div>
                                            <div class="search-result-img-play">
                                                <i class="fas fa-play"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-8 search-result-text">
                                        <h5><?=$song['song_name']?></h5>
                                        <p><?=$song['artist_name']?></p>
                                        <?php if (!empty($song['album_name'])): ?>
                                            <p class="search-result-album"><strong>album</strong> <?=$song['album_name']?></p>
                                        <?php endif; ?>

                                        <div class="search-result-source">
                                            <div class="search-result-source-icon">
                                                <img src="<?=$song['service_icon']?>" alt="<?=$song['service_name']?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?=$song['service_name']?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <div class="col-sm-12 mt-4">
                                <h4><i class="fas fa-guitar"></i> Artists</h4>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-audio-player">
</div>


<?=js_link('calamansi.min', true);?>

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
    })


    const form_state = { submit: false };
    function active_loader (button) {
        button.blur().parent().addClass('active');
    }
    function disable_fields (form) {
        $(form).find('input').attr('readonly', 'true');
    }
    $('form').submit(function (e) {
        if (!form_state.submit) {
            e.preventDefault();
            
            var t = $(e.target);
            disable_fields (t)
            active_loader(t.find('button[type="submit"]'));
            
            form_state.submit = true;
            setTimeout(function () { t.submit(); }, 100);
        }
    });

</script>
