<?php

class redistribution_data extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int');
		$this -> hasColumn('source_facility_code', 'int');
		$this -> hasColumn('receive_facility_code', 'int');
		$this -> hasColumn('commodity_id', 'int');
		$this -> hasColumn('quantity_sent',  'int');
		$this -> hasColumn('quantity_received',  'int');
		$this -> hasColumn('sender_id',  'int');
		$this -> hasColumn('receiver_id',  'int');
		$this -> hasColumn('manufacturer', 'varchar', 100);
		$this -> hasColumn('batch_no', 'varchar', 100);
		$this -> hasColumn('expiry_date', 'date');
		$this -> hasColumn('date_sent', 'date');
		$this -> hasColumn('date_received', 'date');
		$this -> hasColumn('facility_stock_ref_id', 'int');
		$this -> hasColumn('status', 'int');
	}

	public function setUp() {
		$this -> setTableName('redistribution_data');
		$this->hasMany('facility_stocks as stock_detail', array('local' => 'facility_stock_ref_id', 'foreign' => 'id'));
		$this->hasMany('facilities as facility_detail_source', array('local' => 'source_facility_code', 'foreign' => 'facility_code'));
		$this->hasMany('facilities as facility_detail_receive', array('local' => 'receive_facility_code', 'foreign' => 'facility_code'));
	}

	public static function get_all() {
		$query = Doctrine_Query::create() -> select("*") -> from("redistribution_data");
		$redistribution_data = $query -> execute();
		return $redistribution_data;
	}
	public static function get_all_active($facility_code,$option=null) {
		$query = Doctrine_Query::create() -> select("*") -> from("redistribution_data") -> where("source_facility_code=$facility_code and status=0");
		$redistribution_data = $query -> execute();
		return $redistribution_data;
	}
	
}
?>
