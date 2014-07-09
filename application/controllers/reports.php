<?php
/**
 * @author Kariuki
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Reports extends MY_Controller 
{
	function __construct() 
	{
		parent::__construct();
		$this -> load -> helper(array('form', 'url'));
		$this -> load -> library(array('hcmp_functions', 'form_validation'));
	}


	public function index() 
	{
		$identifier = $this -> session -> userdata('user_indicator');

		switch ($identifier) {
			case moh :
				$data['content_view'] = "";
				$view = 'shared_files/template/dashboard_template_v';
				break;
			case district :
				$county_id = $this -> session -> userdata('county_id');
	            $data['district_data'] = districts::getDistrict($county_id);
	            $data['c_data'] = Commodities::get_all_2();
				$data['categories']=commodity_sub_category::get_all_pharm();
				$data['banner_text'] = "Stocking Levels";
				$data['title'] = "Stocking Levels";
				$data['content_view'] = "facility/facility_reports/reports_v";
				$view = 'shared_files/template/template';
				$data['report_view'] = "subcounty/reports/county_stock_level_filter_v";
				$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";		
				break;
			case moh_user :
				$data['content_view'] = "";
				break;
			case facility_admin :
			case facility :
				$facility_code = $this -> session -> userdata('facility_id');
				$data['content_view'] = "facility/facility_reports/reports_v";
				$data['facility_code'] = $facility_code;
				$view = 'shared_files/template/template';
				$data['report_view'] = "facility/facility_reports/potential_expiries_v";
				$data['sidebar'] = "shared_files/report_templates/side_bar_v";
				$data['report_data'] = Facility_stocks::potential_expiries($facility_code);

				break;
			case district_tech :
				$data['content_view'] = "";
				break;
			case rtk_manager :
				$data['content_view'] = "";
				break;
			case super_admin :
				$data['content_view'] = "";
				break;
			case allocation_committee :
				$data['content_view'] = "";
				break;
		}

		$data['title'] = "Reports";
		
		$this -> load -> view($view, $data);
	}

	/*
	 |--------------------------------------------------------------------------
	 | SHARED REPORTS
	 |--------------------------------------------------------------------------
	 */
	//Default function for all non functioning parts of the system
	public function work_in_progress()
	{
		$data['title'] = "Work In Progress";
		$data['banner_text'] = "Work In Progress";
		$data['content_view'] = "shared_files/work_in_progress";
		$view = 'shared_files/template/template';
		$this -> load -> view($view, $data);
	}
	// get the commodity listing here
	public function commodity_listing() 
	{
		$data['title'] = "Commodity Listing";
		$data['banner_text'] = "Commodity Listing";
		$data['content_view'] = "shared_files/commodities/commodity_list_v";
		$data['commodity_list'] = commodity_sub_category::get_all();
		$this -> load -> view('shared_files/template/template', $data);
	}
   public function force_file_download(){
   	$this -> hcmp_functions -> download_file($this->input->get('url'));
   }

	public function get_facilities() {
		$district = $_POST['district'];
		$facilities = Facilities::getFacilities($district);
		$list = "";
		foreach ($facilities as $facilities) {
			$list .= $facilities -> facility_code;
			$list .= "*";
			$list .= $facilities -> facility_name;
			$list .= "_";
		}
		echo $list;
	}
     /*
	|--------------------------------------------------------------------------
	 | FACILITY REPORTS
	 |--------------------------------------------------------------------------
	 */
	///////////////////GET FACILITY STOCK DATA/////////////////////////////
	public function facility_evaluation_report()
	{
		$facility_code = $this -> session -> userdata('facility_id');
		$user_id = $this -> session -> userdata('user_id');
		
		$evaluation = Facility_Evaluation::getAll($facility_code, $user_id) -> toArray();
	
		$data = (count($evaluation) == 0) ? 
		array(0 => array('fhead_no' => null, 'fdep_no' => null, 'nurse_no' => null, 
		'sman_no' => null, 'ptech_no' => null, 'trainer' => null, 'comp_avail' => null, 
		'modem_avail' => null, 'bundles_avail' => null, 'manuals_avail' => null, 
		'satisfaction_lvl' => null, 'agreed_time' => null, 'feedback' => null, 
		'pharm_supervision' => null, 'coord_supervision' => null, 'req_id' => null, 
		'req_spec' => null, 'req_addr' => null, 'train_remarks' => null, 
		'train_recommend' => null, 'train_useful' => null, 'comf_issue' => null, 
		'comf_order' => null, 'comf_update' => null, 'comf_gen' => null, 'use_freq' => null, 
		'freq_spec' => null, 'improvement' => null, 'ease_of_use' => null, 'meet_expect' => null, 
		'expect_suggest' => null, 'retrain' => null)) : $evaluation;
		
		$data['evaluation_data'] = $data;
		$data['facility_code'] = $facility_code;
		$data['user_id'] = $user_id;
		$data['facilities'] = Facilities::get_facility_name($facility_code);
		$data['title'] = "Facility Evaluation Form";
		$data['content_view'] = "facility/facility_reports/facility_evaluation";
		$data['banner_text'] = "Facility Evaluation Form";
		$this -> load -> view("shared_files/template/template", $data);
		
				
	}
	public function facility_impact_report()
	{
		$facility_code = $this -> session -> userdata('facility_id');
		$current_user = $this -> session -> userdata('user_id');
		$report = Facility_Impact_Evaluation::get_all($facility_code, $current_user);
		isset($report)? $data['filled_report']=$report:$data['filled_report']=NULL;
		
		$data['facilities'] = Facilities::get_facility_name($facility_code);
		$data['title'] = "Facility Impact Report";
		$data['content_view'] = "facility/facility_reports/facility_impact_evaluation";
		$data['banner_text'] = "Facility Evaluation Form";
		$this -> load -> view("shared_files/template/template", $data);
		
		
	}
	public function save_facility_impact_evaluation() 
	{
		$facility_code = $this -> session -> userdata('facility_id');
		$current_user = $this -> session -> userdata('user_id');
		$date = date('y-m-d');
		$save_time = date('Y-m-d H:i:s');
		
		//Get data from the form
		$personnel_no = $_POST['personnel_no'];
		$no_still_using_tool = $_POST['no_still_using_tool'];
		$trainee_cadre = $_POST['trainee_cadre'];
		$weekly_no_of_times = $_POST['weekly_no_of_times'];
		$no_of_commodity_stock_out = $_POST['no_of_commodity_stock_out'];
		$duration_of_stock_out = $_POST['duration_of_stock_out'];
		$total_expired_commodities = $_POST['total_expired_commodities'];
		$total_overstocked_commodities = $_POST['total_overstocked_commodities'];
		$date_of_last_order = date('Y-m-d',strtotime($_POST['date_of_last_order']));
		$quarter_served = $_POST['quarter_served'];
		$elaborated_discrepancies = $_POST['elaborated_discrepancies'];
		$expected_date_of_delivery = date('Y-m-d',strtotime($_POST['expected_date_of_delivery']));
		$general_challenges = $_POST['general_challenges'];
		//Values from checkboxes
		$qstn_tally = $_POST['qstn_tally'];
		$adequate_storage =$_POST['adequate_storage'];
		$discrepancies = $_POST['discrepancies'];
				
		$dbData = array('facility_code'=>$facility_code,
						'user_id'=>$current_user,
						'no_of_personnel'=> $personnel_no,
						'no_still_using_tool'=> $no_still_using_tool,
						'cadres_of_users'=> $trainee_cadre,
						'no_of_times_a_week'=> $weekly_no_of_times,
						'does_physical_count_tally'=> $qstn_tally,
						'amount_of_commodities_stocked'=> $no_of_commodity_stock_out,
						'duration_of_stockout'=> $duration_of_stock_out,
						'amount_of_expired_commodities'=> $total_expired_commodities,
						'amount_of_overstocked_commodities'=> $total_overstocked_commodities,
						'adequate_storage'=> $adequate_storage,
						'date_of_last_order'=> $date_of_last_order,
						'quarter_served'=> $quarter_served,
						'discrepancies'=> $discrepancies,
						'reasons_for_discrepancies'=> $elaborated_discrepancies,
						'date_of_delivery'=> $expected_date_of_delivery,
						'general_challenges'=> $general_challenges,
						'report_time'=> $save_time);
	
		$this ->db->insert('facility_impact_evaluation',$dbData);
		$this->facility_impact_report();
		
	}
	public function save_facility_evaluation() 
	{
		$facility_code =  $this -> session -> userdata('facility_id');
		$current_user = $this -> session -> userdata('user_id');
		$date = date('y-m-d');
		$data_array = $_POST['data_array'];
		$dataarray = explode("|", $data_array);
		$f_headno = $dataarray[0];
		$f_depheadno = $dataarray[1];
		$nurse_no = $dataarray[2];
		$store_mgrno = $dataarray[3];
		$p_techno = $dataarray[4];
		$trainer = $dataarray[5];
		$comp_avail = $dataarray[6];
		$modem_avail = $dataarray[7];
		$bundles_avail = $dataarray[8];
		$manuals_avail = $dataarray[9];
		$satisfaction_lvl = $dataarray[10];
		$agreed_time = $dataarray[11];
		$feedback = $dataarray[12];
		$pharm_supervision = $dataarray[13];
		$coord_supervision = $dataarray[14];
		$req_id = $dataarray[15];
		$req_spec = $dataarray[16];
		$req_addr = $dataarray[17];
		$train_remarks = $dataarray[18];
		$train_recommend = $dataarray[19];
		$train_useful = $dataarray[20];
		$comf_issue = $dataarray[21];
		$comf_order = $dataarray[22];
		$comf_update = $dataarray[23];
		$comf_gen = $dataarray[24];
		$use_freq = $dataarray[25];
		$freq_spec = $dataarray[26];
		$improvement = $dataarray[27];
		$ease_of_use = $dataarray[28];
		$meet_expect = $dataarray[29];
		$expect_suggest = $dataarray[30];
		$retrain = $dataarray[31];

		$mydata = array('facility_code' => $facility_code, 
						'assessor' => $current_user, 
						'date' => $date, 
						'fhead_no' => $f_headno, 
						'fdep_no' => $f_depheadno, 
						'nurse_no' => $nurse_no, 
						'sman_no' => $store_mgrno, 
						'ptech_no' => $p_techno, 
						'trainer' => $trainer, 
						'comp_avail' => $comp_avail, 
						'modem_avail' => $modem_avail, 
						'bundles_avail' => $bundles_avail, 
						'manuals_avail' => $manuals_avail, 
						'satisfaction_lvl' => $satisfaction_lvl, 
						'agreed_time' => $agreed_time, 
						'feedback' => $feedback, 
						'pharm_supervision' => $pharm_supervision, 
						'coord_supervision' => $coord_supervision, 
						'req_id' => $req_id, 
						'req_spec' => $req_spec, 
						'req_addr' => $req_addr, 
						'train_remarks' => $train_remarks, 
						'train_recommend' => $train_recommend, 
						'train_useful' => $train_useful, 
						'comf_issue' => $comf_issue, 
						'comf_order' => $comf_order, 
						'comf_update' => $comf_update, 
						'comf_gen' => $comf_gen, 
						'use_freq' => $use_freq, 
						'freq_spec' => $freq_spec, 
						'improvement' => $improvement, 
						'ease_of_use' => $ease_of_use, 
						'meet_expect' => $meet_expect, 
						'expect_suggest' => $expect_suggest, 
						'retrain' => $retrain);

		echo Facility_Evaluation::save_facility_evaluation($mydata);
		
		$facility_code = $this -> session -> userdata('facility_id');
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['report_view'] = "facility/facility_reports/potential_expiries_v";
		$data['report_data'] = Facility_stocks::potential_expiries($facility_code);
		
		$view = 'shared_files/template/template';
				

	}
	public function facility_evaluation_($message = NULL) 
	{
		$this -> session -> set_flashdata('system_success_message', "Facility Training Evaluation Has been " . $message);
		redirect('reports/facility_evaluation_report');
	}
	
	public function facility_stock_data() 
	{
		$facility_code = $this -> session -> userdata('facility_id');
		$data['facility_stock_data'] = facility_stocks::get_distinct_stocks_for_this_facility($facility_code,"batch_data",'all');
		$data['title'] = "Facility Stock";
		$data['content_view'] = "facility/facility_reports/facility_stock_data_v";
		$data['banner_text'] = "Facility Stock";
		$this -> load -> view("shared_files/template/template", $data);

	
	}

	// get the facility transaction data for ordering or quick analysis
	public function facility_transaction_data() {
		$facility_code = $this -> session -> userdata('facility_id');
		$data['facility_stock_data'] = facility_transaction_table::get_all($facility_code);
        $data['last_issued_data']=facility_issues::get_last_time_facility_issued($facility_code);
		$data['title'] = "Facility Stock Summary";
		$data['content_view'] = "facility/facility_reports/facility_transaction_data_v";
		$data['banner_text'] = "Facility Stock Summary";
		$this -> load -> view("shared_files/template/template", $data);
	}

	///////GET THE ITEMS A FACILITY HAS STOCKED OUT ON
	public function facility_stocked_out_items() {
		$facility_code = $this -> session -> userdata('facility_id');
		$data['facility_stock_data'] = facility_stocks::get_items_that_have_stock_out_in_facility($facility_code);
		$data['title'] = "Facility Stock Out Summary";
		$data['content_view'] = "facility/facility_reports/facility_stocked_out_items_v";
		$data['banner_text'] = "Facility Stock Out Summary";
		$this -> load -> view("shared_files/template/template", $data);
	}
    public function order_delivery($order_id) {
    	$test= $this -> hcmp_functions -> create_order_delivery_color_coded_table($order_id);
		$data['content_view'] = "facility/facility_orders/view_order_delivery_details_v";
		$data['table']=$test['table'];
		$data['order_id']=$order_id;
		$data['title'] = "Facility Order delivery Report";
		$data['banner_text'] = "Facility Order delivery Report";
		$this -> load -> view('shared_files/template/template', $data);
	}
	public function download_order_delivery($order_id) {
    	$test= $this -> hcmp_functions -> create_order_delivery_color_coded_table($order_id);
		$file_name = $test['facility_name'] . '_facility_order_no_' .$order_id. "_date_created_" .$test['date_ordered'];
		$pdf_data = array("pdf_title" => "Order Report For $test[facility_name]", 'pdf_html_body' => $test['table'], 'pdf_view_option' => 'view_file', 'file_name' => $file_name);
		$this -> hcmp_functions -> create_pdf($pdf_data);
	}

	public function order_listing($for,$report=null) 
	{
		$facility_code =$county_id=$district_id=null ;	
		
		if($for=='facility'):
			$facility_code = $this -> session -> userdata('facility_id');
			$template = "shared_files/template/template";		
			$desc = 'Facility Orders';
		elseif($for=='subcounty'):
			$template = "shared_files/template/dashboard_template_v";
			$district_id = $this -> session -> userdata('district_id');
			$desc = 'Subcounty Orders';
		elseif($for=='county'):
			$template = "shared_files/template/dashboard_template_v";
			$county_id = $this -> session -> userdata('county_id');
			$desc = 'County Orders';
		endif;		
		
		//get order information from the db
		$facility_order_count_ = facility_orders::get_facility_order_summary_count($facility_code, $district_id, $county_id);
		$facility_order_count = array();
		
		foreach ($facility_order_count_ as $facility_order_count_) 
		{
			$facility_order_count[$facility_order_count_['status']] = $facility_order_count_['total'];
		}
		
		$data['order_counts'] = $facility_order_count;
		$data['delivered'] = facility_orders::get_order_details($facility_code, $district_id, $county_id, "delivered");
		$data['pending'] = facility_orders::get_order_details($facility_code, $district_id, $county_id, "pending");
		$data['approved'] = facility_orders::get_order_details($facility_code, $district_id, $county_id, "approved");
		$data['rejected'] = facility_orders::get_order_details($facility_code, $district_id, $county_id, "rejected");
		$data['facilities']=($for=='subcounty') ? Facilities::get_facilities_all_per_district($this -> session -> userdata('district_id'),'set') : array();
		
		if($report=='true'):
			$data['title'] = "$desc Listing";
			$data['banner_text'] = "$desc Listing";
			$data['content_view'] = "facility/facility_reports/reports_v";
			$data['report_view'] = "facility/facility_orders/order_listing_v";
			$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
		else:
		$data['title'] = $desc;
		$data['banner_text'] = $desc;

		
		$data['content_view'] = "facility/facility_orders/order_listing_v";	
		endif;
		
		$this -> load -> view('shared_files/template/template', $data);
	}

	public function create_excel_facility_stock_template() {
		$facility_code = $this -> session -> userdata('facility_id');
		$facility_details = Facilities::get_facility_name_($facility_code) -> toArray();
		$facility_stock_data = Commodities::get_facility_commodities($facility_code, 'sub category data');
		$excel_data = array('doc_creator' => $facility_details[0]['facility_name'], 'doc_title' => 'facility stock list template ', 'file_name' => 'facility stock list template');
		$row_data = array(); 
		$column_data = array("item HCMP code", "Description", "Supplier","Supplier ID", "Commodity Code", "Unit Size",
		"Total Units", "Batch No", "Manufacturer", "Expiry Date (month-year)", "Stock Level (Pack)", "Stock Level (Units)");
		$excel_data['column_data'] = $column_data;
		foreach ($facility_stock_data as $facility_stock_data_item) :
		array_push($row_data, array($facility_stock_data_item["commodity_id"],
		$facility_stock_data_item["commodity_name"],
		$facility_stock_data_item["source_name"],
		$facility_stock_data_item["supplier_id"],
		$facility_stock_data_item["commodity_code"],
		$facility_stock_data_item["unit_size"], 
		$facility_stock_data_item["total_commodity_units"], 
		"", "", "", "0", "0"));
		endforeach;
		$excel_data['row_data'] = $row_data;

		$this -> hcmp_functions -> create_excel($excel_data);
	}
    
	public function create_excel_facility_order_template($order_id,$facility_code) {

		$this -> hcmp_functions -> clone_excel_order_template($order_id,'download_file');
	}

    public function aggragate_order_new_sorf($order_id){
    $order_id_array=explode("_", $order_id);
	$facility_name=array();
	$order_id=array();
	$order_total=array("","","","");
	$order_total_all=0;
	$order_total_all_items=0;
	foreach($order_id_array as $single_order_id){//get the facility names from the orders 
		if($single_order_id>0):
	    $test=facility_orders::get_order_($single_order_id);
	    $order_id=array_merge($order_id,array($single_order_id));
		foreach($test as $test_){
		$order_total=array_merge($order_total,array("",$test_->order_total));
		$order_total_all=$order_total_all+$test_->order_total;
		foreach($test_->facility_detail as $facility_data){
		array_push($facility_name,"$facility_data->facility_name",'');
		}		
		}
		endif;
	 }
	    array_push($order_total,$order_total_all);
	    array_push($facility_name ,"TOTAL");//combine all of them
        $stock_data=Commodities::get_all_from_supllier(1);
		$from_stock_data=count($stock_data);//get items from a supplier 
		$excel_data = array('doc_creator' => "HCMP", 'doc_title' => 'test ', 'file_name' => 'test');
		$row_data = array(array("Product Code","Item description(Name/form/strength)","Order unit size", "Price"));
		foreach($order_id as $order_id_){
		array_push($row_data[0],"Quantity  to Order","Total");	//push this to go with each facility
		}		
		$column_data = array("","","FACILITY NAME","");
		$column_data=array_merge($column_data ,$facility_name);
		$excel_data['column_data'] = $column_data;
		for($i=0;$i<$from_stock_data;$i++):
		$total_all=0;
	    $temp_array=array();
	    $temp_array_=array();
		if ($i==0) {//push the first sub category		
		array_push($row_data,
		 array($stock_data[$i]["sub_category_name"],"","", "",  ""));
			   }      	
        else if( $stock_data[$i]['sub_category_name']!=$stock_data[$i-1]['sub_category_name']){//push the first sub category
       	 array_push($row_data,array($stock_data[$i]["sub_category_name"],"","", "",  ""));
       	 }
		foreach($order_id as $order_id_){
		$total=facility_order_details::get_order_details_from_order($order_id_,$stock_data[$i]["commodity_id"]);
			    
		if(count($total)==0){
		 array_push($temp_array,0,0);
		}else{
		$total_=$total[0]['total']*str_replace(",", '',$stock_data[$i]["unit_cost"]);
		array_push($temp_array,$total[0]['total'],$total_);
		$total_all=$total_all+$total_;
		$order_total_all_items=$order_total_all_items+$total_;
		}
		}	
        $temp_array_= array($stock_data[$i]["commodity_code"],
		 $stock_data[$i]["commodity_name"], 
		 $stock_data[$i]["unit_size"], 
		 $stock_data[$i]["unit_cost"]);
		 $temp_array_=array_merge($temp_array_,$temp_array);
		 array_push($temp_array_,$total_all);
		 array_push($row_data,$temp_array_);
		endfor;

        array_push($row_data,$order_total);
		$excel_data['row_data'] = $row_data;
      
		$this -> hcmp_functions -> create_excel($excel_data);    	
    }

	public function expiries($facility_code=null) {
        $facility_code=isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name=Facilities::get_facility_name_($facility_code)->toArray();
		$data['facility_name']=$facility_name[0]['facility_name'];
		$data['title'] = "Expiries";
		$data['banner_text'] = "Expiries";
		$data['sidebar'] = (!$this -> session -> userdata('facility_id')) ? "shared_files/report_templates/side_bar_sub_county_v": "shared_files/report_templates/side_bar_v" ;
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['expiry_data'] = Facility_stocks::All_expiries($facility_code);
		$data['report_view'] = "facility/facility_reports/expiries_v";

		$this -> load -> view("shared_files/template/template", $data);

	}

	public function potential_exp_process($facility_code=null) {
        $facility_code=isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name=Facilities::get_facility_name_($facility_code)->toArray();
		$data['facility_name']=$facility_name[0]['facility_name'];;
		$interval = $_POST['option_selected'];
		$data['report_data'] = Facility_stocks::specify_period_potential_expiry($facility_code, $interval);
		$this -> load -> view("facility/facility_reports/ajax/potential_expiries_ajax", $data);

	}
	public function potential_exp_process_subcounty($facility_code=null) {
        $facility_code=isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name=Facilities::get_facility_name_($facility_code)->toArray();
		$data['facility_name']=$facility_name[0]['facility_name'];
		$data['facility_code']=$facility_code;
		$data['title'] = "Potential Expiries";
		$data['banner_text'] = "Potential Expiries";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$view = 'shared_files/template/template';
		$data['report_view'] = "facility/facility_reports/potential_expiries_v";
		$data['sidebar'] = (!$this -> session -> userdata('facility_id')) ? "shared_files/report_templates/side_bar_sub_county_v": "shared_files/report_templates/side_bar_v" ;
		$data['report_data'] = Facility_stocks::potential_expiries($facility_code);
        $this -> load -> view($view, $data);
	}

	public function stock_control($facility_code=null) {
		$facility_code=isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name=Facilities::get_facility_name_($facility_code)->toArray();
		$data['facility_name']=$facility_name[0]['facility_name'];
		$data['title'] = "Bin Card";
		$data['banner_text'] = "Bin Card";
		$data['sidebar'] = (!$this -> session -> userdata('facility_id')) ? "shared_files/report_templates/side_bar_sub_county_v": "shared_files/report_templates/side_bar_v" ;
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['commodities']=Commodities::get_facility_commodities($facility_code);
		$data['report_view'] = "facility/facility_reports/bin_card_v";
		$this -> load -> view("shared_files/template/template", $data);

	}
	public function stock_control_ajax() {

		$facility_code = $this -> session -> userdata('facility_id');
		$commodity_id = $_POST['commodity_select'];
		$to = $_POST['to'];
		$from = $_POST['from'];			
		$data['bin_card'] = Facility_issues::get_bin_card($facility_code,$commodity_id,$from,$to);
		$count_records=count(Facility_issues::get_bin_card($facility_code,$commodity_id,$from,$to));
		
		if ($count_records<=0) {

			echo ' <div class="" id="reports_display" style="min-height: 350px;" >
            <div style="margin:auto; text-align: center">
                
                <h2> No records</h2>
                <h3>
                  If you have selected filters above and you still see this message, You have no Records
                </h3>
                
                </div>
            </div>
            ';
		}else{
		$this -> load -> view("facility/facility_reports/ajax/bin_card_ajax", $data);

		}

	}
	//Gets the consumption data for a particular facility
	public function view_consumption()
	{
		$data['report_view']="facility/facility_reports/consumption_report_v";
		$data['report_title'] =	"Divisional Malaria Reports";
	}
	public function load_expiries($facility_code=null) 
	{
        $facility_code = isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name_($facility_code)->toArray();
		$facility_name = $facility_name[0]['facility_name'];
		
		$expired_commodities = Facility_stocks::get_expiries($facility_code);
		
		//Holds all the months of the year
		//Build the line graph showing the expiries graph
		
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Total Expiries in '.$facility_name));
		$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Expiries  (values in units)'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Expiries"=>array())));
		
		foreach($expired_commodities as $facility_stock_expired):
			$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_stock_expired['month']));	
			$graph_data['series_data']['Expiries'] = array_merge($graph_data['series_data']['Expiries'],array((int)$facility_stock_expired['total_expiries']));	
		endforeach;
		
		$faciliy_expiry_data = $this->hcmp_functions->create_high_chart_graph($graph_data);
		
		$data['title'] = "Facility Expiries";
		$data['banner_text'] = "Facility Expiries";
		$data['graph_data'] = $faciliy_expiry_data;
       	$data['sidebar'] = "shared_files/report_templates/side_bar_v";
		$data['report_view'] = "facility/facility_reports/ajax/facility_expiry_filter_v";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$view = 'shared_files/template/template';
		$this -> load -> view($view, $data);
        
		
	}
	public function filter_expiries($year = null, $month = null, $district_id = null, $option = null, $facility_code = null,$report_type=null)
	{
		$year = strtolower($year);
		$month = strtolower($month);
		
		$option=($option=="NULL") ? "units" :$option;
		
		$facility_code = isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name_($facility_code)->toArray();
		$facility_name = $facility_name[0]['facility_name'];
		
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Total Expiries in '.$facility_name));
		
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Expiries (values in '.$option.')'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Expiries"=>array())));
		//get the filtered expiries
		if(($year != 'null') && $month != 'null' && $month>0)
		{
			//When both Month and year are set
			$graph_data = array_merge($graph_data,array("graph_type"=>'column'));
			$expired_commodities = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,
			$county_id, $year, $month,$option ,"all_");
			
			
			foreach($expired_commodities as $facility_stock_expired):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_stock_expired['name']));	
				$graph_data['series_data']['Expiries'] = array_merge($graph_data['series_data']['Expiries'],array((int)$facility_stock_expired['total']));	
			endforeach;
		
		}elseif(($year == 'null') && $month != 'null' && $month>0)
		{
			//When only month is set
			$year = date("Y");	
			$graph_data = array_merge($graph_data,array("graph_type"=>'column'));
			
			$expired_commodities = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,
			$county_id, $year, $month,$option ,"all_"); 
			
			foreach($expired_commodities as $facility_stock_expired):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_stock_expired['name']));	
				$graph_data['series_data']['Expiries'] = array_merge($graph_data['series_data']['Expiries'],array((int)$facility_stock_expired['total']));	
			endforeach;
		}
		elseif($month == 'null' && $year != 'null')
		{
			//when only year is set
			$graph_data = array_merge($graph_data,array("graph_type"=>'column'));
			$expired_commodities = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,
			$county_id, $year, null,$option ,"all"); 
			
			foreach($expired_commodities as $facility_stock_expired):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_stock_expired['cal_month']));	
				$graph_data['series_data']['Expiries'] = array_merge($graph_data['series_data']['Expiries'],array((int)$facility_stock_expired['total']));	
			endforeach;
		}else if ($month == 'null' && $year == 'null'){
			$year = date("Y");
			$graph_data = array_merge($graph_data,array("graph_type"=>'line')); 
			
			$expired_commodities = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,
			$county_id, $year, null,$option ,"all");
			
			foreach($expired_commodities as $facility_stock_expired):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_stock_expired['cal_month']));	
				$graph_data['series_data']['Expiries'] = array_merge($graph_data['series_data']['Expiries'],array((int)$facility_stock_expired['total']));	
			endforeach; 
		}
		
	
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		
		
	}
	public function load_cost_of_orders()
	{
		$facility_code = isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name_($facility_code)->toArray();
		$facility_name = $facility_name[0]['facility_name'];
		
		$cost_of_orders = facility_orders::get_cost_of_orders($facility_code);
		//Holds all the months of the year
		//Build the line graph showing the expiries graph
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Total Cost of Orders in '.$facility_name));
		$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Cost of Orders (Order Value)'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Total Cost"=>array())));
		
		foreach($cost_of_orders as $facility_cost_of_orders):
			$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_cost_of_orders['mwaka']));	
			$graph_data['series_data']['Total Cost'] = array_merge($graph_data['series_data']['Total Cost'],array((int)$facility_cost_of_orders['order_total']));	
		endforeach;
		
		$faciliy_data = $this->hcmp_functions->create_high_chart_graph($graph_data);
		
		$data['title'] = "Cost of Orders";
		$data['banner_text'] = "Cost of Orders";
		$data['graph_data'] = $faciliy_data;
       	$data['sidebar'] = "shared_files/report_templates/side_bar_v";
		$data['report_view'] = "facility/facility_reports/ajax/cost_of_orders_filter";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$view = 'shared_files/template/template';
		$this -> load -> view($view, $data);
        
	}
	public function load_filtered_cost_of_orders($year=null)
	{
		$year = (isset($year)) ? $year: date("Y");
		
		$facility_code = isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name_($facility_code)->toArray();
		$facility_name = $facility_name[0]['facility_name'];
		
		$cost_of_orders = facility_orders::get_cost_of_orders($facility_code, $year);
		//Holds all the months of the year
		//Build the line graph showing the expiries graph
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Total Cost of Orders in '.$facility_name));
		$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Cost of Orders (Order Value)'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Total Cost"=>array())));
		
		foreach($cost_of_orders as $facility_cost_of_orders):
			$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_cost_of_orders['mwaka']));	
			$graph_data['series_data']['Total Cost'] = array_merge($graph_data['series_data']['Total Cost'],array((int)$facility_cost_of_orders['order_total']));	
		endforeach;
		
		
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		
		
        
	}
	//Gets the consumption data for a particular facility
	//Only the facility that is logged in
	public function consumption() 
	{
		$facility_code = $this -> session -> userdata('facility_id'); 
		$county_id = $this -> session -> userdata('county_id');
		$facility_name = Facilities::get_facility_name2($facility_code);
		$county_id = $this -> session -> userdata('county_id');
		$county_name = counties::get_county_name($county_id);
		
		$consumption = Facility_stocks::get_commodity_consumption_level($facility_code);
		//Holds all the months of the year
		//Build the line graph showing the expiries graph
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Total Consumed Commodities in '.$facility_name['facility_name']));
		$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Consumed Commodities  (values in packs)'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Consumption"=>array())));
		//$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],$months);	
		
		foreach($consumption as $facility_stock_expired):
			$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_stock_expired['month']));	
			$graph_data['series_data']['Consumption'] = array_merge($graph_data['series_data']['Consumption'],array((int)$facility_stock_expired['total_consumption']));	
		endforeach;
		//create the graph here
		$faciliy_stock_data = $this->hcmp_functions->create_high_chart_graph($graph_data);
				
		$loading_icon = base_url().'assests/img/no-record-found.png'; 
		$faciliy_stock_data = isset($faciliy_stock_data)? $faciliy_stock_data : "$('#graph-section').html('<img src=$loading_icon>')'" ;
   			
		$data['title'] = "Consumption"	;
		$data['banner_text'] = "Facility Consumption"	;
		$data['c_data'] = Commodities::get_facility_commodities($facility_code);
		$data['graph_data'] =	$faciliy_stock_data;
		$data['sidebar'] = "shared_files/report_templates/side_bar_v";
		$data['report_view']="facility/facility_reports/ajax/consumption_stats_ajax";
		$data['content_view']="facility/facility_reports/reports_v";
		$view = 'shared_files/template/template';
		$this -> load -> view($view, $data);
		
	}	
	public function filtered_consumption($commodity_id, $year = null, $option = null)
	{
		$year = (isset($year))? $year: date("Y");
		$option = (isset($option)) ? $option: "units";
		
		$facility_code = isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name_($facility_code)->toArray();
		$facility_name = $facility_name[0]['facility_name'];
		
		$consumption = Facility_stocks::get_facility_consumption_level_new($facility_code, $commodity_id, $year, $option);
		//Holds all the months of the year
		//Build the line graph showing the expiries graph
		/*print "<pre>";
		print_r($year);
		print "</pre>";
		exit;*/
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Consumption for '.$facility_name));
		$graph_data = array_merge($graph_data,array("graph_type"=>'column'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Consumption'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Consumption"=>array())));
		
		foreach($consumption as $facility_consumption):
			$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_consumption['month']));	
			$graph_data['series_data']['Consumption'] = array_merge($graph_data['series_data']['Consumption'],array((int)$facility_consumption['total_consumption']));	
		endforeach;
		
		
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		
	}
	public function order_report()
	{
		$facility_code = $this -> session -> userdata('facility_id'); 
		$facility_name = Facilities::get_facility_name2($facility_code);
		$year = date("Y");
						
		$orders = facility_orders::get_facility_orders($facility_code);
		
		//Holds all the months of the year
		//Build the line graph showing the expiries graph
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Total Orders for '.$facility_name['facility_name'].' for '.$year));
		$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Orders (values in KSHS)'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Total Orders"=>array())));
		//$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],$months);	
		
		foreach($orders as $facility_orders):
			$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_orders['month']));	
			$graph_data['series_data']['Total Orders'] = array_merge($graph_data['series_data']['Total Orders'],array((int)$facility_orders['total_orders']));	
		endforeach;
		//create the graph here
		$facility_order_data = $this->hcmp_functions->create_high_chart_graph($graph_data);
				
		$loading_icon = base_url().'assets/img/loader.GIF'; 
		$facility_order_data = isset($facility_order_data)? $facility_order_data : "$('#graph-section').html('<img src=$loading_icon>')'" ;
   			
		$data['title'] = "Facility Orders"	;
		$data['banner_text'] = "Facility Orders"	;
		$data['graph_data'] = $facility_order_data;
		$data['sidebar'] = "shared_files/report_templates/side_bar_v";
		$data['report_view']="facility/facility_reports/ajax/facility_orders_filter_v";
		$data['content_view']="facility/facility_reports/reports_v";
		$view = 'shared_files/template/template';
		$this -> load -> view($view, $data);
		
	}
	public function filter_facility_orders($year, $month, $option)
	{
		$year = (isset($year)) ? $year: date("Y");
		$month = (isset($month)) ? $month: date("m");
		$option = (isset($option)) ? $option: "units";
		//Get the name of the month selected
		$m = date('F',strtotime('2000-'.$month.'-01'));
		
		$facility_code = isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name2($facility_code);
		
		
		$orders = facility_orders::get_filtered_facility_orders($facility_code, $year, $month, $option);
		
		//Holds all the months of the year
		//Build the line graph showing the expiries graph
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Total Orders for '.$facility_name['facility_name'].' in '.$m.' '. $year));
		$graph_data = array_merge($graph_data,array("graph_type"=>'column'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Orders (values in '.$option.')'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Total Orders"=>array())));
		
		foreach($orders as $facility_orders):
			$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_orders['name']));	
			$graph_data['series_data']['Total Orders'] = array_merge($graph_data['series_data']['Total Orders'],array((int)$facility_orders['total']));	
		endforeach;
		
		
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		
	}
	 
	public function get_facility_json_data($district_id) {
		echo json_encode(facilities::get_facilities_which_are_online($district_id));
	}
	     
	 //For system uptake option on SUB-COUNTY dashboard
	 public function get_sub_county_facility_mapping_data($year = null, $month = NULL) 
	 {
	 	$identifier = $this -> session -> userdata('user_indicator');
		$year = isset($year) ? $year : date("Y");
		$month = isset($month) ? $month : date("m");
		$county_id = $this -> session -> userdata('county_id');
		$district_id = $this -> session -> userdata('district_id');
		$district = $this -> session -> userdata('district_id');
		
		
		$county_name = Counties::get_county_name($county_id);
		$county_name = $county_name['county'];
		
		$district_name = Districts::get_district_name_($district_id);
		$district_name = $district_name['district'];
		
		$first_day_of_the_month = date("Y-m-1", strtotime(date($year . "-" . $month)));
		$last_day_of_the_month = date("Y-m-t", strtotime(date($year . "-" . $month)));

		$date_1 = new DateTime($first_day_of_the_month);
		$date_2 = new DateTime($last_day_of_the_month);

		$district_data = districts::getDistrict($county_id);
		
		echo "<br/> This: ";
		echo $district;
		
		$facility_data = Facilities::get_Facilities_using_HCMP($district);

		
		$log_data = Log::get_log_data($district_id,$county_id);
		//$user_data = 
	
		$series_data = array();
		$category_data = array();
		$series_data_monthly = array();
		$category_data_monthly = array();
		$seconds_diff = strtotime($last_day_of_the_month) - strtotime($first_day_of_the_month);
		$date_diff = floor($seconds_diff / 3600 / 24);	
		
		switch ($identifier):
		case 'county':
			$graph_category_data = $district_data;
			$graph_title = $county_name." County ";
			
		break;
		case 'district':
			$graph_category_data = $facility_data;
			$graph_title = $district_name." SubCounty ";
		break;	
		endswitch;

		for ($i = 0; $i <= $date_diff; $i++) :
			$day = 1 + $i;
			$new_date = "$year-$month-" . $day;
				
			$new_date = date('Y-m-d', strtotime($new_date));

			if (date('N', strtotime($new_date)) < 6) 
			{
				$date_ = date('D d', strtotime($new_date));
				$category_data = array_merge($category_data, array($date_));
				$temp_1 = array();
				foreach ($graph_category_data as $facility_) :
					$facility_id = $facility_ -> facility_code;
					$facility_name = $facility_ -> facility_name;
					$subcounty_data = Log::get_subcounty_login_count($county_id, $district_id, $new_date);
					(array_key_exists($facility_name, $series_data)) ? $series_data[$facility_name] = array_merge($series_data[$facility_name], array((int)$subcounty_data[0]['total'])) : $series_data = array_merge($series_data, array($facility_name => array((int)$subcounty_data[0]['total'])));
	
				endforeach;

			} else {
				// do nothing
			}
		endfor;
		//for setting the month name in the graph when filtering
		$m = date('F',strtotime('2000-'.$month.'-01'));
				
		$graph_data_daily = array();
		$graph_data_daily = array_merge($graph_data_daily,array("graph_id"=>'container'));
		$graph_data_daily = array_merge($graph_data_daily,array("graph_title"=>'Daily Facility Access log for '.$m." for ".$graph_title));
		$graph_data_daily = array_merge($graph_data_daily,array("graph_type"=>'line'));
		$graph_data_daily = array_merge($graph_data_daily,array("graph_yaxis_title"=>'log In'));
		$graph_data_daily = array_merge($graph_data_daily,array("graph_categories"=>array()));
		$graph_data_daily = array_merge($graph_data_daily,array("series_data"=>$series_data));
	    $graph_data_daily['graph_categories'] = $category_data;	
		$graph_daily = $this->hcmp_functions->create_high_chart_graph($graph_data_daily);

		for ($i = 0; $i < 12; $i++) :
			$day = 1 + $i;
			//changed it to be a month
			$new_date = "$year-$day";
			$new_date = date('Y-m', strtotime($new_date));
			$date_ = date('M', strtotime($new_date));
			$category_data_monthly = array_merge($category_data_monthly, array($date_));


			foreach ($graph_category_data as $facility_) :
				$facility_id = $facility_ -> facility_code;
				$facility_name = $facility_ -> facility_name;
				$subcounty_data = Log::get_subcounty_login_monthly_count($county_id, $district_id, $new_date);

				(array_key_exists($facility_name, $series_data_monthly)) ? $series_data_monthly[$facility_name] = array_merge($series_data_monthly[$facility_name], array((int)$subcounty_data[0]['total'])) : $series_data_monthly = array_merge($series_data_monthly, array($facility_name => array((int)$subcounty_data[0]['total'])));


			endforeach;

		endfor;
			
			/*echo "<pre>";
			print_r($series_data_monthly);
			echo "</pre>";*/

		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'container_monthly'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Monthly Facility Access for '. $year));
		$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'log In'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>$series_data_monthly));
	    $graph_data['graph_categories']=$category_data_monthly;	
		$graph_monthly = $this->hcmp_functions->create_high_chart_graph($graph_data);

		
		$graph_log_data = array();
		$graph_log_data = array_merge($graph_log_data,array("graph_id"=>'log_data_graph'));
		$graph_log_data = array_merge($graph_log_data,array("graph_title"=>'User Activity for  '.$m.' for '. $graph_title));
		$graph_log_data = array_merge($graph_log_data,array("graph_type"=>'column'));
		$graph_log_data = array_merge($graph_log_data,array("graph_yaxis_title"=>'Activities'));
		$graph_log_data = array_merge($graph_log_data,array("graph_categories"=>array()));
		$graph_log_data['series_data']['Decommissions'] =
		$graph_log_data['series_data']['Redistributions'] =
		$graph_log_data['series_data']['Stock'] =
		$graph_log_data['series_data']['Orders'] = 
		$graph_log_data['series_data']['Issues'] =
		$graph_log_data['series_data']['User Log'] = array();
		
		
		foreach($log_data as $log_data_)
		{
			$sum = array_sum($log_data_);
			$issues = round(($log_data_['total_issues']/$sum)*100);
			$orders = round(($log_data_['total_orders']/$sum)*100);
			$decommissions = round(($log_data_['total_decommisions']/$sum)*100);
			$redistributions = round(($log_data_['total_redistributions']/$sum)*100);
			$stock = round(($log_data_['total_stock_added']/$sum)*100);
			$user = round(($log_data_['user_log']/$sum)*100);
			
			$graph_log_data['series_data']['Issues'] = array_merge($graph_log_data['series_data']['Issues'],array($issues));
			$graph_log_data['series_data']['Orders'] = array_merge($graph_log_data['series_data']['Orders'],array($orders));
			$graph_log_data['series_data']['Decommissions'] = array_merge($graph_log_data['series_data']['Decommissions'],array($decommissions));
			$graph_log_data['series_data']['Redistributions'] = array_merge($graph_log_data['series_data']['Redistributions'],array($redistributions));
			$graph_log_data['series_data']['Stock'] = array_merge($graph_log_data['series_data']['Stock'],array($stock));
			$graph_log_data['series_data']['User Log'] = array_merge($graph_log_data['series_data']['User Log'],array($user));
		
		
		}
		
		$graph_log = $this->hcmp_functions->create_high_chart_graph($graph_log_data);
		
		
		$data['graph_data_monthly'] =	$graph_monthly;
		$data['graph_data_daily'] =	$graph_daily;
		$data['graph_log'] = $graph_log;

		$data['get_facility_data'] = facilities::get_facilities_online_per_district($county_id);
		$get_dates_facility_went_online = facilities::get_dates_facility_went_online($county_id);
		$data['data'] = $this -> get_county_facility_mapping_ajax_request("on_load");
		
		if($this->input->is_ajax_request()):
		return $this -> load -> view('subcounty/ajax/facility_roll_out_at_a_glance_v', $data);
		else:
	    $data['report_view'] = "subcounty/ajax/facility_roll_out_at_a_glance_v";		
		$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$view = 'shared_files/template/template';
		$this -> load -> view($view, $data);
		endif;
		

	}
	public function get_county_facility_mapping_ajax_request($option = null) 
	{
		$district_id = $this -> session -> userdata('district_id');
		$county_id = $this -> session -> userdata('county_id');
		
		$district_data = districts::getDistrict($county_id);
		
		$table_data = "<tbody>";
		$table_data_summary = "<tbody>";
		$district_names = "<thead><tr><th>Monthly Activities</th>";
		
		$district_total = array();
		$district_total_facilities = array();
		$district_total_facilities_targetted = array();
		$district_total_facilities_using_hcmp = array();
		
		$table_district_totals = "";
		$all_facilities = 0;
		$total_facility_list = '';
		$total_facilities_in_county = 0;
		$percentage_coverage = "";
		$percentage_coverage_total = 0;
		
		$percentage_coverage_using = "";
		$percentage_coverage_total_using = 0;
		
		$get_dates_facility_went_online = facilities::get_dates_facility_went_online($county_id);

		foreach ($get_dates_facility_went_online as $facility_dates) :

			$monthly_total = 0;
			$date = $facility_dates['date_when_facility_went_online'];
			$table_data .= "<tr>
	    <td>" . $date . "</td>";
			foreach ($district_data as $district_detail) :

				$district_id = $district_detail -> id;
				$district_name = $district_detail -> district;
				$get_facilities_which_went_online_ = facilities::get_facilities_which_went_online_($district_id, $facility_dates['date_when_facility_went_online']);
				
				$total = $get_facilities_which_went_online_[0]['total'];
				$total_facilities = $get_facilities_which_went_online_[0]['total_facilities'];
				$total_facilities_targetted = $get_facilities_which_went_online_[0]['total_facilities_targetted'];
				$total_facilitites_using_hcmp = $get_facilities_which_went_online_[0]['total_using_hcmp'];
				// echo "<pre>";
				// print_r($total_facilities_targetted);
				// echo "</pre>";
				
				$monthly_total = $monthly_total + $total;
				$all_facilities = $all_facilities + $total;
				
				(array_key_exists($district_name, $district_total)) ? $district_total[$district_name] = $district_total[$district_name] + $total : $district_total = array_merge($district_total, array($district_name => ($total)));
				(array_key_exists($district_name, $district_total_facilities)) ? $district_total_facilities[$district_name] = $total_facilities : $district_total_facilities = array_merge($district_total_facilities, array($district_name => $total_facilities));
				(array_key_exists($district_name, $district_total_facilities_targetted)) ? $district_total_facilities_targetted[$district_name] = $total_facilities_targetted : $district_total_facilities_targetted = array_merge($district_total_facilities_targetted, array($district_name => $total_facilities_targetted));
				(array_key_exists($district_name, $district_total_facilities_using_hcmp)) ? $district_total_facilities_using_hcmp[$district_name] = $total_facilitites_using_hcmp : $district_total_facilities_using_hcmp = array_merge($district_total_facilities_using_hcmp, array($district_name => $total_facilitites_using_hcmp));
				
				$table_data .= ($total > 0) ? "<td><a href='#' id='$district_id' class='ajax_call2 link' date='$date'> $total</a></td>" : "<td>$total</td>";

			endforeach;

			$table_data .= "<td>$monthly_total</td></tr>";

		endforeach;
		
		$table_data .= "<tr>";
		$table_data_summary .= "<tr>";

		$checker = 1;
		foreach ($district_total as $key => $value) :
			$coverage = 0;
			$using = 0;
			@$using_percentage = round((($value / $district_total_facilities_using_hcmp[$key])) * 100, 1);
			@$coverage = round((($value / $district_total_facilities[$key])) * 100, 1);

			$percentage_coverage_total = $percentage_coverage_total + $coverage;
			$percentage_coverage_total_using = $percentage_coverage_total_using + $using_percentage;

			$district_names .= "<th>$key</th>";

			$total_facility_list .= ($checker == 1) ? "<tr><td><b>TOTAL: Facilities in District</b></td><td>$district_total_facilities[$key]</td>" : "<td>$district_total_facilities[$key]</td>";
			$table_data .= ($checker == 1) ? "<td><b>TOTAL: Facilities using HCMP</b></td><td>$value</td>" : "<td>$value</td>";
			
			$table_summary .= ($checker == 1) ? "<td><b>TOTAL: Facilities using HCMP</b></td><td>$value</td>" : "<td>$value</td>";
			
			$total_targetted_facility_list .= ($checker == 1) ? "<tr><td><b>TOTAL: Targetted Facilities in District</b></td><td>$district_total_facilities_targetted[$key]</td>":"<td>$district_total_facilities_targetted[$key]</td>";

			$total_facilities_in_county = $total_facilities_in_county + $district_total_facilities[$key];
			$targetted_total = $targetted_total + $district_total_facilities_targetted[$key];
			
			$percentage_coverage_using .= ($checker == 1) ? "<tr><td><b>Using HCMP vs Targetted %</b></td>
			<td>$using_percentage %</td>" : "<td>$using_percentage %</td>";
			
			$percentage_coverage .= ($checker == 1) ? "<tr><td><b>% Coverage</b></td>
			<td>$coverage %</td>" : "<td>$coverage %</td>";
			
			$checker++;

		endforeach;
		
		$table_data .= "<td><a href='#' id='total' class='ajax_call1 link' option='total' date='total'>$all_facilities</a></td></tr></tbody>";
		$table_data_summary .= "<td><a href='#' id='total' class='ajax_call2 link' date='total'>$all_facilities</a></td></tr></tbody>";
		$table_datas_summary .= "<td><a href='#' id='total' class='ajax_call2 link' date='total'>$all_facilities</a></td>";
		$district_names .= "<th>TOTAL</th></tr></thead>";
		$final_coverage_total = 0;
		//$targetted_vs_using_hcmp = 0;
		$total_facilities_targetted = 1;
		$targetted_vs_using_hcmp = round((($total_facilitites_using_hcmp /$total_facilities_targetted )) * 100, 1);
		@$final_coverage_total = round((($all_facilities / $total_facilities_in_county)) * 100, 1);
		$data_ = "
		<div class='tabbable tabs-left'>
		<div class='tab-content'>
        <ul class='nav nav-tabs'>
        <li class='active'><a href='#A' data-toggle='tab'>Monthly Break Down</a></li>
        <li class=><a href='#B' data-toggle='tab'>Roll out Summary</a></li>
        </ul>
         <div  id='A' class='tab-pane fade active in'>
			<table class='row-fluid table table-hover table-bordered table-update' width='80%' id='test1'>" 
			. $district_names . $table_data . $total_facility_list .  "<td>$total_facilities_in_county</td></tr>" 
			.$total_targetted_facility_list."<td>$targetted_total</td>" 
			. $percentage_coverage . "<td>$final_coverage_total %</td></tr>".$percentage_coverage_using."<td>$targetted_vs_using_hcmp %</td></tr>
			</table>
		</div>
		
		<div id='B' class='tab-pane fade' >
		<table class='row-fluid table table-hover table-bordered table-update' width='80%' id='test2'>" 
		. $district_names . $table_summary . $table_datas_summary . $total_facility_list. "<td>$total_facilities_in_county</td></tr>"
		.$total_targetted_facility_list."<td>$targetted_total</td>" . $percentage_coverage .
		"<td>$final_coverage_total %</td></tr>".$percentage_coverage_using."<td>$targetted_vs_using_hcmp %</td></tr></table>
		 </div>
		 </div>";
		

		if (isset($option)) :
			return $data_;
		else : echo $data_;
		endif;
		
				
		
	}
	public function get_district_drill_down_detail($district_id, $date_of_activation) 
	{
		$district_data = "";
		$county_id = $this -> session -> userdata('county_id');
		$get_facility_data = facilities::get_facilities_reg_on_($district_id, urldecode($date_of_activation));
		
		foreach ($get_facility_data as $facility_data) :
			$facility_code = $facility_data -> facility_code;
			$facility_user_data = users::get_user_info($facility_code);
			$facility_name = $facility_data -> facility_name;
			$district_data .= '<span class="" width="100%"><b>' . $facility_name . '</b></span>
				<table class="row-fluid table table-hover table-bordered table-update" width="100%">
				<thead>
				<tr>
				<th>First Name</th><th>Last Name</th><th>Email </th><th>Phone No.</th>
				</tr>
				</thead>
				<tbody>';
			foreach ($facility_user_data as $user_data_) :
			$district_data .= "<tr><td>".$user_data_['fname']."</td><td>".$user_data_['lname']."</td><td>".$user_data_['email']."</td><td>".$user_data_['telephone']."</td>
			<tr>";
			endforeach;
			$district_data .= "</tbody></table>";
			endforeach;
	
		echo $district_data;

	}
	 //used for both the subcounty and county level program reports
	 public function program_reports()
	 {
	 	$user_indicator = $district_id=$this -> session -> userdata('user_indicator');
	 	switch ($user_indicator) 
	 	{
			case district :
				$district_id = $this -> session -> userdata('district_id');
				$facilities = Facilities::get_district_facilities($district_id);
				$index = 0;
					foreach ($facilities as $ids)
					{
						$facility_id = $ids['facility_code'];
						$report_malaria = Malaria_Data::get_facility_report_details($facility_id);
						$report_RH = RH_Drugs_Data::get_facility_report_details($facility_id) ;
						
						if ((!empty($report_RH))&&(!empty($report_malaria)))
						{
							$report_RH_report[$index] = $report_RH;
							$report_malaria_report[$index] = $report_malaria;
							
						}else{
							
						}
						
						$index++;
					}
					
				$data['malaria'] = $report_malaria_report;
				$data['RH'] = $report_RH_report;
				$data['title'] = "Program Reports";
				$data['banner_text'] = "Program Reports";
			    $data['report_view'] = "subcounty/reports/program_reports_v";
				
			break;
			case county:
			 $county_id = $this -> session -> userdata('county_id');
				
			break;
		}
 		
 		
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
		$this -> load -> view('shared_files/template/template', $data);
		
	 }
	
