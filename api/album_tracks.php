<?php

require_once '../app/start.php';

if (isset($_GET['album']) && !empty($_GET['album']) && !empty(normal_text($_GET['album'])) && is_string($_GET['album'])) {
    $album_id = normal_text($_GET['album']);
} else {
    end_response(403, "Album is not provided", true);
}
if (isset($_GET['service']) && !empty($_GET['service']) && !empty(normal_text($_GET['service'])) && is_string($_GET['service'])) {
    $service = normal_text($_GET['service']);
} else {
    end_response(403, "Service is not provided", true);
}

$finder = new Finder($db);
$services = new Services($db);

if ($service === 'Spotify') {
    $token = $services->get_access_token_of_service('1', true);
    if ($token['status']) {
        $token = $token['data'];
        $results = $finder->spotify_search_album_tracklist ($album_id, $token['atoken_token']);
    } else {
        end_response(400, "Spotify service not available", true);
    }
} else if ($service === 'Deezer') {
    $results = $finder->deezer_search_album_tracklist ($album_id);
} else {
    end_response(400, "Unable to find service", true);
}

end_response(200, $results, true);
