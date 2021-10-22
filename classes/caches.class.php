<?php

class Caches
{
    private $db;
    private $logs;
    private $class_name;
    private $class_name_lower;

    private $table_tracks;
    private $table_albums;
    private $table_artists;

    private $artists = [];
    private $albums = [];
    private $tracks = [];
    
    public function __construct(PDO $db) {
        $this->logs = new Logs((new DB())->connect());
        $this->db = $db;
        $this->class_name = "Caches";
        $this->class_name_lower = "caches_class";

        $this->table_tracks = "tracks_cache";
        $this->table_albums = "albums_cache";
        $this->table_artists = "artists_cache";

        $this->artists = ['all' => [], 'by_id' => [], 'by_external_id' => []];
        $this->albums = ['all' => [], 'by_id' => [], 'by_external_id' => []];
        $this->tracks = ['all' => [], 'by_id' => [], 'by_external_id' => []];
    }

    public function prepare_results ($data)
    {
        $results = ['tracks' => [], 'artists' => [], 'albums' => []];

        foreach ($data as $d) {
            $results['artists'][] = [
                'artist_id' => $d['artist_id'],
                'artist_name' => $d['artist_name'],
                'service_id' => $d['service_id']
            ];

            $results['albums'][] = [
                'artist_id' => $d['artist_id'],
                'album_id' => $d['album_id'],
                'album_name' => $d['album_name'],
                'album_image' => $d['album_image'],
                'service_id' => $d['service_id']
            ];
            
            $results['tracks'][] = [
                'artist_id' => $d['artist_id'],
                'album_id' => $d['album_id'],
                'song_id' => $d['song_id'],
                'song_name' => $d['song_name'],
                'song_duration' => $d['song_duration'],
                'song_preview' => $d['song_preview']
            ];
        }

        return $results;
    }

    // $results = ['tracks' => [], 'artists' => [], 'albums' => []]

    public function add_search_results ($songs)
    {

        $results = $this->prepare_results ($songs);

        $this->populate_artists();
        $this->populate_albums();
        $this->populate_tracks();

        // inserting artists
        foreach ($results['artists'] as $artist) {
            if (!array_key_exists($artist['artist_id'], $this->artists['by_external_id'])) {
                $result = $this->insert_artist($artist['artist_name'], $artist['artist_id'], $artist['service_id']);
                if ($result['status']) {
                    $this->artists['by_external_id'][$artist['artist_id']] = ['artist_id' => $result['artist_id']];
                }
            }
        }

        // inserting albums
        foreach ($results['albums'] as $album) {
            if (!array_key_exists($album['album_id'], $this->albums['by_external_id'])) {
                $result = $this->insert_album($album['album_name'], $album['album_image'], $album['album_id'], $this->artists['by_external_id'][$album['artist_id']]['artist_id'], $album['service_id']);
                if ($result['status']) {
                    $this->albums['by_external_id'][$album['album_id']] = ['album_id' => $result['album_id']];
                }
            }
        }

        // inserting tracks -> multiple at once
        $tracks = [];
        foreach ($results['tracks'] as $track) {
            if (!array_key_exists($track['song_id'], $this->tracks['by_external_id'])) {
                $v = [
                    'track_artist_id' => $this->artists['by_external_id'][$track['artist_id']]['artist_id'],
                    'track_album_id' => $this->albums['by_external_id'][$track['album_id']]['album_id'],
                    'track_name' => $track['song_name'],
                    'track_preview' => $track['song_preview'],
                    'track_duration' => $track['song_duration'],
                    'track_created' => current_date(),
                    'track_external_id' => $track['song_id']
                ];
                $tracks[] = $v;
            }
        }

        if (!empty($tracks)) {
            $result = $this->insert_tracks($tracks);
        }

        return ['status' => true];
    }

    public function populate_artists ()
    {
        $q = "SELECT * FROM `{$this->table_artists}`";
        $s = $this->db->prepare($q);
        if (!$s->execute()) {
            $failure = $this->class_name.'.populate_artists - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        if ($s->rowCount() > 0) {
            $results = $s->fetchAll();

            $this->artists['all'] = $results;

            foreach ($results as $result) {
                $this->artists['by_id'][$result['artist_id']] = $result;
                $this->artists['by_external_id'][$result['artist_external_id']] = $result;
            }

            return ['status' => true, 'type' => 'success'];
        }
        return ['status' => true, 'type' => 'empty'];
    }

