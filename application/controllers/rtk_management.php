<?php
/*

*/
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include_once ('home_controller.php');

class Rtk_Management extends Home_controller {

    function __construct() {
        parent::__construct();
        $this->load->database();
        ini_set('memory_limit', '-1');
        ini_set('max_input_vars', 3000);
    }

    public function index() {
        echo "|";
    }

    //SCMLT FUNCTUONS

    public function scmlt_home(){
        $district = $this->session->userdata('district_id');                
        $facilities = Facilities::get_total_facilities_rtk_in_district($district);       
        $district_name = districts::get_district_name_($district);                    
        $table_body = '';
        $reported = 0;
        $nonreported = 0;
        $date = date('d', time());

        $msg = $this->session->flashdata('message');
        if(isset($msg)){
            $data['notif_message'] = $msg;
        }
        if(isset($popout)){
            $data['popout'] = $popout;
        }
        
        $sql = "select distinct rtk_settings.* 
        from rtk_settings, facilities 
        where facilities.zone = rtk_settings.zone 
        and facilities.rtk_enabled = 1";
        $res_ddl = $this->db->query($sql);
        $deadline_date = null;
        $settings = $res_ddl->result_array();
        foreach ($settings as $key => $value) {
            $deadline_date = $value['deadline'];
            $five_day_alert = $value['5_day_alert'];
            $report_day_alert = $value['report_day_alert'];
            $overdue_alert = $value['overdue_alert'];
        }
        date_default_timezone_set("EUROPE/Moscow");

        foreach ($facilities as $facility_detail) {

           $lastmonth = date('F', strtotime("last day of previous month"));
           if($date>$deadline_date){
            $report_link = "<span class='label label-danger'>  Pending for $lastmonth </span> <a href=" . site_url('rtk_management/get_report/' . $facility_detail['facility_code']) . " class='link report'></a></td>";
        }else{
            $report_link = "<span class='label label-danger'>  Pending for $lastmonth </span> <a href=" . site_url('rtk_management/get_report/' . $facility_detail['facility_code']) . " class='link report'> Report</a></td>";
        }


        $table_body .="<tr><td><a class='ajax_call_1' id='county_facility' name='" . base_url() . "rtk_management/get_rtk_facility_detail/$facility_detail[facility_code]' href='#'>" . $facility_detail["facility_code"] . "</td>";
        $table_body .="<td>" . $facility_detail['facility_name'] . "</td><td>" . $district_name['district'] . "</td>";
        $table_body .="<td>";

        $lab_count = lab_commodity_orders::get_recent_lab_orders($facility_detail['facility_code']);
        if ($lab_count > 0) {
            $reported = $reported + 1;              
            $table_body .="<span class='label label-success'>Submitted  for    $lastmonth </span><a href=" . site_url('rtk_management/rtk_orders') . " class='link'> View</a></td>";
        } else {
            $nonreported = $nonreported + 1;
            $table_body .=$report_link;
        }

        $table_body .="</td>";
    }
    $county = $this->session->userdata('county_name');
    $countyid = $this->session->userdata('county_id');
    $data['countyid'] = $countyid;
    $data['county'] = $county;
    $data['table_body'] = $table_body;
    $data['content_view'] = "rtk/rtk/scmlt/dpp_home_with_table";
    $data['title'] = "Home";
    $data['link'] = "home";
    $total = $reported + $nonreported;
    $percentage_complete = $reported / $total * 100;
    $percentage_complete = number_format($percentage_complete, 0);
    $data['percentage_complete'] = $percentage_complete;
    $data['reported'] = $reported;
    $data['nonreported'] = $nonreported;
    $data['facilities'] = Facilities::get_total_facilities_rtk_in_district($district);
    $this->load->view('rtk/template', $data);

}
public function scmlt_orders($msg = NULL) {
    $district = $this->session->userdata('district_id');        
    $district_name = Districts::get_district_name($district)->toArray();        
    $d_name = $district_name[0]['district'];
    $countyid = $this->session->userdata('county_id');

    $data['countyid'] = $countyid;

    $data['title'] = "Orders";
    $data['content_view'] = "rtk/rtk/scmlt/rtk_orders_listing_v";
    $data['banner_text'] = $d_name . "Orders";
                //        $data['fcdrr_order_list'] = Lab_Commodity_Orders::get_district_orders($district);
    ini_set('memory_limit', '-1');

    date_default_timezone_set('EUROPE/moscow');
    $last_month = date('m');
                //            $month_ago=date('Y-'.$last_month.'-d');
    $month_ago = date('Y-m-d', strtotime("last day of previous month"));
    $sql = 'SELECT  
    facilities.facility_code,facilities.facility_name,lab_commodity_orders.id,lab_commodity_orders.order_date,lab_commodity_orders.district_id,lab_commodity_orders.compiled_by,lab_commodity_orders.facility_code
    FROM lab_commodity_orders, facilities
    WHERE lab_commodity_orders.facility_code = facilities.facility_code 
    AND lab_commodity_orders.order_date between ' . $month_ago . ' AND NOW()
    AND facilities.district =' . $district . '
    ORDER BY  lab_commodity_orders.id DESC ';
                   /*$query = $this->db->query("SELECT  
                    facilities.facility_code,facilities.facility_name,lab_commodity_orders.id,lab_commodity_orders.order_date,lab_commodity_orders.district_id,lab_commodity_orders.compiled_by,lab_commodity_orders.facility_code
                    FROM lab_commodity_orders, facilities
                    WHERE lab_commodity_orders.facility_code = facilities.facility_code 
                    AND lab_commodity_orders.order_date between '$month_ago ' AND NOW()
                    AND lab_commodity_orders.district_id =' . $district . '
                    ORDER BY  lab_commodity_orders.id DESC");*/
$query = $this->db->query($sql);

$data['lab_order_list'] = $query->result_array();
$data['all_orders'] = Lab_Commodity_Orders::get_district_orders($district);
$myobj = Doctrine::getTable('districts')->find($district);
                    //$data['district_incharge']=array($id=>$myobj->district);
$data['myClass'] = $this;
$data['d_name'] = $d_name;
$data['msg'] = $msg;

$this->load->view("rtk/template", $data);
}
public function scmlt_allocations($msg = NULL) {
    $district = $this->session->userdata('district_id');
    $district_name = Districts::get_district_name($district)->toArray();
    $countyid = $this->session->userdata('county_id');
    $data['countyid'] = $countyid;
    $d_name = $district_name[0]['district'];     
    $data['title'] = "Allocations";
    $data['content_view'] = "rtk/rtk/scmlt/rtk_allocation_v";
    $data['banner_text'] = $d_name . "Allocation";
    //        $data['lab_order_list'] = Lab_Commodity_Orders::get_district_orders($district);
    ini_set('memory_limit', '-1');

    $start_date = date("Y-m-", strtotime("-3 Month "));
    $start_date .='1';

    $end_date = date('Y-m-d', strtotime("last day of previous month"));      
    $allocations = $this->_allocation(NULL, NULL, $district,NUll, NULL,NULL);
    $data['lab_order_list'] = $allocations;
    $data['all_orders'] = Lab_Commodity_Orders::get_district_orders($district);
    $myobj = Doctrine::getTable('districts')->find($district);
    $data['myClass'] = $this;
    $data['msg'] = $msg;        
    $data['d_name'] = $d_name;

    $this->load->view("rtk/template", $data);
}

    //Load FCDRR
public function get_report($facility_code) {       

  $data['title'] = "Lab Commodities 3 Report";
  $data['content_view'] = "rtk/rtk/scmlt/fcdrr";
  $data['banner_text'] = "Lab Commodities 3 Report";
  $data['link'] = "rtk_management";
  $data['quick_link'] = "commodity_list";
  $my_arr = $this->_get_begining_balance($facility_code);
  $my_count = count($my_arr);
  $data['beginning_bal'] = $my_arr;         
  $data['facilities'] = Facilities::get_one_facility_details($facility_code);            
  $data['lab_categories'] = Lab_Commodity_Categories::get_active();
  $this->load->view("rtk/template", $data);
}

      //Begining Balances
function _get_begining_balance($facility_code) {
    $result_bal = array();
    $start_date_bal = date('Y-m-d', strtotime("first day of previous month"));
    $end_date_bal = date('Y-m-d', strtotime("last day of previous month"));
    $sql_bal = "SELECT lab_commodity_details.closing_stock from lab_commodity_orders, lab_commodity_details 
    where lab_commodity_orders.id = lab_commodity_details.order_id 
    and lab_commodity_orders.order_date between '$start_date_bal' and '$end_date_bal' 
    and lab_commodity_orders.facility_code='$facility_code'";

    $res_bal = $this->db->query($sql_bal);

    foreach ($res_bal->result_array() as $row_bal) {
        array_push($result_bal, $row_bal['closing_stock']);
    }
    return $result_bal;
}

      //Save FCDRR
public function save_lab_report_data() {

    date_default_timezone_set("EUROPE/Moscow");
    $firstday = date('D dS M Y', strtotime("first day of previous month"));
    $lastday = date('D dS M Y', strtotime("last day of previous month"));
    $lastmonth = date('F', strtotime("last day of previous month"));

    $month = $lastmonth;
    $district_id = $_POST['district'];
    $facility_code = $_POST['facility_code'];
    $drug_id = $_POST['commodity_id'];
    $unit_of_issue = $_POST['unit_of_issue'];
    $b_balance = $_POST['b_balance'];
    $q_received = $_POST['q_received'];
    $q_used = $_POST['q_used'];
    $tests_done = $_POST['tests_done'];
    $losses = $_POST['losses'];
    $pos_adj = $_POST['pos_adj'];
    $neg_adj = $_POST['neg_adj'];
    $physical_count = $_POST['physical_count'];
    $q_expiring = $_POST['q_expiring'];
    $days_out_of_stock = $_POST['days_out_of_stock'];
    $q_requested = $_POST['q_requested'];
    $commodity_count = count($drug_id);

    $vct = $_POST['vct'];
    $pitc = $_POST['pitc'];
    $pmtct = $_POST['pmtct'];
    $b_screening = $_POST['blood_screening'];
    $other = $_POST['other2'];
    $specification = $_POST['specification'];
    $rdt_under_tests = $_POST['rdt_under_tests'];
    $rdt_under_pos = $_POST['rdt_under_positive'];
    $rdt_btwn_tests = $_POST['rdt_to_tests'];
    $rdt_btwn_pos = $_POST['rdt_to_positive'];
    $rdt_over_tests = $_POST['rdt_over_tests'];
    $rdt_over_pos = $_POST['rdt_over_positive'];
    $micro_under_tests = $_POST['micro_under_tests'];
    $micro_under_pos = $_POST['micro_under_positive'];
    $micro_btwn_tests = $_POST['micro_to_tests'];
    $micro_btwn_pos = $_POST['micro_to_positive'];
    $micro_over_tests = $_POST['micro_over_tests'];
    $micro_over_pos = $_POST['micro_over_positive'];
    $beg_date = $_POST['begin_date'];
    $end_date = $_POST['end_date'];
    $explanation = $_POST['explanation'];
    $compiled_by = $_POST['compiled_by'];
    $moh_642 = $_POST['moh_642'];
    $moh_643 = $_POST['moh_643'];

    date_default_timezone_set('EUROPE/Moscow');
    $beg_date = date('Y-m-d', strtotime("first day of previous month"));
    $end_date = date('Y-m-d', strtotime("last day of previous month"));

    $user_id = $this->session->userdata('user_id');        

    $order_date = date('y-m-d');
    $count = 1;
    $data = array('facility_code' => $facility_code, 'district_id' => $district_id, 'compiled_by' => $compiled_by, 'order_date' => $order_date, 'vct' => $vct, 'pitc' => $pitc, 'pmtct' => $pmtct, 'b_screening' => $b_screening, 'other' => $other, 'specification' => $specification, 'rdt_under_tests' => $rdt_under_tests, 'rdt_under_pos' => $rdt_under_pos, 'rdt_btwn_tests' => $rdt_btwn_tests, 'rdt_btwn_pos' => $rdt_btwn_pos, 'rdt_over_tests' => $rdt_over_tests, 'rdt_over_pos' => $rdt_over_pos, 'micro_under_tests' => $micro_under_tests, 'micro_under_pos' => $micro_under_pos, 'micro_btwn_tests' => $micro_btwn_tests, 'micro_btwn_pos' => $micro_btwn_pos, 'micro_over_tests' => $micro_over_tests, 'micro_over_pos' => $micro_over_pos, 'beg_date' => $beg_date, 'end_date' => $end_date, 'explanation' => $explanation, 'moh_642' => $moh_642, 'moh_643' => $moh_643, 'report_for' => $lastmonth);
    $u = new Lab_Commodity_Orders();
    $u->fromArray($data);
    $u->save();
    $object_id = $u->get('id');
    $this->logData('13', $object_id);
    $this->update_amc($facility_code);

    $lastId = Lab_Commodity_Orders::get_new_order($facility_code);
    $new_order_id = $lastId->maxId;
    $count++;

    for ($i = 0; $i < $commodity_count; $i++) {            
        $mydata = array('order_id' => $new_order_id, 'facility_code' => $facility_code, 'district_id' => $district_id, 'commodity_id' => $drug_id[$i], 'unit_of_issue' => $unit_of_issue[$i], 'beginning_bal' => $b_balance[$i], 'q_received' => $q_received[$i], 'q_used' => $q_used[$i], 'no_of_tests_done' => $tests_done[$i], 'losses' => $losses[$i], 'positive_adj' => $pos_adj[$i], 'negative_adj' => $neg_adj[$i], 'closing_stock' => $physical_count[$i], 'q_expiring' => $q_expiring[$i], 'days_out_of_stock' => $days_out_of_stock[$i], 'q_requested' => $q_requested[$i]);
        Lab_Commodity_Details::save_lab_commodities($mydata);           
    }
    $q = "select county from districts where id='$district_id'";
    $res = $this->db->query($q)->result_array();
    foreach ($res as $key => $value) {
        $county = $value['county'];
    }
    $this->_update_reports_count('add',$county,$district_id);
    $this->session->set_flashdata('message', 'The report has been saved');
    redirect('rtk_management/scmlt_home');

}

