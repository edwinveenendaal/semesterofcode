<?php
class Organisations extends Groups{

	private static $instance;
	public static $type = _ORGANISATION_GROUP;	
	public static $fields = array('org_id', 'owner_id', 'name', 'contact_name', 'contact_email', 'url', 'description');
	
	public static function getInstance(){
		if (is_null ( self::$instance )){
			self::$instance = new self ();
		}
		return self::$instance;
	}

	/**
	 * function used to just get the organisation Id and name
	 * Used in some drop down menus of the UI.
	 */
	public function getOrganisationsLite(){
		return db_query("SELECT o.org_id, o.name FROM soc_organisations o ORDER BY o.name;");
	}
	
    public function getOrganisations(){
    	return db_select('soc_organisations')->fields('soc_organisations')->execute()->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getMyOrganisations($detail=FALSE){
    	if ($detail){
    		return Groups::getGroups(_ORGANISATION_GROUP);
    	} else {
    		$user_id = $GLOBALS['user']->uid;
    		$table = tableName(_ORGANISATION_GROUP);
    		$orgids = db_query(
    			"SELECT o.org_id from $table as o ".
    			"LEFT JOIN soc_user_membership as um on o.org_id = um.group_id ".
    			"WHERE um.uid = $user_id AND um.type = :organisation",
    			array(':organisation' =>_ORGANISATION_GROUP))->fetchCol();
    	}
    	return $orgids;	
    }
    
    public function getOrganisationById($id){
    	return db_select('soc_organisations')->fields('soc_organisations')->condition('org_id', $id)->execute()->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getOrganisationsRowCountBySearchCriteria($name){
    	$count_query = db_select('soc_organisations');
    	if(isset($name)){
    		$count_query->condition('name', '%'.$name.'%', 'LIKE');
    	}
    	$count_query->fields('soc_organisations');
    	return $count_query->execute()->rowCount();
    }
    
    public function getOrganisationsBySearchCriteria($name, $sorting="o.name", $startIndex=0, $pageSize=1000){
    	$queryString = "SELECT o.org_id, o.name, o.url"
    			." FROM soc_organisations o";
    	 
    	if(isset($name)){
    		$queryString .=	 " WHERE name LIKE '%".$name."%'";
    	}
    	$queryString .= 	 " ORDER BY " . $sorting
    	." LIMIT " . $startIndex . "," . $pageSize . ";";
    	 
    	$result = db_query($queryString);
    	 
    	$rows = array();
    	foreach ($result as $record) {
    		$rows[] = $record;
    	}
    	return $rows;
    }
	
}