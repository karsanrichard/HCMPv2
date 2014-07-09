<style type="text/css">
.table-bordered{border: 1px solid #FFF !important;}
.table-bordered>thead>tr>th, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>tbody>tr>td, .table-bordered>tfoot>tr>td {
border: 1px solid #FFF !important;
}
</style>
<button type="submit" id="filter" name="filter" class="btn btn-default ">Download PDF <span class="glyphicon glyphicon-download"></span> </button>
 <div style="min-height: 400px; background:#edc1d8">
  <img style="align-content: center;margin-left: 46%; border:0 none;" src="<?php echo base_url();?>assets/img/coat_of_arms-resized1.png" class="img-responsive " alt="Responsive image">
        <div id="" style="text-align: center; ">
          <span style="font-size: 0.95em;font-weight: bold; ">Ministry of Health</span><br />
          <span style="font-size: 0.9em;">Health Commodities Management Platform (HCMP)</span> 
        </div>
    <table  class="table table-bordered table-update" id="" style="text-transform:capitalize">
  <thead style="">
  <tr>
    <th>Date of Issue</th>
    <th>Reference No/S11 No</th>
    <th>Commodity Unit Size</th>
    <th>Batch No -Issued</th>
    <th>Expiry Date</th>
    <th>Opening Bal.</th>
    <th>+ADJ</th>
    <th style="white-space:nowrap;">-ADJ</th>
    <th>Receipts/Issues</th>
    <th>Closing Bal.</th>
    <th>Service Point</th>
    <th>Issuing/Receiving Officer</th>
  </tr>
  </thead>    
    <tbody>   
     <?php   
             foreach ($bin_card as $bin ) { 
                $bin['unit_size'];
                $formatdate = new DateTime($bin['date_issued']);
                $formated_date= $formatdate->format('d M Y');
                $formatdate_exp = new DateTime($bin['expiry_date']);
                $formated_date_exp= $formatdate_exp->format('d M Y');
                $bin['batch_no'];
                $calculated=$bin['balance_as_of'];
                $bin['balance_as_of'];
                $negative_adj= $bin['adjustmentnve'];
                $positive_adj= $bin['adjustmentpve'];
                $bin['fname'];
                $bin['lname'];
                $bin['service_point_name']; 
              if ($positive_adj ==0 && $negative_adj == 0) {

                $closing_bal= ($bin['balance_as_of']-$bin['qty_issued']);
                $adj_value="-";
              
              }elseif ($negative_adj !=0) {

                 $closing_bal= ($bin['balance_as_of']+$negative_adj);
              
              }elseif ($positive_adj !=0) {

                 $closing_bal= ($bin['balance_as_of']+$positive_adj);
               
              }        
               if ($bin['qty_issued'] < 0) {
                 $qty_issued= (string)$bin['qty_issued'];
                 $qty_issued_text= trim($qty_issued, "-");
                 }else{
                   $qty_issued_text= $bin['qty_issued'];
<<<<<<< HEAD
                 }            
        

                  if ($bin['s11_No']=='initial stock update') {
                $color="red";
                $formated_date_exp="N/A";
                 }elseif ($bin['s11_No']=='stock addition') {
                   $color="red";
                 }
                 else{
                  $color="black";
                 }
               ?>
        
            <tr style="color:<?php echo $color;?> " >
              
=======
                 }                   
                ?>
            <tr>             
>>>>>>> 94e035ca7ca8a8c7be4c08a306d8d3567b06e65c
              <td><?php echo $formated_date;?> </td>
             
              <td ><?php echo $bin['s11_No']; ;?> </td>
             
              <td><?php echo $bin['unit_size'];?> </td>
              <td><?php echo $bin['batch_no'];?> </td>
              <td style="white-space:nowrap;"l><?php echo $formated_date_exp;?> </td>
              <td><?php echo $bin['balance_as_of'];?> </td>
              <?php  if ($positive_adj ==0 && $negative_adj == 0) {
               ?>
               <td>-</td>
               <td>-</td>
               <?php
              }elseif ($negative_adj !=0) {
               
               ?>
               <td>-</td>
               <td><?php echo $negative_adj; ?></td>
               <?php
              }elseif ($positive_adj !=0) {
               ?>
               <td><?php echo $positive_adj; ?></td>
               <td>-</td>
               <?php
              } ?>
              <td><?php echo   $qty_issued_text;?> </td>
              <td><?php echo  $closing_bal;?> </td>
              <td><?php echo $bin['service_point_name']; ?> </td>
              <td><?php echo $bin['fname'].' '.$bin['lname'];?> </td>
            </tr>
          <?php
<<<<<<< HEAD
              
              }        

=======
               }
>>>>>>> 94e035ca7ca8a8c7be4c08a306d8d3567b06e65c
          ?>  
   </tbody>
</table>
  </div>
  <script type="text/javascript">
$(document).ready(function() {


 $(".optioncheck").change(function() {
      
      var div="#reports_display";
      var url = "<?php echo base_url()."reports/stock_control_ajax"?>";
      var data= $(this).val();
     
      ajax_post_process_data (url,div,data);
   
     
    });

   function ajax_post_process_data (url,div,data){
    var url =url;
     var loading_icon="<?php echo base_url().'assets/img/loader.gif' ?>";
     $.ajax({
          type: "POST",
          data:{'option_selected': data},
          url: url,
          beforeSend: function() {
            $(div).html("");
            
             $(div).html("<img style='margin:20% 0 20% 30%;' src="+loading_icon+">");
            
          },
          success: function(msg) {
          $(div).html("");
           $(div).html(msg);           
          }
        }); 
}
 

});


</script>