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

    if ($service['mservice_name'] === 'Deezer') {
        $result = $finder->deezer_song_finder($track['artist_name'], $track['track_name'], 1);
        if ($result['status']) {
            $other_service[$i]['export']['export_external_track_id'] = $result['track_id'];
            $other_service[$i]['export']['export_found'] = 'Y';
        }
    }

}

// updating export cache table

$exporter = new Exporter($db);
$cache_export = [];
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

$redirect_uri = urlencode(URL."export_deezer_callback.php?s=Deezer&i=".$playlist['playlist_id']);
$app_id = $service['mservice_client'];
$perms = "manage_library";

$deezer_url = "https://connect.deezer.com/oauth/auth.php?app_id=$app_id&redirect_uri=$redirect_uri&perms=$perms";

header("location: $deezer_url");
