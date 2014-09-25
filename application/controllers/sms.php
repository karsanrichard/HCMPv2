<?php
/**
 * @author Kariuki
 * Edited by Collins
 */
ob_start();
class sms extends MY_Controller {
	var $test_mode = true;
	function __construct() {
		parent::__construct();
		$this -> load -> helper(array('form', 'url', 'file', 'download'));
		$this -> load -> library(array('hcmp_functions', 'form_validation', 'email', 'mpdf/mpdf'));

	}

	/*
	 |--------------------------------------------------------------------------|
	 | SMS	SECTION																	|
	 |--------------------------------------------------------------------------|
	 */

	//Test for sending emails
	public function send_email_test() {
		$this -> hcmp_functions -> send_email();
	}

	//Checks if there are potential expiries in the system
	public function check_potential_expiries() {
		$year = date("Y");
		$potential_expiries = Facility_stocks::get_potential_expiries_sms();
		$total_potential = count($potential_expiries);

		if ($total_potential > 0) {
			$total_facilitites = count($potential_expiries);

			$total_commodities = "";

			foreach ($potential_expiries as $potential) {
				$facility_code = $potential['facility_code'];
				$data = Users::getUsers($facility_code) -> toArray();
				//Filter the array to remove empty sections of the array
				$data = array_filter($data);
				if (!empty($data)) {
					foreach ($data as $users) {
						$user_type = $users['usertype_id'];

						switch ($user_type) {
							//Facility Admin
							case 2 :
								$message = $potential['total'] . " commodities will expire within 3 months. HCMP";
								break;
							//Sub County Head
							case 3 :
								$message = $total_facilitites . " facilities have commodities that will expire within 3 months. HCMP";
								break;
							//County Head
							case 10 :
								$message = "There are " . $total_potential . " potential expiries in the county. HCMP";
								break;
							//For test purposes only
							case 5 :
								$message = "Test SMS. HCMP";
								break;
						}

						$phone = $this -> get_facility_phone_numbers($facility_code);
						$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);
						$this -> hcmp_functions -> send_sms(substr($phone, 0, -1), $message);
					}
				} else {
					//DO Nothing
					//Array is empty
				}

			}

		} else {
			//No Potential Expiries

		}

	}

	//Stock out SMSes
	//will be called once a week by the system automatically
	public function stock_out_sms() {
		$stock_outs = Facility_stocks::get_stock_outs_sms();
		$total_stock_outs = count($stock_outs);
		if ($total_stock_outs > 0) {
			$total_facilitites = count($stock_outs);
			$total_commodities = "";

			foreach ($stock_outs as $stock_outs_) {
				$facility_code = $stock_outs_['facility_code'];
				$data = Users::getUsers($facility_code) -> toArray();
				//Filter the array to remove empty sections of the array
				$data = array_filter($data);
				if (!empty($data)) {
					$total_subcounties = count($data['']);
					foreach ($data as $users) {
						$user_type = $users['usertype_id'];

						switch ($user_type) {
							//Facility Admin
							case 2 :
								$message = $stock_outs_['total'] . " commodities are out of stock.";
								break;
							//Sub County Head
							case 3 :
								$message = $total_facilitites . " facilities have a total number of " . $total_stock_outs . " stock outs.";
								break;
							//County Head
							case 10 :
								$message = "There are " . $total_stock_outs . " stock outs in the county";
								break;
							//For test purposes only
							case 5 :
								$message = "To Me";
								break;
						}

						$phone = $this -> get_facility_phone_numbers($facility_code);
						$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);
						$this -> hcmp_functions -> send_sms(substr($phone, 0, -1), $message);
					}
				} else {
					//DO Nothing
					//Array is empty
				}

			}

		} else {
			//No Stock Outs

		}
	}

	//For Orders
	//Called when an order is placed by a particular facility
	public function send_order_sms() {
		$facility_code = $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name2($facility_code);
		$facility_name = $facility_name['facility_name'];
		$data = Users::getUsers($facility_code) -> toArray();
		foreach ($data as $users) {
			$user_type = $users['usertype_id'];

			switch ($user_type) {
				//Facility Admin
				case 2 :
					$message = "An  order has been placed by " . $facility_name;
					break;
				//Sub County Head
				case 3 :
					$message = $facility_name . " has placed an order";
					break;
				//County Head
				case 10 :
					$message = "An  order has been placed by " . $facility_name;
					break;
				//For test purposes only
				case 5 :
					$message = "Test order sms. Placed by " . $facility_name;
					break;
			}

			$phone = $this -> get_facility_phone_numbers($facility_code);
			$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);
			$this -> hcmp_functions -> send_sms(substr($phone, 0, -1), $message);
		}

	}

	//When stock is updated by a particular facility
	public function send_stock_update_sms() {
		$facility_code = $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name2($facility_code);
		$facility_name = $facility_name['facility_name'];
		$data = Users::getUsers($facility_code) -> toArray();

		foreach ($data as $users) {
			$user_type = $users['usertype_id'];

			switch ($user_type) {
				//Facility Admin
				case 2 :
					$message = "Stock has been updated for " . $facility_name;
					break;
				//Sub County Head
				case 3 :
					$message = $facility_name . " has updated its stock";
					break;
				//County Head
				case 10 :
					$message = "Stock has been updated for " . $facility_name;
					break;
				//For test purposes only
				case 5 :
					$message = "Stock has been updated for " . $facility_name;
					break;
			}

			$phone = $this -> get_facility_phone_numbers($facility_code);
			$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);
			$this -> hcmp_functions -> send_sms(substr($phone, 0, -1), $message);
		}

	}

	//Called when an order is rejected or approved bt the DPP
	//Accepts two parameters $facility code and status of the order
	public function send_order_approval_sms($facility_code, $status) {
		//$facility_code = $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name2($facility_code);
		$facility_name = $facility_name['facility_name'];
		$data = Users::getUsers($facility_code) -> toArray();

		$message = ($status == 1) ? $facility_name . "'s order has been rejected. HCMP" : $facility_name . "'s order has been approved. HCMP";

		$data = Users::getUsers($facility_code) -> toArray();
		$phone = $this -> get_facility_phone_numbers($facility_code);
		$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);

		$this -> send_sms(substr($phone, 0, -1), $message);

	}

	public function order_update_sms($facility_code, $status) {
		//$facility_code = $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name2($facility_code);
		$facility_name = $facility_name['facility_name'];
		$data = Users::getUsers($facility_code) -> toArray();
		$message = $facility_name . " has updated its order";

		$phone = $this -> get_facility_phone_numbers($facility_code);
		$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);
		$this -> hcmp_functions -> send_sms(substr($phone, 0, -1), $message);

	}

	public function send_stock_donate_sms($facility_code, $facility_code2) {
		//Facility code the mfl code for the facility donating drugs
		//Facility code 2 is for the facility recieving the commodities
		$facility_name = Facilities::get_facility_name2($facility_code);
		$facility_name = $facility_name['facility_name'];
		$facility_name2 = Facilities::get_facility_name2($facility_code2);
		$facility_name2 = $facility_name2['facility_name'];
		$data = Users::getUsers($facility_code) -> toArray();
		$data2 = Users::getUsers($facility_code) -> toArray();

		$message = $facility_name . " has donated commodirties to " . $facility_name2;

		$phone = $this -> get_facility_phone_numbers($facility_code);
		$phone .= $this -> get_facility_phone_numbers($facility_code2);
		$phone .= $this -> get_ddp_phone_numbers($data[0]['district']);
		$phone .= $this -> get_ddp_phone_numbers($data2[0]['district']);

		$this -> hcmp_functions -> send_sms(substr($phone, 0, -1), $message);

	}

	/*public function update_stock_out_table()
	 {

	 $in = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("
	 select stock_date,facility_code,
	 kemsa_code from facility_stock
	 where `balance`=0 and `status`=1 and quantity>0
	 group by kemsa_code,facility_code");

	 $jay = count($in);

	 for($i=0;$i<$jay;$i++)
	 {
	 Doctrine_Manager::getInstance()->getCurrentConnection()->execute("
	 insert into facility_stock_out_tracker
	 set facility_id='".$in[$i]['facility_code']."', drug_id='".$in[$i]['kemsa_code']."',
	 `start_date`='".$in[$i]['stock_date']."', status='stocked out' ");

	 }
	 }
	 */

	/*
	 |--------------------------------------------------------------------------|
	 | EMAIL SECTION															|
	 |--------------------------------------------------------------------------|
	 */

	//gets the emails of the respective DPPs in charge
	public function get_ddp_email($district_id) {
		$data = Users::get_dpp_emails($district_id);
		$user_email = "";
		foreach ($data as $info) {
			
			$user_email .= $info -> email . ',';

		}
		return $user_email;
	}

	public function get_county_email($county_id) {
		$data = Users::get_county_emails($county_id);
		$user_email = "";
		foreach ($data as $info) {
			$user_email .= $info -> email . ',';

		}
		return $user_email;
	}
