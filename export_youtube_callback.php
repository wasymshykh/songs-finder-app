<?php

require_once 'app/start.php';

if (!isset($_GET['code'])) {
    die("1- Unable to authenticate");
}
if (!isset($_GET['state'])) {
    die("2- Unable to authenticate");
}
if (!isset($_SESSION['youtube_state']) || !is_array($_SESSION['youtube_state']) || empty($_SESSION['youtube_state']) || !array_key_exists($_GET['state'], $_SESSION['youtube_state'])) {
    die("3- Unable to authenticate");
}

$S = new Services($db);
$P = new Playlists($db);

$code = $_GET['code'];
$playlist_id = $_SESSION['youtube_state'][$_GET['state']]['playlist_id'];

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

$client = new Google_Client();
$client->setAuthConfig(['client_id' => $service['mservice_client'], 'client_secret' => $service['mservice_secret']]);
$client->setScopes(['https://www.googleapis.com/auth/youtube']);

$redirect_uri = URL."/export_youtube_callback.php";
$client->setRedirectUri($redirect_uri);

$accessToken = $client->fetchAccessTokenWithAuthCode($code);

if (isset($accessToken['error']) && !empty($accessToken['error'])) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Youtube access error, try again'];
    move('playlist.php?i='.$playlist_id);
}

$client->setAccessToken($accessToken);
$service = new Google_Service_YouTube($client);

$_playlist = new Google_Service_YouTube_Playlist();
$playlistSnippet = new Google_Service_YouTube_PlaylistSnippet();
$playlistSnippet->setTitle($playlist['playlist_name']);
$_playlist->setSnippet($playlistSnippet);

$playlistStatus = new Google_Service_YouTube_PlaylistStatus();
$playlistStatus->setPrivacyStatus('private');
$_playlist->setStatus($playlistStatus);

$playlist_response = $service->playlists->insert('snippet,status', $_playlist);

$client->setUseBatch(true);
$batch = $service->createBatch();

foreach ($track_ids as $i => $track_id) {

    $playlistItem = new Google_Service_YouTube_PlaylistItem();
    $playlistItemSnippet = new Google_Service_YouTube_PlaylistItemSnippet();
    $playlistItemSnippet->setPlaylistId($playlist_response->id);

    $resourceId = new Google_Service_YouTube_ResourceId();
    $resourceId->setKind('youtube#video');
    $resourceId->setVideoId($track_id);

    $playlistItemSnippet->setResourceId($resourceId);
    $playlistItem->setSnippet($playlistItemSnippet);

    $batch_item = $service->playlistItems->insert('snippet', $playlistItem);

    $batch->add($batch_item, $i."-step");

}

$results = $batch->execute();

$_SESSION['message'] = ['type' => 'success', 'data' => 'Playlist imported to youtube'];
move('playlist.php?i='.$playlist_id);