    //Edit FCDRR
public function edit_lab_order_details($order_id, $msg = NULL) {
    $delivery = $this->uri->segment(3);
    $district = $this->session->userdata('district_id');
    $data['title'] = "Lab Commodity Order Details";    
    ini_set('memory_limit', '-1');
    $data['order_id'] = $order_id;
    $data['content_view'] = "rtk/rtk/scmlt/fcdrr_edit";
    $data['banner_text'] = "Lab Commodity Order Details";
    $data['lab_categories'] = Lab_Commodity_Categories::get_all();
    $data['detail_list'] = Lab_Commodity_Details::get_order($order_id);
    $result = $this->db->query('SELECT * 
        FROM lab_commodity_details, counties, facilities, districts, lab_commodity_orders, lab_commodity_categories, lab_commodities
        WHERE lab_commodity_details.facility_code = facilities.facility_code
        AND counties.id = districts.county
        AND facilities.facility_code = lab_commodity_orders.facility_code
        AND lab_commodity_details.commodity_id = lab_commodities.id
        AND lab_commodity_categories.id = lab_commodities.category
        AND facilities.district = districts.id
        AND lab_commodity_details.order_id = lab_commodity_orders.id
        AND lab_commodity_orders.id = ' . $order_id . '');
    $data['all_details'] = $result->result_array();      
    $this->load->view("rtk/template", $data);
}

    //Update the FCDRR Online
public function update_lab_commodity_orders() {
    $rtk = new Rtk_Management();
    $order_id = $_POST['order_id'];
    $detail_id = $_POST['detail_id'];
    $district_id = $_POST['district'];
    $facility_code = $_POST['facility_code'];
    $drug_id = $_POST['commodity_id'];
    $unit_of_issue = $_POST['unit_of_issue'];
    $b_balance = $_POST['b_balance'];
    $q_received = $_POST['q_received'];
    $q_used = $_POST['q_used'];
    $tests_done = $_POST['tests_done'];
    $losses = $_POST['losses'];
    $pos_adj = $_POST['pos_adj'];
    $neg_adj = $_POST['neg_adj'];
    $physical_count = $_POST['physical_count'];
    $q_expiring = $_POST['q_expiring'];
    $days_out_of_stock = $_POST['days_out_of_stock'];
    $q_requested = $_POST['q_requested'];
    $commodity_count = count($drug_id);
    $detail_count = count($detail_id);

    $vct = $_POST['vct'];
    $pitc = $_POST['pitc'];
    $pmtct = $_POST['pmtct'];
    $b_screening = $_POST['blood_screening'];
    $other = $_POST['other2'];
    $specification = $_POST['specification'];
    $rdt_under_tests = $_POST['rdt_under_tests'];
    $rdt_under_pos = $_POST['rdt_under_positive'];
    $rdt_btwn_tests = $_POST['rdt_to_tests'];
    $rdt_btwn_pos = $_POST['rdt_to_positive'];
    $rdt_over_tests = $_POST['rdt_over_tests'];
    $rdt_over_pos = $_POST['rdt_over_positive'];
    $micro_under_tests = $_POST['micro_under_tests'];
    $micro_under_pos = $_POST['micro_under_positive'];
    $micro_btwn_tests = $_POST['micro_to_tests'];
    $micro_btwn_pos = $_POST['micro_to_positive'];
    $micro_over_tests = $_POST['micro_over_tests'];
    $micro_over_pos = $_POST['micro_over_positive'];
    date_default_timezone_set('EUROPE/Moscow');
    $beg_date = date('y-m-d', strtotime($_POST['begin_date']));
    $end_date = date('y-m-d', strtotime($_POST['end_date']));
    $explanation = $_POST['explanation'];
    $compiled_by = $_POST['compiled_by'];

    $moh_642 = $_POST['moh_642'];
    $moh_643 = $_POST['moh_643'];

    $myobj = Doctrine::getTable('Lab_Commodity_Orders')->find($order_id);

    $myobj->vct = $vct;
    $myobj->pitc = $pitc;
    $myobj->pmtct = $pmtct;
    $myobj->b_screening = $b_screening;
    $myobj->other = $other;
    $myobj->specification = $specification;
    $myobj->rdt_under_tests = $rdt_under_tests;
    $myobj->rdt_under_pos = $rdt_under_pos;
    $myobj->rdt_btwn_tests = $rdt_btwn_tests;
    $myobj->rdt_btwn_pos = $rdt_btwn_pos;
    $myobj->rdt_over_tests = $rdt_over_tests;
    $myobj->rdt_over_pos = $rdt_over_pos;
    $myobj->micro_under_tests = $micro_under_tests;
    $myobj->micro_under_pos = $micro_under_pos;
    $myobj->micro_btwn_tests = $micro_btwn_tests;
    $myobj->micro_btwn_pos = $micro_btwn_pos;
    $myobj->micro_over_tests = $micro_over_tests;
    $myobj->micro_over_pos = $micro_over_pos;
    $myobj->beg_date = $beg_date;
    $myobj->end_date = $end_date;
    $myobj->explanation = $explanation;
    $myobj->compiled_by = $compiled_by;
    $myobj->moh_642 = $moh_642;
    $myobj->moh_643 = $moh_643;
    $myobj->save();
    $object_id = $myobj->get('id');
    $this->logData('14', $object_id);
    $q = "select id from lab_commodity_details where order_id = $order_id";
    $res = $this->db->query($q);
    $ids = $res->result_array();  

    for ($i = 0; $i < $detail_count; $i++) {

        $id = $ids[$i]['id'];           
        $sql = "UPDATE `lab_commodity_details` SET `beginning_bal`=$b_balance[$i],
        `q_received`='$q_received[$i]',`q_used`=$q_used[$i],`no_of_tests_done`=$tests_done[$i],`losses`=$losses[$i],
        `positive_adj`=$pos_adj[$i],`negative_adj`=$neg_adj[$i],`closing_stock`=$physical_count[$i],
        `q_expiring`=$q_expiring[$i],`days_out_of_stock`=$days_out_of_stock[$i],`q_requested`=$q_requested[$i] WHERE id= $id ";
        $this->db->query($sql);
    }

    redirect('rtk_management/scmlt_orders');
}

    //VIew FCDRR Report
public function lab_order_details($order_id, $msg = NULL) {
    $delivery = $this->uri->segment(3);
    $district = $this->session->userdata('district_id');
    $data['title'] = "Lab Commodity Order Details";       
    $data['order_id'] = $order_id;
    $data['content_view'] = "rtk/rtk/scmlt/fcdrr_report";
    $data['banner_text'] = "Lab Commodity Order Details";

    $data['lab_categories'] = Lab_Commodity_Categories::get_all();
    $data['detail_list'] = Lab_Commodity_Details::get_order($order_id);

    $result = $this->db->query('SELECT * 
        FROM lab_commodity_details, counties, facilities, districts, lab_commodity_orders, lab_commodity_categories, lab_commodities
        WHERE lab_commodity_details.facility_code = facilities.facility_code
        AND counties.id = districts.county
        AND facilities.facility_code = lab_commodity_orders.facility_code
        AND lab_commodity_details.commodity_id = lab_commodities.id
        AND lab_commodity_categories.id = lab_commodities.category
        AND facilities.district = districts.id
        AND lab_commodity_details.order_id = lab_commodity_orders.id
        AND lab_commodity_orders.id = ' . $order_id . '');
    $data['all_details'] = $result->result_array();
    $this->load->view("rtk/template", $data);
}

    //Print the FCDRR
public function get_lab_report($order_no, $report_type) {
    $table_head = '<style>table.data-table {border: 1px solid #DDD;margin: 10px auto;border-spacing: 0px;}
    table.data-table th {border: none;color: #036;text-align: center;background-color: #F5F5F5;border: 1px solid #DDD;border-top: none;max-width: 450px;}
    table.data-table td, table th {padding: 4px;}
    table.data-table td {border: none;border-left: 1px solid #DDD;border-right: 1px solid #DDD;height: 30px;margin: 0px;border-bottom: 1px solid #DDD;}
    .col5{background:#D8D8D8;}</style></table>
    <table class="data-table" width="100%">
        <thead>
            <tr>
                <th><strong>Category</strong></th>
                <th><strong>Description</strong></th>
                <th><strong>Unit of Issue</strong></th>
                <th><strong>Beginning Balance</strong></th>
                <th><strong>Quantity Received</strong></th>
                <th><strong>Quantity Used</strong></th>
                <th><strong>Number of Tests Done</strong></th>
                <th><strong>Losses</strong></th>
                <th><strong>Positive Adjustments</strong></th>
                <th><strong>Negative Adjustments</strong></th>
                <th><strong>Closing Stock</strong></th>
                <th><strong>Quantity Expiring in 6 Months</strong></th>
                <th><strong>Days Out of Stock</strong></th>
                <th><strong>Quantity Requested</strong></th>
            </tr>
        </thead>
        <tbody>';
            $detail_list = Lab_Commodity_Details::get_order($order_no);
            $table_body = '';
            foreach ($detail_list as $detail) {
                $table_body .= '<tr><td>' . $detail['category_name'] . '</td>';
                $table_body .= '<td>' . $detail['commodity_name'] . '</td>';
                $table_body .= '<td>' . $detail['unit_of_issue'] . '</td>';
                $table_body .= '<td>' . $detail['beginning_bal'] . '</td>';
                $table_body .= '<td>' . $detail['q_received'] . '</td>';
                $table_body .= '<td>' . $detail['q_used'] . '</td>';
                $table_body .= '<td>' . $detail['no_of_tests_done'] . '</td>';
                $table_body .= '<td>' . $detail['losses'] . '</td>';
                $table_body .= '<td>' . $detail['positive_adj'] . '</td>';
                $table_body .= '<td>' . $detail['negative_adj'] . '</td>';
                $table_body .= '<td>' . $detail['closing_stock'] . '</td>';
                $table_body .= '<td>' . $detail['q_expiring'] . '</td>';
                $table_body .= '<td>' . $detail['days_out_of_stock'] . '</td>';
                $table_body .= '<td>' . $detail['q_requested'] . '</td></tr>';
            }
            $table_foot = '</tbody></table>';
            $report_name = "Lab Commodities Order " . $order_no . " Details";
            $title = "Lab Commodities Order " . $order_no . " Details";
            $html_data = $table_head . $table_body . $table_foot;

            switch ($report_type) {
                case 'excel' :
                $this->_generate_lab_report_excel($report_name, $title, $html_data);
                break;
                case 'pdf' :
                $this->_generate_lab_report_pdf($report_name, $title, $html_data);
                break;
            }
        }


        //Generate the FCDRR PDF

        function _generate_lab_report_pdf($report_name, $title, $html_data) {

            /*         * ******************************************setting the report title******************** */

            $html_title = "<div ALIGN=CENTER><img src='" . base_url() . "assets/img/coat_of_arms-resized.png' height='70' width='70'style='vertical-align: top;' > </img></div>
            <div style='text-align:center; font-size: 14px;display: block;font-weight: bold;'>$title</div>
            <div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold; font-size: 14px;'>
                Ministry of Health</div>
                <div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold;display: block; font-size: 13px;'>Health Commodities Management Platform</div><hr />";

                /*         * ********************************initializing the report ********************* */
                $this->load->library('mpdf');
                $this->mpdf = new mPDF('', 'A4-L', 0, '', 15, 15, 16, 16, 9, 9, '');
                $this->mpdf->SetTitle($title);
                $this->mpdf->WriteHTML($html_title);
                $this->mpdf->simpleTables = true;
                $this->mpdf->WriteHTML('<br/>');
                $this->mpdf->WriteHTML($html_data);
                $report_name = $report_name . ".pdf";
                $this->mpdf->Output($report_name, 'D');
            }

        //Generate the FCDRR Excel
            function _generate_lab_report_excel($report_name, $title, $html_data) {
                $data = $html_data;
                $filename = $report_name;
                header("Content-type: application/excel");
                header("Content-Disposition: attachment; filename=$filename.xls");
                echo "$data";
            }







    ///*** CLC Functions ***///
            public function county_home() {
                $lastday = date('Y-m-d', strtotime("last day of previous month"));
                $countyid = $this->session->userdata('county_id');
                $districts = districts::getDistrict($countyid);
                $county_name = counties::get_county_name($countyid);
                $County = $county_name[0]['county'];
                $reports = array();
                $tdata = ' ';
                foreach ($districts as $value) {
                    $q = $this->db->query('SELECT lab_commodity_orders.id, lab_commodity_orders.facility_code, lab_commodity_orders.compiled_by, lab_commodity_orders.order_date, lab_commodity_orders.district_id, districts.id as distid, districts.district, facilities.facility_name, facilities.facility_code FROM districts, lab_commodity_orders, facilities WHERE lab_commodity_orders.district_id = districts.id AND facilities.facility_code = lab_commodity_orders.facility_code AND districts.id = ' . $value['id'] . '');
                    $res = $q->result_array();
                    foreach ($res as $values) {
                        date_default_timezone_set('EUROPE/Moscow');
                        $order_date = date('F', strtotime($values['order_date']));
                        $tdata .= '<tr>
                        <td>' . $order_date . '</td>
                        <td>' . $values['facility_code'] . '</td>
                        <td>' . $values['facility_name'] . '</td>
                        <td>' . $values['district'] . '</td>
                        <td>' . $values['order_date'] . '</td>
                        <td>' . $values['compiled_by'] . '</td>
                        <td><a href="' . base_url() . 'rtk_management/lab_order_details/' . $values['id'] . '">View</a></td>
                        <tr>';
                        }
                        if (count($res) > 0) {
                            array_push($reports, $res);
                        }
                    }
                    $month = $this->session->userdata('Month');
                    if ($month == '') {
                        $month = date('mY', strtotime('-1 month'));
                    }
                    $sql ="select rtk_district_percentage.percentage,districts.district from rtk_district_percentage,districts,counties
                    where rtk_district_percentage.district_id = districts.id and districts.county = counties.id and counties.id = '$countyid' 
                    and rtk_district_percentage.month = '$month'";
                    $year = substr($month, -4);
                    $month = substr_replace($month, "", -4);
                    $reporting_rates = $this->db->query($sql)->result_array();
                    $districts = array();
                    $reported = array();
                    $nonreported = array();
                    $query = $this->db->query($q);
                    foreach ($reporting_rates as $key => $value) {
                        array_push($districts, $value['district']);  
                        $percentage_reported = intval($value['percentage']);
                        array_push($reported, $percentage_reported);
                        $percentage_non_reported = 100 - $percentage_reported;
                        array_push($nonreported, $percentage_non_reported);
                    }
                    $districts = json_encode($districts);
                    $districts = str_replace('"', "'", $districts);
                    $reported = json_encode($reported);
                    $nonreported = json_encode($nonreported);
                    $reporting_rates = array('districts'=>$districts,'reported'=>$reported,'nonreported'=>$nonreported);                    
                    $data['graphdata'] = $reporting_rates;
            //$data['graphdata'] = $this->county_reporting_percentages($countyid, $year, $month);        
                    $data['county_summary'] = $this->_requested_vs_allocated($year, $month, $countyid);        
                    $data['tdata'] = $tdata;
                    $data['county'] = $County;
                    $data['active_month'] = $month.$year;
                    $data['title'] = 'RTK County Admin';
                    $data['banner_text'] = 'RTK County Admin';
                    $data['content_view'] = "rtk/rtk/clc/home";
                    $this->load->view("rtk/template", $data);
                }
                public function county_profile($county) {
        $data = array();
        $lastday = date('Y-m-d', strtotime("last day of previous month"));
        $County = $this->session->userdata('county_name');
        $Countyid = $this->session->userdata('county_id');
        $districts = districts::getDistrict($Countyid);
        $facility = facilities::get_facility_name_($mfl);

        $data['county'] = $County;
        $data['countyid'] = $Countyid;
        $data['title'] = 'Facility Profile: ' . $facility['facility_name'];
        $data['banner_text'] = 'Facility Profile: ' . $facility['facility_name'];
        $data['content_view'] = "rtk/rtk/clc/county_profile_view";

        $this->load->view("rtk/template", $data);
    }

                public function rca_districts() {
                    $county = $this->session->userdata('county_id');
                    date_default_timezone_set('EUROPE/moscow');
                    $districts = districts::getDistrict($county);
                    $county_name = counties::get_county_name($county);
                    $County = $county_name[0]['county'];
                    $table_data_facilities = array();
                    $res_district = $this->_districts_in_county($county);
                    $data['districts_list'] = $res_district;

                    $count_districts = count($res_district);
                    $data['count_districts'] = $count_districts;
                    for ($i = 0; $i < $count_districts; $i++) {
                        $district_id = $res_district[$i]['id'];
                        $sql1 = "SELECT * FROM facilities WHERE facilities.district = '$district_id' and facilities.rtk_enabled = '1'";
                        $q = $this->db->query($sql1);
                        $res = $q->result_array();
                        $count = count($res);
                        array_push($table_data_facilities, $count);
                    }
                    $data['facilities_count'] = $table_data_facilities;
                    $data['county'] = $County;
                    $data['title'] = 'RTK County Admin';
                    $data['banner_text'] = "RTK County Admin: Sub-Counties in $County County";
                    $data['content_view'] = "rtk/rtk/clc/districts_v";
                    $this->load->view("rtk/template", $data);
                }

                public function rca_facilities_reports() {

                    $county = $this->session->userdata('county_id');

                    date_default_timezone_set('EUROPE/moscow');
                    $lastday = date('Y-m-d', strtotime("last day of previous month"));
                    $districts = districts::getDistrict($county);
                    $county_name = counties::get_county_name($county);         
                    $County = $county_name['county'];        
                    $sql = "SELECT lab_commodity_orders.id, lab_commodity_orders.facility_code, lab_commodity_orders.compiled_by, lab_commodity_orders.order_date, lab_commodity_orders.district_id, districts.district, facilities.facility_name, facilities.facility_code
                    FROM lab_commodity_orders,  facilities, districts, counties
                    WHERE districts.county = counties.id
                    AND facilities.district = districts.id
                    AND lab_commodity_orders.facility_code = facilities.facility_code
                    AND counties.id = $county 
                    ORDER BY   `lab_commodity_orders`.`order_date` DESC ,`lab_commodity_orders`.`district_id` ASC";
                    $res = $this->db->query($sql)->result_array();
                    $data['reports'] = $res;
                    $data['county'] = $County;
                    $data['title'] = 'RTK County Admin';
                    $data['banner_text'] = "RTK County Admin: Available Reports for $County County";
                    $data['content_view'] = "rtk/rtk/clc/facilities_reports_v";
                    $this->load->view("rtk/template", $data);
                }

                public function county_admin($sk = null) {
                    $data = array();
                    $lastday = date('Y-m-d', strtotime("last day of previous month"));
                    $County = $this->session->userdata('county_name');
                    $Countyid = $this->session->userdata('county_id');
                    $districts = districts::getDistrict($Countyid);

                    $facilities = $this->_facilities_in_county($Countyid);
                    $users = $this->_users_in_county($Countyid, 7);        
                    $data['facilities'] = $facilities;
                    $data['users'] = $users;
                    $data['districts'] = $this->_districts_in_county($Countyid);

                    $data['sk'] = $sk;
                    $data['county'] = $County;
                    $data['countyid'] = $Countyid;
                    $data['title'] = 'RTK County Admin';
                    $data['banner_text'] = 'RTK County Admin';
                    $data['content_view'] = "rtk/rtk/clc/admin_dashboard_view";
                    $this->load->view("rtk/template", $data);
                }

                public function create_facility_county() {
                    $facilityname = $_POST['facilityname'];
                    $facilitycode = $_POST['facilitycode'];
                    $facilityowner = $_POST['facilityowner'];
                    $facilitytype = $_POST['facilitytype'];
                    $district = $_POST['district'];
                    $time = date('Y-m-d', time());
                    $facilityname = addslashes($facilityname);
                    $sql = "INSERT INTO `facilities` (`id`, `facility_code`, `facility_name`, `district`, `drawing_rights`, `owner`, `type`, `level`, `rtk_enabled`, `cd4_enabled`, `drawing_rights_balance`, `using_hcmp`, `date_of_activation`) 
                    VALUES (NULL, '$facilitycode', '$facilityname', '$district', '0', '$facilityowner', '$facilitytype', '', '1', '0', '0', '0', '$time')";
                    $this->db->query($sql);
                    $object_id = $this->db->insert_id();
                    $this->logData('20', $object_id);
            //$this->_update_facility_count('add',$county,$district);        
                    redirect('rtk_management/county_admin/facilities');
                }
                public function county_trend($month=null) { 
                    if(isset($month)){           
                        $year = substr($month, -4);
                        $month = substr($month, 0,2);            
                        $monthyear = $year . '-' . $month . '-1';         

                    }else{
                        $month = $this->session->userdata('Month');
                        if ($month == '') {
                            $month = date('mY', time());
                        }
                        $year = substr($month, -4);
                        $month = substr_replace($month, "", -4);
                        $monthyear = $year . '-' . $month . '-1';
                    }
                    $active_month = $month.$year;

                    $countyid = $this->session->userdata('county_id');       
                    $county_name = counties::get_county_name($countyid);        
                    $County = $county_name['county'];
                    $res = $this->db->query("select facilities as total_facilities from rtk_county_percentage 
                        where county_id='$countyid' and month='$active_month'");
                    $result = $res->result_array();        
                    $data['total_facilities'] = $result[0]['total_facilities'];             

                    $englishdate = date('F, Y', strtotime($monthyear));
                    $reporting_rates = $this->reporting_rates($countyid,$year,$month);        
                    $xArr = array();
                    $yArr = array();
                    $xArr1 = array();
                    $cumulative_result = 0;
                    foreach ($reporting_rates as $value) {
                        $count = $value['count'];
                        $order_date = substr($value['order_date'], -2);
                        $order_date = date('jS', strtotime($value['order_date']));

                        $cumulative_result +=$count;
                        array_push($xArr1, $cumulative_result);

                        array_push($yArr, $order_date);
                        array_push($xArr, $count);
                    }

                    $data['cumulative_result'] = $cumulative_result;
                    $data['jsony'] = json_encode($yArr);
                    $data['jsonx'] = str_replace('"', "", json_encode($xArr));
                    $data['jsonx1'] = str_replace('"', "", json_encode($xArr1));
                    $data['englishdate'] = $englishdate;              
                    $data['county'] = $County;
                    $data['active_month'] = $month.$year;
                    $Countyid = $this->session->userdata('county_id');
                    $data['user_logs'] = $this->rtk_logs();
                    $data['content_view'] = "rtk/rtk/clc/trend";
                    $data['banner_text'] = "$County County Monthly Reporting Trends";
                    $data['title'] = "RTK County Admin Trends";
                    $this->load->view('rtk/template', $data);
                }
                public function district_profile($district) {
                    $data = array();
                    $lastday = date('Y-m-d', strtotime("last day of previous month"));

                    $current_month = $this->session->userdata('Month');
                    if ($current_month == '') {
                        $current_month = date('mY', time());
                    }

                    $previous_month = date('m', strtotime("last day of previous month"));
                    $previous_month_1 = date('mY', strtotime('-2 month'));
                    $previous_month_2 = date('mY', strtotime('-3 month'));


                    $year_current = substr($current_month, -4);
                    $year_previous = date('Y', strtotime("last day of previous month"));
                    $year_previous_1 = substr($previous_month_1, -4);
                    $year_previous_2 = substr($previous_month_2, -4);

                    $current_month = substr_replace($current_month, "", -4);        
                    $previous_month_1 = substr_replace($previous_month_1, "", -4);
                    $previous_month_2 = substr_replace($previous_month_2, "", -4);

                    $monthyear_current = $year_current . '-' . $current_month . '-1';
                    $monthyear_previous = $year_previous . '-' . $previous_month . '-1';
                    $monthyear_previous_1 = $year_previous_1 . '-' . $previous_month_1 . '-1';
                    $monthyear_previous_2 = $year_previous_2 . '-' . $previous_month_2 . '-1';

                    $englishdate = date('F, Y', strtotime($monthyear_current));

                    $m_c = date("F", strtotime($monthyear_current));
        //first month               
                    $m0 = date("F", strtotime($monthyear_previous));
                    $m1 = date("F", strtotime($monthyear_previous_1));
                    $m2 = date("F", strtotime($monthyear_previous_2));

                    $month_text = array($m2, $m1, $m0);

                    $district_summary = $this->rtk_summary_district($district, $year_current, $current_month);
                    $district_summary_prev = $this->rtk_summary_district($district, $year_previous, $previous_month);
                    $district_summary1 = $this->rtk_summary_district($district, $year_previous_1, $previous_month_1);
                    $district_summary2 = $this->rtk_summary_district($district, $year_previous_2, $previous_month_2);


                    $county_id = districts::get_county_id($district);
                    $county_name = counties::get_county_name($county_id['county']);

                    $cid = $this->db->select('districts.county')->get_where('districts', array('id' =>$district))->result_array();

                    foreach ($cid as $key => $value) {
                       $myres = $cid[0]['county'];
                   }
                   $mycounties = $this->db->select('districts.district,districts.id')->get_where('districts', array('county' =>$myres))->result_array(); 

                   $data['district_balances_current'] = $this->district_totals($year_current, $previous_month, $district);
                   $data['district_balances_previous'] = $this->district_totals($year_previous, $previous_month, $district);
                   $data['district_balances_previous_1'] = $this->district_totals($year_previous_1, $previous_month_1, $district);
                   $data['district_balances_previous_2'] = $this->district_totals($year_previous_2, $previous_month_2, $district);


                   $data['district_summary'] = $district_summary;

                   $data['districts'] = $mycounties;
                   $data['facilities'] = $this->_facilities_in_district($district);

                   $data['district_name'] = $district_summary['district'];
                   $data['county_id'] = $county_name['id'];
                   $data['county_name'] = $county_name['county'];     

                   $data['title'] = 'RTK County Admin - Sub-County Profile: ' . $district_summary['district'];
                   $data['banner_text'] = 'Sub-County Profile: ' . $district_summary['district'];
                   $data['content_view'] = "rtk/rtk/shared/district_profile_view";
                   $data['months'] = $month_text;

                   $this->load->view("rtk/template", $data);
               }
               public function rca_pending_facilities() {
                $countyid = $this->session->userdata('county_id');        
                $districts = districts::getDistrict($countyid);
                $county_name = counties::get_county_name($countyid);

                $County = $county_name['county'];
                $month = $this->session->userdata('Month');
                if ($month == '') {
                    $month = date('mY', strtotime('-1 month'));
                }
                $year = substr($month, -4);
                $month = substr_replace($month, "", -4);
                $date = date('F-Y', mktime(0, 0, 0, $month, 1, $year));       
                $pending_facilities = $this->rtk_facilities_not_reported(NULL, $countyid,NULL,NULL, $year,$month);
                $new_pending_facilities = array();                
                $data['county'] = $County;
                $data['pending_facility'] = $pending_facilities;
                $data['title'] = 'RTK County Admin';
                $data['banner_text'] = 'RTK County Admin: Non-Reported Facilities';
                $data['content_view'] = "rtk/rtk/clc/pending_facilities_v";
                $this->load->view("rtk/template", $data);
            }


/////////RTK ADMIN FUNCTIONS
public function rtk_manager_home() {
    $data = array();
    $data['title'] = 'RTK Manager';
    $data['banner_text'] = 'RTK Manager';
    $data['content_view'] = "rtk/rtk/admin/home_v";
    $counties = $this->_all_counties();
    $county_arr = array();
    foreach ($counties as $county) {
        array_push($county_arr, $county['county']);
    }
    $counties_json = json_encode($county_arr);
    $counties_json = str_replace('"', "'", $counties_json);
    $data['counties_json'] = $counties_json;

    $thismonth = date('m', time());
    $thismonth_year = date('Y', time());
    $this_month_full = $thismonth.$thismonth_year;

    $previous_month = date('m', strtotime("-1 month", time()));
    $previous_month_year = date('Y', strtotime("-1 month", time()));
    $previous_month_full = $previous_month.$previous_month_year;

    $prev_prev = date('m', strtotime("-2 month", time()));
    $prev_prev_year = date('Y', strtotime("-2 month", time()));
    $prev_prev_month_full = $prev_prev.$prev_prev_year;

    $thismonth_arr1 = array();

    foreach ($counties as $key => $value) {
        $id = $value['id'];
        $q = "select percentage from rtk_county_percentage where month='$this_month_full' and county_id=$id";
        $result = $this->db->query($q)->result_array();
        foreach ($result as $key => $value) {            
            $percentage = intval($value['percentage']);                               
        }        
        array_push($thismonth_arr1, $percentage);
    }     

    $previous_month_arr1 = array();

    foreach ($counties as $key => $value) {
        $id = $value['id'];
        $q = "select percentage from rtk_county_percentage where month='$previous_month_full' and county_id=$id";
        $result = $this->db->query($q)->result_array();
        foreach ($result as $key => $value) {            
            $percentage = intval($value['percentage']);                               
        } 
        array_push($previous_month_arr1, $percentage);
    }  

    $prev_prev_month_arr1 = array();

    foreach ($counties as $key => $value) {
        $id = $value['id'];
        $q = "select percentage from rtk_county_percentage where month='$prev_prev_month_full' and county_id=$id";
        $result = $this->db->query($q)->result_array();
        foreach ($result as $key => $value) {            
            $percentage = intval($value['percentage']);                               
        } 
        array_push($prev_prev_month_arr1, $percentage);
    }         
    $thismonthjson = json_encode($thismonth_arr1);
    $thismonthjson = str_replace('"', "", $thismonthjson);
    $data['thismonthjson'] = $thismonthjson;

    $previous_monthjson = json_encode($previous_month_arr1);
    $previous_monthjson = str_replace('"', "", $previous_monthjson);
    $data['previous_monthjson'] = $previous_monthjson;

    $prev_prev_monthjson = json_encode($prev_prev_month_arr1);
    $prev_prev_monthjson = str_replace('"', "", $prev_prev_monthjson);
    $data['prev_prev_monthjson'] = $prev_prev_monthjson;
    $this->load->view('rtk/template', $data);
}
public function rtk_manager($month=null) {
       if(isset($month)){           
        $year = substr($month, -4);
        $month = substr($month, 0,2);            
        $monthyear = $year . '-' . $month . '-1';         

    }else{
        $month = $this->session->userdata('Month');
        if ($month == '') {
            $month = date('mY', time());
        }
        $year = substr($month, -4);
        $month = substr_replace($month, "", -4);
        $monthyear = $year . '-' . $month . '-1';
    }
    $res = $this->db->query('select count(id) as total_facilities from facilities where rtk_enabled=1');
    $result = $res->result_array();
    $data['total_facilities'] = $result[0]['total_facilities'];
    $englishdate = date('F, Y', strtotime($monthyear));
    $reporting_rates = $this->reporting_rates(null,$year,$month);       
    $xArr = array();
    $yArr = array();
    $xArr1 = array();
    $cumulative_result = 0;
    foreach ($reporting_rates as $value) {
        $count = $value['count'];
        $order_date = substr($value['order_date'], -2);
        $order_date = date('jS', strtotime($value['order_date']));
        $cumulative_result +=$count;
        array_push($xArr1, $cumulative_result);
        array_push($yArr, $order_date);
        array_push($xArr, $count);
    }  
    
    $data['cumulative_result'] = $cumulative_result;
    $data['jsony'] = json_encode($yArr);
    $data['jsonx'] = str_replace('"', "", json_encode($xArr));
    $data['jsonx1'] = str_replace('"', "", json_encode($xArr1));
    $data['englishdate'] = $englishdate;
    $County = $this->session->userdata('county_name');
    $data['county'] = $County;
    $Countyid = $this->session->userdata('county_id');   
    $data['active_month'] = $month.$year;
    $data['content_view'] = "rtk/rtk/admin/admin_home";
    $data['banner_text'] = "RTK Manager";
    $data['title'] = "RTK Manager";
    $this->load->view('rtk/template', $data);
} 

public function rtk_manager_activity() {
    $month = $this->session->userdata('Month');
    if ($month == '') {
        $month = date('mY', time());
    }
    $year = substr($month, -4);
    $month = substr_replace($month, "", -4);
    $monthyear = $year . '-' . $month . '-1';

    $data['englishdate'] = $englishdate;
    $County = $this->session->userdata('county_name');
    $data['county'] = $County;
    $Countyid = $this->session->userdata('county_id');
    $data['user_logs'] = $this->rtk_logs();

    $data['active_month'] = $month.$year;
    $data['content_view'] = "rtk/rtk/admin/admin_logs";
    $data['banner_text'] = "RTK Manager";
    $data['title'] = "RTK Manager";
    $this->load->view('rtk/template', $data);
} 
public function rtk_manager_users() {
    $data['title'] = 'RTK Manager';
    $data['banner_text'] = 'RTK Manager';
    $data['content_view'] = "rtk/rtk/admin/admin_users";
    $users = $this->_get_rtk_users();        
    $data['users'] = $users;
    $this->load->view('rtk/template', $data);
}

public function rtk_manager_settings() {

            $sql = "select rtk_settings.*, user.fname,user.lname from rtk_settings, user where rtk_settings.user_id = user.id ";
            $res = $this->db->query($sql);
            $deadline_data = $res->result_array();

            $sql1 = "select * from rtk_alerts_reference ";
            $res1 = $this->db->query($sql1);
            $alerts_to_data = $res1->result_array();


            $sql3 = "select rtk_alerts.*,rtk_alerts_reference.id as ref_id,rtk_alerts_reference.description as description from rtk_alerts,rtk_alerts_reference where rtk_alerts.reference=rtk_alerts_reference.id order by id ASC,status ASC";
            $res3 = $this->db->query($sql3);
            $alerts_data = $res3->result_array();

            $sql4 = "select lab_commodities.*,lab_commodity_categories.category_name, lab_commodity_categories.id as cat_id from lab_commodities,lab_commodity_categories where lab_commodities.category=lab_commodity_categories.id and lab_commodity_categories.active = '1'";
            $res4 = $this->db->query($sql4);
            $commodities_data = $res4->result_array();

            $sql5 = "select * from lab_commodity_categories";
            $res5 = $this->db->query($sql5);
            $commodity_categories = $res5->result_array();


            $data['deadline_data'] = $deadline_data;
            $data['alerts_to_data'] = $alerts_to_data;
            $data['alerts_data'] = $alerts_data;
            $data['commodities_data'] = $commodities_data;
            $data['commodity_categories'] = $commodity_categories;

            $data['title'] = 'RTK Manager Settings';
            $data['banner_text'] = 'RTK Manager Settings';
        //$data['content_view'] = "rtk/admin/admin_home_view";
            $data['content_view'] = "rtk/rtk/admin/admin_settings";
            $users = $this->_get_rtk_users();
            $data['users'] = $users;
            $this->load->view('rtk/template', $data);
        }
public function rtk_manager_stocks($month=null) {
    if(isset($month)){           
        $year = substr($month, -4);
        $month = substr($month, 0,2);            
        $monthyear = $year . '-' . $month . '-1';         

    }else{
        $month = $this->session->userdata('Month');
        if ($month == '') {
            $month = date('mY', time());
        }
        $year = substr($month, -4);
        $month = substr_replace($month, "", -4);
        $monthyear = $year . '-' . $month . '-1';
    }
    
    $englishdate = date('F, Y', strtotime($monthyear));
    
    $data['stock_status'] = $this->_national_reports_sum($year, $month);           
    $data['englishdate'] = $englishdate;
    $County = $this->session->userdata('county_name');
    $data['county'] = $County;
    $Countyid = $this->session->userdata('county_id');   
    $data['active_month'] = $month.$year;
    $data['content_view'] = "rtk/rtk/admin/stocks_v";
    $data['banner_text'] = "RTK Manager";
    $data['title'] = "RTK Manager";
    $this->load->view('rtk/template', $data);
} 

        public function rtk_manager_messages() {

            $data['title'] = 'RTK Manager Messages';
            $data['banner_text'] = 'RTK Manager';        
            $data['content_view'] = "rtk/rtk/admin/admin_messages";        
            $this->load->view('rtk/template', $data);
        }

        public function rtk_send_message() {
            $receipient_id = mysql_real_escape_string($_POST['id']);
            $subject = mysql_real_escape_string($_POST['subject']);
            $raw_message = mysql_real_escape_string($_POST['message']);             
            $attach_file = null;
            //$bcc_email = 'ttunduny@gmail.com,tngugi@clintonhealthaccess.org,annchemu@gmail.com';
            //$bcc_email = '';
            $receipient = array();
            $month = date('mY');       
            if($receipient_id==1){
            //all users
                $sql = "SELECT email FROM user WHERE usertype_id in (0,7,8,11,13) and status=1 ORDER BY id DESC";
                $res = $this->db->query($sql)->result_array();                  
                //$to =array();
                $to ="";
                foreach ($res as $key => $value) {
                    $one = $value['email'];
                    $to.= $one.',';
                }       
                  
            }elseif($receipient_id==2){
            //All SCMLTs
                $sql = "SELECT email FROM user WHERE usertype_id = 7 and status = 1 ORDER BY id DESC";
                $res = $this->db->query($sql)->result_array();                                  
                $to ="";
                foreach ($res as $key => $value) {
                    $one = $value['email'];
                    $to.= $one.',';
                }             

            }elseif($receipient_id==3){
            //All CLCs
                $sql = "SELECT email FROM user WHERE usertype_id =13 and status =1 ORDER BY id DESC";
                $res = $this->db->query($sql)->result_array();                  
                $to =array();
                foreach ($res as  $value) {
                    $one = $value['email'];
                    array_push($to,$one);
                }          

            }elseif($receipient_id==4){
            //Sub C with more than 75% reporting
                $sql = "select distinct district_id from  rtk_district_percentage  where  month = '$month' and percentage > 75";
                $districts = $this->db->query($sql)->result_array();
                $emails = array();
                foreach ($districts as $value) {
                    $dist = $value['district_id'];
                    $q = "select email from user where district = $dist and usertype_id=7 and status = 1 order by id desc";
                    $res = $this->db->query($q)->result_array();      
                    array_push($emails, $res);
                }       
                foreach ($emails AS $key => $value) {
                    $new_emails[] = $value[0];
                }
                $to = array();
                foreach ($new_emails as $key => $value) {
                    $one = $value['email'];
                    array_push($to,$one);
                }              

            }elseif($receipient_id==5){
            //Sub C with less than 75% reporting            
                $sql = "select distinct district_id from  rtk_district_percentage  where  month = '$month' and percentage < 75";
                $districts = $this->db->query($sql)->result_array();
                $emails = array();
                foreach ($districts as $value) {
                    $dist = $value['district_id'];
                    $q = "select email from user where district = $dist and usertype_id=7 and status = 1 order by id desc";
                    $res = $this->db->query($q)->result_array();      
                    array_push($emails, $res);
                }       
                foreach ($emails AS $key => $value) {
                    $new_emails[] = $value[0];
                }
                $to = array();
                foreach ($new_emails as $key => $value) {
                    $one = $value['email'];
                    array_push($to,$one);
                }      
            }elseif($receipient_id==6){
            //Sub C with less than 50% reporting   
                $sql = "select distinct district_id from  rtk_district_percentage  where  month = '$month' and percentage  > 50";
                $districts = $this->db->query($sql)->result_array();
                $emails = array();
                foreach ($districts as $value) {
                    $dist = $value['district_id'];
                    $q = "select email from user where district = $dist and usertype_id=7 and status = 1 order by id desc";
                    $res = $this->db->query($q)->result_array();      
                    array_push($emails, $res);
                }       
                foreach ($emails AS $key => $value) {
                    $new_emails[] = $value[0];
                }
                $to = array();
                foreach ($new_emails as $key => $value) {
                    $one = $value['email'];
                    array_push($to,$one);
                }               

            }elseif($receipient_id==7){
            //Sub C with less than 25% reporting   
                $sql = "select distinct district_id from  rtk_district_percentage  where  month = '$month' and percentage > 25";
                $districts = $this->db->query($sql)->result_array();
                $emails = array();
                foreach ($districts as $value) {
                    $dist = $value['district_id'];
                    $q = "select email from user where district = $dist and usertype_id=7 and status = 1 order by id desc";
                    $res = $this->db->query($q)->result_array();      
                    array_push($emails, $res);
                }       
                foreach ($emails AS $key => $value) {
                    $new_emails[] = $value[0];
                }
                $to = array();
                foreach ($new_emails as $key => $value) {
                    $one = $value['email'];
                    array_push($to,$one);
                }               

            }elseif($receipient_id==8){
            //C with more than 75% reporting   
                $sql = "select distinct county_id from  rtk_county_percentage  where  month = '$month' and percentage  >75";
                $counties = $this->db->query($sql)->result_array();
                $emails = array();
                foreach ($counties as $value) {
                    $county = $value['county_id'];
                    $q = "select email from user where countyid = 'county' and usertype_id='13' and status = 1 order by id desc";
                    $res = $this->db->query($q)->result_array();      
                    array_push($emails, $res);
                }       
                foreach ($emails AS $key => $value) {
                    $new_emails[] = $value[0];
                }
                $to = array();
                foreach ($new_emails as $key => $value) {
                    $one = $value['email'];
                    array_push($to,$one);
                }                

            }elseif($receipient_id==9){
            //C with less than 75% reporting
                $sql = "select distinct county_id from  rtk_county_percentage  where  month = '$month' and percentage <75";
                $counties = $this->db->query($sql)->result_array();
                $emails = array();
                foreach ($counties as $value) {
                    $county = $value['county_id'];
                    $q = "select email from user where countyid = 'county' and usertype_id='13' and status = 1 order by id desc";
                    $res = $this->db->query($q)->result_array();      
                    array_push($emails, $res);
                }       
                foreach ($emails AS $key => $value) {
                    $new_emails[] = $value[0];
                }
                $to = array();
                foreach ($new_emails as $key => $value) {
                    $one = $value['email'];
                    array_push($to,$one);
                }               
            }elseif($receipient_id==10){
            //C with less than 50% reporting 
                $sql = "select distinct county_id from  rtk_county_percentage  where  month = '$month' and percentage  <50";
                $counties = $this->db->query($sql)->result_array();
                $emails = array();
                foreach ($counties as $value) {
                    $county = $value['county_id'];
                    $q = "select email from user where countyid = 'county' and usertype_id='13' and status = 1 order by id desc";
                    $res = $this->db->query($q)->result_array();      
                    array_push($emails, $res);
                }       
                foreach ($emails AS $key => $value) {
                    $new_emails[] = $value[0];
                }
                $to = array();
                foreach ($new_emails as $key => $value) {
                    $one = $value['email'];
                    array_push($to,$one);
                }                    
            }elseif($receipient_id==11){
            //C with less than 25% reporting  
               $sql = "select distinct county_id from  rtk_county_percentage  where  month = '$month' and percentage  >75";
               $counties = $this->db->query($sql)->result_array();
               $emails = array();
               foreach ($counties as $value) {
                $county = $value['county_id'];
                $q = "select email from user where countyid = 'county' and usertype_id='13' and status = 1 order by id desc";
                $res = $this->db->query($q)->result_array();      
                array_push($emails, $res);
            }       
            foreach ($emails AS $key => $value) {
                $new_emails[] = $value[0];
            }
            $to = array();
            foreach ($new_emails as $key => $value) {
                $one = $value['email'];
                array_push($to,$one);
            }          
        }elseif($receipient_id==12){
            //C with 0% reporting 
            $sql = "select distinct county_id from  rtk_county_percentage  where  month = '$month' and percentage  = 0";
            $districts = $this->db->query($sql)->result_array();
            $emails = array();
            foreach ($districts as $value) {
                $county = $value['county_id'];
                $q = "select email from  user where user.county.id = '$county' and user.usertype_id = '13' and user.status = '1' order by user.id asc";
                $res = $this->db->query($q)->result_array();      
                array_push($emails, $res);
            }       
            foreach ($emails AS $key => $value) {
                $new_emails[] = $value[0];
            }
            $to = array();
            foreach ($new_emails as $key => $value) {
                $one = $value['email'];
                array_push($to,$one);
            }                 
            
        }elseif($receipient_id==13){
            //Sub C with 0% reporting   
            $sql = "select distinct district_id from  rtk_district_percentage  where  month = '$month' and percentage  = 0";
            $districts = $this->db->query($sql)->result_array();
            $emails = array();
            foreach ($districts as $value) {
                $dist = $value['district_id'];
                $q = "select email from user where district = $dist and usertype_id=7 and status = 1 order by id desc";
                $res = $this->db->query($q)->result_array();      
                array_push($emails, $res);
            }       
            foreach ($emails AS $key => $value) {
                $new_emails[] = $value[0];
            }
            $to = array();
            foreach ($new_emails as $key => $value) {
                $one = $value['email'];
                array_push($to,$one);
            }               
            
        }

        //echo "$to";die();
        //$msg = $this->trigger_emails($message);

        //Convert to get individual emails in a format suitable for sending
        //echo nl2br($desc);
       // $message = nl2br($raw_message);
       $message = str_replace(array('\\n', "\r", "\n"), "<br />", $raw_message);
       //  //$message = str_replace('\\n', '', $raw_message); 
       //  echo "$message";die();          
       //  //$receipient = 'ttunduny@gmail.com';        
       //  //$receipient = implode($to, ',');     
       //  //echo "$bcc_email";die();
        
       $bcc_email = 'ttunduny@gmail.com,tngugi@clintonhealthaccess.org,annchemu@gmail.com';
       // // $receipient = array();
       // // $receipient =$to; 

       //  //parse_str($_POST['receipients'], $receipient);
       //  //$receipient = $receipient['hidden-receipients'];
        // include 'rtk_mailer.php';
        // $newmail = new rtk_mailer();
        // $response = $newmail->send_email($to, $message, $subject, null, $bcc_email);
        
        echo $msg;
    }
    function user_details($user = NULL){
        $conditions = '';
        $conditions = (isset($user)) ? $conditions . " AND user.id = $user" : $conditions . ' ';

        $sql = "select * from user, access_level where user.usertype_id = access_level.id and access_level.id BETWEEN 7 AND  13 $conditions";
        $res = $this->db->query($sql);
        $returnable = $res->result_array();
        if ($res->num_rows()<1){ 
            echo "user does not exist";}
        else {
                $main_county = $this->db->query("select counties.id as county_id, counties.county from  counties, user where   user.county_id = counties.id AND user.id = $user");
                $user_county = $main_county->result_array();
                $rca_res = $this->db->query("select counties.id as county_id, counties.county from counties where counties.id in (select rca_county.county from rca_county where rca_county.rca = $user)");
                $other_counties = $rca_res->result_array();
                $counties = array_merge($user_county,$other_counties);       
                array_push($returnable, $counties);

                $main_dist = $this->db->query("select districts.id as district_id, districts.district  from  districts, user where  user.district = districts.id AND user.id = $user");
                $user_dist = $main_dist->result_array();
                $other_dist = $this->db->query("select districts.id as district_id, districts.district from districts where districts.id in (select dmlt_districts.district from dmlt_districts where dmlt_districts.dmlt = $user)");
                $other_districts = $other_dist->result_array();
                $districts = array_merge($user_dist,$other_districts);
                
                array_push($returnable, $districts);
                return $returnable;}

    }
    function user_profile($user_id){
        $user_details = $this->user_details($user_id);
//        echo "<pre>";print_r($user_details);die;
        $full_name = $arr[0]['fname'].' '.$user_details[0]['lname'];
        $data['all_counties'] = $this->all_counties();
        $data['all_subcounties'] = $this->all_districts();

        $data['user_logs'] = $this->rtk_logs($user_id);
        $data['user_id'] = $user_id;
        $data['full_name'] = $full_name;
        $data['user_details'] = $user_details;
        $data['title'] = 'User Profile : '.$full_name;
        $data['banner_text'] = 'User Profile : '.$full_name;
        $data['content_view'] = 'rtk/rtk/admin/user_profile_view';

        $this->load->view('rtk/template',$data);

    }

    function all_counties(){
        $counties = $this->db ->query("select * from counties");
        return ($counties->result_array());
    }
    function all_districts(){
        $districts = $this->db ->query("select * from districts");
        return ($districts->result_array());
    }

    public function dmlt_district_action1() {
        $action = $_POST['action'];
        $dmlt = $_POST['dmlt_id'];
        $district = $_POST['dmlt_district'];

        if ($action == 'add') {
            $this->_add_dmlt_to_district($dmlt, $district);
        } elseif ($action == 'remove') {
            $this->_remove_dmlt_from_district($dmlt, $district);
        }
        //echo "Sub-County Added Successfully";
       redirect('rtk_management/user_profile/'.$dmlt);
    }

     public function add_rca_to_county() {

        $rca = $_POST['rca_id'];

        $county = $_POST['county'];       
        $this->_add_rca_to_county($rca, $county);
        redirect('rtk_management/user_profile/'.$rca);
    }
    function _add_rca_to_county($rca, $county, $redirect_url) {
        $sql = "INSERT INTO `rca_county` (`id`, `rca`, `county`) VALUES (NULL, '$rca', '$county')";
        $this->db->query($sql);
        $object_id = $this->db->insert_id();
        $this->logData('1', $object_id);
    }


    ///Allocation Functions
    public function allocation_home() {

        $data['zone_a_stats'] = $this->zone_allocation_stats('a');
        $data['zone_b_stats'] = $this->zone_allocation_stats('b');
        $data['zone_c_stats'] = $this->zone_allocation_stats('c');
        $data['zone_d_stats'] = $this->zone_allocation_stats('d');

        $data['banner_text'] = 'National';
        $data['content_view'] = 'rtk/rtk/allocation/allocation_home_view';
        $data['title'] = 'National Summary: ';
        $this->load->view("rtk/template", $data);
    }
    function zone_allocation_stats($zone) {

        $last_allocation_sql = "SELECT lab_commodity_details.allocated_date 
        FROM facilities, lab_commodity_details
        WHERE facilities.facility_code = lab_commodity_details.facility_code
        AND lab_commodity_details.commodity_id
        BETWEEN 1 
        AND 3 
        AND facilities.Zone =  'Zone $zone'
        AND lab_commodity_details.allocated >0
        ORDER BY  `lab_commodity_details`.`allocated_date` DESC 
        LIMIT 0,1";

        $last_allocation_res = $this->db->query($last_allocation_sql);
        $last_allocation = $last_allocation_res->result_array();

        $last_allocation_date = $last_allocation[0]['allocated_date'];

        $three_months_ago = date("Y-m-", strtotime("-3 Month "));
        $three_months_ago .='1';

        $total_facilities_sql = "SELECT count(*) as total_facilities
        FROM facilities, districts, counties
        WHERE facilities.district = districts.id
        AND districts.county = counties.id
        AND facilities.zone = 'Zone $zone' 
        AND facilities.rtk_enabled =1";

        $res = $this->db->query($total_facilities_sql);
        $total_facilities_res = $res->result_array();
        $total_facilities = $total_facilities_res[0]['total_facilities'];

        $sql1 = "SELECT count(DISTINCT facilities.facility_code) as facilities_allocated
        FROM lab_commodity_orders, lab_commodity_details, facilities, counties, districts
        WHERE lab_commodity_orders.id = lab_commodity_details.order_id
        AND districts.county = counties.id
        AND lab_commodity_details.allocated > 0
        AND facilities.zone = 'Zone $zone'
        AND districts.id = facilities.district
        AND facilities.facility_code = lab_commodity_orders.facility_code
        AND facilities.rtk_enabled = 1
        AND lab_commodity_orders.order_date BETWEEN  '$three_months_ago'AND  NOW()";

        $res = $this->db->query($sql1);
        $facilities_allocated = $res->result_array();

        $facilities_allocated = $facilities_allocated[0]['facilities_allocated'];
        $allocation_percentage = $facilities_allocated / $total_facilities * 100;
        $allocation_percentage = number_format($allocation_percentage, $decimals = 0);

        $facilities_allocated;
        $zone_stats = array(
            'total_facilities' => $total_facilities,
            'facilities_allocated' => $facilities_allocated,
            'allocation_percentage' => $allocation_percentage,
            'last_allocation' => $last_allocation_date
        );
        return $zone_stats;
    }

    public function allocation_zone($zone = null) {
        if (!isset($zone)) {
            redirect('rtk_management/allocation_home');
        }
        $data['counties_in_zone'] = $this->_zone_counties($zone);
        $data['banner_text'] = 'National';
        $data['content_view'] = 'rtk/rtk/allocation/allocation_zone_view';
        $data['title'] = 'National Summary: ';
        $this->load->view("rtk/template", $data);
    }
    function _zone_counties($zone) {
        $returnable = array();
        $sql = "select Distinct counties.county, counties.id
        FROM  facilities,counties,districts
        WHERE  facilities.Zone = 'Zone $zone'
        AND facilities.district = districts.id
        AND districts.county = counties.id
        order by counties.county";

        $res = $this->db->query($sql);
        foreach ($res->result_array() as $value) {
            $allocation_stats = $this->_county_allocation_stats($value['id']);

            array_push($allocation_stats, $value['county']);
            array_push($allocation_stats, $value['id']);
            array_push($returnable, $allocation_stats);
        }

        return $returnable;
    }
    private function _county_allocation_stats($county) {
        /*
         * We'd like to know the county allocation status for the month
         */

        // Total Facilities in the county

        $three_months_ago = date("Y-m-", strtotime("-3 Month "));
        $three_months_ago .='1';
        $sql = "SELECT count(*) as total_facilities
        FROM facilities, districts, counties
        WHERE facilities.district = districts.id
        AND districts.county = counties.id
        AND counties.id = $county
        AND facilities.rtk_enabled =1";

        $sql1 = "SELECT count(DISTINCT facilities.facility_code) as facilities_allocated,
        lab_commodity_details.allocated_date as last_allocation
        FROM lab_commodity_orders, lab_commodity_details, facilities, counties, districts
        WHERE lab_commodity_orders.id = lab_commodity_details.order_id
        AND districts.county = counties.id
        AND counties.id = $county
        AND lab_commodity_details.allocated > 0
        AND districts.id = facilities.district
        AND facilities.facility_code = lab_commodity_orders.facility_code
        AND facilities.rtk_enabled = 1
        AND lab_commodity_orders.order_date BETWEEN  '$three_months_ago'AND  NOW()
        ORDER BY lab_commodity_details.allocated_date DESC";

        $res = $this->db->query($sql1);
        $facilities_allocated = $res->result_array();
        $last_allocation = $facilities_allocated[0]['last_allocation'];
        $facilities_allocated = $facilities_allocated[0]['facilities_allocated'];

        $res1 = $this->db->query($sql);
        $total_facilities = $res1->result_array();
        $total_facilities = $total_facilities[0]['total_facilities'];
        $allocation_percentage = $facilities_allocated / $total_facilities * 100;
        $allocation_percentage = number_format($allocation_percentage, 0);

        $returnable = array('facilities' => $total_facilities,
            'allocated_facilities' => $facilities_allocated,
            'allocation_percentage' => $allocation_percentage,
            'last_allocation' => $last_allocation);
        return $returnable;
    }

    public function allocation_county_detail_zoom($county_id) {
        $ish;
        $county = counties::get_county_name($county_id);
        $county_name = Counties::get_county_name($county_id);
        $data['countyname'] =$county_name['county'];

        $htm = '';
        $table_body = '';

        $districts_in_county = districts::getDistrict($county_id);
        $data['districts_in_county'] = $districts_in_county;
        $htm .= '<ul class="facility-list">';
        foreach ($districts_in_county as $key => $district_arr)
            $district = $district_arr['id'];
        $district_name = $district_arr['district'];
        $htm .= '<li>' . $district_name . '</li>';
        $htm .= '<ul class="sub-list">';
     
        $three_months_ago = date("Y-m-", strtotime("-1 Month"));
        $three_months_ago .='1';

        //$beg_date = date('Y-m-d', strtotime("first day of this Month"));
        $beg_date = date('Y-m', strtotime("-1 Month"));
        $beg_date.='-01';
        $end_date = date('Y-m-d', strtotime("last day of previous Month"));



        $sql = "SELECT facilities.facility_code,lab_commodity_details.id, lab_commodity_details.q_requested, lab_commodity_details.q_received,lab_commodity_details.commodity_id,lab_commodity_details.closing_stock,lab_commodity_details.beginning_bal,
        facilities.facility_name,lab_commodity_details.allocated,lab_commodity_details.q_used,districts.district,facility_amc.amc,lab_commodities.commodity_name,lab_commodities.unit_of_issue
        FROM facilities, districts, counties,lab_commodity_orders,lab_commodity_details,facility_amc,lab_commodities
        WHERE facilities.district = districts.id
        AND facilities.rtk_enabled = 1
        AND facilities.facility_code = facility_amc.facility_code
        AND facility_amc.commodity_id = lab_commodity_details.commodity_id
        AND counties.id = districts.county
        AND counties.id = $county_id
        AND lab_commodity_orders.facility_code = facilities.facility_code
        AND lab_commodity_orders.id = lab_commodity_details.order_id
        AND lab_commodity_details.commodity_id = lab_commodities.id
        AND lab_commodity_details.commodity_id BETWEEN 0 AND 6
        AND lab_commodity_orders.order_date BETWEEN '$beg_date' AND '$end_date'
        ORDER BY districts.district,facilities.facility_code  ASC,lab_commodity_details.commodity_id ASC ";
        $orders = $this->db->query($sql);
       //echo "<pre>";print_r($orders->result_array());die;
        foreach ($orders->result_array() as $orders_arr) {
            $algorithm = ($orders_arr['commodity_id'] < 4) ? "Old-Algorithm" : 'New-Algorithm';

            $order_detail_id = $orders_arr['id'];
            $q_requested = $orders_arr['q_requested'];
            $q_received = $orders_arr['q_received'];
            $commodity_id = $orders_arr['commodity_id'];
            $closing_stock = $orders_arr['closing_stock'];
            $q_used = $orders_arr['q_used'];
            $beginning_bal = $orders_arr['beginning_bal'];
            $facility_code = $orders_arr['facility_code'];
            $facility_name = $orders_arr['facility_name'];
            $allocated = $orders_arr['allocated'];
            $district_name = $orders_arr['district'];
            $commodity = $orders_arr['commodity_name'];
            $unit_of_issue = $orders_arr['unit_of_issue'];
            //$allocation = '<span class="label label-important">Pending Allocation for  ' . $lastmonth . '</span>';


            $commodity_id = $orders_arr['commodity_id'];
            $amc = $orders_arr['amc'];
            $amc_4month = $amc * 4;
            $firstday = date('D dS M Y', strtotime("first day of previous month"));
            $lastday = date('D dS M Y', strtotime("last day of previous month"));
            $lastmonth = date('F', strtotime("last day of previous month"));
            $allocation = '';
            if ($allocated > 0) {
                $allocation = '<span class="label label-success">Allocated for  ' . $lastmonth . '</span>';
            } else {
                $allocation = '<span class="label label-important">Pending Allocation for  ' . $lastmonth . '</span>';
                $allocated = ($amc_4month / $unit_of_issue);
                $allocated = ceil($allocated);
            }
            $table_body .= "
            <tr id=''>
            <input type='hidden' name='$order_detail_id' value='$order_detail_id' />
            <td>$facility_code</td>
            <td>$facility_name</td>
            <td>$district_name</td>
            <td>$commodity <br/>$algorithm</td>
            <td>$q_received</td>
            <td>$q_used</td>
            <td>$closing_stock</td>
            <td>$q_requested</td>
            <td>$amc</td>
            <td><input type='text' class='user2' name='allocated_$order_detail_id' value='$allocated'/></td>
            <td>$q_received</td>
            <td>$allocation</td>
            </tr>";
        }
//            echo"<table>$table_body</table";die;
        //  $data['content_view'] = 'allocation_committee/ajax_view/rtk_county_allocation_v';
        $data['county_id'] = $county_id;
        $data['table_body'] = $table_body;
        $data['title'] = "County View";
//        $data['table_data'] = $this->rtk_county_sidebar();
        $data['banner_text'] = "Allocate " . $county_name['county'];
        $data['content_view'] = "rtk/allocation_committee/ajax_view/rtk_county_allocation_datatableonly_v";
        $this->load->view("rtk/template", $data);
    }

    function county_allocation($county_id) {
        $county = Counties::get_county_name($county_id);
        $countyname = $county['county'];
        $data['county_name'] = $countyname;
        $data['banner_text'] = "Allocations in " . $countyname;
        $data['title'] = $countyname . " County RTK Allocations";
        $data['content_view'] = "rtk/allocation_committee/ajax_view/county_allocations_v";
        $data['county_allocation'] = $this->_allocation_county($county_id);

        $this->load->view("rtk/template", $data);
    }

    public function national_rtk_allocation() {
        $data['title'] = "National RTK Allocations";
        $data['banner_text'] = "National RTK Allocations";
        $data['title'] = " National RTK Allocations";
        $data['content_view'] = "rtk/allocation_committee/national_rtk_allocations";
        $data['allocations'] = $this->allocation();

        $this->load->view("rtk/template", $data);
    }
    function _allocation_county($county_id) {
        $three_months_ago = date("Y-m-", strtotime("-3 Month"));
        $three_months_ago .='1';

        $beg_date = date('Y-m-d', strtotime("first day of previous month"));
        $end_date = date('Y-m-d', strtotime("last day of previous month"));

        $sql = "SELECT DISTINCT facilities.facility_code,lab_commodity_details.id, lab_commodity_details.q_requested, lab_commodity_details.q_received,lab_commodity_details.commodity_id,lab_commodity_details.closing_stock,lab_commodity_details.beginning_bal,lab_commodity_details.allocated_date,
        facilities.facility_name,lab_commodity_details.allocated,lab_commodity_details.q_used,districts.district,facility_amc.amc,lab_commodities.commodity_name,lab_commodity_orders.order_date
        FROM facilities, districts, counties,lab_commodity_orders,lab_commodity_details,facility_amc,lab_commodities
        WHERE facilities.district = districts.id
        AND facilities.rtk_enabled = 1
        AND facilities.facility_code = facility_amc.facility_code
        AND facility_amc.commodity_id = lab_commodity_details.commodity_id
        AND counties.id = districts.county
        AND counties.id = $county_id
        AND lab_commodity_details.allocated > 1
        AND lab_commodity_orders.facility_code = facilities.facility_code
        AND lab_commodity_orders.id = lab_commodity_details.order_id
        AND lab_commodity_details.commodity_id = lab_commodities.id
        AND lab_commodity_details.commodity_id BETWEEN 0 AND 3
        AND lab_commodity_orders.order_date BETWEEN '$three_months_ago' AND NOW()
        ORDER BY  lab_commodity_orders.order_date, districts.district,facilities.facility_code ASC";

        $res = $this->db->query($sql);

        return $res->result_array();
    }



    public function trigger_emails() {
        $subject = 'RTK DATA VALIDITY';
        $message = "Dear All,<br/>We would like to bring to your notice the following changes to the system:<br/><ol>
        <li>The autocalculating feature for the Screening - Determine has been removed, and you will be required to type in the values, the only calculation done is the ending balance</li>
        <li>Please enter the begining balances of all commmodities as per the FCDRR where there is a zero (to enable data validity)</li>
        <li>Where there are losses, positive adjustments and/or negative adjustments, please ensure that you fill out the explanation for the same, otherwise you wil not be able to save the report</li></ol><br/>
        All these changes have been made in order to ensure that the system will serve you better.<br/>
        Please use the remaining time to ensure that all the reports submitted for this month fulfil the above requirements. <br/>
        Use the edit link on the orders page to edit the reports.<br/>
        <b>With Regards,<br/>Titus Tunduny</b><br/>
        for: The RTK Development Team<br/>";
        $attach_file = null;
        $bcc_email = 'ttunduny@gmail.com';
        include 'rtk_mailer.php';

        $sql = "select distinct email from user where usertype_id='7' and status =1";
        $res = $this->db->query($sql)->result_array();
        $count = count($res);
        $a = 0;
        $b = 99;

        for ($i=$a; $i < $b ; $i++) {             
            $sql1 = "select distinct email from user where usertype_id='7' and status =1 limit $a,$b";
            $res1 = $this->db->query($sql1)->result_array();
            $to ="";
            foreach ($res1 as $key => $value) {
                $one = $value['email'];
                $to.= $one.',';
            }
            $newmail = new rtk_mailer();
            $response = $newmail->send_email('titus.tunduny@strathmore.edu', $message, $subject, $attach_file, $bcc_email); 
            $a = $b;
            if($b<$count){
                $b+=99;
            }else{
                break;
            }           

        }  
        // $sql = "select distinct email from user where usertype_id='7' and status =1";
        // $res = $this->db->query($sql)->result_array();

        // $subject = 'RTK DATA VALIDITY';
        // $message = "Dear All,<br/>We would like to bring to your notice the following changes to the system:<br/><ol>
        // <li>The autocalculating feature for the Screening - Determine has been removed, and you will be required to type in the values, the only calculation done is the ending balance</li>
        // <li>Please enter the begining balances of all commmodities as per the FCDRR where there is a zero (to enable data validity)</li>
        // <li>Where there are losses, positive adjustments and/or negative adjustments, please ensure that you fill out the explanation for the same, otherwise you wil not be able to save the report</li></ol><br/>
        // All these changes have been made in order to ensure that the system will serve you better.<br/>
        // Please use the remaining time to ensure that all the reports submitted for this month fulfil the above requirements. <br/>
        // Use the edit link on the orders page to edit the reports.<br/>
        // <b>With Regards,<br/>Titus Tunduny</b><br/>
        // for: The RTK Development Team<br/>";

        // $attach_file = null;
        // $bcc_email = 'ttunduny@gmail.com';
        // include 'rtk_mailer.php';
        // $to ="";
        // foreach ($res as $key => $value) {
        //     $one = $value['email'];
        //     $to.= $one.',';
        // }       
        //$max = count($res);
        // $a = 0;
        // $b = 100;
        // for ($i=$a; $i < $b ; $i++) {             
        //     $one = $res[$i]['email'];
        //     $to.= $one.',';            
        //     $newmail = new rtk_mailer();
        //     $response = $newmail->send_email('titus.tunduny@strathmore.edu', $message, $subject, $attach_file, $bcc_email);
        //     }  
        // }        
        
        
        
        //$sql = "INSERT INTO `rtk_messages`(`id`, `sender`, `subject`, `message`, `receipient`, `state`) VALUES (NULL,'$sender','$subject','$message','$receipient','0')";
        //$this->db->query($sql);
        //$object_id = $this->db->insert_id();
       // $this->logData('23', $object_id);
        echo "Email Sent";
    }



///////Partner Account
    public function partner_home() {
        $lastday = date('Y-m-d', strtotime("last day of previous month"));
        $countyid = $this->session->userdata('county_id');
        $partner_id = $this->session->userdata('partner_id');  		
        $districts = districts::getDistrict($countyid);
        $county_name = counties::get_county_name($countyid);
        $County = $county_name[0]['county'];

        $reports = array();
        $tdata = ' ';
        foreach ($districts as $value) {
            $q = $this->db->query('SELECT lab_commodity_orders.id, lab_commodity_orders.facility_code, lab_commodity_orders.compiled_by, lab_commodity_orders.order_date, lab_commodity_orders.district_id, districts.id as distid, districts.district, facilities.facility_name, facilities.facility_code FROM districts, lab_commodity_orders, facilities WHERE lab_commodity_orders.district_id = districts.id AND facilities.facility_code = lab_commodity_orders.facility_code AND districts.id = ' . $value['id'] . '');
            $res = $q->result_array();
            foreach ($res as $values) {
                date_default_timezone_set('EUROPE/Moscow');
                $order_date = date('F', strtotime($values['order_date']));
                $tdata .= '<tr>
                <td>' . $order_date . '</td>
                <td>' . $values['facility_code'] . '</td>
                <td>' . $values['facility_name'] . '</td>
                <td>' . $values['district'] . '</td>
                <td>' . $values['order_date'] . '</td>
                <td>' . $values['compiled_by'] . '</td>
                <td><a href="' . base_url() . 'rtk_management/lab_order_details/' . $values['id'] . '">View</a></td>
                <tr>';
            }
            if (count($res) > 0) {
                array_push($reports, $res);
            }
        }
        $month = $this->session->userdata('Month');
        if ($month == '') {
            $month = date('mY', strtotime('-1 month'));
        }

        $year = substr($month, -4);
        $month = substr_replace($month, "", -4);


        $monthyear = $year . '-' . $month . '-1';
        $englishdate = date('F, Y', strtotime($monthyear));
        $data['graphdata'] = $this->partner_reporting_percentages($partner_id, $year, $month);
        //$data['county_summary'] = $this->_requested_vs_allocated($year, $month, $countyid);
        $data['tdata'] = $tdata;
        $data['county'] = $County;
        $data['title'] = 'RTK Partner';
        $data['banner_text'] = 'RTK Partner';
        $data['content_view'] = "rtk/rtk/partner/partner_dashboard";
        $this->load->view("rtk/template", $data);
    }
    public function partner_facilities() {
        $lastday = date('Y-m-d', strtotime("last day of previous month"));        
        $partner_id = $this->session->userdata('partner_id');                        
        $sql = "select distinct counties.id, counties.county from  counties, facilities, districts where
            facilities.district = districts.id and facilities.partner = '$partner_id'  and districts.county = counties.id
             and facilities.rtk_enabled = '1'";
        $res_counties = $this->db->query($sql)->result_array();        
        $table_data_district = array();
        $table_data_facilities = array();
        //$res_district = $this->_districts_in_county($county);
        
        $count_counties = count($res_counties);
        $count = count($res_counties);       
        for ($i = 0; $i < $count; $i++) {
            $county_id = $res_counties[$i]['id'];
            $sql1 = "select count(distinct facilities.id) as facilities, count(distinct districts.id) as districts
            from  counties,  facilities,  districts where  
                        facilities.district = districts.id  and facilities.partner = '$partner_id'
                        and districts.county = counties.id and facilities.rtk_enabled = '1'
                         and counties.id = '$county_id'";
            // echo "sql1<br/>";
            $res = $this->db->query($sql1)->result_array();
      
            foreach ($res as $key => $value) {
                $facilities_count  = $value['facilities'];
                $district_count  = $value['districts'];
                array_push($table_data_facilities, $facilities_count);
                array_push($table_data_district, $district_count);
            }            
            
        }

        $data['counties_list'] = $res_counties;
        $data['facilities_count'] = $table_data_facilities; 
        $data['districts_count'] = $table_data_district;        
        $data['title'] = 'RTK County Admin';
        $data['banner_text'] = "RTK County Admin: Counties in Aphia Rift Region";
        $data['content_view'] = "rtk/rtk/partner/districts_v";
        $this->load->view("rtk/template", $data);        
    }
    public function partner_commodity_usage() {
		$partner = $this->session->userdata('partner_id');          
        $commodity = $this->session->userdata('commodity_id');          
        if($commodity!=''){
            $commodity_id = $commodity;
            $sql = "SELECT lab_commodities.commodity_name FROM lab_commodities WHERE lab_commodities.id =$commodity_id";
            $q = $this->db->query($sql);
            $res = $q->result_array();
            foreach ($res as $values) {               
                $commodity_name = $values['commodity_name'];
            }
        }else{

            $sql = "SELECT lab_commodities.id,lab_commodities.commodity_name FROM lab_commodities,lab_commodity_categories WHERE lab_commodities.category = lab_commodity_categories.id AND lab_commodity_categories.active = '1' limit 0,1";
            $q = $this->db->query($sql);
            $res = $q->result_array();
            foreach ($res as $values) {
                $commodity_id = $values['id'];
                $commodity_name = $values['commodity_name'];
            }
        }
        //echo "$commodity_id";die();
        $lastday = date('Y-m-d', strtotime("last day of previous month"));
        $countyid = $this->session->userdata('county_id');
        $districts = districts::getDistrict($countyid);
        $county_name = counties::get_county_name($countyid);
        $County = $county_name[0]['county'];

        $reports = array();
        
        $month = $this->session->userdata('Month');
        if ($month == '') {
            $month = date('mY', strtotime('-1 month'));
        }

        $year = substr($month, -4);
        $month = substr_replace($month, "", -4);


        $monthyear = $year . '-' . $month . '-1';
        $englishdate = date('F, Y', strtotime($monthyear));
        $data['graphdata'] = $this->partner_commodity_percentages($partner, $commodity_id, $month);
         // echo "<pre>"; print_r($data['graphdata']);die;
        //$data['county_summary'] = $this->_requested_vs_allocated($year, $month, $countyid);
        $data['tdata'] = $tdata;
        $data['county'] = $County;
        $data['commodity_name'] = $commodity_name;
        $data['title'] = 'RTK Partner';
        $data['banner_text'] = 'RTK Partner Commodity Usage';
        $data['content_view'] = "rtk/rtk/partner/commodity_usage";
        $this->load->view("rtk/template", $data);

    }
    function partner_reporting_percentages($partner, $year, $month) {    
        $q = 'SELECT 
                count(lab_commodity_orders.id) as total,
                extract(YEAR_MONTH FROM lab_commodity_orders.order_date) as current_month,
                facilities.partner,
                facilities.facility_code
            FROM
                lab_commodity_orders,
                facilities
            WHERE
                facilities.facility_code = lab_commodity_orders.facility_code
                    AND facilities.partner = 7
            group by extract(YEAR_MONTH FROM lab_commodity_orders.order_date)';
        $query = $this->db->query($q);

        $sql = $this->db->select('count(id) as county_facility')->get_where('facilities', array('partner' =>$partner))->result_array();
        foreach ($sql as $key => $value) {
           $facilities = intval($value['county_facility']);
        }
       

        $month = array();
        $reported = array();
        $nonreported = array();
        $reported_value = array();
        $nonreported_value = array();
        foreach ($query->result_array() as $val) {
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);
            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            //$percentage_reported = $this->district_reporting_percentages($val['district_id'], $year, $month);
            $reports = intval($val['total']);
            $percentage_reported = round((($reports/$facilities)*100),0);
            if ($percentage_reported > 100) {
                $percentage_reported = 100;
            }
            $unreported = $facilities - $reports;
            array_push($reported, $percentage_reported);
            array_push($nonreported_value, $unreported);
            array_push($reported_value, $reports);

            $percentage_non_reported = 100 - $percentage_reported;
            array_push($nonreported, $percentage_non_reported);
        }

        

        $month_array = json_encode($month);
        $reported = json_encode($reported);
        $reported_value = json_encode($reported_value);
        $nonreported_value = json_encode($nonreported_value);
        $nonreported = json_encode($nonreported);

        $data['month'] = $month_array;
        $data['reported'] = $reported;
        $data['nonreported'] = $nonreported;
        $data['reported_value'] = $reported_value;
        $data['nonreported_value'] = $nonreported_value;
        //        $this->load->view('rtk/rtk/rca/county_reporting_view', $data);
        return $data;
    }    
function partner_commodity_percentages($partner, $commodity, $month) {  
        //$q = 'select extract(YEAR_MONTH from lab_commodity_details.created_at)as current_month, lab_commodity_details.commodity_id, lab_commodity_details.q_requested, lab_commodity_details.beginning_bal,lab_commodity_details.q_received,lab_commodity_details.no_of_tests_done,lab_commodity_details.losses,lab_commodity_details.closing_stock,lab_commodity_details.q_received, facilities.partner from facilities, lab_commodity_details where facilities.partner = 7 group by extract(YEAR_MONTH from lab_commodity_details.created_at) ';
        $q = "
        select 
    extract(YEAR_MONTH from lab_commodity_details.created_at) as current_month,
    lab_commodity_details.commodity_id,
    lab_commodity_details.q_requested,
    lab_commodity_details.beginning_bal,
    lab_commodity_details.q_received,
    lab_commodity_details.no_of_tests_done,
    lab_commodity_details.losses,
    lab_commodity_details.closing_stock,
    lab_commodity_details.q_received,
    facilities.partner
from
    facilities,
    lab_commodity_details,
    lab_commodities
where
    facilities.partner = '$partner'
        and lab_commodity_details.facility_code = facilities.facility_code
        and lab_commodity_details.commodity_id = lab_commodities.id
        AND lab_commodities.id ='$commodity'
group by extract(YEAR_MONTH from lab_commodity_details.created_at)";
        $query = $this->db->query($q)->result_array();
        // echo "<pre>";print_r($query);die;

        // $sql = $this->db->select('count(id) as county_facility')->get_where('facilities', array('partner' =>7))->result_array();
        // foreach ($sql as $key => $value) {
        //    $facilities = intval($value['county_facility']);
        // }
       

        $month = array();
        $beginning_bal = array();
        $qty_received = array();;
        $total_tests = array();
        $losses = array();
        $ending_bal = array();
        $qty_requested = array();
        $month_array = array();
        $beginning_bal_array = array();
        $qty_received_array = array();
        $total_tests_array = array();
        $losses_array = array();
        $ending_bal_array = array();
        $qty_requested_array = array();


        foreach ($query as $val) {
            //echo intval($val['current_month']);die();
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);
            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            array_push($beginning_bal, intval($val['beginning_bal']));
            array_push($qty_received, intval($val['q_received']));
            array_push($total_tests, intval($val['no_of_tests_done']));
            array_push($losses, intval($val['losses']));
            array_push($ending_bal, intval($val['closing_stock']));
            array_push($qty_requested, intval($val['q_requested']));
            //$percentage_reported = $this->district_reporting_percentages($val['district_id'], $year, $month);
            
           

            // array_push($month_array, $month);
            // array_push($beginning_bal_array, $beginning_bal);
            // array_push($qty_received_array, $qty_received);
            // array_push($total_tests_array, $total_tests);
            // array_push($losses_array, $losses);
            // array_push($ending_bal_array, $ending_bal);
            // array_push($qty_requested_array, $qty_requested);
        }
       

        $month_data = json_encode($month);
        $beginning_bal_data = json_encode($beginning_bal);
        $qty_received_data = json_encode($qty_received);
        $total_tests_data = json_encode($total_tests);
        $losses_data = json_encode($losses);
        $ending_bal_data = json_encode($ending_bal);
        $qty_requested_data = json_encode($qty_requested);

        $data['month'] = $month_data;
        $data['beginning_bal'] = $beginning_bal_data;
        $data['qty_received'] = $qty_received_data;
        $data['total_tests'] = $total_tests_data;
        $data['losses'] = $losses_data;
        $data['ending_bal'] = $ending_bal_data;
        $data['qty_requested'] = $qty_requested_data;
        //        $this->load->view('rtk/rtk/rca/county_reporting_view', $data);
        return $data;
    }    

    public function partner_stock_status() {        
        $partner = $this->session->userdata('partner_id');          
        
        $lastday = date('Y-m-d', strtotime("last day of previous month"));        
        
        $month = $this->session->userdata('Month');
        if ($month == '') {
            $month = date('mY', strtotime('-1 month'));
        }

        $year = substr($month, -4);
        $month = substr_replace($month, "", -4);


        $monthyear = $year . '-' . $month . '-1';
        $englishdate = date('F, Y', strtotime($monthyear));
        $data['graphdata'] = $this->partner_stock_percentages($partner, $month);       
        $data['tdata'] = $tdata;
        $data['county'] = $County;
        $data['commodity_name'] = $commodity_name;
        $data['title'] = 'RTK Partner';
        $data['banner_text'] = 'RTK Partner Stock Status: Losses';
        $data['content_view'] = "rtk/rtk/partner/partner_stock_status";
        $this->load->view("rtk/template", $data);

    }
     public function partner_stock_status_expiries() {
        $commodity = $this->session->userdata('commodity_id');          
        $partner = $this->session->userdata('partner_id');          
      
        $lastday = date('Y-m-d', strtotime("last day of previous month"));       

        $reports = array();
        
        $month = $this->session->userdata('Month');
        if ($month == '') {
            $month = date('mY', strtotime('-1 month'));
        }

        $year = substr($month, -4);
        $month = substr_replace($month, "", -4);


        $monthyear = $year . '-' . $month . '-1';
        $englishdate = date('F, Y', strtotime($monthyear));
        $data['graphdata'] = $this->partner_stock_expiring_percentages($partner);       
        $data['tdata'] = $tdata;
        $data['county'] = $County;
        $data['commodity_name'] = $commodity_name;
        $data['title'] = 'RTK Partner';
        $data['banner_text'] = 'RTK Partner Stock Status: Expiries';
        $data['content_view'] = "rtk/rtk/partner/partner_stock_status_expiries";
        $this->load->view("rtk/template", $data);

    }
 public function partner_stock_level() {
        $partner = $this->session->userdata('partner_id'); 
        
        $month = $this->session->userdata('Month');
        if ($month == '') {
            $month = date('mY', strtotime('-1 month'));
        }

        $year = substr($month, -4);
        $month = substr_replace($month, "", -4);


        $monthyear = $year . '-' . $month . '-1';
        $englishdate = date('F, Y', strtotime($monthyear));
        $data['graphdata'] = $this->partner_stock_level_percentages($partner);        
        $data['tdata'] = $tdata;        
        $data['title'] = 'RTK Partner';
        $data['banner_text'] = 'RTK Partner Stock Status: Stock Level';
        $data['content_view'] = "rtk/rtk/partner/partner_stock_level";
        $this->load->view("rtk/template", $data);

    }
    function partner_stock_card(){
        $commodity = $this->session->userdata('commodity_id');          
        $partner = $this->session->userdata('partner_id');          
                
        $lastday = date('Y-m-d', strtotime("last day of previous month"));        
        $reports = array();                

        $month = $this->session->userdata('Month');
        if ($month == '') {
            $month = date('mY', strtotime('-1 month'));
        }

        $year = substr($month, -4);
        $month = substr_replace($month, "", -4);      
        $monthyear = $year . '-' . $month . '-1';
        $firstdate = $year . '-' . $month . '-1';
        $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $lastdate = $year . '-' . $month . '-' . $num_days;
        //echo "The dates are  $firstdate and $lastdate";die();
        $englishdate = date('F, Y', strtotime($monthyear));
        $sql = "select    
                lab_commodity_details.commodity_id,
    sum(lab_commodity_details.q_requested) as qty_requested,
    sum(lab_commodity_details.beginning_bal) as beg_bal,
    sum(lab_commodity_details.q_received) as qty_received,
    sum(lab_commodity_details.no_of_tests_done) as test_done,
    sum(lab_commodity_details.losses) as losses,
    sum(lab_commodity_details.closing_stock) as closing_stock,
    sum(lab_commodity_details.q_used) as qty_used,
    sum(lab_commodity_details.q_expiring) as qty_expiring,
    sum(lab_commodity_details.days_out_of_stock) as days_out_of_stock,
    facilities.partner,
    lab_commodities.commodity_name
            from
                facilities,
                lab_commodity_details,
                lab_commodities
            where
                facilities.partner = '$partner'
                    and lab_commodity_details.facility_code = facilities.facility_code
                    and lab_commodity_details.commodity_id = lab_commodities.id
                    and lab_commodities.category = '1'
                    and lab_commodity_details.created_at between '$firstdate' and '$lastdate'
            group by lab_commodities.id";

        $data['result'] = $this->db->query($sql)->result_array();
        $data['active_month'] = $month.$year;
        $data['current_month'] = date('mY', time());       
        $data['tdata'] = $tdata;
        $data['county'] = $County;
        $data['commodity_name'] = $commodity_name;
        $data['title'] = 'RTK Partner';
        $data['banner_text'] = 'RTK Partner Stock Status: Stock Card';
        $data['content_view'] = "rtk/rtk/partner/partner_stock_card";
        $this->load->view("rtk/template", $data);
    }
function partner_stock_percentages($partner, $month) {    
        //$q = 'select extract(YEAR_MONTH from lab_commodity_details.created_at)as current_month, lab_commodity_details.commodity_id, lab_commodity_details.q_requested, lab_commodity_details.beginning_bal,lab_commodity_details.q_received,lab_commodity_details.no_of_tests_done,lab_commodity_details.losses,lab_commodity_details.closing_stock,lab_commodity_details.q_received, facilities.partner from facilities, lab_commodity_details where facilities.partner = 7 group by extract(YEAR_MONTH from lab_commodity_details.created_at) ';
        $q = "select extract(YEAR_MONTH from lab_commodity_details.created_at) as current_month,
                lab_commodities.commodity_name,
                lab_commodity_details.commodity_id,
                lab_commodity_details.losses,
                facilities.partner
            from
                facilities,
                lab_commodity_details,
                lab_commodities
            where
                facilities.partner = '$partner'
                    and lab_commodity_details.facility_code = facilities.facility_code
                    and lab_commodity_details.commodity_id = lab_commodities.id";
         $q_screen_det   = $q." and commodity_id = 1 
            group by extract(YEAR_MONTH from lab_commodity_details.created_at)";
        $query = $this->db->query($q_screen_det)->result_array();

        $q_confirm_uni   = $q." and commodity_id = 2 
            group by extract(YEAR_MONTH from lab_commodity_details.created_at)";
        $query2 = $this->db->query($q_confirm_uni)->result_array();

        $q_screening_khb   = $q." and commodity_id = 4 
            group by extract(YEAR_MONTH from lab_commodity_details.created_at)";
        $query3 = $this->db->query($q_screening_khb)->result_array();
        
        $q_confrim_first   = $q." and commodity_id = 5 
            group by extract(YEAR_MONTH from lab_commodity_details.created_at)";
        $query4 = $this->db->query($q_confrim_first)->result_array();
        
        $q_confrim_first   = $q." and commodity_id = 6 
            group by extract(YEAR_MONTH from lab_commodity_details.created_at)";
        $query5 = $this->db->query($q_confrim_first)->result_array();
        //echo("<pre>"); print_r($query);die;
       
        $month = array();
        $screening_det = array();
        $confirm_uni = array();;
        $screening_khb = array();
        $confrim_first = array();
        $tie_breaker = array();        
        $month_array = array();
        $screening_det_array = array();
        $confirm_uni_array = array();
        $screening_khb_array = array();
        $confrim_first_array = array();
        $tie_breaker_array = array();        


        foreach ($query as $val) {
            //echo intval($val['current_month']);die();
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);
            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            // if($val['commodity_id' ==1]){
            array_push($screening_det, intval($val['losses']));

            }

               foreach ($query2 as $val) {
            //echo intval($val['current_month']);die();
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);
            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            // if($val['commodity_id' ==1]){
            array_push($confirm_uni, intval($val['losses']));

            }

               foreach ($query3 as $val) {
            //echo intval($val['current_month']);die();
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);
            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            // if($val['commodity_id' ==1]){
            array_push($screening_khb, intval($val['losses']));

            }
               foreach ($query4 as $val) {
            //echo intval($val['current_month']);die();
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);
            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            // if($val['commodity_id' ==1]){
            array_push($confrim_first, intval($val['losses']));

            }

   foreach ($query5 as $val) {
            //echo intval($val['current_month']);die();
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);
            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            // if($val['commodity_id' ==1]){
            array_push($tie_breaker, intval($val['losses']));

            }  
        $month_data = json_encode($month);
        $screening_det_data = json_encode($screening_det);
        $confirm_uni_data = json_encode($confirm_uni);
        $screening_khb_data = json_encode($screening_khb);
        $confrim_first_data = json_encode($confrim_first);
        $tie_breaker_data = json_encode($tie_breaker);        

