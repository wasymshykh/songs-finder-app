<?php

require_once 'app/start.php';

$P = new Playlists($db);

$playlists = [];

$_playlists = $P->get_playlists_details_by_user($logged_user['user_id']);
if ($_playlists['status']) {
    $playlists = $_playlists['data'];

    // ids 
    $playlist_ids = "";
    foreach ($playlists as $playlist) {
        if (!empty($playlist_ids)) { $playlist_ids .= ", "; }
        $playlist_ids .= "'".$playlist['playlist_id']."'";
    }

    $playlists_tracks = $P->playlist_songs_in_playlists($playlist_ids);

    if ($playlists_tracks['status']) {
        $playlists_tracks = $playlists_tracks['data'];

    } else {
        $playlists_tracks = [];
    }
    // sorting into playlist[:id] => $tracks

    $playlist_tracks = [];

    foreach ($playlists_tracks as $ptrack) {
        if (!array_key_exists($ptrack['ptrack_playlist_id'], $playlist_tracks)) {
            $playlist_tracks[$ptrack['ptrack_playlist_id']] = [];
        }
        $playlist_tracks[$ptrack['ptrack_playlist_id']][] = $ptrack;
    }


    foreach ($playlist_tracks as $i => $tracks) {
        $_artists = [];

        foreach ($tracks as $track) {
            if (!in_array($track['artist_id'], $_artists)) {
                $_artists[] = $track['artist_id'];
            }
        }
        
        $playlist_tracks[$i]['total_artists_count'] = count($_artists);
    }
    
}


$playlist_error = false;

if (isset($_POST) && !empty($_POST)) {

    if (isset($_POST['create-playlist'])) {

        if (isset($_POST['playlist_name']) && !empty($_POST['playlist_name']) && is_string($_POST['playlist_name']) && !empty(normal_text($_POST['playlist_name']))) {
            $playlist_name = normal_text($_POST['playlist_name']);

            $created = $P->add($playlist_name, $logged_user['user_id']);

            if ($created['status']) {

                $_SESSION['message'] = ['type' => 'success', 'data' => 'Playlist "'.$playlist_name.'" is successfully created!'];
                move('playlists.php');

            } else {
                $playlist_error = "Unable to create playlist, try again.";
            }

        } else {
            $playlist_error = "Playlist cannot be empty";
        }

    }

}


require_once DIR.'views/layout/header.view.php';
require_once DIR.'views/pages/playlists.view.php';
require_once DIR.'views/layout/footer.view.php';
