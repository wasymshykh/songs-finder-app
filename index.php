<?php 

require_once 'app/start.php';

if (!$logged) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Authentication required to access page'];
    move('login.php');
}

$services = new Services($db);
$finder = new Finder($db);

$errors = [];

if (isset($_POST) && !empty($_POST)) {

    
    $_service = $services->get_service_by_name('Spotify');
    if ($_service['status']) {
        $_service = $_service['data'];

        if ($_service['mservice_status'] === 'Y') {
            $token = $services->get_access_token_of_service($_service['mservice_id'], true);
            if ($token['status']) {
                $token = $token['data'];
                
                $songs = $finder->spotify_finder($_POST['search'], $token['atoken_token'], ['name' => $token['mservice_name'], 'icon' => $token['mservice_icon']]);
                if ($songs['status']) {
                    $songs = $songs['songs'];
                } else {
                    $songs = [];
                    $errors[] = "Unable to find songs";
                }
            }
        } else {
            $errors[] = "Spotify service is disabled";
        }
    
    } else {
        $errors[] = "Spotify service not found";
    }

    $_service = $services->get_service_by_name('Deezer');
    if ($_service['status']) {
        $_service = $_service['data'];

        if ($_service['mservice_status'] === 'Y') {

            $songs = $finder->deezer_finder($_POST['search'], ['name' => $_service['mservice_name'], 'icon' => $_service['mservice_icon']]);
            if ($songs['status']) {
                $songs = $songs['songs'];
            } else {
                $songs = [];
                $errors[] = "Unable to find songs";
            }

        } else {
            $errors[] = "Deezer service is disabled";
        }

    } else {
        $errors[] = "Deezer service not found";
    }
    
    $_service = $services->get_service_by_name('Youtube');
    if ($_service['status']) {
        $_service = $_service['data'];

        if ($_service['mservice_status'] === 'Y') {

            $songs = $finder->youtube_finder($_POST['search'], $_service['mservice_secret'], ['name' => $_service['mservice_name'], 'icon' => $_service['mservice_icon']]);
            if ($songs['status']) {
                $songs = $songs['songs'];
            } else {
                $songs = [];
                $errors[] = "Unable to find songs";
            }

        } else {
            $errors[] = "Youtube service is disabled";
        }

    } else {
        $errors[] = "Youtube service not found";
    }


}

$css_before = [
    css_link('calamansi.min', true)
];

$popper_js = true;

require_once DIR.'views/layout/header.view.php';
require_once DIR.'views/pages/index.view.php';
require_once DIR.'views/layout/footer.view.php';
