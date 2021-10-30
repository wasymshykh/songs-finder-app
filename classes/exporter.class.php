<?php

class Exporter
{
    private $db;
    private $logs;
    private $class_name;
    private $class_name_lower;
    private $table_name;

    public function __construct(PDO $db) {
        $this->logs = new Logs((new DB())->connect());
        $this->db = $db;
        $this->class_name = "Exporter";
        $this->class_name_lower = "exporter_class";
        $this->table_name = "export_cache";
    }

    public function insert_exports ($records)
    {
        $vals = "";

        $data = [];
        foreach ($records as $i => $record) {
            if ($record['export']['export_insert']) {
                if ($vals !== "") { $vals .= ", "; }
                $vals .= "(:{$i}t, :{$i}s, :{$i}e, :dt, :{$i}f)";

                $data[":{$i}t"] = $record['export']['export_track_id'];
                $data[":{$i}s"] = $record['export']['export_mservice_id'];
                $data[":{$i}e"] = $record['export']['export_external_track_id'];
                $data[":{$i}f"] = $record['export']['export_found'];
            }
        }

        if (!empty($vals)) {
            $q = "INSERT INTO `{$this->table_name}` (`export_track_id`, `export_mservice_id`, `export_external_track_id`, `export_created`, `export_found`) VALUES $vals";
            $s = $this->db->prepare($q);
            $data[":dt"] = current_date();
            if (!$s->execute($data)) {
                $failure = $this->class_name.'.insert_exports - E.02: Failure';
                $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
                return ['status' => false, 'type' => 'query', 'data' => $failure];
            } 
        }
        
        return ['status' => true];
    }

    public function get_export_of_songs ($in)
    {
        $q = "SELECT * FROM `{$this->table_name}` WHERE `export_track_id` IN ($in)";
        $s = $this->db->prepare($q);
        if (!$s->execute()) {
            $failure = $this->class_name.'.get_export_of_songs - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        } 
        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }
        return ['status' => true, 'data' => $s->fetchAll()];
    }

    public function get_export_of_songs_of_service ($in, $service_id)
    {
        $q = "SELECT * FROM `{$this->table_name}` WHERE `export_track_id` IN ($in) AND `export_mservice_id` = :s";
        $s = $this->db->prepare($q);
        $s->bindParam(":s", $service_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.get_export_of_songs_of_service - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        } 
        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }
        return ['status' => true, 'data' => $s->fetchAll()];
    }

}
