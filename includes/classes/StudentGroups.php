<?php
class StudentGroups extends Groups {
	public static $type = _STUDENT_GROUP;
	public static $fields = array('studentgroup_id', 'owner_id', 'inst_id', 'name', 'description');
}