        $data['month'] = $month_data;
        $data['screening_det'] = $screening_det_data;
        $data['confirm_uni'] = $confirm_uni_data;
        $data['screening_khb'] = $screening_khb_data;
        $data['confrim_first'] = $confrim_first_data;
        $data['tie_breaker'] = $tie_breaker_data;        
        //        $this->load->view('rtk/rtk/rca/county_reporting_view', $data);
        return $data;
    }    
    function partner_stock_expiring_percentages($partner) {    
        //$q = 'select extract(YEAR_MONTH from lab_commodity_details.created_at)as current_month, lab_commodity_details.commodity_id, lab_commodity_details.q_requested, lab_commodity_details.beginning_bal,lab_commodity_details.q_received,lab_commodity_details.no_of_tests_done,lab_commodity_details.losses,lab_commodity_details.closing_stock,lab_commodity_details.q_received, facilities.partner from facilities, lab_commodity_details where facilities.partner = 7 group by extract(YEAR_MONTH from lab_commodity_details.created_at) ';
        $q = "select extract(YEAR_MONTH from lab_commodity_details.created_at) as current_month,
                lab_commodities.commodity_name,
                lab_commodity_details.commodity_id,
                lab_commodity_details.q_expiring,
                facilities.partner
            from
                facilities,
                lab_commodity_details,
                lab_commodities
            where
                facilities.partner = '$partner'
                    and lab_commodity_details.facility_code = facilities.facility_code
                    and lab_commodity_details.commodity_id = lab_commodities.id";
         $q_screen_det   = $q." and commodity_id = 1 
            group by extract(YEAR_MONTH from lab_commodity_details.created_at)";
        $query = $this->db->query($q_screen_det)->result_array();

        $q_confirm_uni   = $q." and commodity_id = 2 
            group by extract(YEAR_MONTH from lab_commodity_details.created_at)";
        $query2 = $this->db->query($q_confirm_uni)->result_array();

        $q_screening_khb   = $q." and commodity_id = 4 
            group by extract(YEAR_MONTH from lab_commodity_details.created_at)";
        $query3 = $this->db->query($q_screening_khb)->result_array();
        
        $q_confrim_first   = $q." and commodity_id = 5 
            group by extract(YEAR_MONTH from lab_commodity_details.created_at)";
        $query4 = $this->db->query($q_confrim_first)->result_array();
        
        $q_confrim_first   = $q." and commodity_id = 6 
            group by extract(YEAR_MONTH from lab_commodity_details.created_at)";
        $query5 = $this->db->query($q_confrim_first)->result_array();
        // echo("<pre>"); print_r($query);die;
       
        $month = array();
        $screening_det = array();
        $confirm_uni = array();;
        $screening_khb = array();
        $confrim_first = array();
        $tie_breaker = array();        
        $month_array = array();
        $screening_det_array = array();
        $confirm_uni_array = array();
        $screening_khb_array = array();
        $confrim_first_array = array();
        $tie_breaker_array = array();        


        foreach ($query as $val) {
            //echo intval($val['current_month']);die();
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);
            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            // if($val['commodity_id' ==1]){
            array_push($screening_det, intval($val['q_expiring']));

            }

               foreach ($query2 as $val) {
            //echo intval($val['current_month']);die();
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);
            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            // if($val['commodity_id' ==1]){
            array_push($confirm_uni, intval($val['q_expiring']));

            }

               foreach ($query3 as $val) {
            //echo intval($val['current_month']);die();
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);
            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            // if($val['commodity_id' ==1]){
            array_push($screening_khb, intval($val['q_expiring']));

            }
               foreach ($query4 as $val) {
            //echo intval($val['current_month']);die();
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);
            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            // if($val['commodity_id' ==1]){
            array_push($confrim_first, intval($val['q_expiring']));

            }

   foreach ($query5 as $val) {
            //echo intval($val['current_month']);die();
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);
            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            // if($val['commodity_id' ==1]){
            array_push($tie_breaker, intval($val['q_expiring']));

            }  
        $month_data = json_encode($month);
        $screening_det_data = json_encode($screening_det);
        $confirm_uni_data = json_encode($confirm_uni);
        $screening_khb_data = json_encode($screening_khb);
        $confrim_first_data = json_encode($confrim_first);
        $tie_breaker_data = json_encode($tie_breaker);        

        $data['month'] = $month_data;
        $data['screening_det'] = $screening_det_data;
        $data['confirm_uni'] = $confirm_uni_data;
        $data['screening_khb'] = $screening_khb_data;
        $data['confrim_first'] = $confrim_first_data;
        $data['tie_breaker'] = $tie_breaker_data;        
        //        $this->load->view('rtk/rtk/rca/county_reporting_view', $data);
        return $data;
    }    
 function partner_stock_level_percentages($partner) {   
    
        
        $q = "select 
                extract(YEAR_MONTH from lab_commodity_details.created_at) as current_month,
                lab_commodity_details.commodity_id,
                lab_commodity_details.q_requested,
                lab_commodity_details.beginning_bal,
                lab_commodity_details.q_received,
                lab_commodity_details.no_of_tests_done,
                lab_commodity_details.losses,
                lab_commodity_details.closing_stock,
                lab_commodity_details.q_received,
                facilities.partner
            from
                facilities,
                lab_commodity_details,
                lab_commodities
            where
                facilities.partner = '$partner'
                    and lab_commodity_details.facility_code = facilities.facility_code
                    and lab_commodity_details.commodity_id = lab_commodities.id
                    AND lab_commodities.id in (
                        select lab_commodities.id from lab_commodities, lab_commodity_categories where 
                        lab_commodities.category = lab_commodity_categories.id and lab_commodity_categories.active='1'
                        )
            group by extract(YEAR_MONTH from lab_commodity_details.created_at)";
        $query = $this->db->query($q)->result_array();        
       

        $month = array();
        $beginning_bal = array();
        $qty_received = array();;
        $total_tests = array();
        $losses = array();
        $ending_bal = array();
        $qty_requested = array();
        $month_array = array();
        $beginning_bal_array = array();
        $qty_received_array = array();
        $total_tests_array = array();
        $losses_array = array();
        $ending_bal_array = array();
        $qty_requested_array = array();


        foreach ($query as $val) {
            //echo intval($val['current_month']);die();
            $raw_month =  $val['current_month'];
            $year = substr($raw_month, 0,4);            
            $month_val = substr($raw_month, 4,2);
            $month_text = date('M',mktime(0,0,0,$month_val,10)).' '.$year;
            array_push($month, $month_text) ;
            array_push($beginning_bal, intval($val['beginning_bal']));
            array_push($qty_received, intval($val['q_received']));
            array_push($total_tests, intval($val['no_of_tests_done']));
            array_push($losses, intval($val['losses']));
            array_push($ending_bal, intval($val['closing_stock']));
            array_push($qty_requested, intval($val['q_requested']));
            //$percentage_reported = $this->district_reporting_percentages($val['district_id'], $year, $month);
            
           

            // array_push($month_array, $month);
            // array_push($beginning_bal_array, $beginning_bal);
            // array_push($qty_received_array, $qty_received);
            // array_push($total_tests_array, $total_tests);
            // array_push($losses_array, $losses);
            // array_push($ending_bal_array, $ending_bal);
            // array_push($qty_requested_array, $qty_requested);
        }
       

        $month_data = json_encode($month);
        $beginning_bal_data = json_encode($beginning_bal);
        $qty_received_data = json_encode($qty_received);
        $total_tests_data = json_encode($total_tests);
        $losses_data = json_encode($losses);
        $ending_bal_data = json_encode($ending_bal);
        $qty_requested_data = json_encode($qty_requested);

        $data['month'] = $month_data;
        $data['beginning_bal'] = $beginning_bal_data;
        $data['qty_received'] = $qty_received_data;
        $data['total_tests'] = $total_tests_data;
        $data['losses'] = $losses_data;
        $data['ending_bal'] = $ending_bal_data;
        $data['qty_requested'] = $qty_requested_data;
        //        $this->load->view('rtk/rtk/rca/county_reporting_view', $data);
        return $data;
    }   

