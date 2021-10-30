
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
            <div class="card">
                <div class="card-header row">
                    <div class="col">
                        <h4>Your Playlist - <strong><?=$playlist['playlist_name']?></strong></h4>
                    </div>
                    <div class="col-auto">
                        <a href="<?=href('playlists')?>" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Go Back</a>
                        <a href="<?=href('playlists.php?d='.$playlist['playlist_id'], false)?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
                <div class="card-body row text-center">
                    <div class="col-lg-4">
                        <h4><?=count($playlist_tracks)?></h4>
                        <p><i class="fas fa-music me-2"></i> Tracks</p>
                    </div>
                    <div class="col-lg-4">
                        <h4><?=count($artists)?></h4>
                        <p><i class="fas fa-users me-2"></i> Artists</p>
                    </div>
                    <div class="col-lg-4">
                        <p class="mb-1"><i class="fas fa-calendar-alt me-2"></i>  created on</p>
                        <h4><?=normal_date($playlist['playlist_created'], 'dS F')?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 mt-0">
            <div class="card">
                <div class="card-header row">
                    <div class="col">
                        Tracks
                    </div>
                    <div class="col-auto">
                        <p>
                            <i class="fas fa-file-export"></i>
                            <span class="mx-1">Export to</span>

                            <!-- <a href="<?=href('export.php?s=Spotify&i='.$playlist['playlist_id'], false)?>" class="btn btn-sm btn-dark">Spotify</a> -->
                            <!-- <a href="" class="btn btn-sm btn-dark">Youtube</a> -->
                            <a href="<?=href('export_deezer.php?s=Deezer&i='.$playlist['playlist_id'], false)?>" class="btn btn-sm btn-dark">Deezer</a>
                        </p>
                    </div>
                </div>
                <div class="card-body">
                    
                    <div class="row">
                        <?php foreach ($playlist_tracks as $track): ?>
                        <div class="col-md-4">
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
                                            <img src="<?=$track['mservice_icon']?>" alt="<?=$track['mservice_name']?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?=$track['service_name']?>">
                                        </div>

                                        <div class="search-result-source-add">
                                            <button class="add-to-playlist btn btn-sm btn-primary" data-track-id="<?=$track['ptrack_id']?>">remove from playlist</button>
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
