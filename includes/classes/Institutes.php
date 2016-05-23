<?php

class Institutes extends Groups {

    public static $fields = array('inst_id', 'owner_id', 'name', 'contact_name', 'contact_email');
    private static $instance;
    public static $type = _INSTITUTE_GROUP;

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self ();
        }
        return self::$instance;
    }

    /**
     * function used to just get the institute Id and name
     * Used in some drop down menus of the UI.
     */
    public function getInstitutesLite() {
        return db_query("SELECT i.inst_id, i.name FROM soc_institutes i ORDER BY i.name;");
    }

    public function getInstitutes() {
        return db_select('soc_institutes')->fields('soc_institutes')->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInstituteById($id) {
        return db_select('soc_institutes')->fields('soc_institutes')->condition('inst_id', $id)->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInstitutesRowCountBySearchCriteria($name) {
        $count_query = db_select('soc_institutes');
        if (isset($name)) {
            $count_query->condition('name', '%' . $name . '%', 'LIKE');
        }
        $count_query->fields('soc_institutes');
        return $count_query->execute()->rowCount();
    }

    public function getInstitutesBySearchCriteria($name, $sorting, $startIndex, $pageSize) {
        $queryString = "SELECT i.inst_id, i.name"
                . " FROM soc_institutes i";

        if (isset($name)) {
            $queryString .= " WHERE name LIKE '%" . $name . "%'";
        }
        $queryString .= " ORDER BY " . $sorting
                . " LIMIT " . $startIndex . "," . $pageSize . ";";

        $result = db_query($queryString);

        $rows = array();
        foreach ($result as $record) {
            $rows[] = $record;
        }
        return $rows;
    }

}