public function partner_county_profile($district) {
        $data = array();
        $partner_id = $this->session->userdata('partner_id');      
        $sql_facilities = "select * from facilities, districts,counties 
        where facilities.district = districts.id and facilities.partner = '$partner_id' 
        and districts.county = counties.id and counties.id = '$district' ";        
        $facilities = $this->db->query($sql_facilities)->result_array();        
      
        $lastday = date('Y-m-d', strtotime("last day of previous month"));

        $current_month = $this->session->userdata('Month');
        if ($current_month == '') {
            $current_month = date('mY', time());
        }

        $previous_month = date('m', strtotime("last day of previous month"));
        $previous_month_1 = date('mY', strtotime('-2 month'));
        $previous_month_2 = date('mY', strtotime('-3 month'));


        $year_current = substr($current_month, -4);
        $year_previous = date('Y', strtotime("last day of previous month"));
        $year_previous_1 = substr($previous_month_1, -4);
        $year_previous_2 = substr($previous_month_2, -4);

        $current_month = substr_replace($current_month, "", -4);        
        $previous_month_1 = substr_replace($previous_month_1, "", -4);
        $previous_month_2 = substr_replace($previous_month_2, "", -4);

        $monthyear_current = $year_current . '-' . $current_month . '-1';
        $monthyear_previous = $year_previous . '-' . $previous_month . '-1';
        $monthyear_previous_1 = $year_previous_1 . '-' . $previous_month_1 . '-1';
        $monthyear_previous_2 = $year_previous_2 . '-' . $previous_month_2 . '-1';

        $englishdate = date('F, Y', strtotime($monthyear_current));

        $m_c = date("F", strtotime($monthyear_current));
             
        $m0 = date("F", strtotime($monthyear_previous));
        $m1 = date("F", strtotime($monthyear_previous_1));
        $m2 = date("F", strtotime($monthyear_previous_2));

        $month_text = array($m2, $m1, $m0);

        $district_summary = $this->partner_summary($district, $year_current, $current_month,$partner_id);
        $district_summary_prev = $this->partner_summary($district, $year_previous, $previous_month,$partner_id);
        $district_summary1 = $this->partner_summary($district, $year_previous_1, $previous_month_1,$partner_id);
        $district_summary2 = $this->partner_summary($district, $year_previous_2, $previous_month_2,$partner_id);


        $county_id = districts::get_county_id($district);
        $county_name = counties::get_county_name($district);

       // $cid = $this->db->select('districts.county')->get_where('districts', array('id' =>$district))->result_array();

       //  foreach ($cid as $key => $value) {
       //     $myres = $cid[0]['county'];
       // }
       $sql_counties = "select distinct counties.id,counties.county from facilities, districts,counties 
        where facilities.district = districts.id and facilities.partner = '$partner_id' and facilities.rtk_enabled='1' 
        and districts.county = counties.id";          
       $mycounties = $this->db->query($sql_counties)->result_array();       

       $data['district_balances_current'] = $this->partner_county_totals($year_current, $previous_month, $district);
       $data['district_balances_previous'] = $this->partner_county_totals($year_previous, $previous_month, $district);
       $data['district_balances_previous_1'] = $this->partner_county_totals($year_previous_1, $previous_month_1, $district);
       $data['district_balances_previous_2'] = $this->partner_county_totals($year_previous_2, $previous_month_2, $district);


       $data['district_summary'] = $district_summary;

       $data['counties_list'] = $mycounties;
       $data['facilities']= $facilities;        

       $data['district_name'] = $district_summary['district'];
       $data['county_id'] = $county_name['id'];
       $data['county_name'] = $county_name['county'];     

       $data['title'] = 'RTK Partner County Profile: ' . $district_summary['district'];
       $data['banner_text'] = 'Partner County Profile: ' . $district_summary['district'];
       $data['content_view'] = "rtk/rtk/partner/partner_profile_v";
       $data['months'] = $month_text;

       $this->load->view("rtk/template", $data);
   }
    //Shared Functions

            function _allocation($zone = NULL, $county = NULL, $district = NULL, $facility = NULL, $sincedate = NULL, $enddate = NULL) {
            // function to filter allocation based on multiple parameter
            // zone, county,district, sincedate,
                $conditions = '';
                $conditions = (isset($zone)) ? " AND facilities.Zone = 'Zone $zone'" : '';
                $conditions = (isset($county)) ? $conditions . " AND counties.id = $county" : $conditions . ' ';
                $conditions = (isset($district)) ? $conditions . " AND districts.id = $district" : $conditions . ' ';
                $conditions = (isset($facility)) ? $conditions . " AND facilities.facility_code = $facility" : $conditions . ' ';
                $conditions = (isset($sincedate)) ? $conditions . " AND lab_commodity_details.allocated_date >= $sincedate" : $conditions . ' ';
                $conditions = (isset($enddate)) ? $conditions . " AND lab_commodity_details.allocated_date <= $enddate" : $conditions . ' ';

                $sql = "select facilities.facility_name,facilities.facility_code,facilities.Zone, facilities.contactperson,facilities.cellphone, lab_commodity_details.commodity_id,
                lab_commodity_details.allocated,lab_commodity_details.allocated_date,lab_commodity_orders.order_date,lab_commodities.commodity_name,facility_amc.amc,lab_commodity_details.closing_stock,lab_commodity_details.q_requested
                from facilities, lab_commodity_orders,lab_commodity_details, counties,districts,lab_commodities,lab_commodity_categories,facility_amc
                WHERE facilities.facility_code = lab_commodity_orders.facility_code
                AND lab_commodity_categories.id = 1
                AND lab_commodity_categories.id = lab_commodities.category
                AND counties.id = districts.county
                AND facilities.district = districts.id
                AND facilities.rtk_enabled = 1
                and lab_commodities.id = lab_commodity_details.commodity_id
                and lab_commodities.id = facility_amc.commodity_id
                and facilities.facility_code = facility_amc.facility_code
                AND lab_commodity_orders.id = lab_commodity_details.order_id
                AND lab_commodity_details.commodity_id between 1 AND 3
                $conditions
                GROUP BY facilities.facility_code, lab_commodity_details.commodity_id";
                $res = $this->db->query($sql);
                $returnable = $res->result_array();
                return $returnable;
            #$nonexistent = "AND lab_commodity_orders.order_date BETWEEN '2014-04-01' AND '2014-04-30'";
            }


        //Switch Districts
            public function switch_district($new_dist = null, $switched_as, $month = NULL, $redirect_url = NULL, $newcounty = null, $switched_from = null) {    
                if ($new_dist == 0) {
                    $new_dist = null;
                }
                if ($month == 0) {
                    $month = null;
                }
                if ($redirect_url == 0) {
                    $redirect_url = null;
                }
                if ($newcounty == 0) {
                    $newcounty = null;
                }
                if ($redirect_url == NULL) {
                    $redirect_url = 'home_controller';
                }           

                if (!isset($newcounty)) {
                    $newcounty = $this->session->userdata('county_id');
                }

                $session_data = array("session_id" => $this->session->userdata('session_id'),
                 "ip_address" => $this->session->userdata('ip_address'),
                 "user_agent" => $this->session->userdata('user_agent'),
                 "last_activity" => $this->session->userdata('last_activity'),
                 "county_id" => $newcounty,
                 "phone_no" => $this->session->userdata('phone_no'),
                 "user_email" => $this->session->userdata('user_email'),
                 "user_db_id" => $this->session->userdata('user_db_id'),
                 "full_name" => $this->session->userdata('full_name'),
                 "user_id" => $this->session->userdata('user_id'),
                 "user_indicator" => $switched_as,
                 "names" => $this->session->userdata('names'),
                 "inames" => $this->session->userdata('inames'),
                 "identity" => $this->session->userdata('identity'),
                 "news" => $this->session->userdata('news'),
                 "district_id" => $new_dist,
                 "drawing_rights" => $this->session->userdata('drawing_rights'),
                 "switched_as" => $switched_as,
                 "Month" => $month,
                 'switched_from' => $switched_from);


$this->session->set_userdata($session_data);
redirect($redirect_url);
}
public function switch_commodity($month = NULL, $redirect_url = NULL,$commodity) {
        
        if ($month == 0) {
            $month = null;
        }        
        
        if ($redirect_url == NULL) {
            $redirect_url = 'home_controller';
        }     


        $url = 'rtk_management/';
        $url.=$redirect_url;
   
        $session_data = array("session_id" => $this->session->userdata('session_id'),
         "ip_address" => $this->session->userdata('ip_address'),
         "user_agent" => $this->session->userdata('user_agent'),
         "last_activity" => $this->session->userdata('last_activity'),
         "phone_no" => $this->session->userdata('phone_no'),
         "user_email" => $this->session->userdata('user_email'),
         "user_db_id" => $this->session->userdata('user_db_id'),
         "full_name" => $this->session->userdata('full_name'),
         "user_id" => $this->session->userdata('user_id'),
         "names" => $this->session->userdata('names'),
         "inames" => $this->session->userdata('inames'),
         "identity" => $this->session->userdata('identity'),
         "news" => $this->session->userdata('news'),         
         "drawing_rights" => $this->session->userdata('drawing_rights'),         
         "commodity_id" => $commodity,         
         "Month" => $month);


        $this->session->set_userdata($session_data);
        redirect($url);
    }
