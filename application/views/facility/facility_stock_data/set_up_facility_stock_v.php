<div class="test"><div class="container" style="width: 96%; margin: auto;">
<span  class='label label-info' style="padding-left: 1%"> Select the Commodities that are used in this facility by inputting the AMC or check box</span>
</div>
<div style="max-height:600px; overflow-y:auto; width: 100%">
<table width="100%" class="row-fluid table table-hover table-bordered table-update"  id="example">
<thead>
<tr style="background-color: white">
						<th>Category</th>
						<th>Description</th>
						<th>Commodity&nbsp;Code</th>
						<th>Unit Size</th>
						<th>Issue Type</th>
					    <th>Average&nbsp;Consumption&nbsp;Quantity </th>
					    <th>Total&nbsp;Units</th>
					    <th>Select</th>			    
</tr>
</thead>
<tbody>
<?php   foreach($commodities as $facility_commodities):
       $status=isset($facility_commodities['selected_option']) ? 'checked="true"'  : null;  $pack_size=$unit_size=null;
	   isset($facility_commodities['selected_option']) ? 
	   ($facility_commodities['selected_option']=='Pack_Size')? $pack_size='selected="selected"' :$unit_size='selected="selected"'  : null; 
	   echo "<tr><input type='hidden' name='actual_units[]' class='actual_units' value='$facility_commodities[total_commodity_units]'/>
	   <input type='hidden' name='commodity_id[]' class='commodity_id' value='$facility_commodities[commodity_id]'/>
	   <td>$facility_commodities[sub_category_name]</td>
	   <td>$facility_commodities[commodity_name]</td>
	   <td>$facility_commodities[commodity_code]</td>
	   <td>$facility_commodities[unit_size]</td>
	   <td><select class='form-control commodity_unit_of_issue input-small' name='commodity_unit_of_issue[]'>
			<option value='Pack_Size'>Pack Size</option>
			<option value='Unit_Size'>Unit Size</option>
			</select></td>
	<td><input class='form-control input-small quantity' type='text' name='quantity[]' value='$facility_commodities[consumption_level]' </td>
	<td><input class='form-control input-small actual_quantity' type='text' name='actual_quantity[]' value='$facility_commodities[total_units]'</td>
	<td><input type='checkbox' class='checkbox'  $status/></td>
	   </tr>"; 
       endforeach; 
?>
</tbody>
</table> 
</div> 
<hr />
<div class="container-fluid">
<div style="float: right">
<button class="save btn btn-success"><span class="glyphicon glyphicon-open"></span>Update</button></div>
</div>
</div>
<script>
$(document).ready(function() {	
var $table = $('#example');
div = null; 
//float the headers
  $table.floatThead({ 
	 scrollingTop: 5,
	 zIndex: 1002,
	 scrollContainer: function($table){ return $table.closest('.test'); }
	});	
	//datatables settings 
	$('#example').dataTable( {
		   "sDom": "T lfrtip",
	     "sScrollY": "377px",
	     "sScrollX": "100%",
                    "sPaginationType": "bootstrap",
                    "oLanguage": {
                        "sLengthMenu": "_MENU_ Records per page",
                        "sInfo": "Showing _START_ to _END_ of _TOTAL_ records",
                    },
			      "oTableTools": {
                 "aButtons": [
				"copy",
				"print",
				{
					"sExtends":    "collection",
					"sButtonText": 'Save',
					"aButtons":    [ "csv", "xls", "pdf" ]
				}
			],
			"sSwfPath": "<?php echo base_url(); ?>assets/datatable/media/swf/copy_csv_xls_pdf.swf"
		},
        "bPaginate": false} );
	$('#example_filter label input').addClass('form-control');
	$('#example_length label select').addClass('form-control');			
	$('.quantity').on('keyup',function(){
	var selector_object=$(this);
	var num=selector_object.val();	
	var commodity_unit_of_issue=selector_object.closest("tr").find('.commodity_unit_of_issue').val();
    var actual_unit_size=selector_object.closest("tr").find('.actual_units').val();	
    if(num==''){ //check if the user has entered a value or just clearing the textfield   
    selector_object.closest("tr").find('.checkbox').prop('checked', false);  
    } else{
    selector_object.closest("tr").find('.checkbox').prop('checked', true);  	
    }  
	// finally calculate the stock 
    calculate_actual_stock(actual_unit_size,commodity_unit_of_issue,num,".actual_quantity",selector_object);
    get_the_data_from_the_form_to_save(selector_object);
	});	
	$('.commodity_unit_of_issue').on('change',function(){
    var selector_object=$(this);
    var num=selector_object.closest("tr").find('.quantity').val();
	var commodity_unit_of_issue=selector_object.closest("tr").find('.commodity_unit_of_issue').val();
    var actual_unit_size=selector_object.closest("tr").find('.actual_units').val();	  
	// finally calculate the stock 
    calculate_actual_stock(actual_unit_size,commodity_unit_of_issue,num,".actual_quantity",selector_object);
    var data =get_the_data_from_the_form_to_save(selector_object);	
     //save the data in the db          
	var url = "<?php echo base_url().'stock/save_set_up_facility_stock'?>";	

    ajax_simple_post_with_console_response(url, data,div);	  	
	});	//check box movement
	$(".checkbox").change(function() {
	var selector_object=$(this);
    if(this.checked) {
    var data =get_the_data_from_the_form_to_save(selector_object);	
     //save the data in the db          
	var url = "<?php echo base_url().'stock/save_set_up_facility_stock'?>";	  
    ajax_simple_post_with_console_response(url, data,div);	
    } else{
    selector_object.closest("tr").find('.actual_units').val(0);
    selector_object.closest("tr").find('.quantity').val(0);
    var data =get_the_data_from_the_form_to_save(selector_object);	
     //save the data in the db          
	var url = "<?php echo base_url().'stock/save_set_up_facility_stock/delete'?>";	  
    ajax_simple_post_with_console_response(url, data,div);	  	
    }
    });
	function get_the_data_from_the_form_to_save(selector_object){
	var commodity_id=selector_object.closest("tr").find('.commodity_id').val();
	
	var consumption_level=selector_object.closest("tr").find('.quantity').val();
	var selected_option=selector_object.closest("tr").find('.commodity_unit_of_issue').val();
	var total_units=selector_object.closest("tr").find('.actual_quantity').val();
	var data="commodity_id="+commodity_id+"&consumption_level="
	          +consumption_level+" &selected_option="+selected_option+"&total_units="+total_units;
	return data;
	   	
	}
})
 
</script>