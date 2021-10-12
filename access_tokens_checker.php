<?php 

require_once 'app/start.php';

$services = new Services($db);

$_service = $services->get_service_by_name('Spotify');

if ($_service['status']) {

    $_service = $_service['data'];

    $client_token = $_service['mservice_client'];
    $secret_token = $_service['mservice_secret'];

    $session = new SpotifyWebAPI\Session($client_token, $secret_token);

    // checking available access tokens before send request for new token

    $_access_token = $services->get_verified_access_token ($_service['mservice_id'], $session);    
    if ($_access_token['status']) {
        end_response(200, ['data' => 'Spotify token is ok']);
    } else {
        end_response(400, ['data' => 'Unable to get access token']);
    }

}