public function summary_tab_display($county, $year, $month) {
        // county may be 1 for Nairobi, 5 for busia or 31 for Nakuru
    $htmltable = '';

    $countyname = counties::get_county_name($county);
    $countyname = $countyname[0]['county'];
    $ish = $this->rtk_summary_county($county, $year, $month);            
    $htmltable .= '<tr>
    <td rowspan ="' . $ish['districts'] . '">' . $countyname . '';            
        $total_punctual = 0;
        $county_percentage = 0;

        foreach ($ish['district_summary'] as $vals) {
            $early = $vals['reported'] - $vals['late_reports'];
            $total_punctual += $early;
            $htmltable .= ' 
        </td><td>' . $vals['district'].'</td>
        <td>' . $vals['total_facilities'] . '</td>
        <td>' . $early . '</td>
        <td>' . $vals['late_reports'] . '</td>
        <td>' . $vals['nonreported'] . '</td>
        <td>' . $vals['reported_percentage'] . '%</td></tr>';

    }
    $county_percentage = ($total_punctual + $ish['late_reports']) / $ish['facilities'] * 100;
    $county_percentage = number_format($county_percentage, 0);

    $htmltable .= '<tr style="background: #E9E9E3; border-top: solid 1px #ccc;">
    <td>Totals</td>
    <td>' . $ish['districts'] . ' Sub-Counties</td>
    <td>' . $ish['facilities'] . '</td>
    <td>' . $total_punctual . '</td>
    <td>' . $ish['late_reports'] . '</td>
    <td>' . $ish['nonreported'] . '</td>
    <td>' . $county_percentage . '%</td>

</tr>';
echo '
<table class="data-table">
  <thead><tr>
      <th>County</th>
      <th>Sub-County</th>
      <th>No of facilities</th>
      <th>No reports before 10th</th>
      <th>No of late reports (10th-12th)</th>
      <th>No of non reporting facilities</th>
      <th>Overall reporting rate in % (no of reports submitted/expected no of reports)</th>
  </tr></thead>
  ' . $htmltable . '

</table>';
}
public function rtk_summary_county($county, $year, $month) {
    date_default_timezone_set('EUROPE/moscow');
    $county_summary = array();
    $county_summary['districts'] = 0;
    $county_summary['facilities'] = 0;
    $county_summary['reported'] = 0;
    $county_summary['reported_percentage'] = 0;
    $county_summary['nonreported'] = 0;
    $county_summary['nonreported_percentage'] = 0;
    $county_summary['late_reports'] = 0;
    $county_summary['late_reports_percentage'] = 0;
    $county_summary['district_summary'] = array();
        /*
         * countyname,numberofdistricts,numberoffacilities,reported,nonreported,late
         */
        $q = 'SELECT * 
        FROM counties, districts
        WHERE counties.id = districts.county
        AND counties.id = ' . $county . '';
        $q_res = $this->db->query($q);
        $districts_num = $q_res->num_rows();
        foreach ($q_res->result_array() as $districts) {
            $dist_id = $districts['id'];
            $dist = $districts['district'];

            //$county_summary['district_summary']['district'] = $dist;
            //$county_summary['district_summary']['district_id'] = $dist_id;

            $district_summary = $this->rtk_summary_district($districts['id'], $year, $month);

            $county_summary['districts'] += 1;
            $county_summary['facilities'] += $district_summary['total_facilities'];
            $county_summary['reported'] += $district_summary['reported'];
            $county_summary['reported_percentage'] += $district_summary['reported_percentage'];
            $county_summary['nonreported'] += $district_summary['nonreported'];
            $county_summary['nonreported_percentage'] += $district_summary['nonreported_percentage'];
            $county_summary['punctual_reports'] = 1;
            $county_summary['late_reports'] += $district_summary['late_reports'];

            $county_summary['late_reports_percentage'] += $district_summary['late_reports_percentage'];
            array_push($county_summary['district_summary'], $district_summary);
        }

        $county_summary['reported_percentage'] = ($county_summary['reported_percentage'] / $county_summary['districts']);
        $county_summary['reported_percentage'] = number_format($county_summary['reported_percentage'], 0);

        $sortArray = array();
        foreach ($county_summary['district_summary'] as $person) {
            foreach ($person as $key => $value) {
                if (!isset($sortArray[$key])) {
                    $sortArray[$key] = array();
                }
                $sortArray[$key][] = $value;
            }
        }

        $orderby = "reported_percentage";

        array_multisort($sortArray[$orderby], SORT_DESC, $county_summary['district_summary']);
        return $county_summary;
    }

    public function switch_month($month = NULL, $redirect_url = NULL) {

        if ($month == 0) {
            $month = null;
        }        

        if ($redirect_url == NULL) {
            $redirect_url = 'home_controller';
        }            

        $url = 'rtk_management/';
        $url.=$redirect_url;

        $session_data = array("session_id" => $this->session->userdata('session_id'),
           "ip_address" => $this->session->userdata('ip_address'),
           "user_agent" => $this->session->userdata('user_agent'),
           "last_activity" => $this->session->userdata('last_activity'),
           "phone_no" => $this->session->userdata('phone_no'),
           "user_email" => $this->session->userdata('user_email'),
           "user_db_id" => $this->session->userdata('user_db_id'),
           "full_name" => $this->session->userdata('full_name'),
           "user_id" => $this->session->userdata('user_id'),
           "names" => $this->session->userdata('names'),
           "inames" => $this->session->userdata('inames'),
           "identity" => $this->session->userdata('identity'),
           "news" => $this->session->userdata('news'),         
           "drawing_rights" => $this->session->userdata('drawing_rights'),         
           "Month" => $month);


        $this->session->set_userdata($session_data);
        redirect($url);
    }


    public function rtk_summary_district($district, $year, $month) {
        $distname = districts::get_district_name($district);
        $districtname = $distname[0]['district'];
        $district_id = $district;
        $returnable = array();
        $nonreported;
        $reported_percentage;
        $late_percentage;

        // Sets the timezone and date variables for last day of previous month and this month
        date_default_timezone_set('EUROPE/moscow');
        $month = $month + 1;
        $prev_month = $month - 1;
        $last_day_current_month = date('Y-m-d', mktime(0, 0, 0, $month, 0, $year));
        $first_day_current_month = date('Y-m-', mktime(0, 0, 0, $month, 0, $year));
        $first_day_current_month .= '1';
        $lastday_thismonth = date('Y-m-d', strtotime("last day of this month"));
        $month -= 1;
        $day10 = $year . '-' . $month . '-10';
        $day11 = $year . '-' . $month . '-11';
        $day12 = $year . '-' . $month . '-12';
        $late_reporting = 0;
        $text_month = date('F', strtotime($day10));

        $q = 'SELECT * 
        FROM facilities, districts, counties
        WHERE facilities.district = districts.id
        AND districts.county = counties.id
        AND districts.id = '.$district.' 
        AND facilities.rtk_enabled =1
        ORDER BY  `facilities`.`facility_name` ASC ';
        $q_res = $this->db->query($q);
        $total_reporting_facilities = $q_res->num_rows();
        $q = "SELECT DISTINCT lab_commodity_orders.facility_code, lab_commodity_orders.id,lab_commodity_orders.order_date
        FROM lab_commodity_orders, districts, counties
        WHERE districts.id = lab_commodity_orders.district_id
        AND districts.county = counties.id
        AND districts.id = $district
        AND lab_commodity_orders.order_date
        BETWEEN '$first_day_current_month'
        AND '$last_day_current_month'";
        $q_res1 = $this->db->query($q);
        $total_reported_facilities = $q_res1->num_rows();

        foreach ($q_res1->result_array() as $vals) {
            //            echo "<pre>";var_dump($vals);echo "</pre>";
            if ($vals['order_date'] == $day10 || $vals['order_date'] == $day11 || $vals['order_date'] == $day12) {
                $late_reporting += 1;
                //                echo "<pre>";var_dump($vals);echo "</pre>";
            }
        }

        $nonreported = $total_reporting_facilities - $total_reported_facilities;

        if ($total_reporting_facilities == 0) {
            $non_reported_percentage = 0;
        } else {
            $non_reported_percentage = $nonreported / $total_reporting_facilities * 100;
        }

        $non_reported_percentage = number_format($non_reported_percentage, 0);

        if ($total_reporting_facilities == 0) {
            $reported_percentage = 0;
        } else {
            $reported_percentage = $total_reported_facilities / $total_reporting_facilities * 100;
        }

        $reported_percentage = number_format($reported_percentage, 0);

        if ($total_reporting_facilities == 0) {
            $late_percentage = 0;
        } else {
            $late_percentage = $late_reporting / $total_reporting_facilities * 100;
        }


        $late_percentage = number_format($late_percentage, 0);
        if ($total_reported_facilities > $total_reporting_facilities) {
            $reported_percentage = 100;
            $nonreported = 0;
            $total_reported_facilities = $total_reporting_facilities;
        }
        if ($late_reporting > $total_reporting_facilities) {
            $late_reporting = $total_reporting_facilities;
            $late_percentage = $reported_percentage;
        }
        $returnable = array('Month' => $text_month, 'Year' => $year, 'district' => $districtname, 'district_id' => $district_id, 'total_facilities' => $total_reporting_facilities, 'reported' => $total_reported_facilities, 'reported_percentage' => $reported_percentage, 'nonreported' => $nonreported, 'nonreported_percentage' => $non_reported_percentage, 'late_reports' => $late_reporting, 'late_reports_percentage' => $late_percentage);
        return $returnable;
    }

    public function partner_summary($county, $year, $month,$partner_id) {                
        $returnable = array();
        $nonreported;
        $reported_percentage;
        $late_percentage;

        // Sets the timezone and date variables for last day of previous month and this month
        date_default_timezone_set('EUROPE/moscow');
        $month = $month + 1;
        $prev_month = $month - 1;
        $last_day_current_month = date('Y-m-d', mktime(0, 0, 0, $month, 0, $year));
        $first_day_current_month = date('Y-m-', mktime(0, 0, 0, $month, 0, $year));
        $first_day_current_month .= '1';
        $lastday_thismonth = date('Y-m-d', strtotime("last day of this month"));
        $month -= 1;
        $day10 = $year . '-' . $month . '-10';
        $day11 = $year . '-' . $month . '-11';
        $day12 = $year . '-' . $month . '-12';
        $late_reporting = 0;
        $text_month = date('F', strtotime($day10));

        $q = "SELECT * 
        FROM facilities, districts, counties
        WHERE facilities.district = districts.id
        AND districts.county = counties.id
        and counties.id = '$county'
        AND facilities.district = districts.id 
        and facilities.partner = '$partner_id'
        AND facilities.rtk_enabled =1
        ORDER BY  `facilities`.`facility_name` ASC ";

        $q_res = $this->db->query($q);
        $total_reporting_facilities = $q_res->num_rows();        
        $q = "SELECT DISTINCT lab_commodity_orders.facility_code, lab_commodity_orders.id,lab_commodity_orders.order_date
        FROM lab_commodity_orders, districts, counties,facilities
        WHERE districts.id = lab_commodity_orders.district_id
        AND districts.county = counties.id
        and counties.id = '$county'
        AND facilities.district = districts.id 
        and facilities.partner = '$partner_id'
        and lab_commodity_orders.facility_code = facilities.facility_code
        AND lab_commodity_orders.order_date
        BETWEEN '$first_day_current_month'
        AND '$last_day_current_month'";       
        $q_res1 = $this->db->query($q);
        $total_reported_facilities = $q_res1->num_rows();    

        foreach ($q_res1->result_array() as $vals) {
            //            echo "<pre>";var_dump($vals);echo "</pre>";
            if ($vals['order_date'] == $day10 || $vals['order_date'] == $day11 || $vals['order_date'] == $day12) {
                $late_reporting += 1;
                //                echo "<pre>";var_dump($vals);echo "</pre>";
            }
        }

        $nonreported = $total_reporting_facilities - $total_reported_facilities;

        if ($total_reporting_facilities == 0) {
            $non_reported_percentage = 0;
        } else {
            $non_reported_percentage = $nonreported / $total_reporting_facilities * 100;
        }

        $non_reported_percentage = number_format($non_reported_percentage, 0);

        if ($total_reporting_facilities == 0) {
            $reported_percentage = 0;
        } else {
            $reported_percentage = $total_reported_facilities / $total_reporting_facilities * 100;
        }

        $reported_percentage = number_format($reported_percentage, 0);

        if ($total_reporting_facilities == 0) {
            $late_percentage = 0;
        } else {
            $late_percentage = $late_reporting / $total_reporting_facilities * 100;
        }


        $late_percentage = number_format($late_percentage, 0);
        if ($total_reported_facilities > $total_reporting_facilities) {
            $reported_percentage = 100;
            $nonreported = 0;
            $total_reported_facilities = $total_reporting_facilities;
        }
        if ($late_reporting > $total_reporting_facilities) {
            $late_reporting = $total_reporting_facilities;
            $late_percentage = $reported_percentage;
        }
        $returnable = array('Month' => $text_month, 'Year' => $year,'total_facilities' => $total_reporting_facilities, 'reported' => $total_reported_facilities, 'reported_percentage' => $reported_percentage, 'nonreported' => $nonreported, 'nonreported_percentage' => $non_reported_percentage, 'late_reports' => $late_reporting, 'late_reports_percentage' => $late_percentage);
        return $returnable;
    }

    //Logging Function
    public function logData($reference, $object) {
        $timestamp = time();
        $user_id = $this->session->userdata('user_id');
        $sql = "INSERT INTO `rtk_logs`(`id`, `user_id`, `reference`,`reference_object`,`timestamp`) VALUES (NULL,'$user_id','$reference','$object','$timestamp')";
        $this->db->query($sql);
    }

    //Update the Average Monthly Consumption
    private function update_amc($mfl) {
        $last_update = time();
        $amc = 0;
        for ($commodity_id = 1; $commodity_id <= 6; $commodity_id++) {
            $amc = $this->_facility_amc($mfl, $commodity_id);
            $sql = "update facility_amc set amc = '$amc', last_update = '$last_update' where facility_code = '$mfl' and commodity_id='$commodity_id'";
            $res = $this->db->query($sql);
        }
    }


    //Facility Amc
    public function _facility_amc($mfl_code, $commodity = null) {
        $three_months_ago = date("Y-m-", strtotime("-3 Month "));
        $three_months_ago .='1';
        $end_date = date("Y-m-", strtotime("-1 Month "));
        $end_date .='31';
        //echo "Three months ago = $three_months_ago and End Date =$end_date ";die();
        $q = "SELECT avg(lab_commodity_details.q_used) as avg_used
        FROM  lab_commodity_details,lab_commodity_orders
        WHERE lab_commodity_orders.id =  lab_commodity_details.order_id
        AND lab_commodity_details.facility_code =  $mfl_code
        AND lab_commodity_orders.order_date BETWEEN '$three_months_ago' AND '$end_date'";
        
        if (isset($commodity)) {
            $q.=" AND lab_commodity_details.commodity_id = $commodity";
        } else {
            $q.=" AND lab_commodity_details.commodity_id = 1";
        }

        $res = $this->db->query($q);
        $result = $res->result_array();
        $result = $result[0]['avg_used'];
        $result = number_format($result, 0);
        return $result;
    }


    //Update the Number of Reports Online
    function _update_reports_count($state,$county,$district){ 
        $month = date('mY',time());             
        if($state=="add"){
            $sql = "update rtk_county_percentage set reported = (reported + 1) where month='$month' and county_id = '$county'";
            $sql1 = "update rtk_district_percentage set reported = (reported + 1) where month='$month' and district_id = '$district'";
        }elseif ($state=="remove") {
            $sql = "update rtk_county_percentage set reported = (reported - 1) where month='$month' and county_id = '$county'";
            $sql1 = "update rtk_district_percentage set reported = (reported - 1) where month='$month' and district_id = '$district'";                
        }
        $this->db->query($sql);
        $this->db->query($sql1);
        $q = "update rtk_district_percentage set percentage = round(((reported/facilities)*100),0) where month='$month' and district_id = '$district'";                
        $q1 = "update rtk_county_percentage set percentage = round(((reported/facilities)*100),0) where month='$month' and county_id = '$county'";
        $this->db->query($q);
        $this->db->query($q1);
    } 

    //Function for the Amounts Allocated versus those Requested 
    function _requested_vs_allocated($year, $month, $county = null) {

        $firstdate = $year . '-' . $month . '-01';
        $firstday = date("Y-m-d", strtotime("$firstdate +1 Month "));

        $month = date("m", strtotime("$firstdate +1 Month "));
        $year = date("Y", strtotime("$firstdate +1 Month "));
        $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $lastdate = $year . '-' . $month . '-' . $num_days;

        $returnable = array();

        $common_q = "SELECT
        lab_commodities.commodity_name,
        sum(lab_commodity_details.beginning_bal) as sum_opening, 
        sum(lab_commodity_details.q_received) as sum_received, 
        sum(lab_commodity_details.q_used) as sum_used, 
        sum(lab_commodity_details.no_of_tests_done) as sum_tests, 
        sum(lab_commodity_details.positive_adj) as sum_positive, 
        sum(lab_commodity_details.negative_adj) as sum_negative,
        sum(lab_commodity_details.losses) as sum_losses,
        sum(lab_commodity_details.closing_stock) as sum_closing_bal,
        sum(lab_commodity_details.q_requested) as sum_requested, 
        sum(lab_commodity_details.allocated) as sum_allocated,
        sum(lab_commodity_details.allocated) as sum_days,
        sum(lab_commodity_details.q_expiring) as sum_expiring
        FROM lab_commodities, lab_commodity_details, lab_commodity_orders, facilities, districts, counties 
        WHERE lab_commodity_details.commodity_id = lab_commodities.id 
        AND lab_commodity_orders.id = lab_commodity_details.order_id 
        AND facilities.facility_code = lab_commodity_details.facility_code 
        AND facilities.district = districts.id 
        AND districts.county = counties.id 
        AND lab_commodity_orders.order_date BETWEEN  '$firstday' AND  '$lastdate'
        AND lab_commodities.id in (select lab_commodities.id from lab_commodities,lab_commodity_categories 
            where lab_commodities.category = lab_commodity_categories.id and lab_commodity_categories.active = '1')";
if (isset($county)) {
    $common_q.= ' AND counties.id =' . $county;
}

$common_q.= ' group by lab_commodities.id';

$res = $this->db->query($common_q);        

        // $result = $res->result_array();
        // array_push($returnable, $result[0]);


        // $q = $common_q . " AND lab_commodities.id = 1";
        // $res = $this->db->query($q);
        // $result = $res->result_array();
        // array_push($returnable, $result[0]);

        // $q2 = $common_q . " AND lab_commodities.id = 2";
        // $res2 = $this->db->query($q2);
        // $result2 = $res2->result_array();
        // array_push($returnable, $result2[0]);

        // $q3 = $common_q . " AND lab_commodities.id = 3";
        // $res3 = $this->db->query($q3);
        // $result3 = $res3->result_array();
        // array_push($returnable, $result3[0]);

        // $q4 = $common_q . " AND lab_commodities.id = 4";
        // $res4 = $this->db->query($q4);
        // $result4 = $res4->result_array();
        // array_push($returnable, $result4[0]);

        // $q5 = $common_q . " AND lab_commodities.id = 5";
        // $res5 = $this->db->query($q5);
        // $result5 = $res5->result_array();
        // array_push($returnable, $result5[0]);

        // $q6 = $common_q . " AND lab_commodities.id = 6";
        // $res6 = $this->db->query($q6);
        // $result6 = $res6->result_array();
        // array_push($returnable, $result6[0]);
$returnable = $res->result_array();
return $returnable;
}