//Getting the bcc emails 
	public function get_bcc_notifications() {
		$bcc_emails = "collinsojenge@gmail.com,kelvinmwas@gmail.com,smutheu@clintonhealthaccess.org,
						jhungu@clintonhealthacces.org,gmacharia@clintonhealthacces.org,tngugi@clintonhealthaccess.org,
						bwariari@clintonhealthaccess.org,amwaura@clintonhealthaccess.org,eongute@clintonhealthaccess.org,
						rkihoto@clintonhealthaccess.org,teddyodera@gmail.com,ericmurugami@yahoo.co.uk,
						raykiprono@gmail.com,margie.dora@gmail.com,amostum5@gmail.com,muirurisk@gmail.com,
						valwacu@gmail.com,odiwuorybrian@gmail.com,mwakiojoy@gmail.com,emgitar@yahoo.com";
		
		return $bcc_emails;
	}
	//Getting the CC email addresses
	public function get_cc_emails() {
			
		$cc = "mukumbig@yahoo.com,anganga.pmo@gmail.com,sochola06@yahoo.com";
		
		return $cc;
	}
	

	//
	//gets the emails of users in the facility
	public function get_facility_email($facility_code) {
		$data = Users::get_user_emails($facility_code);
		$user_email = "";
		foreach ($data as $info) {
			
			$user_email .= $info -> email . ',';

		}
		return $user_email;
	}

	public function send_stock_decommission_email($message, $subject, $attach_file) {
		$facility_code = $this -> session -> userdata('facility_id');
		$data = Users::getUsers($facility_code) -> toArray();
		$email_address = $this -> get_facility_email($facility_code);
		$email_address .= $this -> get_ddp_email($data[0]['district']);

		$this -> send_email(substr($email_address, 0, -1), $message, $subject, $attach_file);

	}

	public function weekly_potential_expiries_report() {
		//Set the current year
		$year = date("Y");
		$picurl = base_url() . 'assets/img/coat_of_arms-resized1.png';
		//get the facilities in the district
		$counties = Facilities::get_counties_all_using_HCMP();

		foreach ($counties as $counties) {
			//holds the dat for the entire county
			//once it is done executing for one county it is reset to zero
			$county_total = array();
			//pick the county nae and county ID accordingly
			$county_id = $counties['county'];
			$county_name = $counties['county_name'];

			//Get all the ddistricts in that  particular county
			$districts = Facilities::get_all_using_HCMP($county_id);
			//holds the data for all the districts in a particular county
			$district_total = array();

			foreach ($districts as $districts) {

				$district_id = $districts['district'];
				$district_name = $districts['name'];
				//get all facilities in that district
				$facilities = Facilities::getFacilities_for_email($district_id);
				//holds all the data for all facilities in a particular district
				$facility_total = array();

				foreach ($facilities as $facilities_) :
					//holds the total value of expiries for that particular facility in that district
					$facility_potential_expiries_total = 0;
					//$facility_potential_expiries = array();
					$facility_code = $facilities_ -> facility_code;
					$facility_name = Facilities::get_facility_name2($facility_code);
					$facility_name = $facility_name['facility_name'];

					//get potential expiries in that particular facility
					$facility_potential_expiries = Facility_stocks::potential_expiries_email($district_id, $facility_code);

					//push the result into another array that will be used by the distrct
					(array_key_exists($facility_name, $facility_total)) ? $facility_total[$facility_name] = array_merge($facility_total[$facility_name], array($facility_potential_expiries)) : $facility_total = array_merge($facility_total, array($facility_name => array($facility_potential_expiries)));
					//Start buliding the excel file
					$excel_data = array();
					$excel_data = array('doc_creator' => $facility_name, 'doc_title' => 'facility potential expiries weekly report ', 'file_name' => 'facility weekly report');
					$row_data = array();
					$column_data = array("County", "Subcounty", "Facility Code", "Facility Name", "Commodity Name", "Manufacturer", "Expiry Date", "Unit Cost(KSH)", "Unit Size", "Units", "Packs", "Total Cost (KSH)");
					$excel_data['column_data'] = $column_data;

					foreach ($facility_potential_expiries as $facility_potential_expiries) :
						array_push($row_data, array($facility_potential_expiries["county"], $facility_potential_expiries["subcounty"], $facility_potential_expiries["facility_code"], $facility_potential_expiries["facility_name"], $facility_potential_expiries["commodity_name"], $facility_potential_expiries["manufacture"], $facility_potential_expiries["expiry_date"], $facility_potential_expiries["unit_cost"], $facility_potential_expiries["unit_size"], $facility_potential_expiries["units"], $facility_potential_expiries["packs"], $facility_potential_expiries["total_ksh"]));
						$facility_potential_expiries_total += $facility_potential_expiries["total_ksh"];
					endforeach;

					if (empty($row_data)) {
						//do nothing
					} else {
						$excel_data['row_data'] = $row_data;
						$excel_data['report_type'] = "download_file";
						$excel_data['file_name'] = $facility_name . "_Potential_Expiries_Report";
						$excel_data['excel_title'] = "Potential Expiries Report for ".$facility_name." as at ".date("jS F Y");

						$subject = "Potential Expiries: " . $facility_name . " (Next 3 Months)";
						
						$message = "Dear ".$facility_name." facility team,
								Find attached an excel sheet with the ".$facility_name." Facility breakdown for the Potential Expiries.
								Kindly sensitize the need to re-distribute these short expiry commodities.
								You may log onto health-cmp.or.ke for follow up.
								
								----
								
								HCMP
								
								This email was automatically generated. Please do not respond to this email address or it will be ignored.";
				
						
						$this -> create_excel_potential_expiries($excel_data);
						$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
						
						$email_address = $this->get_facility_email($facility_code);
						//$email_address = "collinsojenge2014@gmail.com";
						$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);

					}

					//End foreach for facility
				endforeach;

				(array_key_exists($district_name, $district_total)) ? $district_total[$district_name] = array_merge($district_total[$district_name], array($facility_total)) : $district_total = array_merge($district_total, array($district_name => array($facility_total)));
				//Building the excel sheet to be sent to the district admin
				$excel_data = array();
				$excel_data = array('doc_creator' => $district_name, 'doc_title' => 'district potential expiries weekly report ', 'file_name' => 'district weekly report');
				$row_data = array();
				$column_data = array("County", "Subcounty", "Facility Code", "Facility Name", "Commodity Name", "Manufacturer", "Expiry Date", "Unit Cost(KSH)", "Unit Size", "Units", "Packs", "Total Cost (KSH)");
				$excel_data['column_data'] = $column_data;

				foreach ($facility_total as $facility_total_1) :
					foreach ($facility_total_1 as $facility_total_2) :
						foreach ($facility_total_2 as $facility_total1) :
							array_push($row_data, array($facility_total1["county"], $facility_total1["subcounty"], $facility_total1["facility_code"], $facility_total1["facility_name"], $facility_total1["commodity_name"], $facility_total1["manufacture"], $facility_total1["expiry_date"], $facility_total1["unit_cost"], $facility_total1["unit_size"], $facility_total1["units"], $facility_total1["packs"], $facility_total1["total_ksh"]));
							
						endforeach;
					endforeach;
				endforeach;

				if (empty($row_data)) {
					//do nothing
				} else {

					$excel_data['row_data'] = $row_data;
					$excel_data['report_type'] = "download_file";
					$excel_data['file_name'] = $district_name . "_Weekly_District_Potential_Expiries_Report";
					$excel_data['excel_title'] = "Potential Expiries Report for ".$district_name." Sub County as at ".date("jS F Y");
					
					$this -> create_excel_potential_expiries($excel_data);
					$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
					$subject = "Potential Expiries: " . $district_name . " Sub County (Next 3 Months)";
					
					$message = "Dear ".$district_name." Sub County team,
								Find attached an excel sheet with the ".$district_name." Sub County breakdown for the Potential Expiries.
								Kindly sensitize the need to re-distribute these short expiry commodities.
								You may log onto health-cmp.or.ke for follow up.
								
								----
								
								HCMP
								
								This email was automatically generated. Please do not respond to this email address or it will be ignored.";
			
					$email_address = $this -> get_ddp_email($district_id);
					//$email_address = "collinsojenge@2014@gmail.com";
					$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);
				}

			}

			//Building the excel sheet to be sent to the district admin
			$excel_data = array();
			$excel_data = array('doc_creator' => $district_name, 'doc_title' => 'district potential expiries weekly report ', 'file_name' => 'district weekly report');
			$row_data = array();
			$column_data = array("County", "Subcounty", "Facility Code", "Facility Name", "Commodity Name", "Manufacturer", "Expiry Date", "Unit Cost", "Unit Size", "Units", "Packs", "Total Cost (KSH)");
			$excel_data['column_data'] = $column_data;

			foreach ($district_total as $facility_total_1) :
				foreach ($facility_total_1 as $facility_total_2) :
					foreach ($facility_total_2 as $facility_total_3) :
						foreach ($facility_total_3 as $facility_total_4) :
							foreach ($facility_total_4 as $facility_total1) :
								array_push($row_data, array($facility_total1["county"], $facility_total1["subcounty"], $facility_total1["facility_code"], $facility_total1["facility_name"], $facility_total1["commodity_name"], $facility_total1["manufacture"], $facility_total1["expiry_date"], $facility_total1["unit_cost"], $facility_total1["unit_size"], $facility_total1["units"], $facility_total1["packs"], $facility_total1["total_ksh"]));

							endforeach;
						endforeach;
					endforeach;
				endforeach;
			endforeach;

			if (empty($row_data)) {
				//do nothing
			} else {
				$excel_data['row_data'] = $row_data;
				$excel_data['report_type'] = "download_file";
				$excel_data['file_name'] = $county_name . "_Weekly_County_Potential_Expiries_Report";
				$excel_data['excel_title'] = "Potential Expiries Report for ".$county_name." County as at ".date("jS F Y");
					
				$this -> create_excel_potential_expiries($excel_data);
				$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
				$subject = "Potential Expiries: " . $county_name . " County (Next 3 Months)";
				
				$message = "Dear ".$county_name." County team,
								Find attached an excel sheet with the ".$county_name." County breakdown for the Potential Expiries.
								Kindly sensitize the need to re-distribute these short expiry commodities.
								You may log onto health-cmp.or.ke for follow up.
								
								----
								
								HCMP
								
								This email was automatically generated. Please do not respond to this email address or it will be ignored.";
				
				$email_address = $this -> get_county_email($county_id);
				//$email_address = "collinsojenge@2014@gmail.com";
				//$bcc = "collinsojenge@gmail.com";
				//$cc_email = "collinsojenge@gmail.com";
				$bcc = $this -> get_bcc_notifications();
				$cc_email = $this -> get_cc_emails();

				$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler, $bcc, $cc_email);
			}

		}

	}

	public function weekly_stockouts_report() {
		//Set the current year
		$year = date("Y");
		//get the facilities in the district
		$counties = Facilities::get_counties_all_using_HCMP();

		foreach ($counties as $counties) {
			//holds the dat for the entire county
			//once it is done executing for one county it is reset to zero
			$county_total = array();
			//pick the county nae and county ID accordingly
			$county_id = $counties['county'];
			$county_name = $counties['county_name'];

			//Get all the ddistricts in that  particular county
			$districts = Facilities::get_all_using_HCMP($county_id);
			//holds the data for all the districts in a particular county
			$district_total = array();

			foreach ($districts as $districts) {

				$district_id = $districts['district'];
				$district_name = $districts['name'];
				//get all facilities in that district
				$facilities = Facilities::getFacilities_for_email($district_id);
				//holds all the data for all facilities in a particular district
				$facility_total = array();

				foreach ($facilities as $facilities_) :
					//holds the total value of expiries for that particular facility in that district
					$facility_potential_expiries_total = 0;
					//$facility_potential_expiries = array();
					$facility_code = $facilities_ -> facility_code;
					$facility_name = Facilities::get_facility_name2($facility_code);
					$facility_name = $facility_name['facility_name'];
					
					$facility_potential_expiries = Facility_stocks::get_stock_outs_for_email($facility_code);
					
					//get potential expiries in that particular facility
					//$facility_potential_expiries = Facility_stocks::potential_expiries_email($district_id, $facility_code);

					//push the result into another array that will be used by the distrct
					(array_key_exists($facility_name, $facility_total)) ? $facility_total[$facility_name] = array_merge($facility_total[$facility_name], array($facility_potential_expiries)) : $facility_total = array_merge($facility_total, array($facility_name => array($facility_potential_expiries)));
					//Start building the excel file
					$excel_data = array();
					$excel_data = array('doc_creator' => $facility_name, 'doc_title' => 'facility stokouts weekly report ', 'file_name' => 'facility weekly report');
					$row_data = array();
					$column_data = array("Commodity Code", "Commodity Name","Unit Size", "Quantity Available (Units)","Supplier","Subcounty");
					$excel_data['column_data'] = $column_data;

					foreach ($facility_potential_expiries as $facility_potential_expiries) :
						array_push($row_data, array($facility_potential_expiries["commodity_code"], $facility_potential_expiries["commodity_name"],$facility_potential_expiries["unit_size"],$facility_potential_expiries["current_balance"],$facility_potential_expiries["source_name"],$facility_potential_expiries["district"]));

					endforeach;
					if (empty($row_data)) {
						//do nothing
					} else {
						$excel_data['row_data'] = $row_data;
						$excel_data['report_type'] = "download_file";
						$excel_data['file_name'] = $facility_name . "_Potential_Stock_Outs_Report";
						$excel_data['excel_title'] = "Potential Stock Outs Report for ".$facility_name." as at ".date("jS F Y");
						//$message = "Find attached an excel sheet with the breakdown of Potential Stock Outs in the facility.";
						$message = "Dear ".$facility_name." facility team,
								Find attached an excel sheet with the ".$facility_name." Facility breakdown for the Potential Stock Outs.
								You may log onto health-cmp.or.ke for follow up.
								
								----
								
								HCMP
								
								This email was automatically generated. Please do not respond to this email address or it will be ignored.";
				
						
						
						$this ->create_excel_stockouts($excel_data);
						
						$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
						$subject = "Potential Stock Outs: " . $facility_name;

						$email_address = $this -> get_facility_email($facility_code);
						//$email_address ="collinsojenge2014@gmail.com";
				

						$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);
						
					}

					//End foreach for facility
				endforeach;

				(array_key_exists($district_name, $district_total)) ? $district_total[$district_name] = array_merge($district_total[$district_name], array($facility_total)) : $district_total = array_merge($district_total, array($district_name => array($facility_total)));
				//Building the excel sheet to be sent to the district admin
				$excel_data = array();
				$excel_data = array('doc_creator' => $district_name, 'doc_title' => 'district potential stock outs weekly report ', 'file_name' => 'district weekly report');
				$row_data = array();
				$column_data = array("Commodity Code", "Commodity Name","Unit Size", "Quantity Available (Units)","Supplier","Subcounty");
				$excel_data['column_data'] = $column_data;

				foreach ($facility_total as $facility_total_1) :
					foreach ($facility_total_1 as $facility_total_2) :
						foreach ($facility_total_2 as $facility_total1) :
							array_push($row_data, array($facility_total1["commodity_code"], $facility_total1["commodity_name"],$facility_total1["unit_size"],$facility_total1["current_balance"],$facility_total1["source_name"],$facility_total1["district"]));
							
							//array_push($row_data, array($facility_total1["facility_code"], $facility_total1["facility_name"], $facility_total1["commodity_code"], $facility_total1["commodity_name"],$facility_total1["unit_size"],$facility_total1["current_balance_packs"],$facility_total1["amc"],$facility_total1["source_name"],$facility_total1["manufacture"],$facility_total1["district"], $facility_total1["county"]));
							
						endforeach;
					endforeach;
				endforeach;
				if (empty($row_data)) {
					//do nothing
				} else {
					$excel_data['row_data'] = $row_data;
					$excel_data['report_type'] = "download_file";
					$excel_data['file_name'] = $district_name . "_Weekly_Sub_County_Potential_Stock_Outs_Report";
					$excel_data['excel_title'] = "Potential Stock Outs Report for ".$district_name." Sub County as at ".date("jS F Y");
						
					$this -> create_excel_stockouts($excel_data);
					$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
					$subject = "Potential Stock Outs: " . $district_name . " Sub County";
					
					$message = "Dear ".$district_name." Sub County team,
								Find attached an excel sheet with the ".$district_name." Sub County breakdown for the Potential Stock Outs.
								You may log onto health-cmp.or.ke for follow up.
								
								----
								
								HCMP
								
								This email was automatically generated. Please do not respond to this email address or it will be ignored.";
				
					$email_address = $this -> get_ddp_email($district_id);
					//$email_address ="collinsojenge2014@gmail.com";
				
					$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);

				}

			}

			//Building the excel sheet to be sent to the district admin
			$excel_data = array();
			$excel_data = array('doc_creator' => $district_name, 'doc_title' => 'district potential stock outs weekly report ', 'file_name' => 'district weekly report');
			$row_data = array();
			$column_data = array("Commodity Code", "Commodity Name","Unit Size", "Quantity Available (Units)","Supplier","Subcounty");
			$excel_data['column_data'] = $column_data;

			foreach ($district_total as $facility_total_1) :
				foreach ($facility_total_1 as $facility_total_2) :
					foreach ($facility_total_2 as $facility_total_3) :
						foreach ($facility_total_3 as $facility_total_4) :
							foreach ($facility_total_4 as $facility_total1) :
								array_push($row_data, array($facility_total1["commodity_code"], $facility_total1["commodity_name"],$facility_total1["unit_size"],$facility_total1["current_balance"],$facility_total1["source_name"],$facility_total1["district"]));
							
							endforeach;
						endforeach;
					endforeach;
				endforeach;
			endforeach;
			if (empty($row_data)) {
				//do nothing
			} else {
				$excel_data['row_data'] = $row_data;
				$excel_data['report_type'] = "download_file";
				$excel_data['file_name'] = $county_name . "_Weekly_County_Potential_Stock_Outs_Report";
				$excel_data['excel_title'] = "Potential Stock Outs Report for ".$county_name." County as at ".date("jS F Y");
					
				$this -> create_excel_stockouts($excel_data);
				$handler = "./print_docs/excel/excel_files/" . $excel_data['file_name'] . ".xls";
				$subject = "Potential Stock Outs: " . $county_name . " County";
				
				$message = "Dear ".$county_name." County team,
								Find attached an excel sheet with the ".$county_name." County breakdown for the Potential Stock Outs.
								You may log onto health-cmp.or.ke for follow up.
								
								----
								
								HCMP
								
								This email was automatically generated. Please do not respond to this email address or it will be ignored.";
				
						
				$email_address = $this -> get_county_email($county_id);
				$bcc = $this -> get_bcc_notifications();
				$cc_email = $this->get_cc_emails();
				$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler, $bcc, $cc_email);
			}

		}

	}


	public function create_excel_stockouts($excel_data = NUll) {

		//check if the excel data has been set if not exit the excel generation
	$styleArray = array('font' => array('bold' => true),'alignment'=>array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
		
		if (count($excel_data) > 0) :

			$objPHPExcel = new PHPExcel();
			$objPHPExcel -> getProperties() -> setCreator("HCMP");
			$objPHPExcel -> getProperties() -> setLastModifiedBy($excel_data['doc_creator']);
			$objPHPExcel -> getProperties() -> setTitle($excel_data['doc_title']);
			$objPHPExcel -> getProperties() -> setSubject($excel_data['doc_title']);
			$objPHPExcel -> getProperties() -> setDescription("");

			$objPHPExcel -> setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
			$objPHPExcel->getActiveSheet()->setCellValue('A1', $excel_data['excel_title']);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);

			$rowExec = 2;

			//Looping through the cells
			$column = 0;

			foreach ($excel_data['column_data'] as $column_data) {
				$objPHPExcel -> getActiveSheet() -> setCellValueByColumnAndRow($column, $rowExec, $column_data);
				$objPHPExcel -> getActiveSheet() -> getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column)) -> setAutoSize(true);
				//$objPHPExcel->getActiveSheet()->getStyle($column, $rowExec)->getFont()->setBold(true);
				$objPHPExcel -> getActiveSheet() -> getStyleByColumnAndRow($column, $rowExec) -> getFont() -> setBold(true);
				$column++;
			}
			$rowExec = 3;

			foreach ($excel_data['row_data'] as $row_data) {
				$column = 0;
				foreach ($row_data as $cell) {
					//Looping through the cells per facility
					$objPHPExcel -> getActiveSheet() -> setCellValueByColumnAndRow($column, $rowExec, $cell);

					$column++;
				}
				$rowExec++;
			}

			$objPHPExcel -> getActiveSheet() -> setTitle('Simple');

			// Save Excel 2007 file
			//echo date('H:i:s') . " Write to Excel2007 format\n";
			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

			// We'll be outputting an excel file
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			// It will be called file.xls
			header("Content-Disposition: attachment; filename=" . $excel_data['file_name'] . ".xlsx");

			// Write file to the browser
			$objWriter -> save('php://output');
			$objPHPExcel -> disconnectWorksheets();
			unset($objPHPExcel);
		// Echo done
		endif;
	}
