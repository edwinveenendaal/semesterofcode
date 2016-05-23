<?php
class ThreadedComments extends AbstractEntity {
	
	private static $instance;
	
	public static $fields = array('id', 'parent_id', 'entity_id', 'entity_type', 'author', 
			'date_posted', 'description');
	
	public static function getInstance(){
		if (is_null ( self::$instance )){
			self::$instance = new self ();
		}
		return self::$instance;
	}
	
	public function getKeylessFields(){
		return array_slice(ThreadedComments::$fields, 1);
	}
	
	function getPostById($id, $fetch_style=PDO::FETCH_ASSOC){
		$query = db_select('soc_comments', 'c');
		$query->join('users', 'u', 'c.author = u.uid');
		$query->join('users_roles', 'ur', 'c.author = ur.uid');
		$query->join('role', 'r', 'r.rid = ur.rid');
		$query->fields('c', self::$fields);
		$query->fields('u',  array('name'));
		$query->addField('r', 'name', 'type');
		$query->condition('c.id', $id);
		$post = $query->execute()->fetch($fetch_style);
		return $post;
	}

	function removethreadsForEntity($entity_id, $entity_type){
		try{
			$num_deleted = db_delete('soc_comments')
			->condition('entity_id', $entity_id)
			->condition('entity_type', $entity_type)
			->execute();
			// fail silently if no record/s found
		} catch (Exception $e){
			drupal_set_message(tt(' We could not delete the comments for this %1$s', t_type($entity_type)).(_DEBUG ? $ex->getMessage():''), 'error');
			return FALSE;
		}
	}
	
	function getThreadsForEntity($entity_id, $entity_type){
		$queryString = "SELECT s.*, u.name, u.mail, r.name AS type FROM soc_comments s, users u 
			LEFT JOIN users_roles ur ON u.uid = ur.uid
			LEFT JOIN role r ON ur.rid = r.rid
			WHERE entity_id=" . $entity_id .
			" AND entity_type='" . $entity_type . "' AND u.uid = s.author;";
		$result = db_query($queryString)->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	function addComment($props){
		if (! $props){
			drupal_set_message(t('Insert requested with empty (filtered) data set'), 'error');
			return false;
		}

		
		//global $user;
		$txn = db_transaction();
		try {
			//$uid = $user->uid;
			//$props['author'] = $uid;
			$now = new DateTime();
			$props['date_posted'] = $now->format('Y-m-d H:i:s');
			// check for top level posts with an empty parent & set it to mysql null.
			if(!isset($props['parent_id']) || empty($props['parent_id'])) { 
				$props['parent_id'] = null;
			}
			$result = FALSE;
			$query = db_insert(tableName('comment'))->fields($props);
			$id = $query->execute();
			if ($id){
				$result = $id;
			}
			else {
				drupal_set_message(t('We could not add your comment'), 'error');
			}
		}
		catch (Exception $ex) {
			$txn->rollback();
			drupal_set_message(t('We could not add your comment. '). (_DEBUG? $ex->__toString(): ''), 'error');
		}
		return $result;
	}
}