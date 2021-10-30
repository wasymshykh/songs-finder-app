<?php

require_once 'app/start.php';

$S = new Services($db);
$P = new Playlists($db);

$service_name = normal_text($_GET['s']);
$playlist_id = normal_text($_GET['i']);

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

$playlist_tracks = $P->playlist_songs_by_playlist_id($playlist['playlist_id']);
if ($playlist_tracks['status']) {
    $playlist_tracks = $playlist_tracks['data'];
} else {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Add tracks to playlist to export'];
    move('playlist.php?i='.$playlist['playlist_id']);
}

// checking if the songs are of same service as export

$same_service = [];
$other_service = [];

foreach ($playlist_tracks as $track) {
    if ($track['mservice_name'] !== $service['mservice_name']) {
        $other_service[] = $track;
    } else {
        $same_service[] = $track;
    }
}

// checking if the spotify access code is available 
$token = $S->get_access_token_of_service($service['mservice_id'], false);
if ($token['status']) {
    $access_code = $token['data'];
} else {
    // creating log for no access code available
    $logs = new Logs((new DB())->connect());
    $logs->create("export_spotify.php", "Unable to get access code from database", json_encode(['message' => "Unable to get access code from database during spotify export attempt", 'service_name' => $service_name, 'playlist_id' => $playlist_id]));

    $_SESSION['message'] = ['type' => 'error', 'data' => 'Service token error. Contact administrator.'];
    move('playlist.php?i='.$playlist['playlist_id']);
}

// finding songs on platform
$finder = new Finder($db);

$other_service_track_ids_str = "";

foreach ($other_service as $i => $track) {

    if (!empty($other_service_track_ids_str)) { $other_service_track_ids_str .= ","; }
    $other_service_track_ids_str .= "'".$track['track_id']."'";

    $other_service[$i]['export'] = [
        'export_insert' => true,
        'export_track_id' => $track['track_id'], 'export_mservice_id' => $service['mservice_id'],'export_external_track_id' => NULL, 'export_found' => 'N'
    ];

    $result = $finder->spotify_song_finder($track['artist_name'], $track['track_name'], $access_code['atoken_token'], 1);
    if ($result['status']) {
        $other_service[$i]['export']['export_external_track_id'] = $result['track_id'];
        $other_service[$i]['export']['export_found'] = 'Y';
    }

}

// updating export cache table

$exporter = new Exporter($db);
$export_tracks = $exporter->get_export_of_songs($other_service_track_ids_str);
$export_tracks_by_id = [];

if ($export_tracks['status']) {
    $export_tracks = $export_tracks['data'];
    foreach ($export_tracks as $track) {
        $export_tracks_by_id[$track['export_track_id']] = $track;
    }
} else {
    $export_tracks = [];
}

foreach ($other_service as $i => $track) {
    $other_service[$i]['export']['export_insert'] = !array_key_exists($track['track_id'], $export_tracks_by_id);
}

$result = $exporter->insert_exports($other_service);
if (!$result['status']) {
    $_SESSION['message'] = ['type' => 'error', 'data' => '3- Unable to export playlist'];
    move('playlist.php?i='.$playlist['playlist_id']);
}


$redirect_uri = URL."/export_spotify_callback.php";
$session = new SpotifyWebAPI\Session($service['mservice_client'], $service['mservice_secret'], $redirect_uri);
$state = $session->generateState();
$options = [
    'scope' => [
        'playlist-modify-public',
        'playlist-modify-private'
    ],
    'state' => $state,
];

if (!isset($_SESSION['spotify_state']) || !is_array($_SESSION['spotify_state'])) {
    $_SESSION['spotify_state'] = [];
}
$_SESSION['spotify_state'][$state] = ['playlist_id' => $playlist['playlist_id']];

header('Location: ' . $session->getAuthorizeUrl($options));
die();
