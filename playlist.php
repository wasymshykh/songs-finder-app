<?php

require_once 'app/start.php';

$P = new Playlists($db);

if (isset($_GET) && !empty($_GET) && isset($_GET['i']) && !empty(normal_text($_GET['i'])) && is_numeric($_GET['i'])) {

    $playlist_id = normal_text($_GET['i']);
    $playlist = $P->get_playlist_by_id($playlist_id);

    if ($playlist['status']) {

        $playlist = $playlist['data'];

        if ($playlist['playlist_user_id'] === $logged_user['user_id']) {

            $playlist_tracks = $P->playlist_songs_by_playlist_id($playlist['playlist_id']);
            if ($playlist_tracks['status']) {
                $playlist_tracks = $playlist_tracks['data'];
            } else {
                $playlist_tracks = [];
            }

        } else {
            $_SESSION['message'] = ['type' => 'error', 'data' => 'You dont have access to playlist'];
            move('playlists.php');
        }

    } else {
        $_SESSION['message'] = ['type' => 'error', 'data' => 'Playlist does not exists'];
        move('playlists.php');
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Wrong data passed'];
    move('playlists.php');
}


$artists = [];
foreach ($playlist_tracks as $artist) {
    if (!in_array($artist['artist_id'], $artists)) {
        $artists[] = $artist['artist_id'];
    }
}


$css_before = [
    css_link('calamansi.min', true)
];

$js_after = [
    js_link('playlist', true)
];

$popper_js = true;

require_once DIR.'views/layout/header.view.php';
require_once DIR.'views/pages/playlist.view.php';
require_once DIR.'views/layout/footer.view.php';
