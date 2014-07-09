<?php
class Access_level extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int',15);
		$this -> hasColumn('level', 'varchar',255);	
		$this -> hasColumn('user_indicator', 'varchar',255);	
		$this -> hasColumn('type', 'int',255);		
	}

	public function setUp() {
		$this -> setTableName('access_level');
	//	$this -> hasMany('user as u_type1', array('local' => 'id', 'foreign' => 'usertype_id'));
		
	}

	public static function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("access_level");
		$level = $query -> execute();
		return $level;
	}
	
	public static function get_access_level_name($access_typeid) {
		$query = Doctrine_Query::create() -> select("*") -> from("access_level")-> where("id=$access_typeid");
		$level = $query -> execute();
		$level=$level->toArray();
		return $level[0];
	}
	

}