//generates the pdf for a particular report
	public function get_facility_report_pdf($report_id, $facility_code, $report_type) 
	{
		
			$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($facility_code);
			$facility_name = $myobj -> facility_name;
			// get the order form details here
			//create the pdf here
			$pdf_body = $this ->  create_program_report_pdf_template($report_id, $facility_code, $report_type);
			$file_name = $facility_name . '_facility_program_report_date_created_'. date('d-m-y');
			$pdf_data = array("pdf_title" => "Program Report For $facility_name", 'pdf_html_body' => $pdf_body, 'pdf_view_option' => 'download', 'file_name' => $file_name);

			$this -> hcmp_functions -> create_pdf($pdf_data);
		redirect();
	}
	
	public function create_program_report_pdf_template($report_id, $facility_code, $report_type) 
	{
		if($report_type == "malaria")
		{
			//$report_time= strtotime($report_time);
			$from_malaria_data_table = Malaria_Data::get_facility_report($report_id, $facility_code);
			
			$from_malaria_data_table_count = count(Malaria_Data::get_facility_report($report_id, $facility_code));
			foreach ($from_malaria_data_table as $report_details) 
			{
				$mfl = $report_details['facility_id'];
				$commodity_code = $report_details['Kemsa_Code'];
				$mydrug_name = Doctrine::getTable('Malaria_drugs') -> findOneBykemsa_code($commodity_code);
				$commodityname = $mydrug_name -> drug_name;
				
				$report_date = $report_details['Report_Date'];
						
				$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($mfl);
				$sub_county_id = $myobj -> district;
				$facility_name = $myobj -> facility_name;
				// get the order form details here
	
				$myobj1 = Doctrine::getTable('Districts') -> find($sub_county_id);
				$sub_county_name = $myobj1 -> district;
				$county = $myobj1 -> county;
	
				$myobj2 = Doctrine::getTable('Counties') -> find($county);
				$county_name = $myobj2 -> county;
	
				$myobj_order = Doctrine::getTable('users') -> find($report_details['user_id']);
				$creator_email = $myobj_order -> email;
				$creator_name1 = $myobj_order -> fname;
				$creator_name2 = $myobj_order -> lname;
				$creator_telephone = $myobj_order -> telephone;
	
			}
	
		//create the table for displaying the order details
		$html_body = "<table class='data-table' width=100%>
			<tr>
			<td>MFL No: $mfl</td> 
			<td>Health Facility Name:<br/> $facility_name</td>
			<td>Level:</td>
			<td>Dispensary</td>
			<td>Health Centre</td>
			</tr>
			<tr>
			<td>County: $county_name</td> 
			<td> District: $sub_county_name</td>
			<td >Reporting Period <br/>
			Start Date:  <br/>  End Date: " . date('d M, Y', strtotime($report_date)) . "
			</td>
			</tr>
			</table>";
		$html_body .= "
		<table class='data-table'>
		<thead>
		<tr>
			<th><b>KEMSA Code</b></th>
			<th><b>Commodity Name</b></th>
			<th ><b>Beginning Balance</b></th>
			<th ><b>Quantity Received</b></th>
			<th ><b>Quantity Dispensed</b></th>
			<th ><b>Losses Excluding Expiries</b></th>
			<th ><b>Adjustments</b></th>
			<th ><b>Physical Count</b></th>
			<th ><b>Expired Drugs</b></th>
			<th ><b>Days Out of Stock</b></th>
			<th ><b>Report Total</b></th>
		</tr> 
		</thead>
		<tbody>";

		$html_body .= '<ol type="a">';
		for ($i = 0; $i < $from_malaria_data_table_count; $i++) 
		{
				
			$mydrug_name = Doctrine::getTable('Malaria_drugs') -> findOneBykemsa_code($from_malaria_data_table[$i]['Kemsa_Code']);
			//$commodityname[$i] = $mydrug_name -> drug_name;
			$adjs = $from_malaria_data_table[$i]['Positive_Adjustments'] + $from_malaria_data_table[$i]['Negative_Adjustments'];
			$html_body .= "<tr>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Kemsa_Code'] . "</td>";
			$html_body .= "<td>" . $commodityname = $mydrug_name -> drug_name. "</td>";
			$html_body .= "<td>". $from_malaria_data_table[$i]['Beginning_Balance']."</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Quantity_Received'] . "</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Quantity_Dispensed'] . "</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Losses_Excluding_Expiries'] . "</td>";
			$html_body .= "<td>" . $adjs. "</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Physical_Count'] . "</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Expired_Drugs'] . "</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Days_Out_Stock'] . "</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Report_Total'] . "</td>";
			$html_body .= "</tr>";
			
			
			
		}
	$html_body .= '</tbody></table></ol>';
	
	}elseif($report_type == "RH")
	{
		$from_RH_data_table = RH_Drugs_Data::get_facility_report($report_id, $facility_code);
		$from_RH_data_table_count = count(RH_Drugs_Data::get_facility_report($report_id, $facility_code));
		


		foreach ($from_RH_data_table as $report_details) 
		{
			$mfl = $report_details['facility_id'];
			$report_date = $report_details['Report_Date'];
					
			$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($mfl);
			$sub_county_id = $myobj -> district;
			$facility_name = $myobj -> facility_name;
			
			$myobj1 = Doctrine::getTable('Districts') -> find($sub_county_id);
			$sub_county_name = $myobj1 -> district;
			$county = $myobj1 -> county;

			$myobj2 = Doctrine::getTable('Counties') -> find($county);
			$county_name = $myobj2 -> county;

			$myobj_order = Doctrine::getTable('users') -> find($report_details['user_id']);
			$creator_email = $myobj_order -> email;
			$creator_name1 = $myobj_order -> fname;
			$creator_name2 = $myobj_order -> lname;
			$creator_telephone = $myobj_order -> telephone;
	
		}
	
		//create the table for displaying the order details
		$html_body = "<table class='data-table' width=100%>
			<tr>
			<td>MFL No: $mfl</td> 
			<td>Health Facility Name:<br/> $facility_name</td>
			<td>Level:</td>
			<td>Dispensary</td>
			<td>Health Centre</td>
			</tr>
			<tr>
			<td>County: $county_name</td> 
			<td> District: $sub_county_name</td>
			<td >Reporting Period <br/>
			Start Date:  <br/>  End Date: " . date('d M, Y', strtotime($report_date)) . "
			</td>
			</tr>
			</table>";
		$html_body .= "
		<table class='data-table'>
		<thead>
		<tr>
			<th ><b>Beginning Balance</b></th>
			<th ><b>Quantity Received This Month</b></th>
			<th ><b>Quantity Dispensed</b></th>
			<th ><b>Losses</b></th>
			<th ><b>Adjustments</b></th>
			<th ><b>Ending Balance</b></th>
			<th ><b>Quantity Requested</b></th>
		</tr> 
		</thead>
		<tbody>";
		$html_body .= '<ol type="a">';
		for ($i = 0; $i < $from_RH_data_table_count; $i++) 
		{
			$html_body .= "<tr>";
			$html_body .= "<td>". $from_RH_data_table[$i]['Beginning_Balance']."</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Received_This_Month'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Dispensed'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Losses'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Adjustments'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Ending_Balance'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Quantity_Requested'] . "</td>";
			$html_body .= "</tr>";
			
			
		}	
		
		$html_body .= '</tbody></table></ol>';
		
	}elseif($report_type == "TB")
	{
		$from_tb_data_table = tb_data::get_facility_report($report_id, $facility_code);
		$from_tb_data_table_count = count(tb_data::get_facility_report($report_id, $facility_code));

		foreach ($from_tb_data_table as $report_details) 
		{
			$mfl = $report_details['facility_code'];
			$report_date = $report_details['Report_Date'];
					
			$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($mfl);
			$sub_county_id = $myobj -> district;
			$facility_name = $myobj -> facility_name;
			
			$myobj1 = Doctrine::getTable('Districts') -> find($sub_county_id);
			$sub_county_name = $myobj1 -> district;
			$county = $myobj1 -> county;

			$myobj2 = Doctrine::getTable('Counties') -> find($county);
			$county_name = $myobj2 -> county;

			$myobj_order = Doctrine::getTable('users') -> find($report_details['user_id']);
			$creator_email = $myobj_order -> email;
			$creator_name1 = $myobj_order -> fname;
			$creator_name2 = $myobj_order -> lname;
			$creator_telephone = $myobj_order -> telephone;
	
		}
	
		//create the table for displaying the order details
		$html_body = "<table class='data-table' width=100%>
			<tr>
			<td>MFL No: $mfl</td> 
			<td>Health Facility Name:<br/> $facility_name</td>
			<td>Level:</td>
			<td>Dispensary</td>
			<td>Health Centre</td>
			</tr>
			<tr>
			<td>County: $county_name</td> 
			<td> District: $sub_county_name</td>
			<td >Reporting Period <br/>
			Start Date:  <br/>  End Date: " . date('d M, Y', strtotime($report_date)) . "
			</td>
			</tr>
			</table>";
		$html_body .= "
		<table class='data-table'>
		<thead>
		<tr>
			<th ><b>Beginning Balance</b></th>
			<th ><b>Quantity Received This Month</b></th>
			<th ><b>Quantity Dispensed</b></th>
			<th ><b>Losses</b></th>
			<th ><b>Adjustments</b></th>
			<th ><b>Ending Balance</b></th>
			<th ><b>Quantity Requested</b></th>
		</tr> 
		</thead>
		<tbody>";
		$html_body .= '<ol type="a">';
		for ($i = 0; $i < $from_RH_data_table_count; $i++) 
		{
			$html_body .= "<tr>";
			$html_body .= "<td>". $from_RH_data_table[$i]['Beginning_Balance']."</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Received_This_Month'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Dispensed'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Losses'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Adjustments'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Ending_Balance'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Quantity_Requested'] . "</td>";
			$html_body .= "</tr>";
			
			
		}	
		
		$html_body .= '</tbody></table></ol>';
		
	}

			
		return $html_body;
	}
	public function create_excel_facility_program_report($report_id,$facility_code, $report_type) 
	{
		if($report_type == "malaria")
		{
			$facility_details = Facilities::get_facility_name_($facility_code) -> toArray();
			$from_malaria_data_table = Malaria_Data::get_facility_report($report_id, $facility_code);
			$from_malaria_data_table_count = count(Malaria_Data::get_facility_report($report_id, $facility_code));
			
	

			$excel_data = array('doc_creator' => $facility_details[0]['facility_name'], 
			'doc_title' => 'facility programm report template ', 'file_name' => $facility_details[0]['facility_name'].'facility programm report template ');
			$row_data = array();
			$column_data = array("Product Code","Item description(Name/form/strength)",
								"Beginning Balance", "Quantity Received",
								"Quantity Dispensed","Losses Excluding Expiries","Adjustments",
								"Physical Count", "Amount Expired","Days Out of Stock",
								"Report Total");
			$excel_data['column_data'] = $column_data;
			$from_malaria_data_table_count = count(Malaria_Data::get_facility_report($report_id, $facility_code));
			for($i=0;$i<$from_malaria_data_table_count;$i++):
			
			$adjs = $from_malaria_data_table[$i]['Positive_Adjustments'] + $from_malaria_data_table[$i]['Negative_Adjustments'];
			$mydrug_name = Doctrine::getTable('Malaria_drugs') -> findOneBykemsa_code($from_malaria_data_table[$i]['Kemsa_Code']);
			
			array_push($row_data, array($from_malaria_data_table[$i]["Kemsa_Code"], 
			$commodityname = $mydrug_name -> drug_name,
				$from_malaria_data_table[$i]["Beginning_Balance"], 
				$from_malaria_data_table[$i]["Quantity_Received"], 
				$from_malaria_data_table[$i]["Quantity_Dispensed"],
				$from_malaria_data_table[$i]["Losses_Excluding_Expiries"],
				$adjs,
				$from_malaria_data_table[$i]["Physical_Count"],
				$from_malaria_data_table[$i]["Expired_Drugs"],
				$from_malaria_data_table[$i]["Days_Out_Stock"],
				$from_malaria_data_table[$i]["Report_Total"])); 
			endfor;
			$excel_data['row_data'] = $row_data;
	
			$this -> hcmp_functions -> create_excel($excel_data);
		}elseif($report_type == "RH"){
			
			$facility_details = Facilities::get_facility_name_($facility_code) -> toArray();
			
			$from_RH_data_table = RH_Drugs_Data::get_facility_report($report_id, $facility_code);
			$from_RH_data_table_count = count(RH_Drugs_Data::get_facility_report($report_id, $facility_code));
		
		echo "<pre>";
		print_r($from_RH_data_table);
		echo "</pre>";exit;
			$excel_data = array('doc_creator' => $facility_details[0]['facility_name'], 
			'doc_title' => 'facility program report ', 'file_name' => $facility_details[0]['facility_name'].'facility program report template');
			$row_data = array();
			$column_data = array("Beginning Balance",
								"Received This Month", 
								"Dispensed",
								"Losses",
								"Adjustments",
								"AEnding Balance",
								"Quantity Requested");
			$excel_data['column_data'] = $column_data;
			$from_RH_data_table_count = count(RH_Drugs_Data::get_facility_report($report_id, $facility_code));
			
			for($i=0;$i<$from_RH_data_table_count;$i++):
			
			array_push($row_data, array($from_RH_data_table[$i]["Beginning_Balance"], 
			$from_RH_data_table[$i]["Received_This_Month"], 
			$from_RH_data_table[$i]["Dispensed"], 
			$from_RH_data_table[$i]["Losses"],
			$from_RH_data_table[$i]["Adjustments"],
			$from_RH_data_table[$i]["Ending_Balance"],
			$from_RH_data_table[$i]["Quantity_Requested"])); //
			endfor;
			$excel_data['row_data'] = $row_data;
	
			$this -> hcmp_functions -> create_excel($excel_data);
		}elseif($report_type == "TB"){
			
			$facility_details = Facilities::get_facility_name_($facility_code) -> toArray();
			
			$from_TB_data_table = tb_data::get_facility_report($report_id, $facility_code);
			$from_TB_data_table_count = count(tb_data::get_facility_report($report_id, $facility_code));

			$tb_details = tb_data::get_facility_report($facility_code);

			$excel_data = array('doc_creator' => $facility_details[0]['facility_name'], 
			'doc_title' => 'facility program report ', 'file_name' => $facility_details[0]['facility_name'].'facility program report template');
			$row_data = array();
			$column_data = array("Beginning Balance",
								"Received This Month", 
								"Dispensed",
								"Losses",
								"Adjustments",
								"AEnding Balance",
								"Quantity Requested");
			$excel_data['column_data'] = $column_data;
			$from_RH_data_table_count = count(tb_data::get_facility_report($report_id, $facility_code));
			
			for($i=0;$i<$from_TB_data_table_count;$i++):
			
			array_push($row_data, array($from_RH_data_table[$i]["Beginning_Balance"], 
			$from_RH_data_table[$i]["Received_This_Month"], 
			$from_RH_data_table[$i]["Dispensed"], 
			$from_RH_data_table[$i]["Losses"],
			$from_RH_data_table[$i]["Adjustments"],
			$from_RH_data_table[$i]["Ending_Balance"],
			$from_RH_data_table[$i]["Quantity_Requested"])); //
			endfor;
			$excel_data['row_data'] = $row_data;
	
			$this -> hcmp_functions -> create_excel($excel_data);
		}
		
	}
 /*
     |--------------------------------------------------------------------------|
     | COUNTY SUB-COUNTY dashboard                                              |
     |--------------------------------------------------------------------------|
     */
	 public function expiries_dashboard()
	 {
		$county_id = $this -> session -> userdata('county_id');
		$data['district_data'] = districts::getDistrict($county_id);
		return $this -> load -> view("subcounty/ajax/county_expiry_filter_v", $data);	
	 }

	 public function get_county_cost_of_expiries_new($year = null, $month = null, $district_id = null, $option = null, $facility_code = null,$report_type=null) {
	 	//get_county_cost_of_expiries_new/0/null/88/0/17401
	 	$year=($year=="NULL") ? null :$year;
	 	$month=($month=="NULL") ? date("m") :$month;
	 	$district_id=($district_id=="NULL") ? null :$district_id;
	 	$option=($option=="NULL") ? null :$option;
	 	$facility_code=($facility_code=="NULL") ? null :$facility_code;
		$option=($option=="NULL" || $option=="null") ? null :$option;
		//set up the variables 
		$year = isset($year)? $year :date("Y");		
		$county_id = $this -> session -> userdata('county_id');
		$district_id_checker = $this -> session -> userdata('district_id');
		$county_name = counties::get_county_name($county_id);
		$category_data = array();
		$series_data =$series_data_ = array();		
		$temp_array =$temp_array_ = array();
		$graph_data=array();
		$graph_type='';
		$title='';
		
        //months
		$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');		
		$month_ = isset($month) ?$months[(int) $month-1] : null ;
        //check if the district is set
		$district_data = (isset($district_id) && ($district_id > 0)) ? districts::get_district_name($district_id) -> toArray() : null;
		$district_name_ = (isset($district_data)) ? " :" . $district_data[0]['district'] . " subcounty" : null;
		$option_new = isset($option) ? $option : "ksh";
		$facility_code_ = isset($facility_code) ? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name = $facility_code_[0]['facility_name'];
		$title=isset($facility_code) && isset($district_id)? "$district_name_ : $facility_name" :( 
	 	isset($district_id) && !isset($facility_code) ?  "$district_name_": "$county_name[county] county") ;
        //get the expiry for the entire year either for a facility sub-county or county     
         
		if ($year == date("Y") && $month == null) 
		{
			echo "Only year isset";
			exit;
			$category_data = array_merge($category_data, $months);
			$commodity_array = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,
			$county_id, $year, null,$option ,"all");   
		
			foreach ($commodity_array as $data) :
			$temp_array = array_merge($temp_array, array($data["cal_month"] => (int)$data['total']));
			endforeach;
			foreach ($months as $key => $data) :
			$val = (array_key_exists($data, $temp_array)) ? (int)$temp_array[$data] : (int)0;
			$series_data = array_merge($series_data, array($val));
			array_push($series_data_, array($data,$val));
			endforeach;
			
			$graph_type='spline';
		}
        //get the expiry for a specific month base on the set parameters
		if (isset($month) && $month>0) 
		{
			
			$commodity_array = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,
			$county_id, $year, $month,$option ,"all_");
		
			print_r($commodity_array);
			exit;
		
			foreach ($commodity_array as $data) :
			$series_data  = array_merge($series_data , array($data["name"] => (int) $data['total']));
			$category_data=array_merge($category_data, array($data["name"]));
			array_push($series_data_, array($data["name"], (int) $data['total']));
			endforeach;
	        $graph_type='column';
		}
		
		if($report_type=="csv_data"):
			$excel_data = array('doc_creator' =>$this -> session -> userdata('full_name'), 'doc_title' => "stock expired in $commodity_name $title $month_ $year", 'file_name' => "Stock_expired_$commodity_name_$title_$month_$year");
			$row_data = array(array("stock expired in $title $month_ $year","stock expired in $option_new"));
			$column_data = array("");
			$excel_data['column_data'] = $column_data;
			$row_data=array_merge($row_data,$series_data_);
			$excel_data['row_data'] = $row_data;
			$this -> hcmp_functions -> create_excel($excel_data);
		else:   
		    $graph_data=array_merge($graph_data,array("graph_id"=>'dem_graph_'));
		    $graph_data=array_merge($graph_data,array("graph_title"=>"stock expired in $title $month_ $year"));
		    $graph_data=array_merge($graph_data,array("graph_type"=>$graph_type));
		    $graph_data=array_merge($graph_data,array("graph_yaxis_title"=>"stock expired in $option_new"));
		    $graph_data=array_merge($graph_data,array("graph_categories"=>$category_data ));
		    $graph_data=array_merge($graph_data,array("series_data"=>array('total'=>$series_data)));
			$data = array();
		
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		endif;
	}
   public function stock_level_dashboard(){
 			$district_id = $this -> session -> userdata('district_id');
			$county_id = $this -> session -> userdata('county_id');
         	$final_graph_data = facility_stocks_temp::get_months_of_stock($district_id);
			$tracer_item_names = facility_stocks_temp::get_tracer_item_names($district_id);
			
			$month = date('F Y');

			$district = $this -> session -> userdata('district_id');
			$district_name = districts::get_district_name_($district);
			$name = $district_name['district'];
			 

			// variable values for drugs
			$albendazole_final= $final_graph_data[0]['month_stock'];
			$amoxicilin_final = $final_graph_data[1]['month_stock'];
			$paracetamol_final = $final_graph_data[2]['month_stock'];
			$zinc_sulphate = $final_graph_data[3]['month_stock'];
			$amoxicillin_oral_final = $final_graph_data[4]['month_stock'];
			$cotrimazole_final = $final_graph_data[5]['month_stock'];
			$metronidazole_final = $final_graph_data[6]['month_stock'];
			$ors_final = $final_graph_data[7]['month_stock'];
			$atropine_final = $final_graph_data[8]['month_stock'];
			$adrenaline_final = $final_graph_data[9]['month_stock'];
			$benzylpenicillin_final = $final_graph_data[10]['month_stock'];
			$hydrocortisone_final = $final_graph_data[11]['month_stock'];
			$tetracycline_final = $final_graph_data[12]['month_stock'];
			// end of variable values for drugs
			
         	$graph_data = array();
       		$graph_data = array_merge($graph_data, array("graph_id" => 'graph_default'));
			$graph_data = array_merge($graph_data, array("graph_title" => "Months of stock for tracer items in ". $name." subcounty"));
			$graph_data = array_merge($graph_data, array("graph_type" => 'column'));
			$graph_data = array_merge($graph_data, array("graph_yaxis_title" => 'Months of Stock'));
			$graph_data = array_merge($graph_data, array("graph_categories" => array()));
			$graph_data = array_merge($graph_data, array("series_data" => array("Stock" =>array())));	
	
			foreach($final_graph_data as $final_graph_data_):
			$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'], array($final_graph_data_['commodity_name']));
			$graph_data['series_data']['Stock'] = array_merge($graph_data['series_data']['Stock'],array((int)$final_graph_data_['month_stock']));	
			endforeach;
			
		 $data['default_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		
	     
	     $data['district_data'] = districts::getDistrict($county_id);
	     $data['c_data'] = Commodities::get_all_2();
         $data['tracer_items'] = Commodities::get_tracer_items();
		 $data['categories']= commodity_sub_category::get_all_pharm();
		 $data['number_of_tracer_items'] = count($tracer_item_names);
	     return $this -> load -> view("subcounty/ajax/county_stock_level_filter_v", $data);	
	   
	    }

	    public function tb_report(){
	    $data['title'] = "Facility Expiries";
		$data['banner_text'] = "Facility Tuberculosis & Leprosy Commodities Consumption Data Report & Request Form";
		$data['graph_data'] = $faciliy_expiry_data;
		$data['content_view'] = "subcounty/reports/tb_report";;
		$view = 'shared_files/template/template';
		$this -> load -> view($view, $data);
	    }

     	public function get_county_stock_level_new($commodity_id = null, $category_id = null, $district_id = null, $facility_code=null, $option = null,$report_type=null) 
     	{
     	//reset the values here

      	$commodity_id = ($commodity_id=="NULL") ? null :$commodity_id;
		$category_id = ($category_id=="NULL") ? null :$category_id;
	 	$district_id = ($district_id=="NULL") ? null :$district_id;
	 	$option = ($optionr=="NULL") ? null :$option;
	 	$facility_code = ($facility_code=="NULL") ? null :$facility_code;
		$option = ($option=="NULL" || $option=="null") ? null :$option;	
     	//setting up the data
		$county_id = $this -> session -> userdata('county_id');
		$county_name = counties::get_county_name($county_id);
		$category_data = $series_data = $series_data_ =  $graph_data = $data =array();
		$title='';	
		$year = date('Y');
		$month_ = date('M d');
        //check if the district is set
		$district_data = (isset($district_id) && ($district_id > 0)) ? districts::get_district_name($district_id) -> toArray() : null;
		$district_name_ = (isset($district_data)) ? " :" . $district_data[0]['district'] . " subcounty" : null;
		$option_new = isset($option) ? $option : "ksh";
		$facility_code_ = isset($facility_code) ? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name = $facility_code_[0]['facility_name'];
		$commodity_name = (isset($commodity_id))? Commodities::get_details($commodity_id)->toArray() : null;
		$category_name_ = @$commodity_name[0]['commodity_name'];
		$commodity_name = isset($category_name_)? " for ".$category_name_ : null;
		$title = isset($facility_code) && isset($district_id)? "$district_name_ : $facility_name" :( 
	 	isset($district_id) && !isset($facility_code) ?  "$district_name_": "$county_name[county] county") ;

		$commodity_array = facility_stocks::get_county_drug_stock_level_new($facility_code, $district_id, $county_id,
		$category_id, $commodity_id, $option_new, $report_type);
       //var_dump($commodity_array);
		//exit;

        foreach ($commodity_array as $data) :
			if($report_type=="table_data"):
				if($commodity_id>0):
					array_push($series_data , array($data['district'],$data["facility_name"],$data["facility_code"], $data['total']));
				else:
					array_push($series_data , array($data["name"], $data['total']));
				endif;						
			else:
				$series_data  = array_merge($series_data , array($data["name"] => (int)$data['total']));
				$series_data_  = array_merge($series_data_ , array($data["name"],(int)$data['total']));
				$category_data=array_merge($category_data, array($data["name"]));
			endif;
		endforeach;
		
		if($report_type=="table_data"):
			if($commodity_id>0):
				$category_data = array(array("Sub-county","Facility Name","Mfl","TOTAL ".$option_new));
			else:
				array_push($category_data, array("Stock level $commodity_name $title $month_ $year","stocks worth in $option_new"));
			endif;	
	       	$graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_'));
		    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
		    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
					
			$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
			$data['table_id'] ="dem_graph_";
			
			return $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
		
		elseif($report_type=="csv_data"):
			$excel_data = array('doc_creator' =>$this -> session -> userdata('full_name'), 'doc_title' => "Stock level $commodity_name $title $month_ $year", 'file_name' => "Stock_level_$commodity_name_$title_$month_$year");
			$row_data = array(array("Stock level $commodity_name $title $month_ $year",$option_new));
			$column_data = array("");
			$excel_data['column_data'] = $column_data;
			array_push($row_data,$series_data_); 
			$excel_data['row_data'] = $row_data;
			$this -> hcmp_functions -> create_excel($excel_data);
		else:
    		$graph_type = 'column';			
    		$graph_data = array_merge($graph_data,array("graph_id"=>'dem_graph_'));
		    $graph_data = array_merge($graph_data,array("graph_title"=>"Stock level $commodity_name $title $month_ $year"));
		    $graph_data = array_merge($graph_data,array("graph_type"=>$graph_type));
		    $graph_data = array_merge($graph_data,array("graph_yaxis_title"=>"Commodity Stock level in $option_new"));
		    $graph_data = array_merge($graph_data,array("graph_categories"=>$category_data ));
		    $graph_data = array_merge($graph_data,array("series_data"=>array('total'=>$series_data)));

		 $data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		 
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		endif;
	}
        public function consumption_data_dashboard() {

		 $county_id = $this -> session -> userdata('county_id');
	     $data['district_data'] = districts::getDistrict($county_id);
	     $data['c_data'] = Commodities::get_all_2();
		 
         $data['tracer_items'] = Commodities::get_tracer_items();
		 $data['categories']=commodity_sub_category::get_all_pharm();
		return $this -> load -> view("subcounty/ajax/county_consumption_data_filter_v", $data);
	}
	    public function consumption_stats_graph($commodity_id = null,$category_id = null, $district_id = null, $facility_code=null, $option = null,$from=null,$to=null,$report_type=null) {
	    //reset the values here
     	$commodity_id=($commodity_id=="NULL") ? null :$commodity_id;
	 	$district_id=($district_id=="NULL") ? null :$district_id;
	 	$facility_code=($facility_code=="NULL") ? null :$facility_code;
		$option=($option=="NULL" || $option=="null") ? null :$option;
		$from=($from=="NULL") ? strtotime(date('d-m-y')) :strtotime(urldecode($from));	
		$to=($to=="NULL") ? strtotime(date('d-m-y')) : strtotime(urldecode($to));
		$category_id=($category_id=="NULL") ? null :$category_id;		
		$county_id = $this -> session -> userdata('county_id');
		$county_name = counties::get_county_name($county_id);
		$category_data=$series_data = $graph_data= $series_data_=array();
		//check if the district is set
		$district_data = (isset($district_id) && ($district_id > 0)) ? districts::get_district_name($district_id) -> toArray() : null;
		$district_name_ = (isset($district_data)) ? " :" . $district_data[0]['district'] . " subcounty" : null;
		$option_new = isset($option) ? $option : "ksh";
		$facility_code_ = isset($facility_code) ? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name=$facility_code_[0]['facility_name'];
		$commodity_name=(isset($commodity_id))? Commodities::get_details($commodity_id)->toArray() : null;
		$category_name_=@$commodity_name[0]['commodity_name'];
		$commodity_name=isset($category_name_)? " for ".$category_name_ : null;
		$title=isset($facility_code) && isset($district_id)? "$district_name_ : $facility_name" :( 
	    $district_id>0 && !isset($facility_code) ?  "$district_name_": "$county_name[county] county") ;
		$time= "between ".date('j M y', $from)." and ".date('j M y', $to);
		$consumption_data = Facility_stocks::get_county_consumption_level_new($facility_code,$district_id, $county_id,$category_id, $commodity_id, $option,$from, $to,$report_type);
		foreach ($consumption_data as $data):
	    if($report_type=="table_data"):
		if($commodity_id>0):
		array_push($series_data , array($data['district'],$data["facility_name"],$data["facility_code"], $data['total']));
		else:
		array_push($series_data , array($data["name"], $data['total']));
		endif;						
		else:
		$series_data  = array_merge($series_data , array($data['total']));
		$series_data_=array_merge($series_data_ , array(array($data["name"],$data['total'])));
		$category_data=array_merge($category_data, array($data["name"]));
		endif;
		//
		endforeach;
		
		if($report_type=="csv_data"):
		$excel_data = array('doc_creator' =>$this -> session -> userdata('full_name'), 'doc_title' 
		=> "Consumption level $commodity_name $title $time", 'file_name' => "consumption_level_$commodity_name_$title_$time");
		$row_data = array(array("Consumption level in $commodity_name $title $time","Consumption level in $option_new"));
		$column_data = array("");
		$excel_data['column_data'] = $column_data;
		$row_data=array_merge($row_data,$series_data_);
		$excel_data['row_data'] = $row_data;;
		$this -> hcmp_functions -> create_excel($excel_data);
		elseif($report_type=="table_data"):
		if($commodity_id>0):
		$category_data=array(array("Sub-county","Facility Name","Mfl","TOTAL ".$option_new));
		else:
		array_push($category_data, array("Consumption level $commodity_name $title $time","stocks worth in $option_new"));
		endif;	
        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_'));
	    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
				
		$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
		$data['table_id'] ="dem_graph_";
		return $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
		else: 
        $graph_type='column';			
        $graph_data=array_merge($graph_data,array("graph_id"=>'dem_graph_'));
	    $graph_data=array_merge($graph_data,array("graph_title"=>"Consumption level $commodity_name $title $time"));
	    $graph_data=array_merge($graph_data,array("graph_type"=>$graph_type));
	    $graph_data=array_merge($graph_data,array("graph_yaxis_title"=>"Commodity Consumption level in $option_new"));
	    $graph_data=array_merge($graph_data,array("graph_categories"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("series_data"=>array('total'=>$series_data)));
		$data = array();
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
        endif;
	}
    public function notification_dashboard() 
    {
        $facility_code=(!$this -> session -> userdata('facility_id')) ? null: $this -> session -> userdata('facility_id');
		$district_id=(!$this -> session -> userdata('district_id')) ? null:$this -> session -> userdata('district_id');
		$county_id=(!$this -> session -> userdata('county_id')) ? null:$this -> session -> userdata('county_id');
	    //compute stocked out items
	    $items_stocked_out_in_facility = count(facility_stocks::get_items_that_have_stock_out_in_facility($facility_code,$district_id,$county_id));
		//get order information from the db
		$facility_order_count_ = facility_orders::get_facility_order_summary_count($facility_code,$district_id,$county_id);
		$facility_order_count = array();
	     foreach($facility_order_count_ as $facility_order_count_)
	     {
	     	$facility_order_count[$facility_order_count_['status']]=$facility_order_count_['total'];
	     }
	    //get potential expiries infor here
	    $potential_expiries = count(Facility_stocks::get_potential_expiry_summary($county_id,6,$district_id,$facility_code));
	    //get actual Expiries infor here
	    $actual_expiries = count(Facility_stocks::get_county_expiries($county_id,date('Y'),$district_id,$facility_code));
		//get items they have been donated for
		$facility_donations = count(redistribution_data::get_redistribution_data($facility_code,$district_id,$county_id,date('Y')));
	    
	    //get the roll out status here
	    $facility_roll_out_status = Facilities::get_tragetted_rolled_out_facilities($facility_code,$district_id,$county_id);
	    
		$data['county_dashboard_notifications'] = array(
		'items_stocked_out_in_facility'=>$items_stocked_out_in_facility,
		'facility_order_count'=>$facility_order_count,
		'potential_expiries'=>$potential_expiries,
		'actual_expiries'=>$actual_expiries,
		'facility_donations'=>$facility_donations,
	    'facility_roll_out_status'=>$facility_roll_out_status);	
	    
		return $this -> load -> view("subcounty/ajax/county_notification_v", $data);
	}
     public function monitoring(){
        $facility_code=(!$this -> session -> userdata('facility_id')) ? null: $this -> session -> userdata('facility_id');
        $district_id=(!$this -> session -> userdata('district_id')) ? null:$this -> session -> userdata('district_id');
        $county_id=(!$this -> session -> userdata('county_id')) ? null:$this -> session -> userdata('county_id');
        $category_data=$series_data = $graph_data= $series_data_=array();
        $facility_data=Facilities::get_facilities_monitoring_data( $facility_code,$district_id,$county_id);
        foreach($facility_data as $facility){

          array_push($series_data,array(
          date('j M, Y',strtotime($facility['last_seen'])),
          $facility['days_last_seen'],
          date('j M, Y',strtotime($facility['last_issued'])) ,
          $facility['days_last_issued'],
          $facility['district'],
          $facility['facility_name'],
          $facility['facility_code'])) ; 
        }
        $category_data=array(array("date last seen","# of days","date last issued","# of days","sub county","Facility Name","Mfl"));
        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_'));
        $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
        $graph_data=array_merge($graph_data,array("table_body"=>$series_data));                
        $data['table'] = $this->hcmp_functions->create_data_table($graph_data);
        $data['table_id'] ="dem_graph_";
        return $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
         
     }
    /*
	 |--------------------------------------------------------------------------
	 | COUNTY SUB-COUNTY reports
	 |--------------------------------------------------------------------------
	 */
		public function county_expiries() {
        $county_id=$this -> session -> userdata('county_id');
		$data['title'] = "Expired Products";
		$data['banner_text'] = "Expired Products";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['report_view'] = "subcounty/reports/county_expiries_v";
		$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
		$this -> load -> view("shared_files/template/template", $data);
	}
		public function county_consumption() {
        $county_id=$this -> session -> userdata('county_id');
	    $data['district_data'] = districts::getDistrict($county_id);
	    $data['c_data'] = Commodities::get_all_2();
		$data['categories']=commodity_sub_category::get_all_pharm();
		$data['title'] = "Consumption";
		$data['banner_text'] = "Consumption";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['report_view'] = "subcounty/reports/county_consumption_data_filter_v";
		$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
		$this -> load -> view("shared_files/template/template", $data);
	}
		public function county_donation() {
		$county_id=$this -> session -> userdata('county_id');
	    $data['district_data'] = districts::getDistrict($county_id);
		$data['title'] = $data['banner_text']="Donations";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['report_view'] = "subcounty/reports/county_donation_filter_v";
		$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
		$this -> load -> view("shared_files/template/template", $data);
	}
		public function stock_out(){
        $county_id=$this -> session -> userdata('county_id');
	    $data['district_data'] = districts::getDistrict($county_id);
		$data['title'] = $data['banner_text']="Stock Outd";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['report_view'] = "subcounty/reports/county_stock_out_facilities_filter_v";
		$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
		$this -> load -> view("shared_files/template/template", $data);
         	
         }
	     public function actual_expiries_reports($county_id,$year){
		 $expiries_array=Facility_stocks::get_county_expiries($county_id,$year);
		 $graph_data=$series_data=array();
		 $total_expiry=0;
		 foreach($expiries_array as $facility_expiry_data):
	     $district=$facility_expiry_data['district'];
		 $name=$facility_expiry_data['facility_name'];
		 $mfl=$facility_expiry_data['facility_code'];
		 $total=$facility_expiry_data['total'];	 
		 $total_expiry=$total_expiry+$total;
		 $year=date('Y');
		 $total=number_format($total, 2, '.', ',');
		 $district_id=$facility_expiry_data['district_id'];
		 $link=base_url("reports/expiries/$mfl");
	     array_push($series_data, array($district,$name,$mfl,$total,'<a href="'.$link.'">
         <button type="button" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-zoom-in"></span>View</button></a>'));

	    endforeach;
		$total_expiry=number_format($total_expiry, 2, '.', ',');
	    array_push($series_data, array("","","Total for $year",$total_expiry,''));
	   
		$category_data=array(array("Sub-county","Facility Name","Mfl","TOTAL (ksh)",''));

        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_'));
	    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
				
		$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
		$data['table_id'] ="dem_graph_";
		return $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
		}
	     public function potential_expiries_reports($county_id,$year){
		 $expiries_array=Facility_stocks::get_potential_expiry_summary($county_id,$year);
		 $graph_data=$series_data=array();
		 $total_expiry=0;
		 foreach($expiries_array as $facility_expiry_data):
	     $district=$facility_expiry_data['district'];
		 $name=$facility_expiry_data['facility_name'];
		 $mfl=$facility_expiry_data['facility_code'];
		 $total=$facility_expiry_data['total'];	 
		 $total_expiry=$total_expiry+$total;
		 $total=number_format($total, 2, '.', ',');
		 $district_id=$facility_expiry_data['district_id'];
		 $link=base_url("reports/potential_exp_process_subcounty/$mfl");
	    array_push($series_data, array($district,$name,$mfl,$total,'<a href="'.$link.'">
         <button type="button" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-zoom-in"></span>View</button></a>'));
	    endforeach;
		$total_expiry=number_format($total_expiry, 2, '.', ',');
	    array_push($series_data, array("","","Total for the next $year months",$total_expiry,''));
	   
		$category_data=array(array("Sub-county","Facility Name","Mfl","TOTAL (ksh)",''));

        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_1'));
	    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
				
		$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
		$data['table_id'] ="dem_graph_1";
		return $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
		}
        public function donation_reports($year=null,$district_id=null,$facility_code=null)
        {
        	//reset the values here
	     	$year=($year=="NULL") ? date('Y') :$year;
		 	$district_id=($district_id=="NULL") ? null :$district_id;
		 	$facility_code=($facility_code=="NULL") ? null :$facility_code;
	       	$county_id = $this -> session -> userdata('county_id');
			 $expiries_array=redistribution_data::get_redistribution_data($facility_code,$district_id,$county_id,$year);
			 $graph_data=$series_data=array();

		 foreach($expiries_array as $facility_expiry_data):
	     $total_units=$facility_expiry_data['total_commodity_units'];
		 $sent_units=$facility_expiry_data['quantity_sent'];
		 $received_units=$facility_expiry_data['quantity_received'];
		 $total_sent=round(($sent_units/$total_units),1);	 
		 $total_received=round(($received_units/$total_units),1);    ///date_sent
         $date_received=strtotime($facility_expiry_data['date_received']) ? date('d M, Y', strtotime($facility_expiry_data['date_received'])) : "N/A";
         $status=$facility_expiry_data['status']==0 ? "<span class='label label-danger'>Pending</span>" : ( $facility_expiry_data['status']==1? "<span class='label label-success'>Received</span>" : null );
	    array_push($series_data, array($facility_expiry_data['source_facility_name']." :".$facility_expiry_data['source_facility_code'],
	    $facility_expiry_data['receiver_facility_name']." :".$facility_expiry_data['receiver_facility_code'],
		$facility_expiry_data['commodity_name'],$facility_expiry_data['commodity_code'],$facility_expiry_data['unit_size'],$facility_expiry_data['batch_no'],
		date('d M, Y', strtotime($facility_expiry_data['expiry_date'])),$facility_expiry_data['manufacturer'],$total_sent,$sent_units,$total_received,$received_units,
		date('d M, Y', strtotime($facility_expiry_data['date_sent'])),$date_received,$status));
	    endforeach;
		$total_expiry=number_format($total_expiry, 2, '.', ',');
	   // array_push($series_data, array("","","Total for the next $year months",$total_expiry,''));
	   
		$category_data=array(array("From",'To',"Commodity Name","Commodity Code",
		"Unit Size",'Batch No','Expiry Date','Manufacturer','Quantity Sent(units)','Quantity Sent(packs)',
		'Quantity Received (units)','Quantity Received (packs)','Date sent','Date Received','status'));
	
        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_1'));
	    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
				
		$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
		$data['table_id'] ="dem_graph_1";
		return  $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
		}

         public function stock_out_reports($district_id=null,$facility_code=null){
         $district_id=($district_id=="NULL") ? null :$district_id;
	 	 $facility_code=($facility_code=="NULL") ? null :$facility_code;
         $county_id = $this -> session -> userdata('county_id');
		 $stock_out_array=Facility_stocks::get_items_that_have_stock_out_in_facility( $facility_code,$district_id,$county_id);
		 
		 $graph_data=$series_data=array();

		 foreach($stock_out_array as $facility_stock_data):
	     $day=date('j M, Y ',strtotime($facility_stock_data['last_day']));
         $ts1 = strtotime($facility_stock_data['last_day']); $ts2 = strtotime(date("Y/m/d")); $seconds_diff = $ts2 - $ts1;
         $days= floor($seconds_diff/3600/24);
	    array_push($series_data, array($facility_stock_data['district'],
	    $facility_stock_data['facility_name'],
		$facility_stock_data['facility_code'],$facility_stock_data['commodity_name'],$facility_stock_data['commodity_code'],$day,$days));
	    endforeach;

         $category_data=array(array("Sub County",'Facility Name',"MLF No.","Commodity Name","Commodity Code","Last day of usage",
"No. days out of stock"));

        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_1'));
	    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
				
		$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
		$data['table_id'] ="dem_graph_1";
		return  $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
   	
   }
     /*
	 |--------------------------------------------------------------------------
	 | COUNTY SUB-COUNTY Facility Mapping
	 |--------------------------------------------------------------------------
	 */
     public function mapping(){
     	$identifier = $this -> session -> userdata('user_indicator');
		$county_id=$district_id=null;
		if($identifier=='district'){
		$district_id=$this -> session -> userdata('district_id');	
		}else{
		$county_id=$this -> session -> userdata('county_id');	
		}     	
	    $data['district_data'] = districts::getDistrict($county_id,$district_id);
		$data['title'] = $data['banner_text']="Facility Mapping";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['report_view'] = "subcounty/reports/facility_mapping_v";
		$data['sidebar'] = "shared_files/report_templates/side_bar_facility_mapping_v";
		$this -> load -> view("shared_files/template/template", $data);
     	
     }
	 
	 	public function get_district_facility_mapping_($district_id) {
		$facility_data = facilities::getFacilities($district_id);
		$dpp_details = Users::get_dpp_details($district_id) -> toArray();
		$district_name = districts::getDistrict(null,$district_id)-> toArray();;
		
        $table_body = "";
		$dpp_fname = '';
		$dpp_lname = '';
		$dpp_phone = '';
		$dpp_email = '';
		$indicator = "SubCounty";
		$no_of_facility_users = 0;
		$no_of_facility_users_online = 0;
		$no_of_facilities = 0;
		$no_of_facilities_using = 0;
		$no_of_facilities_using_targetted = 0;
        $series_data= $graph_data=array();
		if (count($dpp_details) > 0) {

			$dpp_fname = $dpp_details[0]['fname'];
			$dpp_lname = $dpp_details[0]['lname'];
			$dpp_phone = $dpp_details[0]['telephone'];
			$dpp_email = $dpp_details[0]['email'];

		}
		
		foreach ($facility_data as $facility_detail) {
			$facility_code = $facility_detail -> facility_code;
			$facility_extra_data = facilities::get_facility_status_no_users_status($facility_code);
			$no_of_facility_users = $no_of_facility_users + $facility_extra_data[0]['number_of_users'];
			$no_of_facility_users_online = $no_of_facility_users_online + $facility_extra_data[0]['number_of_users_online'];
			$no_of_facilities = $no_of_facilities + 1;
	
			$using=$facility_detail->using_hcmp;
            $status_radio=$facility_detail->targetted==1 ? 'checked="true"'  : null; 
			($using== 1) ? $no_of_facilities_using = $no_of_facilities_using + 1 : 
			$status = null;
			$temp = $facility_extra_data[0]['status'];
			$status_using=$using== 1? 'checked="true"'  : null;  
			$a_date = strtotime($facility_detail->date_of_activation) ? date('d M, Y', strtotime($facility_detail->date_of_activation)) : "N/A";
			($using== 1) ? $status = "<span class='label label-success'>Active</span>" : $status = "<span class='label label-warning'>Inactive</span>";
            ($facility_detail->targetted==1) ? $no_of_facilities_using_targetted=$no_of_facilities_using_targetted+1 : null;
	   	array_push($series_data, array($district_name[0]['district'],
	    $facility_detail->facility_name,
		$facility_code,$facility_detail->owner,$status,
		"<input id='$facility_detail->id' type='checkbox' name='targetted' class='checkbox'  $status_radio/>",
		"<input id='$facility_detail->id' name='using_hcmp' type='checkbox' class='checkbox'  $status_using/>",
		$a_date,
		$facility_extra_data[0]['number_of_users']
		));
		}

		$stats_data = '
		<table style="float:left">
		<tr>
		<td><label style=" font-weight: ">' . $district_name[0]['district'] . ' ' . $indicator . ' Pharmacist :</label></td>
		<td><a class="badge">' . $dpp_fname . ' ' . $dpp_lname . '</a></td>
		</tr>
		<tr>
		<td><label style="font-weight: ">Phone No.</label></td>
		<td><a class="badge">' . $dpp_phone . '</a></td>
		</tr>
		<tr>
		<td><label style="font-weight: ">Email Address</label></td>
		<td><a class="badge">' . $dpp_email . '</a></td>
		</tr>
		</table>
		<table style="float:left">
		<tr>
		<td><label style=" font-weight: ">Total No of Facilities</label></td>
		<td><a class="badge" >' . $no_of_facilities . '</a></td>
		</tr>
		<tr>
		<td><label style="font-weight: ">Total No of Facilities  Targeted</label></td>
		<td>	<a class="badge">' . $no_of_facilities_using_targetted. '</a></td>
		</tr>
		<tr>
		<td><label style="font-weight: ">Total No of Facilities Using HCMP </label></td>
		<td>	<a class="badge">' . $no_of_facilities_using . '</a></td>
		</tr>
		<tr>
		<td><label style="font-weight: ">Total No of Users</label></td>
		<td><a class="badge" >' . $no_of_facility_users . '</a></td>
		</tr>
		</table>
        </br><p>';

        $category_data=array(array("Sub County",'Facility Name',"MLF No","Owner", "Facility Status","Targeted For Roll Out","Using HCMP","Date Activated", "No. Facility Users"));
        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_1'));
	    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));				
		$data['table'] = $this->hcmp_functions->create_data_table($graph_data);		
		$data['table_id'] ="dem_graph_1";
		$data['stats_data']=$stats_data;
		return  $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);

	}
       public function set_tragget_facility($facility_id,$status,$type){
       	//security check	  
       if($this->input->is_ajax_request()):
	   $set=($type=='targetted') ? "`targetted` =$status" : "`using_hcmp` =$status";
       $inserttransaction = Doctrine_Manager::getInstance()->getCurrentConnection();
	   $inserttransaction->execute("UPDATE `Facilities` SET $set
                                          WHERE `id`=$facility_id"); 
	   echo "success";
       endif;
       }
	 
  
}
?>