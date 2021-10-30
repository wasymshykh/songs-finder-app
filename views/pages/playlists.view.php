
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
                <div class="card-body row">
                
                    <div class="col-md-12">
                        <?php if(isset($playlist_error) && !empty($playlist_error)): ?>
                            <div class="alert alert-danger">
                                <?=$playlist_error?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="<?=href('playlists')?>" class="row align-items-end" method="post">
                            <input type="hidden" name="create-playlist" value="1">

                            <div class="col">
                                <label for="playlist_name" class="form-label">Create new playlist</label>
                                <input type="text" name="playlist_name" id="playlist_name" placeholder="Playlist name" class="form-control" value="<?=$_POST['playlist_name']??''?>" required>
                            </div>

                            <div class="col-auto ms-2">
                                <div class="submit-loader-btn">
                                    <div class="loader-icon">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                    <button type="submit" class="loader-btn btn btn-primary"><i class="fas fa-plus"></i> Add</button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-12 mt-4">
            <h3>Your Playlists</h3>
        </div>

        <?php foreach ($playlists as $playlist): ?>
            <div class="col-lg-5 mt-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <?=$playlist['playlist_name']?>
                            </div>
                            <div class="col-auto">
                                <a href="<?=href('playlist.php?i='.$playlist['playlist_id'], false)?>" class="btn btn-sm btn-primary">view playlist</a>
                                <a href="<?=href('playlists.php?d='.$playlist['playlist_id'], false)?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body playlist-box">
                        <div class="row">
                            <div class="col-lg-4">
                                <h5><?=array_key_exists($playlist['playlist_id'], $playlist_tracks) ? count($playlist_tracks[$playlist['playlist_id']]) : '0'?></h5>
                                <p>Tracks</p>
                            </div>
                            <div class="col-lg-4">
                                <h5><?=array_key_exists($playlist['playlist_id'], $playlist_tracks) ? $playlist_tracks[$playlist['playlist_id']]['total_artists_count'] : '0'?></h5>
                                <p>Artists</p>
                            </div>
                            <div class="col-lg-4">
                                <p class="mb-1">created on</p>
                                <h5><?=normal_date($playlist['playlist_created'], 'dS F')?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    
    </div>
</div>


