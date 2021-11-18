<?php

require_once 'app/start.php';

if (!$logged) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Authentication required to access page'];
    move('login.php');
}

$artist_id = $_GET['i'];

$services = new Services($db);
$finder = new Finder($db);
$caches = new Caches($db);


// checking cache table

$artist = $caches->get_artist_by_id ($artist_id);

$albums = [];

if ($artist['status']) {
    
    $artist = $artist['data'];

    if ($artist['mservice_name'] === 'Spotify') {
        $token = $services->get_access_token_of_service($artist['mservice_id'], true);
        if ($token['status']) {
            $token = $token['data'];
            $results = $finder->spotify_search_artist_albums ($artist['artist_external_id'], $token['atoken_token']);
            if ($results['status']) {
                $albums = $results['albums'];
            }
        }
    } else {
        $results = $finder->deezer_search_artist_albums ($artist['artist_external_id']);
        if ($results['status']) {
            $albums = $results['albums'];
        }
    }

} else {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Unable to find the artist'];
    move('index.php');
}




require_once DIR.'views/layout/header.view.php';
require_once DIR.'views/pages/artist.view.php';
require_once DIR.'views/layout/footer.view.php';
