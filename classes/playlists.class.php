<?php

class Playlists
{
    private $db;
    private $logs;
    private $class_name;
    private $class_name_lower;
    private $table_name;
    
    public function __construct(PDO $db) {
        $this->logs = new Logs((new DB())->connect());
        $this->db = $db;
        $this->class_name = "Playlists";
        $this->class_name_lower = "playlists_class";
        $this->table_name = "playlists";
    }

    public function add ($name, $user_id)
    {
        $q = "INSERT INTO `{$this->table_name}` (`playlist_user_id`, `playlist_name`, `playlist_created`) VALUE (:u, :n, :dt)";
        $s = $this->db->prepare($q);
        $s->bindParam(":u", $user_id);
        $s->bindParam(":n", $name);
        $dt = current_date();
        $s->bindParam(":dt", $dt);

        if (!$s->execute()) {
            $failure = $this->class_name.'.add - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        
        return ['status' => true, 'playlist_id' => $this->db->lastInsertId()];
    }

    public function get_playlists_details_by_user ($user_id)
    {
        $q = "SELECT * FROM `{$this->table_name}` WHERE `playlist_user_id` = :u";
        $s = $this->db->prepare($q);
        $s->bindParam(":u", $user_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.get_playlists_details_by_user - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }

        return ['status' => true, 'data' => $s->fetchAll()];
    }

    public function get_playlists_by_user ($user_id)
    {
        $q = "SELECT * FROM `{$this->table_name}` WHERE `playlist_user_id` = :u";
        $s = $this->db->prepare($q);
        $s->bindParam(":u", $user_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.get_playlists_by_user - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }

        return ['status' => true, 'data' => $s->fetchAll()];
    }

    public function get_playlist_by_id ($id)
    {
        $q = "SELECT * FROM `{$this->table_name}` WHERE `playlist_id` = :i";
        $s = $this->db->prepare($q);
        $s->bindParam(":i", $id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.get_playlist_by_id - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }

        return ['status' => true, 'data' => $s->fetch()];
    }

    public function is_user_playlist_exist ($playlist_id, $user_id)
    {
        $q = "SELECT * FROM `{$this->table_name}` WHERE `playlist_id` = :p AND `playlist_user_id` = :u";
        $s = $this->db->prepare($q);
        $s->bindParam(":p", $playlist_id);
        $s->bindParam(":u", $user_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.is_user_playlist_exist - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }

        return ['status' => true, 'data' => $s->fetch()];
    }

    public function is_track_in_playlist_exists ($track_id, $playlist_id)
    {
        $q = "SELECT * FROM `playlist_tracks` WHERE `ptrack_playlist_id` = :p AND `ptrack_track_id` = :t";
        $s = $this->db->prepare($q);
        $s->bindParam(":p", $playlist_id);
        $s->bindParam(":t", $track_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.is_track_in_playlist_exists - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }

        return ['status' => true, 'data' => $s->fetch()];
    }

    public function is_ptrack_in_playlist_exists ($track_id, $playlist_id)
    {
        $q = "SELECT * FROM `playlist_tracks` WHERE `ptrack_playlist_id` = :p AND `ptrack_id` = :t";
        $s = $this->db->prepare($q);
        $s->bindParam(":p", $playlist_id);
        $s->bindParam(":t", $track_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.is_ptrack_in_playlist_exists - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }

        return ['status' => true, 'data' => $s->fetch()];
    }

    public function insert_track_to_playlist ($track_id, $playlist_id)
    {
        $q = "INSERT INTO `playlist_tracks` (`ptrack_playlist_id`, `ptrack_track_id`,  `ptrack_created`) VALUE (:p, :t, :dt)";

        $s = $this->db->prepare($q);
        $s->bindParam(":p", $playlist_id);
        $s->bindParam(":t", $track_id);
        $dt = current_date();
        $s->bindParam(":dt", $dt);
        if (!$s->execute()) {
            $failure = $this->class_name.'.insert_track_to_playlist - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        return ['status' => true, 'ptrack_id' => $this->db->lastInsertId()];
    }

    public function remove_track_from_playlist ($track_id, $playlist_id)
    {
        $q = "DELETE FROM `playlist_tracks` WHERE `ptrack_id` = :t AND `ptrack_playlist_id` = :p";
        $s = $this->db->prepare($q);
        $s->bindParam(":t", $track_id);
        $s->bindParam(":p", $playlist_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.remove_track_from_playlist - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        return ['status' => true];
    }

    // $in -> string
    public function playlist_songs_in_playlists ($in)
    {

        $q = "SELECT * FROM `playlist_tracks` JOIN `tracks_cache` ON `ptrack_track_id` = `track_id` JOIN `artists_cache` ON `track_artist_id` = `artist_id` WHERE `ptrack_playlist_id` IN ($in)";

        $s = $this->db->prepare($q);

        if (!$s->execute()) {
            $failure = $this->class_name.'.playlist_songs_in_playlists - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        } 

        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }

        return ['status' => true, 'data' => $s->fetchAll()];
    }

    public function playlist_songs_by_playlist_id ($playlist_id) {
        $q = "SELECT * FROM `playlist_tracks` JOIN `tracks_cache` ON `ptrack_track_id` = `track_id` JOIN `artists_cache` ON `track_artist_id` = `artist_id` JOIN `music_services` ON `mservice_id` = `artist_mservice_id` JOIN `albums_cache` ON `album_id` = `track_album_id` WHERE `ptrack_playlist_id` = :p";
        $s = $this->db->prepare($q);
        $s->bindParam(":p", $playlist_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.playlist_songs_by_playlist_id - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        } 

        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }

        return ['status' => true, 'data' => $s->fetchAll()];
    }

    public function playlist_songs_export_by_playlist_id ($playlist_id, $service_id) {
        $q = "SELECT * FROM `playlist_tracks` JOIN `tracks_cache` ON `ptrack_track_id` = `track_id` LEFT JOIN `export_cache` ON `track_id` = `export_track_id` JOIN `artists_cache` ON `track_artist_id` = `artist_id` JOIN `music_services` ON `mservice_id` = `artist_mservice_id` JOIN `albums_cache` ON `album_id` = `track_album_id` WHERE `ptrack_playlist_id` = :p AND (`export_mservice_id` = :s OR `export_mservice_id` IS NULL)";
        $s = $this->db->prepare($q);
        $s->bindParam(":p", $playlist_id);
        $s->bindParam(":s", $service_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.playlist_songs_export_by_playlist_id - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        } 

        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }

        return ['status' => true, 'data' => $s->fetchAll()];
    }

    public function remove_playlist_by_id ($id)
    {
        $q = "DELETE FROM `{$this->table_name}` WHERE `playlist_id` = :i";
        $s = $this->db->prepare($q);
        $s->bindParam(":i", $id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.remove_playlist_by_id - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        } 

        return ['status' => true];
    }

}
