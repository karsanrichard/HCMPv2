<?php
/**
 * @author Kariuki
 */
class Hcmp_functions extends MY_Controller {
	
	var $test_mode=true;
	
		function __construct() {
		parent::__construct();
		$this -> load -> helper(array('url','file'));
		$this -> load -> library(array('phpexcel/phpexcel','mpdf/mpdf'));
	}

	
public function send_stock_update_sms(){
       $facility_name = $this -> session -> userdata('full_name');
	   $facility_code=$this -> session -> userdata('news');
	   $data=User::getUsers($facility_code)->toArray();

	   $message= "Stock level for ".$facility_name." have been updated. HCMP";
       
	   $phone=$this->get_facility_phone_numbers($facility_code);
	   $phone .=$this->get_ddp_phone_numbers($data[0]['district']);

	   
	   $this->send_sms(substr($phone,0,-1),$message);

	}

public function send_order_sms(){
	

       $facility_name = $this -> session -> userdata('full_name');
	   $facility_code=$this -> session -> userdata('news');
	   $data=User::getUsers($facility_code)->toArray();

	   $message= $facility_name." has submitted an order. HCMP";
       
	   $phone=$this->get_facility_phone_numbers($facility_code);
	   $phone .=$this->get_ddp_phone_numbers($data[0]['district']);

	   
	   $this->send_sms(substr($phone,0,-1),$message);

	}
public function send_order_approval_sms($facility_code,$status){

	  
       
       $message=($status==1)?$facility_name." order has been rejected. HCMP":
       $facility_name." order has been approved. HCMP";
 
        $data=User::getUsers($facility_code)->toArray();
	   $phone=$this->get_facility_phone_numbers($facility_code);
	   $phone .=$this->get_ddp_phone_numbers($data[0]['district']);

	   
	   $this->send_sms(substr($phone,0,-1),$message);

	}

public function send_stock_decommission_email($message,$subject,$attach_file){
	
	   $facility_code=$this -> session -> userdata('news');
	   
	   $data=User::getUsers($facility_code)->toArray();
	   
	   $email_address=$this->get_facility_email($facility_code);
	   
	   $email_address .=$this->get_ddp_email($data[0]['district']);

	   
	   $this->send_email(substr($email_address,0,-1),$message,$subject,$attach_file);
	   
	 
	}
public function get_facility_phone_numbers($facility_code){
	$data=User::get_user_info($facility_code);
	$phone=""; 
	foreach ($data as $info) {

			$telephone =preg_replace('(^0+)', "254",$info->telephone);

		    $phone .=$telephone.'+';	
		}
	return $phone;
}
public function get_facility_email($facility_code){
	$data=User::get_user_info($facility_code);
	$user_email=""; 
	foreach ($data as $info) {

			$user_email .=$info->email.',';

		   	
		}
	return $user_email;
}

public function get_ddp_phone_numbers($district_id){
	$data=User::get_dpp_details($district_id);
	$phone=""; 
	
	foreach ($data as $info) {
			$telephone =preg_replace('(^0+)', "254",$info->telephone);
		    $phone .=$telephone.'+';	
		}
	return $phone;
}
public function get_ddp_email($district_id){
	$data=User::get_dpp_details($district_id);
	$user_email=""; 
	foreach ($data as $info) {

			$user_email .=$info->email.',';
		}
	return $user_email;
}
public function send_stock_donate_sms(){
		
       $facility_name = $this -> session -> userdata('full_name');
	   $facility_code=$this -> session -> userdata('news');
	   $data=User::getUsers($facility_code)->toArray();

	   //$message= "Stock level for ".$facility_name." has been updated. HCMP";
       
	   $phone=$this->get_facility_phone_numbers($facility_code);
	   $phone .=$this->get_ddp_phone_numbers($data[0]['district']);
	    $message= $facility_name." have been donated commodities. HCMP";		
		$this->send_sms(substr($phone,0,-1),$message);
		
		

	}


public function send_sms($phones,$message) {
	
   $message=urlencode($message);
   //$spam_sms='254726534272+254720167245';	
   $spam_sms='254720167245+254726534272+254726416795+254725227833+'.$phones;
//  $spam_sms='254726534272';
 	# code...
 	
 	$phone_numbers=explode("+", $spam_sms);
	
	//foreach($phone_numbers as $key=>$user_no):
  //  break;
	//file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$user_no&text=$message");
		
	//endforeach;
 		
	}
public function send_order_submission_email($message,$subject,$attach_file){
	 
      $cc_email=($this->test_mode)?'kariukijackson@gmail.com': 'anganga.pmo@gmail.com,sochola06@yahoo.com';
	 
		
	   $facility_code=$this -> session -> userdata('news');
	   
	   $data=User::getUsers($facility_code)->toArray();
	   
	   $email_address=$this->get_facility_email($facility_code);
	   
	   $email_address .=$this->get_ddp_email($data[0]['district']);
		
		
		return $this->send_email(substr($email_address,0,-1),$message, $subject,$attach_file,null,$cc_email);
	
}
public function send_order_approval_email($message,$subject,$attach_file,$facility_code,$reject_order=null){
	  
 $cc_email=($this->test_mode)?'kariukijackson@gmail.com': 'anganga.pmo@gmail.com,sochola06@yahoo.com';
	   
	  
 $data=User::getUsers($facility_code)->toArray();
  if($reject_order==1):
	  $email_address=$this->get_facility_email($facility_code);
	  $cc_email .=$this->get_ddp_email($data[0]['district']);
	  
	  else:
		  
		  $email_address=($this->test_mode)?'kariukijackson@gmail.com': 'shamim.kuppuswamy@kemsa.co.ke,
samuel.wataku@kemsa.co.ke,
jmunyu@kemsa.co.ke,
imugada@kemsa.co.ke,
laban.okune@kemsa.co.ke,
samuel.wataku@kemsa.co.ke,'; 

	  $cc_email .=$this->get_ddp_email($data[0]['district']);
	   $cc_email .=$this->get_facility_email($facility_code); 
  endif;
	  

		return $this->send_email(substr($email_address,0,-1),$message, $subject,$attach_file,null,substr($cc_email,0,-1));
	
}
public function send_order_delivery_email($message,$subject,$attach_file=null){

$cc_email=($this->test_mode)?'kariukijackson@gmail.com': 'anganga.pmo@gmail.com,sochola06@yahoo.com';

	   $facility_code=$this -> session -> userdata('news');
	   
	   $data=User::getUsers($facility_code)->toArray();
	   
	   $cc_email .=$this->get_facility_email($facility_code);
	   
		
		
	return $this->send_email(substr($this->get_ddp_email($data[0]['district']),0,-1),$message,$subject,null,null,substr($cc_email,0,-1));
	
}
/*****************************************Email function for HCMP, all the deafult email addresses and email content have been set ***************/

public function send_email($email_address,$message,$subject,$attach_file=NULL,$bcc_email=NULL,$cc_email=NULL){
  
		$mail_list=($this->test_mode)?'kariukijackson@gmail.com,kariukijackson@ymail.com': 'rkihoto@clintonhealthaccess.org,
  		eunicew2000@yahoo.com,
  		gmacharia@clintonhealthaccess.org,
  		Jhungu@clintonhealthaccess.org,
  		nmaingi@strathmore.edu,
  		bwariari@clintonhealthaccess.org,
  		kyalocatherine@gmail.com,
  		ashminneh.mugo@gmail.com,
  		smutheu@clintonhealthaccess.org,
  		kariukijackson@gmail.com,
  		kelvinmwas@gmail.com,';
			
		$fromm='hcmpkenya@gmail.com';
		$messages=$message;
  		$config['protocol']    = 'smtp';
        $config['smtp_host']    = 'ssl://smtp.gmail.com';
        $config['smtp_port']    = '465';
        $config['smtp_timeout'] = '7';
        $config['smtp_user']    = 'hcmpkenya@gmail.com';
        $config['smtp_pass']    = 'healthkenya';
        $config['charset']    = 'utf-8';
        $config['newline']    = "\r\n";
        $config['mailtype'] = 'html'; // or html
        $config['validation'] = TRUE; // bool whether to validate email or not  
		$this->load->library('email', $config);

        $this->email->initialize($config);
		
  		$this->email->set_newline("\r\n");
  		$this->email->from($fromm,'Health Commodities Management Platform'); // change it to yours
  		$this->email->to($email_address); // change it to yours
  		
  		isset($cc_email)? $this->email->cc($cc_email): //
  		
  		(isset($bcc_email))?
  		$this->email->bcc($mail_list.$bcc_email)
  		
  		:
  		$this->email->bcc(substr($mail_list,0,-1))

		;
  		
		 (isset($attach_file))? $this->email->attach($attach_file) :	'';
			
  		$this->email->subject($subject);
 		$this->email->message($messages);
 
  if($this->email->send())
 {
return TRUE;
 }
 else
{
 return FALSE;

 
}

}
/**************************************** creating excel sheet for the system *************************/
	public function create_excel($excel_data=NUll) {
		
 //check if the excel data has been set if not exit the excel generation    
     
if(count($excel_data)>0):
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel -> getProperties() -> setCreator("HCMP");
		$objPHPExcel -> getProperties() -> setLastModifiedBy($excel_data['doc_creator']);
		$objPHPExcel -> getProperties() -> setTitle($excel_data['doc_title']);
		$objPHPExcel -> getProperties() -> setSubject($excel_data['doc_title']);
		$objPHPExcel -> getProperties() -> setDescription("");

		$objPHPExcel -> setActiveSheetIndex(0);

		$rowExec = 1;

		//Looping through the cells
		$column = 0;

		foreach ($excel_data['column_data'] as $column_data) {
			$objPHPExcel -> getActiveSheet() -> setCellValueByColumnAndRow($column, $rowExec, $column_data);
			$objPHPExcel -> getActiveSheet() -> getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column)) -> setAutoSize(true);
			//$objPHPExcel->getActiveSheet()->getStyle($column, $rowExec)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowExec)->getFont()->setBold(true);
			$column++;
		}		
		$rowExec = 2;
				
		foreach ($excel_data['row_data'] as $row_data) {
		$column = 0;
        foreach($row_data as $cell){
         //Looping through the cells per facility
		$objPHPExcel -> getActiveSheet() -> setCellValueByColumnAndRow($column, $rowExec, $cell);
				
		$column++;	
         }
        $rowExec++;
		}

		//	echo date('H:i:s') . " Rename sheet\n";
		$objPHPExcel -> getActiveSheet() -> setTitle('Simple');

		// Save Excel 2007 file
		//echo date('H:i:s') . " Write to Excel2007 format\n";
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

		// We'll be outputting an excel file
		header('Content-type: application/vnd.ms-excel');

		// It will be called file.xls
		header("Content-Disposition: attachment; filename=".$excel_data['file_name'].".xls");

		// Write file to the browser
		$objWriter -> save('php://output');
		// Echo done