function county_reporting_percentages($county, $year, $month) {
    $q = 'SELECT counties.id as county_id,counties.county as countyname,districts.district,districts.county,districts.id as district_id
    FROM  `districts`,`counties` 
    WHERE districts.county = counties.id 
    AND  counties.`id` =' . $county . '';
    $districts = array();
    $reported = array();
    $nonreported = array();
    $query = $this->db->query($q);
    foreach ($query->result_array() as $val) {
        array_push($districts, $val['district']);
        $percentage_reported = $this->district_reporting_percentages($val['district_id'], $year, $month);
        $percentage_reported = $percentage_reported + 0;
        if ($percentage_reported > 100) {
            $percentage_reported = 100;
        }
        array_push($reported, $percentage_reported);
        $percentage_non_reported = 100 - $percentage_reported;
        array_push($nonreported, $percentage_non_reported);
    }

    $districts = json_encode($districts);
    $reported = json_encode($reported);
    $nonreported = json_encode($nonreported);

    $data['districts'] = $districts;
    $data['reported'] = $reported;
    $data['nonreported'] = $nonreported;
        //        $this->load->view('rtk/rtk/rca/county_reporting_view', $data);
    return $data;
}

function district_reporting_percentages($district, $year, $month) {


    $firstdate = $year . '-' . $month . '-01';
    $firstday = date("Y-m-d", strtotime("$firstdate +1 Month "));
    $month = date("m", strtotime("$firstdate +1 Month "));
    $year = date("Y", strtotime("$firstdate +1 Month "));
    $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $lastdate = $year . '-' . $month . '-' . $num_days;

    $lastday = date('Y-m-d', strtotime("last day of previous month"));
    $q = 'SELECT * FROM  `facilities` WHERE  `district` =' . $district . ' AND  `rtk_enabled` =1';
    $q2 = "SELECT DISTINCT lab_commodity_orders.facility_code, facilities.facility_code, facilities.facility_name, lab_commodity_orders.id, lab_commodity_orders.district_id, lab_commodity_orders.order_date
    FROM lab_commodity_orders, districts, counties, facilities
    WHERE districts.id = lab_commodity_orders.district_id
    AND districts.county = counties.id
    AND facilities.facility_code = lab_commodity_orders.facility_code
    AND districts.id = $district
    AND lab_commodity_orders.order_date
    BETWEEN  '$firstday'
    AND  '$lastdate'";
    $query = $this->db->query($q);
    $query2 = $this->db->query($q2);
    $percentage = $query2->num_rows() / $query->num_rows() * 100;
    $percentage = number_format($percentage, $decimals = 0);
    return $percentage;
}

