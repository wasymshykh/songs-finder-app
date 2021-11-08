<?php 

require_once 'app/start.php';

if (!$logged) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Authentication required to access page'];
    move('login.php');
}

$services = new Services($db);
$finder = new Finder($db);
$caches = new Caches($db);
$P = new Playlists($db);

$errors = [];

if (isset($_POST) && !empty($_POST)) {

    $songs = [];
    $artists = [];

    // checking search cache for the searched term

    $search_term = normal_text($_POST['search']);

    $available_cache = false;

    $_search_cache = $caches->search_cache_check($search_term, $settings->fetch('search_cache_time'));

    if ($_search_cache['status']) {
        $available_cache = true;

        // getting the search data
        $search_results = $caches->get_search_cache_of($_search_cache['data']['search_id']);
        if ($search_results['status']) {

            $search_results = $caches->adjust_search_cache_results($search_results);

            $songs = $search_results['tracks'];
            $artists = $search_results['artists'];

        } else {
            $errors[] = "Unable to fetch the results";
        }
    } 


    if (!$available_cache) {

        $_service = $services->get_service_by_name('Spotify');
        if ($_service['status']) {
            $_service = $_service['data'];
    
            if ($_service['mservice_status'] === 'Y' && $_service['mservice_enable_search'] === 'Y') {
                $token = $services->get_access_token_of_service($_service['mservice_id'], true);
                if ($token['status']) {
                    $token = $token['data'];
                    
                    $results = $finder->spotify_finder($_POST['search'], $token['atoken_token'], service_simple_data_array($_service));
                    if ($results['status']) {
                        $songs = array_merge($songs, $results['songs']);
                        $artists = array_merge($artists, $results['artists']);
                    }
                }
            }
        
        } else {
            $errors[] = "Spotify service not found";
        }
        
        $_service = $services->get_service_by_name('Deezer');
        if ($_service['status']) {
            $_service = $_service['data'];
    
            if ($_service['mservice_status'] === 'Y' && $_service['mservice_enable_search'] === 'Y') {
    
                $results = $finder->deezer_finder($_POST['search'], service_simple_data_array($_service));
                if ($results['status']) {
                    $songs = array_merge($songs, $results['songs']);
                    $artists = array_merge($artists, $results['artists']);
                }
    
            }
    
        } else {
            $errors[] = "Deezer service not found";
        }
        
        $_service = $services->get_service_by_name('Youtube');
        if ($_service['status']) {
            $_service = $_service['data'];
    
            if ($_service['mservice_status'] === 'Y' && $_service['mservice_enable_search'] === 'Y') {
    
                $results = $finder->youtube_finder($_POST['search'], $_service['mservice_api_key'], service_simple_data_array($_service));
                if ($results['status']) {
                    $songs = array_merge($songs, $results['songs']);
                }
    
            }
    
        } else {
            $errors[] = "Youtube service not found";
        }
        
        if (empty($errors)) {
            $result = $caches->add_search_results($songs, $artists, $search_term);
            if (!$result['status']) {
                $errors[] = "Unable to add to cache";
            }
        }

    }

}

$playlists = [];

$_playlists = $P->get_playlists_by_user($logged_user['user_id']);
if ($_playlists['status']) {
    $playlists = $_playlists['data'];
}

$css_before = [
    css_link('calamansi.min', true)
];

$js_after = [
    js_link('index', true)
];

$popper_js = true;

require_once DIR.'views/layout/header.view.php';
require_once DIR.'views/pages/index.view.php';
require_once DIR.'views/layout/footer.view.php';