endif;
}
/*************/	
/* HCMP PDF creator
/********/	

public function create_pdf($pdf_data=NULL){

if(count($pdf_data)>0):	
$url=base_url().'assets/img/coat_of_arms.png';
$html_title="<div align=center><img src='$url' height='70' width='70'style='vertical-align: top;'> </img></div>
<div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold; font-size: 14px;'>$pdf_data[pdf_title]</div>
<div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold; font-size: 14px;'>
Ministry of Health</div>
<div style='text-align:center; font-family: arial,helvetica,clean,sans-serif;display: block; font-weight: bold;display: block; font-size: 13px;'>
Health Commodities Management Platform</div><hr/>";

$table_style='<style>table.data-table {border: 1px solid #DDD;margin: 10px auto;border-spacing: 0px;}
table.data-table th {border: none;color: #036;text-align: center;border: 1px solid #DDD;border-top: none;max-width: 450px;}
table.data-table td, table th {padding: 4px;}
table.data-table td {border: none;border-left: 1px solid #DDD;border-right: 1px solid #DDD;height: 30px;margin: 0px;border-bottom: 1px solid #DDD;}
</style>';
            $name=$this -> session -> userdata('full_name');
	        $this->mpdf = new mPDF('', 'A4-L', 0, '', 15, 15, 16, 16, 9, 9, '');
            $this->mpdf->WriteHTML($html_title);
            $this->mpdf->defaultheaderline = 1;  
            $this->mpdf->simpleTables = true;
            $this->mpdf->WriteHTML($table_style.$pdf_data['pdf_html_body']);
            $this->mpdf->SetFooter("{DATE D j M Y }|{PAGENO}/{nb}|Prepared by: $name, source HCMP");

			
	if($pdf_data['pdf_view_option']=='save_file'):
		 //change the pdf to a binary file then use codeigniter write function to write the file as pdf in a specific folder 
		 
		 // $this->mpdf->Output(realpath($path).'arif.pdf','F'); 
		    if(write_file( './pdf/'.$pdf_data['file_name'].'.pdf',$this->mpdf->Output($pdf_data['file_name'],'S'))):return true; else: return false; endif;
	        else:
			//show the pdf on the bowser let the user determine where to save it;
	        $this->mpdf->Output($pdf_data['file_name'],'I');
			exit;
	endif;		

	
endif;
}
/****************************END************************/
 //// /////HCMP Create high chart graph
  public function create_high_chart_graph($graph_data=null)
  {
  	$high_chart='';
  	if(isset($graph_data)):
		$graph_id=$graph_data['graph_id'];
		$graph_title=$graph_data['graph_title'];
		$graph_type=$graph_data['graph_type'];
		$graph_categories=json_encode($graph_data['graph_categories']);
		//echo json_encode($graph_data['graph_categories']);
		$graph_yaxis_title=$graph_data['graph_yaxis_title'];
		$graph_series_data=$graph_data['series_data'];
		//$new_array=$graph_series_data;
		//return ($graph_series_data[0]); key		
		//$size_of_graph=sizeof($graph_series_data[key($graph_series_data)])*200;
		//set up the graph here
		$high_chart .="
		$('#$graph_id').highcharts({
		    chart: { zoomType:'x', type: '$graph_type'},
            credits: { enabled:false},
            title: {text: '$graph_title'},
            yAxis: { min: 0, title: {text: '$graph_yaxis_title' }},
            subtitle: {text: 'Source: HCMP', x: -20 },
            xAxis: { categories: $graph_categories },
            tooltip: { crosshairs: [true,true] },
            series: [";			 
		    foreach($graph_series_data as $key=>$raw_data):
					$temp_array=array();
					$high_chart .="{ name: '$key', data:";					 
					  foreach($raw_data as $key_data):
						$temp_array=array_merge($temp_array,array((int)$key_data));
						endforeach;
					  $high_chart .= json_encode($temp_array)."},";				  
				   endforeach;
	     $high_chart .="]  })";

	endif;
	return $high_chart; 	
  }
/****************************END************************/
  public function create_data_table($table_data=null){
  	$table_data_html='';
  	if(isset($table_data)):
		$table_id=$table_data['table_id'];
		$table_header=$table_data['table_header'];
		$table_body=$table_data['table_body'];
		$table_data_html .="<table width='100%' style='margin-top:1em;'  
		class='row-fluid table table-hover table-bordered table-update'  id='$table_id'>
	    <thead><tr>";
		foreach($table_header as $key=> $header_data):
		foreach($header_data as $header):
		$table_data_html .="<th>$header</th>";
		endforeach;	
		endforeach;
		$table_data_html .="</tr></thead><tbody>";
		foreach($table_body as $key=> $row):
			$table_data_html .="<tr>";
			foreach($row as $body_data):
				$table_data_html .="<td>$body_data</td>";
			endforeach;	
			$table_data_html .="</tr>";	
		endforeach;
       $table_data_html .="</tbody></table>";
	endif;
	return $table_data_html; 	
  }
/****************************END************************/
 public function create_order_delivery_color_coded_table($order_id){
// get the order and order details here
$detail_list=facility_order_details::get_order_details($order_id,true);
$dates=facility_orders::get_order_($order_id)->toArray();
$facility_name=Facilities::get_facility_name_($dates[0]['facility_code'])->toArray();
$facility_name=$facility_name[0]['facility_name'];
//set up the details		
$table_body="";
$total_fill_rate=0;
$order_value =0;
//get the lead time
$ts1 = strtotime(date($dates[0]["order_date"]));
$ts2 = strtotime(date($dates[0]["deliver_date"]));
$seconds_diff = $ts2 - $ts1; //strtotime($a_date) ? date('d M, Y', strtotime($a_date)) : "N/A";
$date_diff=strtotime($dates[0]["deliver_date"]) ? floor($seconds_diff/3600/24) : "N/A";
$order_date=strtotime($dates[0]["order_date"]) ? date('D j M, Y', $ts1) : "N/A";
$deliver_date=strtotime($dates[0]["deliver_date"]) ? date('D j M, Y', $ts2) : "N/A";
$kemsa_order_no=$dates[0]['kemsa_order_id'];
$order_total=number_format($dates[0]['order_total'], 2, '.', ','); 
$actual_order_total=number_format($date[0]['deliver_total'], 2, '.', ',');
$tester= count($detail_list);
      if($tester==0){ }
	  else{
		foreach($detail_list as $rows){
			//setting the values to display
			 $received=$rows['quantity_recieved'];
			 $price=$rows['unit_cost'];
			 $ordered=$rows['quantity_ordered_unit'];
			 $code=$rows['commodity_id'];	 	
			 $drug_name=$rows['commodity_name'];
			 $kemsa_code=$rows['commodity_code'];
			 $unit_size=$rows['unit_size'];
			 $total_units=$rows['total_commodity_units'];
			 $cat_name=$rows['sub_category_name'];		
		     $received=round(@$received/$total_units);
		     $fill_rate=round(@($received/$ordered)*100);
			 $total=$price* $ordered;	
			 $total_=$price* $received;	
	         $total_fill_rate=$total_fill_rate+$fill_rate;			
		switch (true) {
		case $fill_rate==0:
		 $table_body .="<tr style='background-color: #FBBBB9;'>";
		 $table_body .= "<td>$cat_name</td>";
	     $table_body .= '<td>'.$drug_name.'</td><td>'. $kemsa_code.'</td>'.'<td>'.$unit_size.'</td>';
		 $table_body .='<td>'. $price.'</td>';
		 $table_body .='<td>'.$ordered.'</td>';
		 $table_body .='<td>'.number_format($total, 2, '.', ',').'</td>';
		 $table_body .='<td>'.$received.'</td>';	
         $table_body .='<td>'.number_format($total_, 2, '.', ',').'</td>';
         $table_body .='<td>'.$fill_rate .'% '.'</td>';
		 $table_body .='</tr>'; 
		 break;  					 
		 case $fill_rate<=60:
		 $table_body .="<tr style=' background-color: #FAF8CC;'>";
		 $table_body .= "<td>$cat_name</td>";
	     $table_body .= '<td>'.$drug_name.'</td><td>'. $kemsa_code.'</td>'.'<td>'.$unit_size.'</td>';
		 $table_body .='<td>'. $price.'</td>';
		 $table_body .='<td>'.$ordered.'</td>';
		 $table_body .='<td>'.number_format($total, 2, '.', ',').'</td>';
		 $table_body .='<td>'.$received.'</td>';	
         $table_body .='<td>'.number_format($total_, 2, '.', ',').'</td>';
         $table_body .='<td>'.$fill_rate .'% '.'</td>';
		 $table_body .='</tr>'; 
		 break; 
         case $fill_rate>100.01: 
		 case $fill_rate==100.01:
		 $table_body .="<tr style='background-color: #ea1e17'>";
		 $table_body .= "<td>$cat_name</td>";
	     $table_body .= '<td>'.$drug_name.'</td><td>'. $kemsa_code.'</td>'.'<td>'.$unit_size.'</td>';
		 $table_body .='<td>'. $price.'</td>';
		 $table_body .='<td>'.$ordered.'</td>';
		 $table_body .='<td>'.number_format($total, 2, '.', ',').'</td>';
		 $table_body .='<td>'.$received.'</td>';	
         $table_body .='<td>'.number_format($total_, 2, '.', ',').'</td>';
         $table_body .='<td>'.$fill_rate .'% '.'</td>';
		 $table_body .='</tr>'; 
		 break;		  
		 case $fill_rate==100:
		 $table_body .="<tr style=' background-color: #C3FDB8;'>";
		 $table_body .= "<td>$cat_name</td>";
	     $table_body .= '<td>'.$drug_name.'</td><td>'. $kemsa_code.'</td>'.'<td>'.$unit_size.'</td>';
		 $table_body .='<td>'. $price.'</td>';
		 $table_body .='<td>'.$ordered.'</td>';
		 $table_body .='<td>'.number_format($total, 2, '.', ',').'</td>';
		 $table_body .='<td>'.$received.'</td>';	
         $table_body .='<td>'.number_format($total_, 2, '.', ',').'</td>';
        $table_body .='<td>'.$fill_rate .'% '.'</td>';
		 $table_body .='</tr>'; 
		 break;				 
		 default :
		 $table_body .="<tr>";
		 $table_body .= "<td>$cat_name</td>";
	     $table_body .= '<td>'.$drug_name.'</td><td>'. $kemsa_code.'</td>'.'<td>'.$unit_size.'</td>';
		 $table_body .='<td>'. $price.'</td>';
		 $table_body .='<td>'.$ordered.'</td>';
		 $table_body .='<td>'.number_format($total, 2, '.', ',').'</td>';
		 $table_body .='<td>'.$received.'</td>';	
         $table_body .='<td>'.number_format($total_, 2, '.', ',').'</td>';
        $table_body .='<td>'.$fill_rate .'% '.'</td>';
		 $table_body .='</tr>'; 
		 break;			
		 }
		} 
	$order_value  = round(($total_fill_rate/count($detail_list)),0,PHP_ROUND_HALF_UP);
	}	
	$message=<<<HTML_DATA
<table id="main1" width="100%" class="row-fluid table table-bordered data-table">
	<thead>
		<tr>
		<th colspan='11'>
		<p>$facility_name</p>
		<p>Fill rate(Quantity Ordered/Quantity Received)</p>
         <p style="letter-spacing: 1px;font-weight: bold;text-shadow: 0 1px rgba(0, 0, 0, 0.1);">
Facility Order No $order_id| KEMSA Order No $kemsa_order_no | Total ordered value(ksh) $order_total | Total recieved order value(ksh) $actual_order_total |Date Ordered $order_date| Date Delivered $deliver_date| Order lead Time $date_diff; day(s)</p>
		</th>
		</tr>
		<tr>
		<th width="50px" style="background-color: #C3FDB8; "></th>
		<th>Full Delivery 100%</th>
		<th width="50px" style="background-color:#FFFFFF"></th>
		<th>Ok Delivery 60%-less than 100%</th>
		<th width="50px" style="background-color:#FAF8CC;"></th> 
		<th>Partial Delivery less than 60% </th>
		<th width="50px" style="background-color:#FBBBB9;"></th>
		<th>Problematic Delivery 0% </th>
		<th width="50px" style="background-color:#ea1e17;"></th>
		<th>Problematic Delivery over 100%</th>
		</tr>
		<tr>
		<th><strong>Category</strong></th>
		<th><strong>Description</strong></th>
		<th><strong>Commodity Code</strong></th>
		<th><strong>Unit Size</strong></th>
		<th><strong>Unit Cost Ksh</strong></th>
		<th><strong>Quantity Ordered</strong></th>
		<th><strong>Total Cost</strong></th>
		<th><strong>Quantity Received</strong></th>
		<th><strong>Total Cost</strong></th>
		<th><strong>Fill rate</strong></th>	
		</tr>
	</thead>
	<tbody>	
		 $table_body	
	</tbody>
</table>
HTML_DATA;
return array('table'=>$message,'date_ordered'=>$order_date,'date_received'=>$deliver_date,'order_total'=>$order_total,'actual_order_total'=>$actual_order_total,'lead_time'=>$date_diff,'facility_name'=>$facility_name);
 }
}