function _districts_in_county($county) {
    $q = 'SELECT id,district FROM  `districts` WHERE  `county` =' . $county;
    $this->db->query($q);
    $res = $this->db->query($q);
    $returnable = $res->result_array();
    return $returnable;
}

function _facilities_in_county($county, $type = null) {
    $q = 'SELECT 
    facilities.id as facil_id,facilities.facility_name,facilities.owner,facilities.facility_code, facilities.rtk_enabled,
    districts.district as districtname, districts.id as district_id,
    counties.county, counties.id 
    from districts,counties,facilities 
    where facilities.district = districts.id
    AND districts.county = counties.id
    AND counties.id =' . $county . '
    ORDER BY  `districts`.`district` ASC ';
    $res = $this->db->query($q);
    $returnable = $res->result_array();
    return $returnable;
}

function _get_dmlt_districts($dmlt) {
    $sql = 'SELECT DISTINCT districts.district, districts.id
    FROM dmlt_districts, districts
    WHERE districts.id = dmlt_districts.district
    AND dmlt_districts.dmlt =' . $dmlt;
    $res = $this->db->query($sql);
    $returnable = $res->result_array();
    return $returnable;
}
function _users_in_county($county, $type = null) {
    $q = 'SELECT user.fname, user.lname, user.email, user.id AS id, user.telephone, districts.id AS district_id, districts.district 
    FROM user, districts
    WHERE user.district = districts.id
    AND county_id =' . $county;

    if ($type) {
        $q .= ' AND usertype_id =' . $type;
    }
    $res = $this->db->query($q);
    $returnable = $res->result_array();
    return $returnable;
}
public function show_dmlt_districts($dmlt, $mode = NULL) {
    $districts = $this->_get_dmlt_districts($dmlt);
    if ($mode == '') {

    }
    foreach ($districts as $value) {
        echo '<a href="' . base_url() . 'rtk_management/remove_dmlt_from_district/' . $dmlt . '/' . $value['id'] . '" style="color: #DD6A6A;">' . $value['district'] . '</a>, ';
    }
}

public function dmlt_district_action() {
    $action = mysql_real_escape_string($_POST['action']);
    $dmlt = mysql_real_escape_string($_POST['dmlt_id']);
    $district = mysql_real_escape_string($_POST['dmlt_district']);

    if ($action == 'add') {
        $this->_add_dmlt_to_district($dmlt, $district);
    } elseif ($action == 'remove') {
        $this->_remove_dmlt_from_district($dmlt, $district);
    }
    echo "Sub-County Added Successfully";        
}

function _add_dmlt_to_district($dmlt, $district) {
    $sql = "INSERT INTO `dmlt_districts` (`id`, `dmlt`, `district`) VALUES (NULL, $dmlt, $district);";
    $this->db->query($sql);
    $object_id = $this->db->insert_id();
    $this->logData('1', $object_id);
}

function _remove_dmlt_from_district($dmlt, $district, $redirect_url) {
    $sql = "DELETE FROM `dmlt_districts` WHERE  dmlt=$dmlt AND district = $district";

    $object_id = $dmlt;
    $this->logData('2', $object_id);
    $this->db->query($sql);
}

public function deactivate_facility($facility_code) {
    $this->db->query('UPDATE `facilities` SET  `rtk_enabled` =  0 WHERE  `facility_code` =' . $facility_code . '');
    $q = $this->db->query('SELECT * FROM  `facilities` WHERE  `facility_code` =' . $facility_code . '');
    $facil = $q->result_array();
    $object_id = $facil[0]['id'];
    $this->logData('24', $object_id);
    $sql = "select district from facilities where facility_code = '$facility_code'";
    $res = $this->db->query($sql)->result_array();
    foreach ($res as $key => $value) {
        $district = $value['district'];
    }
    $sql1 = "select county from districts where id = '$district'";
    $res1 = $this->db->query($sql1)->result_array();
    foreach ($res1 as $key => $value) {
        $county = $value['county'];
    }
    $this->_update_facility_count('remove',$county,$district);        
    redirect('rtk_management/county_admin/facilities');
}

function _update_facility_count($state,$county,$district){
    $month = date('mY',time());             
    if($state=="add"){
        $sql = "update rtk_county_percentage set facilities = (facilities + 1) where month='$month' and county_id = '$county'";
        $sql1 = "update rtk_district_percentage set facilities = (facilities + 1) where month='$month' and district_id = '$district'";
    }elseif ($state=="remove") {
        $sql = "update rtk_county_percentage set facilities = (facilities - 1) where month='$month' and county_id = '$county'";
        $sql1 = "update rtk_district_percentage set facilities = (facilities - 1) where month='$month' and district_id = '$district'";                
    }
    $this->db->query($sql);
    $this->db->query($sql1);
    $q = "update rtk_district_percentage set percentage = round(((reported/facilities)*100),0) where month='$month' and district_id = '$district'";                
    $q1 = "update rtk_county_percentage set percentage = round(((reported/facilities)*100),0) where month='$month' and county_id = '$county'";
    $this->db->query($q);
    $this->db->query($q1);
}

public function activate_facility($facility_code) {
    $this->db->query('UPDATE `facilities` SET  `rtk_enabled` = 1 WHERE  `facility_code` =' . $facility_code . '');
    $q = $this->db->query('SELECT * FROM  `facilities` WHERE  `facility_code` =' . $facility_code . '');
    $facil = $q->result_array();
    $object_id = $facil[0]['id'];
    $this->logData('21', $object_id);
    $sql = "select district from facilities where facility_code = '$facility_code'";
    $res = $this->db->query($sql)->result_array();
    foreach ($res as $key => $value) {
        $district = $value['district'];
    }
    $sql1 = "select county from districts where id = '$district'";
    $res1 = $this->db->query($sql1)->result_array();
    foreach ($res1 as $key => $value) {
        $county = $value['county'];
    }
    $this->_update_facility_count('add',$county,$district);        
    redirect('rtk_management/county_admin/facilities');
}


function reporting_rates($County = NULL,$year = NULL, $month = NULL) {
    if ($year == NULL) {
        $year = date('Y', time());
        $month = date('m', time());
    }

    if($County!=NULL){
        $from = ',districts,counties';
        $conditions .="and lab_commodity_orders.district_id= districts.id and districts.county = counties.id and counties.id = $County";
    }
    $firstdate = $year . '-' . $month . '-01';
    $month = date("m", strtotime("$firstdate"));
    $year = date("Y", strtotime("$firstdate"));
    $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $lastdate = $year . '-' . $month . '-' . $num_days;
    $firstdate = $year . '-' . $month . '-01';

    $sql = "select 
    lab_commodity_orders.order_date as order_date,
    count(distinct lab_commodity_orders.facility_code) as count
    from
    lab_commodity_orders $from
    WHERE
    lab_commodity_orders.order_date between '$firstdate' and '$lastdate' $conditions
    Group BY lab_commodity_orders.order_date";          
    $res = $this->db->query($sql);
    return ($res->result_array());
}

function rtk_logs($user = NULL, $UserType = NULL, $Action = NULL, $SinceDate = NULL, $FromDate = NULL) {
    $conditions = '';
    $conditions = (isset($user)) ? $conditions . " AND user.id = $user" : $conditions . ' ';
    $conditions = (isset($Action)) ? $conditions . " AND rtk_logs_reference.action = $Action" : $conditions . ' ';
    $conditions = (isset($sincedate)) ? $conditions . "AND rtk_logs.timestamp > = $sincedate" : $conditions . ' ';
    $conditions = (isset($enddate)) ? $conditions . "AND rtk_logs.timestamp < = $enddate" : $conditions . ' ';

    $sql = "SELECT *
    FROM rtk_logs,rtk_logs_reference,user
    WHERE rtk_logs.reference = rtk_logs_reference.id
    AND rtk_logs.user_id = user.id
    $conditions
    ORDER BY `rtk_logs`.`id` DESC";
    $res = $this->db->query($sql);
    return ($res->result_array());
}

public function facility_profile($mfl) {
    $data = array();
    $lastday = date('Y-m-d', strtotime("last day of previous month"));
    $County = $this->session->userdata('county_name');
    $Countyid = $this->session->userdata('county_id');
    $districts = districts::getDistrict($Countyid);         
    $sql = "select * from facilities where facility_code=$mfl"; 

    $facility = $this->db->query($sql)->result_array();        
    $mfl =  $facility[0]['facility_code'];       
    $data['reports'] = $this->_monthly_facility_reports($mfl);

    $data['facility_county'] = $data['reports'][0]['county'];
    $data['facility_district'] = $data['reports'][0]['district'];
    $data['district_id'] = $data['reports'][0]['district_id'];
    $data['facilities_in_district'] = json_encode($this->_facilities_in_district($data['district_id']));
    $data['facilities_in_district'] = str_replace('"', "'", $data['facilities_in_district']);

    $data['county_id'] = $data['reports'][0]['county_id'];
    $data['districts'] = $districts;
    $data['county'] = $County;
    $data['mfl'] = $mfl;
    $data['countyid'] = $Countyid;
    $data['title'] = $facility[0]['facility_name'] . '-' . $mfl;
    $data['facility_name'] = $facility[0]['facility_name'];
    $data['banner_text'] = 'Facility Profile: ' . $facility[0]['facility_name'] . '-' . $mfl;
    $data['content_view'] = "rtk/rtk/shared/facility_profile_view";

    $this->load->view("rtk/template", $data);
}

private function _monthly_facility_reports($mfl, $monthyear = null) {
    $conditions = '';
    if (isset($monthyear)) {
        $year = substr($monthyear, -4);
        $month = substr_replace($monthyear, "", -4);
        $firstdate = $year . '-' . $month . '-01';
        $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $lastdate = $year . '-' . $month . '-' . $num_days;
        $conditions=" AND lab_commodity_orders.order_date
        BETWEEN  '$firstdate'
        AND  '$lastdate'";
    }

    $sql = "select lab_commodity_orders.order_date,lab_commodity_orders.compiled_by,lab_commodity_orders.id,
    facilities.facility_name,districts.district,districts.id as district_id, counties.county,counties.id as county_id
    FROM lab_commodity_orders,facilities,districts,counties
    WHERE lab_commodity_orders.facility_code = facilities.facility_code
    AND facilities.district = districts.id
    AND counties.id = districts.county
    AND facilities.facility_code =$mfl $conditions";        


    $sql .=' Order by lab_commodity_orders.order_date desc';

    $res = $this->db->query($sql);
    $sum_facilities = array();
    $facility_arr = array();

    foreach ($res->result_array() as $key => $value) {
        $facility_arr = $value;
        $details = $this->fcdrr_values($value['id']);

        array_push($facility_arr, $details);
        array_push($sum_facilities, $facility_arr);
    }
    return $sum_facilities;
}
public function fcdrr_values($order_id, $commodity = null) {
    $q = 'SELECT * 
    FROM lab_commodities, lab_commodity_details, facility_amc
    WHERE lab_commodity_details.order_id =' . $order_id . '
    AND facility_amc.facility_code = lab_commodity_details.facility_code
    AND facility_amc.commodity_id = lab_commodity_details.commodity_id
    AND lab_commodity_details.commodity_id = lab_commodities.id ';


    if (isset($commodity)) {
        $q = 'SELECT * 
        FROM lab_commodities, lab_commodity_details
        WHERE lab_commodity_details.order_id =' . $order_id . '
        AND lab_commodity_details.commodity_id = lab_commodities.id
        AND commodity_id=' . $commodity;
    }

    $q_res = $this->db->query($q);
    $returnable = $q_res->result_array();
    return $returnable;
}
private function _facilities_in_district($district) {
    $sql = 'select facility_code,facility_name from facilities where district=' . $district;
    $res = $this->db->query($sql);


    return $res->result_array();
}

function district_totals($year, $month, $district = NULL) {

    $firstdate = $year . '-' . $month . '-01';
    $firstday = date("Y-m-d", strtotime("$firstdate +1 Month "));

    $month = date("m", strtotime("$firstdate +1 Month "));
    $year = date("Y", strtotime("$firstdate +1 Month "));
    $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $lastdate = $year . '-' . $month . '-' . $num_days;

    $returnable = array();

    $common_q = "SELECT lab_commodities.commodity_name,
    sum(lab_commodity_details.beginning_bal) as sum_opening, 
    sum(lab_commodity_details.q_received) as sum_received, 
    sum(lab_commodity_details.q_used) as sum_used, 
    sum(lab_commodity_details.no_of_tests_done) as sum_tests, 
    sum(lab_commodity_details.positive_adj) as sum_positive, 
    sum(lab_commodity_details.negative_adj) as sum_negative,
    sum(lab_commodity_details.losses) as sum_losses,
    sum(lab_commodity_details.closing_stock) as sum_closing_bal,
    sum(lab_commodity_details.q_requested) as sum_requested, 
    sum(lab_commodity_details.allocated) as sum_allocated,
    sum(lab_commodity_details.allocated) as sum_days,
    sum(lab_commodity_details.q_expiring) as sum_expiring
    FROM lab_commodities, lab_commodity_details, lab_commodity_orders, facilities, districts, counties 
    WHERE lab_commodity_details.commodity_id = lab_commodities.id 
    AND lab_commodity_orders.id = lab_commodity_details.order_id 
    AND facilities.facility_code = lab_commodity_details.facility_code AND facilities.district = districts.id 
    AND districts.county = counties.id 
    AND lab_commodity_orders.order_date BETWEEN  '$firstday' AND  '$lastdate'
    AND lab_commodities.id in (select lab_commodities.id from lab_commodities,lab_commodity_categories 
        where lab_commodities.category = lab_commodity_categories.id and lab_commodity_categories.active = '1')";

if (isset($district)) {
    $common_q.= ' AND districts.id =' . $district;
}

$common_q.= ' group by lab_commodities.id';

$res = $this->db->query($common_q)->result_array();       

return $res;
}
function partner_county_totals($year, $month, $county = NULL) {

    $firstdate = $year . '-' . $month . '-01';
    $firstday = date("Y-m-d", strtotime("$firstdate +1 Month "));

    $month = date("m", strtotime("$firstdate +1 Month "));
    $year = date("Y", strtotime("$firstdate +1 Month "));
    $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $lastdate = $year . '-' . $month . '-' . $num_days;

    $returnable = array();

    $common_q = "SELECT lab_commodities.commodity_name,
    sum(lab_commodity_details.beginning_bal) as sum_opening, 
    sum(lab_commodity_details.q_received) as sum_received, 
    sum(lab_commodity_details.q_used) as sum_used, 
    sum(lab_commodity_details.no_of_tests_done) as sum_tests, 
    sum(lab_commodity_details.positive_adj) as sum_positive, 
    sum(lab_commodity_details.negative_adj) as sum_negative,
    sum(lab_commodity_details.losses) as sum_losses,
    sum(lab_commodity_details.closing_stock) as sum_closing_bal,
    sum(lab_commodity_details.q_requested) as sum_requested, 
    sum(lab_commodity_details.allocated) as sum_allocated,
    sum(lab_commodity_details.allocated) as sum_days,
    sum(lab_commodity_details.q_expiring) as sum_expiring
    FROM lab_commodities, lab_commodity_details, lab_commodity_orders, facilities, districts, counties 
    WHERE lab_commodity_details.commodity_id = lab_commodities.id 
    AND lab_commodity_orders.id = lab_commodity_details.order_id 
    AND facilities.facility_code = lab_commodity_details.facility_code AND facilities.district = districts.id 
    AND districts.county = counties.id 
    AND lab_commodity_orders.order_date BETWEEN  '$firstday' AND  '$lastdate'
    AND lab_commodities.id in (select lab_commodities.id from lab_commodities,lab_commodity_categories 
        where lab_commodities.category = lab_commodity_categories.id and lab_commodity_categories.active = '1')";

if (isset($county)) {
    $common_q.= ' AND districts.county = counties.id and counties.id=' . $county;
}

$common_q.= ' group by lab_commodities.id';

$res = $this->db->query($common_q)->result_array(); 

return $res;
}

public function rtk_facilities_not_reported($zone = NULL, $county = NULL, $district = NULL, $facility = NULL, $year = NULL, $month = NULL) {

    if (!isset($month)) {
        $month_text = date('mY', strtotime('-1 month'));
        $month = date('m', strtotime("-1 month", time()));
    }

    if (!isset($year)) {
        $year = substr($month_text, -4);
    }

    $firstdate = $year . '-' . $month . '-01';
    $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $lastdate = $year . '-' . $month . '-' . $num_days;

    $conditions = '';
    $conditions = (isset($zone)) ? "AND facilities.Zone = 'Zone $zone'" : '';
    $conditions = (isset($county)) ? $conditions . " AND counties.id = $county" : $conditions . ' ';
    $conditions = (isset($district)) ? $conditions . " AND districts.id = $district" : $conditions . ' ';
    $conditions = (isset($facility)) ? $conditions . " AND facilities.facility_code = $facility" : $conditions . ' ';

    $sql = "select distinct lab_commodity_orders.facility_code
    from lab_commodity_orders, facilities, districts, counties 
    where lab_commodity_orders.order_date between '$firstdate' and '$lastdate'
    and facilities.district=districts.id 
    and districts.county = counties.id
    and facilities.rtk_enabled='1'";

        //echo "$sql";die();

    $sql2 = "select facilities.facility_code
    from facilities, districts, counties 
    where facilities.district=districts.id
    $conditions
    and districts.county = counties.id
    and facilities.rtk_enabled='1'
    ";

    $res = $this->db->query($sql);
    $reported = $res->result_array();
    $res2 = $this->db->query($sql2);
    $all = $res2->result_array();


    $unreported = array();
    $new_all = array();
    $new_reported = array();

    foreach ($all AS $key => $value) {
        $new_all[] = $value['facility_code'];
    }
    foreach ($reported AS $key => $value) {
        $new_reported[] = $value['facility_code'];
    }
    sort($new_all);
    sort($new_reported);

    $returnable = $this->flip_array_diff_key($new_all, $new_reported);

    foreach ($returnable as $value) {
        $sql3 = "select facilities.facility_code,facilities.facility_name, districts.district, counties.county,facilities.zone
        from facilities, districts, counties 
        where facilities.district=districts.id 
        and districts.county = counties.id
        and rtk_enabled='1'
        and facilities.facility_code = '$value'
        $conditions";
        $res3 = $this->db->query($sql3);
        $my_value = $res3->result_array();
        array_push($unreported, $my_value);
    }
    $report_for = $month . "-" . $year;



    foreach ($unreported AS $key => $value) {
        $new_unreported[] = $value[0];
    }
    foreach ($new_unreported as $key => $value) {
        $new_unreported[$key]['report_for'] = $report_for;
    }


    return $new_unreported;
}

function _all_counties() {
    $q = 'SELECT id,county FROM  `counties` ';
    $q_res = $this->db->query($q);
    $returnable = $q_res->result_array();
    return $returnable;
}

function flip_array_diff_key($b, $a) {
    $at = array_flip($a);
    $bt = array_flip($b);
    $d = array_diff_key($bt, $at);
    return array_keys($d);
}


function _get_rtk_users() {      
    $q = 'SELECT access_level.level,access_level.user_indicator,
    user.email, user.id AS user_id,user.fname,user.lname,user.email,user.county_id,
    user.district,counties.county,user.usertype_id FROM access_level,
    user,counties 
    WHERE user.county_id = counties.id AND user.usertype_id = access_level.id
    AND user.usertype_id = 13 ORDER BY `user`.`district` ASC';

    $res = $this->db->query($q);
    $arr = $res->result_array();
    $q2 = 'SELECT access_level.level,access_level.user_indicator,user.email,user.id AS user_id,user.fname,user.lname,user.email,user.county_id,districts.district as district,counties.county,user.usertype_id
    FROM access_level,user,counties,districts 
    WHERE user.district = districts.id
    AND districts.county = counties.id
    AND user.county_id = counties.id
    AND user.usertype_id = access_level.id
    AND user.usertype_id = 7';
    $res2 = $this->db->query($q2);
    $arr2 = $res2->result_array();
    $returnable = array_merge($arr, $arr2);       
    return $returnable;
}

