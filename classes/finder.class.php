<?php

class Finder
{
    private $db;
    private $logs;
    private $class_name;
    private $class_name_lower;

    public function __construct(PDO $db) {
        $this->logs = new Logs((new DB())->connect());
        $this->db = $db;
        $this->class_name = "Finder";
        $this->class_name_lower = "finder_class";
    }

    public function spotify_finder ($query, $access_token, $service)
    {
        $api = new SpotifyWebAPI\SpotifyWebAPI();
        $api->setAccessToken($access_token);

        try {
            $results = $api->search($query, ['track']);
            // convert $results to associative array
            $results = json_decode(json_encode($results), true);

            /*
            $songs => [
                [
                    'artist_name' => '', // $track[album][artists] -> foreach $artist[name]
                    'album_name' => '', // $track[album][name]
                    'album_image' => '', // $track[album][images][:last-index][url]
                ]
            ]
            */

            $songs = []; 

            foreach ($results['tracks']['items'] as $track) {

                // Spotify API issue with preview: https://github.com/spotify/web-api/issues/148
                if (empty($track['preview_url'])) { continue; }
                // For future: we can check cache for the song preview url

                $artists_names = "";
                foreach ($track['album']['artists'] as $artist) {
                    if (!empty($artists_names)) { $artists_names .= ", "; }

                    $artists_names .= $artist['name'];
                } 

                $album_image = $track['album']['images'][count($track['album']['images']) - 1]['url'];

                $duration_ms = $track['duration_ms'];
                $song_duration = str_pad(floor($duration_ms/60000), 2, '0', STR_PAD_LEFT).':'.str_pad(floor(($duration_ms%60000)/1000), 2, '0', STR_PAD_LEFT);

                $songs[] = [
                    'artist_name' => $artists_names,
                    'album_name' => $track['album']['name'],
                    'album_image' => $album_image,
                    'song_name' => $track['name'],
                    'song_duration' => $song_duration,
                    'song_preview' => $track['preview_url'],
                    'service_name' => $service['name'],
                    'service_icon' => $service['icon']
                ];

            }

            return ['status' => true, 'songs' => $songs];

        } catch (Exception $e) {
            $failure = $this->class_name.'.spotify_finder - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode(['message' => $e->getMessage(), 'query' => $query, 'token' => $access_token]));
            return ['status' => false, 'type' => 'error'];
        }

    }

    public function deezer_finder ($query, $service)
    {
        $client = new wasymshykh\DeezerAPI\DeezerAPIClient();
        $api = new wasymshykh\DeezerAPI\DeezerPublicAPI($client);

        try {

            $results = $api->searchTrack($query);

            $results = json_decode(json_encode($results), true);

            $results = $results['data'];

            $songs = []; 
            foreach ($results as $track) {

                $duration_s = $track['duration'];
                $song_duration = str_pad(floor($duration_s/60), 2, '0', STR_PAD_LEFT).':'.str_pad(floor(($duration_s%60)), 2, '0', STR_PAD_LEFT);

                $songs[] = [
                    'artist_name' => $track['artist']['name'],
                    'album_name' => $track['album']['title'],
                    'album_image' => $track['album']['cover_small'],
                    'song_name' => $track['title'],
                    'song_duration' => $song_duration,
                    'song_preview' => $track['preview'],
                    'service_name' => $service['name'],
                    'service_icon' => $service['icon']
                ];
            }

            return ['status' => true, 'songs' => $songs];

        } catch (Exception $e) {
            $failure = $this->class_name.'.deezer_finder - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode(['message' => $e->getMessage(), 'query' => $query]));
            return ['status' => false, 'type' => 'error'];
        }

    }

    public function youtube_finder ($query, $key, $service)
    {
        $youtube = new Madcoda\Youtube\Youtube(['key' => $key]);

        $params = ['q' => $query, 'type' => 'video', 'part' => 'id', 'maxResult' => 5, 'videoCategoryId' => 10, 'videoLicense' => 'youtube'];        
        $results = $youtube->searchAdvanced($params);
        $results = json_decode(json_encode($results), true);

        $videos = [];
        foreach ($results as $result) {
            $videos[] = $result['id']['videoId'];
        }

        if (!empty($videos)) {
            $results = $youtube->getVideosInfo($videos);
            $results = json_decode(json_encode($results), true);

            // Only retriving licensed content
            $filtered_results = [];
            foreach ($results as $result) {
                if ($result['contentDetails']['licensedContent'] === true) {
                    $filtered_results[] = $result;
                }
            }

            if (!empty($filtered_results)) {
                $songs = [];
                foreach ($filtered_results as $video) {
                    $songs[] = [
                        'artist_name' => $video['snippet']['channelTitle'],
                        'album_name' => '',
                        'album_image' => $video['snippet']['thumbnails']['default']['url'],
                        'song_name' => $video['snippet']['title'],
                        'song_duration' => youtube_duration_format($video['contentDetails']['duration']),
                        'song_preview' => '',
                        'service_name' => $service['name'],
                        'service_icon' => $service['icon']
                    ];
                }

                return ['status' => true, 'songs' => $songs];
            } else {
                return ['status' => false, 'type' => 'error', 'data' => 'Unable to find videos'];
            }

        } else {
            return ['status' => false, 'type' => 'error', 'data' => 'Unable to find videos'];
        }

        return ['status' => false, 'type' => 'error'];
    }

}