public function create_excel_potential_expiries($excel_data = NUll) {

		//check if the excel data has been set if not exit the excel generation
	$styleArray = array('font' => array('bold' => true),'alignment'=>array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
		
		if (count($excel_data) > 0) :

			$objPHPExcel = new PHPExcel();
			$objPHPExcel -> getProperties() -> setCreator("HCMP");
			$objPHPExcel -> getProperties() -> setLastModifiedBy($excel_data['doc_creator']);
			$objPHPExcel -> getProperties() -> setTitle($excel_data['doc_title']);
			$objPHPExcel -> getProperties() -> setSubject($excel_data['doc_title']);
			$objPHPExcel -> getProperties() -> setDescription("");

			$objPHPExcel -> setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
			$objPHPExcel->getActiveSheet()->setCellValue('A1', $excel_data['excel_title']);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);

			$rowExec = 2;

			//Looping through the cells
			$column = 0;

			foreach ($excel_data['column_data'] as $column_data) {
				$objPHPExcel -> getActiveSheet() -> setCellValueByColumnAndRow($column, $rowExec, $column_data);
				$objPHPExcel -> getActiveSheet() -> getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column)) -> setAutoSize(true);
				//$objPHPExcel->getActiveSheet()->getStyle($column, $rowExec)->getFont()->setBold(true);
				$objPHPExcel -> getActiveSheet() -> getStyleByColumnAndRow($column, $rowExec) -> getFont() -> setBold(true);
				$column++;
			}
			$rowExec = 3;

			foreach ($excel_data['row_data'] as $row_data) {
				$column = 0;
				foreach ($row_data as $cell) {
					//Looping through the cells per facility
					$objPHPExcel -> getActiveSheet() -> setCellValueByColumnAndRow($column, $rowExec, $cell);

					$column++;
				}
				$rowExec++;
			}

			$objPHPExcel -> getActiveSheet() -> setTitle('Simple');

			// Save Excel 2007 file
			//echo date('H:i:s') . " Write to Excel2007 format\n";
			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

			// We'll be outputting an excel file
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			// It will be called file.xls
			header("Content-Disposition: attachment; filename=" . $excel_data['file_name'] . ".xlsx");

			// Write file to the browser
			$objWriter -> save('php://output');
			$objPHPExcel -> disconnectWorksheets();
			unset($objPHPExcel);
		// Echo done
		endif;
	}
	public function create_pdf($pdf_data = NULL, $district_id = NULL, $html_body = NULL) {
		//echo $district_id;
		//exit;
		if (count($pdf_data) > 0) :
			$url = base_url() . 'assets/img/coat_of_arms.png';
			$html_title = "<div align=center><img src='$url' height='70' width='70'style='vertical-align: top;'> </img></div>
		<div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold; font-size: 14px;'>
		Ministry of Health</div>
		<div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold;display: block; font-size: 13px;'>
		Health Commodities Management Platform</div>
		<div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold;display: block; font-size: 13px;'>
		Weekly Reports</div><hr/>";

			$table_style = '<style>table.data-table {border: 1px solid #DDD;margin: 10px auto;border-spacing: 0px;}
		table.data-table th {border: none;color: #036;text-align: center;border: 1px solid #DDD;border-top: none;max-width: 450px;}
		table.data-table td, table th {padding: 4px;}
		table.data-table td {border: none;border-left: 1px solid #DDD;border-right: 1px solid #DDD;height: 30px;margin: 0px;border-bottom: 1px solid #DDD;}
		</style>';
			//$name=$this -> session -> userdata('full_name');

			$this -> mpdf = new mPDF('', 'A4-L', 0, '', 15, 15, 16, 16, 9, 9, '');
			$this -> mpdf -> ignore_invalid_utf8 = true;
			$this -> mpdf -> WriteHTML($html_title);
			$this -> mpdf -> defaultheaderline = 1;
			$this -> mpdf -> simpleTables = true;
			$this -> mpdf -> WriteHTML($table_style . $pdf_data);
			$this -> mpdf -> SetFooter("{DATE D j M Y }|{PAGENO}/{nb}|Prepared by: HCMP");
			//$this -> mpdf -> Output($report_name, 'I');
			//exit;
			$report_name = "Weekly Report.pdf";
			$path = $_SERVER["DOCUMENT_ROOT"];
			$handler = $path . "/HCMPv2/pdf/" . $report_name;
			write_file($handler, $this -> mpdf -> Output($report_name, 'S'));
			//$email_address = "collinsojenge@gmail.com";
			$message = $html_body;
			//echo $html_body;
			//exit;
			$subject = "Weekly Reports";
			//Get the email address of the dpp to whom the email is going to
			$email_address = $this -> get_ddp_email($district_id);
			//$email_address = "collinsojenge@gmail.com";
			$this -> hcmp_functions -> send_email($email_address, $message, $subject, $handler);
		endif;
	}

	function email_sender($report_name) {

		//setting the connection variables
		$config['protocol'] = 'smtp';
		$config['smtp_host'] = 'ssl://smtp.googlemail.com';
		$config['smtp_port'] = 465;
		$config['smtp_user'] = stripslashes('hcmpkenya.test@gmail.com');
		$config['smtp_pass'] = stripslashes('verystrongpassword');
		ini_set("SMTP", "ssl://smtp.gmail.com");
		ini_set("smtp_port", "465");
		ini_set("max_execution_time", "50000");

		$emails = "collinsojenge@gmail.com";

		//pulling emails from the DB
		$this -> load -> library('email', $config);
		$path = $_SERVER["DOCUMENT_ROOT"];
		$file = $path . "/HCMPv2/application/assets/pdf/" . $report_name;
		//puts the path where the pdf's are stored

		foreach ($emails as $email) {
			$this -> email -> attach($file);
			$address = $email['email'];
			$this -> email -> set_newline("\r\n");
			$this -> email -> from('hcmpkenya.test@gmail.com', "HCMP Kenya");
			//user variable displays current user logged in from sessions
			$this -> email -> to("$address");
			$this -> email -> subject('Weekly HCMP Report ');
			$this -> email -> message('Please find the Report Attached');

			//success message else show the error
			if ($this -> email -> send()) {
				echo 'Your email was sent, successfully to ' . $address . '<br/>';
				//unlink($file);
				$this -> email -> clear(TRUE);

			} else {
				show_error($this -> email -> print_debugger());
			}

		}
		ob_end_flush();
		unlink($file);
		//delete the attachment after sending to avoid clog up of pdf'ss
	}
}