public function allocation($zone = NULL, $county = NULL, $district = NULL, $facility = NULL, $sincedate = NULL, $enddate = NULL) {
        // function to filter allocation based on multiple parameter
        // zone, county,district, sincedate,
        $conditions = '';
        $conditions = (isset($zone)) ? " AND facilities.Zone = 'Zone $zone'" : '';
        $conditions = (isset($county)) ? $conditions . " AND counties.id = $county" : $conditions . ' ';
        $conditions = (isset($district)) ? $conditions . " AND districts.id = $district" : $conditions . ' ';
        $conditions = (isset($facility)) ? $conditions . " AND facilities.facility_code = $facility" : $conditions . ' ';
        $conditions = (isset($sincedate)) ? $conditions . " AND lab_commodity_details.allocated_date >= $sincedate" : $conditions . ' ';
        $conditions = (isset($enddate)) ? $conditions . " AND lab_commodity_details.allocated_date <= $enddate" : $conditions . ' ';

        $sql = "select facilities.facility_name,facilities.facility_code,facilities.Zone, facilities.contactperson,facilities.cellphone, lab_commodity_details.commodity_id,
        lab_commodity_details.allocated,lab_commodity_details.allocated_date,lab_commodity_orders.order_date,lab_commodities.commodity_name,facility_amc.amc,lab_commodity_details.closing_stock,lab_commodity_details.q_requested
        from facilities, lab_commodity_orders,lab_commodity_details, counties,districts,lab_commodities,lab_commodity_categories,facility_amc
        WHERE facilities.facility_code = lab_commodity_orders.facility_code
        AND lab_commodity_categories.id = 1
        AND lab_commodity_categories.id = lab_commodities.category
        AND counties.id = districts.county
        AND facilities.district = districts.id
        AND facilities.rtk_enabled = 1
        and lab_commodities.id = lab_commodity_details.commodity_id
        and lab_commodities.id = facility_amc.commodity_id
        and facilities.facility_code = facility_amc.facility_code
        AND lab_commodity_orders.id = lab_commodity_details.order_id
        AND lab_commodity_details.commodity_id between 1 AND 3
        $conditions
        GROUP BY facilities.facility_code, lab_commodity_details.commodity_id";
        $res = $this->db->query($sql);
        $returnable = $res->result_array();      
        return $returnable;
        #$nonexistent = "AND lab_commodity_orders.order_date BETWEEN '2014-04-01' AND '2014-04-30'";
    }


    //////Administration stuff

    public function insert_percentage_tables(){
        $month = date('mY', time());           
        $this->get_county_percentages_month($month);
        $this->get_district_percentages_month($month);
    }

    public function update_percentage_tables($month=null){
        if(isset($month)){           
            $year = substr($month, -4);
            $month = substr($month, 0,2);            
            $monthyear = $month.$year;
        }else{
            $month = date('mY',time());        
        }
        $this->get_county_percentages_month($month);
        $this->get_district_percentages_month($month);
    }
      function update_county_percentages_month($month=null){
    if(isset($month)){           
        $year = substr($month, -4);
        $month = substr($month, 0,2);            
        $monthyear = $month.$year;                    

    }
    
    $sql = "select id from counties";
    $result = $this->db->query($sql)->result_array();
     foreach ($result as $key => $value) {
        $id = $value['id'];               
        $sql = "select count(facilities.facility_code) as facilities     from
        facilities,
        districts,
        counties
        where
        facilities.district = districts.id
        and districts.county = counties.id
        and counties.id = '$id'
        and facilities.rtk_enabled = 1";
        $facilities = $this->db->query($sql)->result_array();            
        foreach ($facilities as $key => $value) {
            $facility_count = $value['facilities'];
        }
        $percentage = $this->rtk_summary_county($id,$year,$month);        
        $reported = $percentage['reported']; 
        //$reported = 0;
        $q = "insert into rtk_county_percentage (county_id, facilities,reported,month) values ($id,$facility_count,$reported,'$monthyear')";
        $this->db->query($q);
    }
}
    function get_county_percentages_month($month=null){
    if(isset($month)){           
        $year = substr($month, -4);
        $month = substr($month, 0,2);            
        $monthyear = $month.$year;                    

    }
    
    $sql = "select id from counties";
    $result = $this->db->query($sql)->result_array();
     foreach ($result as $key => $value) {
        $id = $value['id'];               
        $sql = "select count(facilities.facility_code) as facilities     from
        facilities,
        districts,
        counties
        where
        facilities.district = districts.id
        and districts.county = counties.id
        and counties.id = '$id'
        and facilities.rtk_enabled = 1";
        $facilities = $this->db->query($sql)->result_array();            
        foreach ($facilities as $key => $value) {
            $facility_count = $value['facilities'];
        }
        //$percentage = $this->rtk_summary_county($id,$year,$month);        
        //$reported = $percentage['reported']; 
        $reported = 0;
        $q = "insert into rtk_county_percentage (county_id, facilities,reported,month) values ($id,$facility_count,$reported,'$monthyear')";
        $this->db->query($q);
    }
}
function get_district_percentages_month($month=null){
    if(isset($month)){           
        $year = substr($month, -4);
        $month = substr($month, 0,2);            
        $monthyear = $month.$year;                    

    }
    $sql = "select id from districts";
    $result = $this->db->query($sql)->result_array();
    foreach ($result as $key => $value) {
        $id = $value['id'];
        $q = "select count(facilities.facility_code) as facilities from
        facilities
        where
        facilities.district = '$id'
        and facilities.rtk_enabled = '1'";               
        $facilities = $this->db->query($q)->result_array();
        foreach ($facilities as $key => $value) {
            $facility_count = $value['facilities'];
        }            
        //$percentage = $this->rtk_summary_district($id, $year, $month);
        $reported = 0;
        //$reported = $percentage['reported']; 
        $q = "insert into rtk_district_percentage (district_id, facilities,reported,month) values ($id,$facility_count,$reported,'$monthyear')";
        $this->db->query($q);

    }             

    
}
function update_district_percentages_month($month=null){
    if(isset($month)){           
        $year = substr($month, -4);
        $month = substr($month, 0,2);            
        $monthyear = $month.$year;                    

    }
    $sql = "select id from districts";
    $result = $this->db->query($sql)->result_array();
    foreach ($result as $key => $value) {
        $id = $value['id'];
        $q = "select count(facilities.facility_code) as facilities from
        facilities
        where
        facilities.district = '$id'
        and facilities.rtk_enabled = '1'";               
        $facilities = $this->db->query($q)->result_array();
        foreach ($facilities as $key => $value) {
            $facility_count = $value['facilities'];
        }            
        $percentage = $this->rtk_summary_district($id, $year, $month);
        //$reported = 0;
        $reported = $percentage['reported']; 
        $q = "insert into rtk_district_percentage (district_id, facilities,reported,month) values ($id,$facility_count,$reported,'$monthyear')";
        $this->db->query($q);

    }             

    
}

public function kemsa_district_reports($district) {
    $pdf_htm = '';
    $month = date('mY', strtotime('-1 month', time()));
    $year = substr($month, -4);
    $month = date('m', strtotime('-1 month', time()));
    $date = date('F-Y', mktime(0, 0, 0, $month, 1, $year));
    $q = 'SELECT * FROM  `districts` WHERE  `id` =' . $district;
    $res = $this->db->query($q);
    $resval = $res->result_array();
    $reportname = $resval['0']['district'] . ' district FCDRR-RTK Reports for ' . $date;
    $reports_html = "<h2>" . $reportname . "</h2><hr> ";        

    $reports_html .= $this->district_reports($year, $month, $district);
        //       echo($reports_html);die;
//      $email_address = "cecilia.wanjala@kemsa.co.ke,jbatuka@usaid.gov";
//        $email_address = "lab@kemsa.co.ke,shamim.kuppuswamy@kemsa.co.ke,onjathi@clintonhealthaccess.org,jbatuka@usaid.gov,williamnguru@gmail.com,ttunduny@gmail.com";
      // $email_address = "lab@kemsa.co.ke,williamnguru@gmail.com,ttunduny@gmail.com";
    $email_address = "ttunduny@gmail.com";
    $this->sendmail($reports_html, $reportname, $email_address);
}

public function district_reports($year, $month, $district) {
    $pdf_htm = '';
    $first_day_current_month = $year . '-' . $month . '-1';
    $firstdate = $year . '-' . $month . '-01';
    $month = date("m", strtotime("$firstdate"));
    $year = date("Y", strtotime("$firstdate"));
    $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $lastdate = $year . '-' . $month . '-' . $num_days;
    $firstdate = $year . '-' . $month . '-01';

    $thismonth = date('Y-m', time());
    $thismonth .="-1";


    $q = "SELECT DISTINCT lab_commodity_orders.facility_code, lab_commodity_orders.id,lab_commodity_orders.order_date
    FROM lab_commodity_orders, districts, counties
    WHERE districts.id = lab_commodity_orders.district_id
    AND districts.county = counties.id
    AND districts.id = $district
    AND lab_commodity_orders.order_date
    BETWEEN '$firstdate'
    AND NOW()";
//        echo $q;die;
    $res = $this->db->query($q);
    foreach ($res->result_array() as $key => $value) {
        $id = $value['id'];
        $pdf_htm .= $this->generate_lastpdf($id);
        $pdf_htm .= '<br /><br /><br /><hr/><br /><br />';
    }
    return $pdf_htm;
}

function generate_lastpdf($id) {
    $query = $this->db->query('SELECT id
        FROM  `lab_commodity_orders` 
        where `id` = ' . $id . '
        LIMIT 0 , 1');
    foreach ($query->result_array() as $row) {
        $order_no = $row['id'];
    }
    $query1 = $this->db->query('SELECT * 
        FROM lab_commodity_orders, facilities, districts,counties
        WHERE lab_commodity_orders.district_id = districts.id
        AND counties.id = districts.county
        AND facilities.facility_code = lab_commodity_orders.facility_code
        AND lab_commodity_orders.id =' . $order_no . '');
    $lab_order = $query1->result_array();

    date_default_timezone_set("EUROPE/Moscow");
    $firstday = date('D dS M Y', strtotime("first day of previous month"));
    $lastday = date('D dS M Y', strtotime("last day of previous month"));
    $lastmonth = date('F', strtotime("last day of previous month"));
    $end_date = date('dS F Y', strtotime($lab_order[0]['end_date']));
    $beg_date = date('dS F Y', strtotime($lab_order[0]['beg_date']));

    $orderdate = $lab_order[0]['order_date'];
    $month = date('F', strtotime($orderdate));
    $html_title = "<div ALIGN=CENTER><img src='" . base_url() . "Images/coat_of_arms.png' height='70' width='70'style='vertical-align: top;' > </img></div>
    <div style='text-align:center; font-size: 14px;display: block;font-weight: bold;'>RTK FCDRR Report for " . $lab_order[0]['facility_name'] . "  $month  2014</div>
    <div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold; font-size: 14px;'>
     Ministry of Health</div>
     <div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold;display: block; font-size: 13px;'>Health Commodities Management Platform</div><hr />
     <style>table.data-table {border: 1px solid #DDD;font-size: 13px;border-spacing: 0px;}
        table.data-table th {border: none;color: #036;text-align: center;background-color: #F5F5F5;border: 1px solid #DDD;border-top: none;max-width: 450px;}
        table.data-table td, table th {padding: 4px;}
        table.data-table td {border: none;border-right: 1px solid #DDD;height: 30px;margin: 0px;border-bottom: 1px solid #DDD;}
        .col5{background:#D8D8D8;}</style>";
        $table_head = '
        <table border="0" class="data-table" style="width: 100%; margin: 10px auto;">
            <tr>
                <td>Name of Facility:</td>
                <td colspan="2">' . $lab_order[0]['facility_name'] . '</td>
                <td colspan="3">Applicable to HIV Test Kits Only</td>
                <td colspan="4" style="text-align:center">Applicable to Malaria Testing Only</td>                  
            </tr>
            <tr>
                <td colspan="2" style="text-align:left">MFL Code:</td>
                <td>' . $lab_order[0]['facility_code'] . '</td>
                <td colspan="2" style="text-align:center">Type of Service</td>
                <td colspan="1" style="text-align:center">No. of Tests Done</td>
                <td colspan="1">Test</td>
                <td colspan="1">Category</td>
                <td colspan="1">No. of Tests Performed</td>
                <td colspan="1">No. Positive</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:left">District:</td>
                <td>' . $lab_order[0]['district'] . '</td>
                <td colspan="2">VCT</td>
                <td>' . $lab_order[0]['vct'] . '</td>
                <td rowspan="3">RDT</td>
                <td style="text-align:left">Patients&nbsp;<u>under</u> 5&nbsp;years</td>
                <td>' . $lab_order[0]['rdt_under_tests'] . '</td>
                <td>' . $lab_order[0]['rdt_under_pos'] . '</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:left">County:</td>                     
                <td>' . $lab_order[0]['county'] . '</td>
                <td colspan="2">PITC</td>
                <td>' . $lab_order[0]['pitc'] . '</td>
                <td style="text-align:left">Patients&nbsp;aged 5-14&nbsp;yrs</td>
                <td>' . $lab_order[0]['rdt_btwn_tests'] . '</td>
                <td>' . $lab_order[0]['rdt_btwn_pos'] . '</td>
            </tr>
            <tr><td colspan="2" style="text-align:right">Beginning:</td> 
                <td>' . $beg_date . '</td>
                <td colspan="2">PMTCT</td>
                <td>' . $lab_order[0]['pmtct'] . '</td>
                <td style="text-align:left">Patients&nbsp;<u>over</u> 14&nbsp;years</td>
                <td>' . $lab_order[0]['rdt_over_tests'] . '</td>
                <td>' . $lab_order[0]['rdt_over_pos'] . '</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:right">Ending:</td>
                <td>' . $end_date . '</td>
                <td colspan="2">Blood&nbsp;Screening</td>
                <td>' . $lab_order[0]['b_screening'] . '</td>
                <td rowspan="3">Microscopy</td>
                <td style="text-align:left">Patients&nbsp;<u>under</u> 5&nbsp;years</td>
                <td>' . $lab_order[0]['micro_under_tests'] . '</td>
                <td>' . $lab_order[0]['micro_under_pos'] . '</td>                          
            </tr>
            <tr>
                <td colspan="3"></td>
                <td colspan="2">Other&nbsp;(Please&nbsp;Specify)</td>
                <td>' . $lab_order[0]['other'] . '</td> 
                <td style="text-align:left">Patients&nbsp;aged 5-14&nbsp;yrs</td>
                <td>' . $lab_order[0]['micro_btwn_tests'] . '</td>
                <td>' . $lab_order[0]['micro_btwn_pos'] . '</td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td colspan="2">Specify&nbsp;Here:</td>
                <td>' . $lab_order[0]['specification'] . '</td>   
                <td style="text-align:left">Patients&nbsp;<u>over</u> 14&nbsp;years</td>
                <td>' . $lab_order[0]['micro_over_tests'] . '</td>
                <td>' . $lab_order[0]['micro_over_pos'] . '</td>
            </tr></table>';
            $table_head .= '<style>table.data-table {border: 1px solid #DDD;margin: 10px auto;border-spacing: 0px;}
            table.data-table th {border: none;color: #036;text-align: center;background-color: #F5F5F5;border: 1px solid #DDD;border-top: none;max-width: 450px;}
            table.data-table td, table th {padding: 4px;}
            table.data-table td {border: none;border-left: 1px solid #DDD;border-right: 1px solid #DDD;height: 20px;margin: 0px;border-bottom: 1px solid #DDD;}
            .col5{background:#D8D8D8;}</style></table>
            <table class="data-table" width="100%">
                <thead>
                    <tr>
                        <th rowspan="2"><strong>Commodity</strong></th>
                        <th rowspan="2"><strong>Unit of Issue</strong></th>
                        <th rowspan="2"><strong>Beginning Balance</strong></th>
                        <th rowspan="2"><strong>Quantity Received</strong></th>
                        <th rowspan="2"><strong>Quantity Used</strong></th>
                        <th rowspan="2"><strong>Tests Done</strong></th>
                        <th rowspan="2"><strong>Losses</strong></th>
                        <th colspan="2"><strong>Adjustments</strong></th>
                        <th rowspan="2"><strong>Closing Stock</strong></th>
                        <th rowspan="2"><strong>Qty Expiring <br />in 6 Months</strong></th>
                        <th rowspan="2"><strong>Days Out of <br />Stock</strong></th>
                        <th rowspan="2"><strong>Qty Requested</strong></th>
                    </tr>
                    <tr>
                        <th><strong>Positive</strong></th>
                        <th><strong>Negative</strong></th>
                    </tr>
                </thead>
                <tbody>';
                    $detail_list = Lab_Commodity_Details::get_order($order_no);
                    $table_body = '';
                    foreach ($detail_list as $detail) {
                        $table_body .= '<tr><td>' . $detail['commodity_name'] . '</td>';
                        $table_body .= '<td>' . $detail['unit_of_issue'] . '</td>';
                        $table_body .= '<td>' . $detail['beginning_bal'] . '</td>';
                        $table_body .= '<td>' . $detail['q_received'] . '</td>';
                        $table_body .= '<td>' . $detail['q_used'] . '</td>';
                        $table_body .= '<td>' . $detail['no_of_tests_done'] . '</td>';
                        $table_body .= '<td>' . $detail['losses'] . '</td>';
                        $table_body .= '<td>' . $detail['positive_adj'] . '</td>';
                        $table_body .= '<td>' . $detail['negative_adj'] . '</td>';
                        $table_body .= '<td>' . $detail['closing_stock'] . '</td>';
                        $table_body .= '<td>' . $detail['q_expiring'] . '</td>';
                        $table_body .= '<td>' . $detail['days_out_of_stock'] . '</td>';
                        $table_body .= '<td>' . $detail['q_requested'] . '</td></tr>';
                    }
                    $table_foot = '</tbody></table>';

                    $table_foot .= '
                    <table border="0" style="width: 100%;border: 1px solid #DDD;">
                        <tr>
                            <td style="text-align:left">Explaination of Losses and Adjustments</td><td  style="width: 57%;">' . $lab_order[0]['explanation'] . '</td>
                        </tr>
                        <tr style="background: #ECE8FD;">
                            <td>(1) Daily Activity Register for Laboratory Reagents and Consumables (MOH 642):</td><td>' . $lab_order[0]['moh_642'] . '</td>
                        </tr>
                        <tr>
                            <td  >(2) F-CDRR for Laboratory Commodities (MOH 643):</b></td><td>' . $lab_order[0]['moh_643'] . '</td>
                        </tr>
                        <tr style="background: #ECE8FD;">                   
                            <td style="text-align:left">Compiled by: </td><td>' . $lab_order[0]['compiled_by'] . '</td>
                        </tr> 
                    </table>';
                    $report_name = "Lab Commodities Order " . $order_no . " Details";
                    $title = "Lab Commodities Order " . $order_no . " Details";
                    $html_data = $html_title . $table_head . $table_body . $table_foot;

                    $filename = "RTK FCDRR Report for " . $lab_order[0]['facility_name'] . "  $lastmonth  2014";
                    return $html_data;
                }

public function sendmail($output, $reportname, $email_address) {
    $this->load->helper('file');
    include 'rtk_mailer.php';
    $newmail = new rtk_mailer();
    $this->load->library('mpdf');
    $mpdf = new mPDF('', 'A4-L', 0, '', 15, 15, 16, 16, 9, 5, 'L');
    $mpdf->WriteHTML($output);
    $emailAttachment = $mpdf->Output($reportname . '.pdf', 'S');
    $attach_file = './pdf/' . $reportname . '.pdf';
    if (!write_file('./pdf/' . $reportname . '.pdf', $mpdf->Output('report_name.pdf', 'S'))) {
        $this->session->set_flashdata('system_error_message', 'An error occured');
    } else {
        $subject = '' . $reportname;

        $attach_file = './pdf/' . $reportname . '.pdf';
        $bcc_email = 'tngugi@clintonhealthaccess.org';
        $message = $output;
        $response = $newmail->send_email($email_address, $message, $subject, $attach_file, $bcc_email);
            // $response= $newmail->send_email(substr($email_address,0,-1),$message,$subject,$attach_file,$bcc_email);
        if ($response) {
            delete_files('./pdf/' . $reportname . '.pdf');
        }
    }
}
function _national_reports_sum($year, $month) {

        $returnable = array();

        $firstdate = $year . '-' . $month . '-01';
        $firstday = date("Y-m-d", strtotime("$firstdate +1 Month "));

        $month = date("m", strtotime("$firstdate  +1 Month "));
        $year = date("Y", strtotime("$firstdate  +1 Month "));
        $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $lastdate = $year . '-' . $month . '-' . $num_days;
        $sql = "SELECT     
        counties.county, counties.id, lab_commodities.commodity_name,
        sum(lab_commodity_details.beginning_bal) as sum_opening,
        sum(lab_commodity_details.q_received) as sum_received,
        sum(lab_commodity_details.q_used) as sum_used,
        sum(lab_commodity_details.no_of_tests_done) as sum_tests,
        sum(lab_commodity_details.positive_adj) as sum_positive,
        sum(lab_commodity_details.negative_adj) as sum_negative,
        sum(lab_commodity_details.losses) as sum_losses,
        sum(lab_commodity_details.closing_stock) as sum_closing_bal,
        sum(lab_commodity_details.q_requested) as sum_requested,
        sum(lab_commodity_details.allocated) as sum_allocated,
        sum(lab_commodity_details.allocated) as sum_days,
        sum(lab_commodity_details.q_expiring) as sum_expiring
        FROM
        lab_commodities,
        lab_commodity_details,
        lab_commodity_orders,
        facilities,
        districts,
        counties
        WHERE
        lab_commodity_details.commodity_id = lab_commodities.id
        AND lab_commodity_orders.id = lab_commodity_details.order_id
        AND facilities.facility_code = lab_commodity_details.facility_code
        AND facilities.district = districts.id
        AND districts.county = counties.id
        AND lab_commodity_orders.order_date BETWEEN '$firstdate' AND '$lastdate'";    

        $sql2 = $sql . " AND lab_commodities.id = 1 Group By counties.county";
        $res = $this->db->query($sql2)->result_array();
        array_push($returnable, $res);

        $sql3 = $sql . " AND lab_commodities.id = 2 Group By counties.county";
        $res2 = $this->db->query($sql3)->result_array();
        array_push($returnable, $res2);

        $sql4 = $sql . " AND lab_commodities.id = 4 Group By counties.county";
        $res3 = $this->db->query($sql4)->result_array();
        array_push($returnable, $res3);

        $sql5 = $sql . " AND lab_commodities.id = 5 Group By counties.county";
        $res4 = $this->db->query($sql5)->result_array();
        array_push($returnable, $res4);

        $sql6 = $sql . " AND lab_commodities.id = 6 Group By counties.county";
        $res5 = $this->db->query($sql6)->result_array();
        array_push($returnable, $res5);
        
//         echo "<pre>";print_r($returnable);die;
        return $returnable;
      }



}
?>