    public function populate_albums ()
    {
        $q = "SELECT * FROM `{$this->table_albums}`";
        $s = $this->db->prepare($q);
        if (!$s->execute()) {
            $failure = $this->class_name.'.populate_albums - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        if ($s->rowCount() > 0) {
            $results = $s->fetchAll();

            $this->albums['all'] = $results;

            foreach ($results as $result) {
                $this->albums['by_id'][$result['album_id']] = $result;
                $this->albums['by_external_id'][$result['album_external_id']] = $result;
            }

            return ['status' => true, 'type' => 'success'];
        }
        return ['status' => true, 'type' => 'empty'];
    }

    public function populate_tracks ()
    {
        $q = "SELECT * FROM `{$this->table_tracks}`";
        $s = $this->db->prepare($q);
        if (!$s->execute()) {
            $failure = $this->class_name.'.populate_tracks - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        if ($s->rowCount() > 0) {
            $results = $s->fetchAll();

            $this->tracks['all'] = $results;

            foreach ($results as $result) {
                $this->tracks['by_id'][$result['track_id']] = $result;
                $this->tracks['by_external_id'][$result['track_external_id']] = $result;
            }

            return ['status' => true, 'type' => 'success'];
        }
        return ['status' => true, 'type' => 'empty'];
    }

    public function insert_artist ($name, $external_id, $service_id)
    {
        $q = "INSERT INTO `{$this->table_artists}` (`artist_mservice_id`, `artist_name`, `artist_external_id`, `artist_created`) VALUE (:si, :n, :ei, :dt)";

        $s = $this->db->prepare($q);
        $s->bindParam(":si", $service_id);
        $s->bindParam(":n", $name);
        $s->bindParam(":ei", $external_id);
        $dt = current_date();
        $s->bindParam(":dt", $dt);

        if (!$s->execute()) {
            $failure = $this->class_name.'.insert_artist - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode(['error' => $s->errorInfo(), 'data' => ['name' => $name, 'external_id' => $external_id, 'service_id' => $service_id]]));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        return ['status' => true, 'artist_id' => $this->db->lastInsertId()];
    }
    
    public function insert_album ($name, $image, $external_id, $artist_id, $service_id)
    {
        $q = "INSERT INTO `{$this->table_albums}` (`album_artist_id`, `album_mservice_id`, `album_name`, `album_image`, `album_external_id`, `album_created`) VALUE (:ai, :si, :n, :im, :ei, :dt)";

        $s = $this->db->prepare($q);
        $s->bindParam(":ai", $artist_id);
        $s->bindParam(":si", $service_id);
        $s->bindParam(":n", $name);
        $s->bindParam(":im", $image);
        $s->bindParam(":ei", $external_id);
        $dt = current_date();
        $s->bindParam(":dt", $dt);

        if (!$s->execute()) {
            $failure = $this->class_name.'.insert_album - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode(['error' => $s->errorInfo(), 'data' => ['name' => $name, 'image' => $image, 'external_id' => $external_id, 'artist_id' => $artist_id, 'service_id' => $service_id]]));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        return ['status' => true, 'album_id' => $this->db->lastInsertId()];
    }

    // $tracks => [['track_artist_id' => '', 'track_album_id' => '', :col => :val]]

    public function insert_tracks ($tracks)
    {
        $data = [];
        $cols = "";
        $vals = "";
        
        foreach ($tracks as $i => $track) {
            if (!empty($vals)) { $vals .= ", "; }

            $v = "";
            foreach ($track as $col => $val) {
                if ($i === 0) { 
                    if (!empty($cols)) {
                        $cols .= ", ";
                    }
                    
                    $cols .= "`$col`";
                }
    
                if (!empty($v)) { $v .= ", "; }
    
                $_placeholder = ":$col$i";
                $data[$_placeholder] = $val;
                $v .= $_placeholder;
            }

            $vals .= "($v)";
        }

        $q = "INSERT INTO `{$this->table_tracks}` ($cols) VALUES $vals";
        $s = $this->db->prepare($q);
        if (!$s->execute($data)) {
            $failure = $this->class_name.'.insert_tracks - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode(['error' => $s->errorInfo(), 'data' => ['tracks' => $tracks]]));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        return ['status' => true];
    }

    public function is_external_track_exists ($track_external_id, $service_id)
    {
        $q = "SELECT * FROM `{$this->table_tracks}` JOIN `{$this->table_artists}` ON `track_artist_id` = `artist_id` WHERE `track_external_id` = :ex AND `artist_mservice_id` = :mi";
        $s = $this->db->prepare($q);
        $s->bindParam(":ex", $track_external_id);
        $s->bindParam(":mi", $service_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.is_external_track_exists - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }

        return ['status' => true, 'data' => $s->fetch()];
    }

}
