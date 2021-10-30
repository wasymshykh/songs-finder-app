<?php

require_once 'app/start.php';

$S = new Services($db);
$P = new Playlists($db);

$service_name = normal_text($_GET['s']);
$playlist_id = normal_text($_GET['i']);
$code = normal_text($_GET['code']);

if (!isset($_GET['code'])) {
    die("Unable to authenticate");
}

$service = $S->get_service_by_name($service_name);
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

$check = file_get_contents("https://connect.deezer.com/oauth/access_token.php?app_id=".($service['mservice_client'])."&secret=".($service['mservice_secret'])."&code=$code");
if ($check === 'wrong code') {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Unable to authenticate deezer'];
    move('playlist.php?i='.$playlist_id);
}
$parsed = [];
parse_str($check, $parsed);
if (!array_key_exists('access_token', $parsed)) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Unable to authenticate deezer'];
    move('playlist.php?i='.$playlist_id);
}

$client = new wasymshykh\DeezerAPI\DeezerAPIClient();
$client->setAccessToken($parsed['access_token']);
$api = new wasymshykh\DeezerAPI\DeezerAPI($client);

$playlist_created = $api->createPlaylist($playlist_name);

if (property_exists($playlist_created, 'id')) {
    $response = $api->addTracksToPlaylist($playlist_created->id, $track_ids);
    if ($response == '1') {
        $_SESSION['message'] = ['type' => 'success', 'data' => 'Playlist imported to deezer'];
        move('playlist.php?i='.$playlist_id);
    } else {
        $_SESSION['message'] = ['type' => 'error', 'data' => 'Unable to add tracks to playlist'];
        move('playlist.php?i='.$playlist_id);
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Unable to create playlist'];
    move('playlist.php?i='.$playlist_id);
}
