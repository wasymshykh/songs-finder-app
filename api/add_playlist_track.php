<?php

require_once '../app/start.php';


if (isset($_GET['playlist']) && !empty($_GET['playlist']) && !empty(normal_text($_GET['playlist'])) && is_numeric($_GET['playlist'])) {

    $playlist_id = normal_text($_GET['playlist']);

} else {
    end_response(403, "Playlist is not provided", true);
}

if (isset($_GET['service']) && !empty($_GET['service']) && !empty(normal_text($_GET['service'])) && is_numeric($_GET['service'])) {

    $service_id = normal_text($_GET['service']);

} else {
    end_response(403, "Service is not provided", true);
}


if (isset($_GET['track']) && !empty($_GET['track']) && !empty(normal_text($_GET['track']))) {

    $track_external_id = normal_text($_GET['track']);

} else {
    end_response(403, "Track is not provided", true);
}

// checking if playlist exists for the user

$P = new Playlists($db);

$result = $P->is_user_playlist_exist($playlist_id, $logged_user['user_id']);
if (!$result['status']) {
    end_response(403, "Playlist does not exists", true);
}

// checking if track exists in cache

$C = new Caches($db);

$result = $C->is_external_track_exists($track_external_id, $service_id);
if (!$result['status']) {
    end_response(403, "Track is not available", true);
}

$track_id = $result['data']['track_id'];

// checking if track exists in playlist

$result = $P->is_track_in_playlist_exists($track_id, $playlist_id);
if ($result['status']) {
    end_response(200, "Track is already in playlist", true);
}

// inserting track into playlist

$result = $P->insert_track_to_playlist($track_id, $playlist_id);
if ($result['status']) {
    end_response(200, "Track is added to playlist", true);
} else {
    end_response(403, "Unable to add track to playlist", true);
}

end_response(403, "Invalid Request", true);
