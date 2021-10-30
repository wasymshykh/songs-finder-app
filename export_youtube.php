<?php

require_once 'app/start.php';

$S = new Services($db);
$P = new Playlists($db);

$service_name = normal_text($_GET['s']);
$playlist_id = normal_text($_GET['i']);

$service = $S->get_service_by_name('Youtube');
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

    $result = $finder->youtube_song_finder($track['artist_name'], $track['track_name'], $service['mservice_api_key'], 5);
    if ($result['status']) {
        $other_service[$i]['export']['export_external_track_id'] = $result['track_id'];
        $other_service[$i]['export']['export_found'] = 'Y';
    }

}

// updating export cache table

$exporter = new Exporter($db);
$export_tracks = $exporter->get_export_of_songs_of_service($other_service_track_ids_str, $service['mservice_id']);
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

$client = new Google_Client();
$client->setAuthConfig(['client_id' => $service['mservice_client'], 'client_secret' => $service['mservice_secret']]);
$client->setScopes(['https://www.googleapis.com/auth/youtube']);

$state = Ramsey\Uuid\Uuid::uuid4()->toString();
if (!isset($_SESSION['youtube_state']) || !is_array($_SESSION['youtube_state'])) {
    $_SESSION['youtube_state'] = [];
}
$_SESSION['youtube_state'][$state] = ['playlist_id' => $playlist['playlist_id']];

$client->setAccessType('offline');
$client->setState($state);

$redirect_uri = URL."/export_youtube_callback.php";
$client->setRedirectUri($redirect_uri);

$youtube_url = $client->createAuthUrl();

header("location: $youtube_url");
die();
