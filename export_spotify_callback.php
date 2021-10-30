<?php

require_once 'app/start.php';

if (!isset($_GET['code'])) {
    die("1- Unable to authenticate");
}
if (!isset($_GET['state'])) {
    die("2- Unable to authenticate");
}
if (!isset($_SESSION['spotify_state']) || !is_array($_SESSION['spotify_state']) || empty($_SESSION['spotify_state']) || !array_key_exists($_GET['state'], $_SESSION['spotify_state'])) {
    die("3- Unable to authenticate");
}

$S = new Services($db);
$P = new Playlists($db);

$code = $_GET['code'];
$playlist_id = $_SESSION['spotify_state'][$_GET['state']]['playlist_id'];

$service = $S->get_service_by_name('Spotify');
if (!$service['status']) {
    die("No service found.");
}
$service = $service['data'];


$playlist = $P->get_playlist_by_id($playlist_id);
if ($playlist['status']) {
    $playlist = $playlist['data'];
    if ($playlist['playlist_user_id'] !== $logged_user['user_id']) {
        $_SESSION['message'] = ['type' => 'error', 'data' => 'You dont have access to playlist'];
        move('playlists.php');
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Playlist does not exists'];
    move('playlists.php');
}

$playlist_tracks = $P->playlist_songs_export_by_playlist_id($playlist['playlist_id'], $service['mservice_id']);
if ($playlist_tracks['status']) {
    $playlist_tracks = $playlist_tracks['data'];
} else {
    $playlist_tracks = [];
}

$playlist_name = $playlist['playlist_name'];
$track_ids = [];

foreach ($playlist_tracks as $track) {
    if ($track['artist_mservice_id'] === $service['mservice_id']) {
        $track_ids[] = $track['track_external_id'];
    } else if (!empty($track['export_external_track_id']) && $track['export_found'] === 'Y') {
        $track_ids[] = $track['export_external_track_id'];
    }
}

$redirect_uri = URL."/export_spotify_callback.php";
$session = new SpotifyWebAPI\Session($service['mservice_client'], $service['mservice_secret'], $redirect_uri);

try {
    $session->requestAccessToken($code);
    
    $api = new SpotifyWebAPI\SpotifyWebAPI();
    $api->setAccessToken($session->getAccessToken());
    
    $playlist_response = $api->createPlaylist([
        'name' => $playlist['playlist_name']
    ]);
    
    $playlist_response = json_decode(json_encode($playlist_response), true);
    
    $api->addPlaylistTracks($playlist_response['id'], $track_ids);

    $_SESSION['message'] = ['type' => 'success', 'data' => 'Playlist imported to spotify'];
    move('playlist.php?i='.$playlist_id);

} catch (Exception $e) {
    $logs = new Logs((new DB())->connect());
    $logs->create("export_spotify_callback.php", "Unable to export playlist to spotify", json_encode(['message' => $e->getMessage(), 'playlist_id' => $playlist_id]));

    $_SESSION['message'] = ['type' => 'error', 'data' => '4- Unable to export playlist to spotify'];
    move('playlist.php?i='.$playlist_id);
}
