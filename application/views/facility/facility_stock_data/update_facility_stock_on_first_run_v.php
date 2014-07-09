<div class="container" style="width: 96%; margin: auto;">
<span  class='label label-info' >Please note this is a one off activity Stock level as of <?php $today= ( date('d M Y')); //get today's date in full?>
<input type="hidden" name="datepicker" readonly="readonly" value="<?php echo $today;?>"/><?php echo $today;?> 
To add facility stock data, first do physical stock count</span>
<hr />
<?php $att=array("name"=>'myform','id'=>'myform');
echo form_open('stock/add_stock_level',$att); ?>
<div class="table-responsive" style="height:400px; overflow-y: auto;">
<table  class="table table-hover table-bordered table-update"  id="facility_stock_table">
	<thead style="background-color: white">
		<tr>
			<th> Description</th>
			<th> Supplier</th>
			<th> Unit Size</th>
			<th> Issue Type</th>
			<th> Batch No</th>
			<th> Source of Item</th>
			<th> Manufacturer</th>
			<th> Expiry Date</th>
			<th> Stock Level</th>
			<th> Total Unit Count</th>
			<th> Options</th>
		</tr>
	</thead>
	    <tbody>
		<tr table_row="1">
		<td><input type="hidden" class="commodity_id" value=""  name="commodity_id[]"/>
			<select  name="desc[]" class="form-control desc">
	<option special_data="0" value="0">-Select One--</option>
				<?php
				foreach ($commodities as $commodities) {
					
					$id=$commodities->id;
					$commodities_name=$commodities->commodity_name;
					$unit_size=$commodities->unit_size;
					$commodity_code=$commodities->commodity_code;
					$total_units=$commodities->total_commodity_units;					
					foreach($commodities->supplier_name as $supplier_name):
					$name=$supplier_name->source_name;
					endforeach;
					
					echo "<option special_data='" . $id."^".$name."^".$commodity_code."^".$unit_size ."^".$total_units."' 
					value='$id'>".$commodities_name."</option>" ;
				}
				?>
			</select></td>
			<td>
			<input type="text" class="form-control input-small commodity_supplier" name="commodity_supplier[]" readonly="readonly"/>
			<input type="hidden" class="actual_units"/>
			</td>
			<td><input type="text" class="form-control input-small unit_size"   name="commodity_unit_size[]" readonly="readonly"/></td>
			<td>
			<select name="commodity_unit_of_issue[]" class="form-control commodity_unit_of_issue input-small">
			<option value="Pack_Size">Pack Size</option>
			<option value="Unit_Size">Unit Size</option>
			</select>	
			</td>
			<td><input class='form-control input-small commodity_batch_no' required="required" name='commodity_batch_no[]' type='text'/></td>
			<td><select class="form-control input-small source_of_item" name="source_of_item[]">
				<?php foreach($commodity_source as $commodity_source){
					$id=$commodity_source->id;
					$commodity_source_name=$commodity_source->source_name;
					echo "<option value='$id'>$commodity_source_name</option>";	}  ?>
			</select></td>
			<td><input id='commodity_manufacture' required="required" class="form-control commodity_manufacture input-small" 
			name='commodity_manufacture[]' type='text' value="" /></td>
			<td><input  class='form-control input-small clone_datepicker' required="required"  name='clone_datepicker[]' type='text' /></td>
			<td><input id='commodity_available_stock' name='commodity_available_stock[]' 
			type='text' class="form-control input-small input-small commodity_available_stock" required="true"/></td>
			<td><input type='text' class='form-control input-small commodity_total_units' readonly="readonly" name='commodity_total_units[]' value=''/></td>
	<td><button type="button" class="remove btn btn-danger btn-xs"><span class="glyphicon glyphicon-minus"></span>Remove Row</button></td>
		</tr>
	</tbody>
</table>
</div>
<hr />
<div class="container-fluid">
<div style="float: right">
<button type="button" class="add btn btn-primary"><span class="glyphicon glyphicon-plus"></span>Add Row</button>
<button class=" save btn btn-sm btn-success"><span class="glyphicon glyphicon-open"></span>Save</button></div>
</div>
</div>
<?php echo form_close();?>
<script type="text/javascript">
$(document).ready(function() {		
 var $table = $('table');
//float the headers
  $table.floatThead({ 
	 scrollingTop: 100,
	 zIndex: 1001,
	 scrollContainer: function($table){ return $table.closest('.table-responsive'); }
	});	
	//Check if drugs were temporarily saved
		var link="<?php echo base_url().'stock/get_temp_stock_data_json' ?>";
		$.ajax({
			url : link,
			type : 'POST',
			dataType : 'json',
			success : function(data) {
				var data_count=data.length;
				var x=1;
				var last_row=$('#facility_stock_table tr:last');
				$.each(data, function(i, jsondata) {
					//prepare the data
					var commodity_id=data[i]['commodity_id'];
					var unit_size=data[i]['unit_size'];
					var batch_no=data[i]['batch_no'];
					var commodity_manufacture=data[i]['manu'];
					var clone_datepicker=data[i]['expiry_date'];
					var stock_level=data[i]['stock_level'];
					var total_unit_count=data[i]['total_unit_count'];
					var facility_code=data[i]['facility_code'];
					var total_units=data[i]['total_units'];
				    var unit_issue=data[i]['unit_issue'];
				    var source_of_item=data[i]['unit_issue'];
					var supplier=data[i]['supplier'];
					var cloned_object = $('#facility_stock_table tr:last').clone(true);
					var table_row = cloned_object.attr("table_row");
					var next_table_row = parseInt(table_row) + 1;
					//set the data
					cloned_object.attr("table_row", next_table_row);
					cloned_object.find(".remove").show();
					cloned_object.find(".desc").val(commodity_id);
					cloned_object.find(".unit_size").attr('value',unit_size);
					cloned_object.find(".commodity_batch_no").attr('value',batch_no);
					cloned_object.find(".commodity_manufacture").attr('value',commodity_manufacture);
					cloned_object.find(".clone_datepicker").attr('value',clone_datepicker);
					cloned_object.find(".commodity_available_stock").attr('value',stock_level);
					cloned_object.find(".commodity_total_units").attr('value',total_unit_count);
					cloned_object.find(".commodity_unit_of_issue").attr('value',unit_issue);
					cloned_object.find(".actual_units").attr('value',total_units);
					cloned_object.find(".source_of_item").attr('value',source_of_item);
					cloned_object.find(".commodity_supplier").attr('value',supplier);
					cloned_object.insertAfter('#facility_stock_table tr:last');					
					if(x==data_count){
						$('#facility_stock_table tbody tr:first').remove();
					}
					x++;					
				});
			}
		}); 		
		 ////when the user selects the commodity name
			$(".desc").change(function(){
			var data =$('option:selected', this).attr('special_data'); 
	       	var data_array=data.split("^");
	       	//set the user data here
	       	$(this).closest("tr").find(".unit_size").val(data_array[3]);
	     	$(this).closest("tr").find(".commodity_supplier").val(data_array[1]);
	     	$(this).closest("tr").find(".commodity_id").val(data_array[0]);
            $(this).closest("tr").find(".actual_units").val(data_array[4]);
		   });		   
		////when the user clicks add a row
		  $(".add").click(function() {											
        /////update the record      		
			var selector_object=$("#facility_stock_table tr:last");
			var url = "<?php echo base_url().'stock/autosave_update_stock'?>";
			var temp_data=send_data_to_the_temp_table(selector_object);	
			var alert_message=check_if_the_form_has_been_filled_correctly(selector_object);
			if(isNaN(alert_message)){
			var notification='<ol>'+alert_message+'</ol>&nbsp;&nbsp;&nbsp;&nbsp;<span class="label label-danger">Before adding a new row</span>';
			//hcmp custom dialog box
			dialog_box(notification,'<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>');
            //This event is fired immediately when the hide instance method has been called.
            $('#communication_dialog').on('hide.bs.modal', function (e) { selector_object.closest("tr").find('.desc').focus();	});			
			return;
			}
			if(temp_data[1]==0 && temp_data[2]=="" && temp_data[5]=='' ){
			return;
			}
			else{
			 //send data to the temp table
	        ajax_simple_post_with_console_response(url, temp_data[0]);  /// uncomment this	
			}     
	        //Get New Object id's
			var cloned_object = $('#facility_stock_table tr:last').clone(true);
			var table_row = cloned_object.attr("table_row");
			var next_table_row = parseInt(table_row) + 1;
			cloned_object.attr("drug_row", next_table_row);
			var commodity_id_id = "commodity_id_" + next_table_row;
			var commodity_batch_no_id = "commodity_batch_no_" + next_table_row;
			var source_of_item_id = "source_of_item_" + next_table_row;
			var commodity_manufacture_id = "commodity_manufacture_" + next_table_row;
			var clone_datepicker_id = "clone_datepicker_" + next_table_row;
			var commodity_total_units_id = "commodity_total_units_" + next_table_row;          
			//Find Old Objects and reset values
			var desc = cloned_object.find(".desc");
			var commodity_batch_no = cloned_object.find(".commodity_batch_no");
			var commodity_id = cloned_object.find(".commodity_id");
			var source_of_item = cloned_object.find(".source_of_item");
			var commodity_manufacture = cloned_object.find(".commodity_manufacture");
			var clone_datepicker = cloned_object.find(".clone_datepicker");
			var commodity_total_units = cloned_object.find(".commodity_total_units");		
			var commodity_batch_no = cloned_object.find(".commodity_batch_no");
			//reset the values
			cloned_object.find('.commodity_supplier').attr("value",'');
	        cloned_object.find('.unit_size').attr("value",'');
	        cloned_object.find('.actual_units').attr("value",'');
	        cloned_object.find('.commodity_available_stock').attr("value",'');
			desc.attr("value", "0");
			commodity_batch_no.attr("id", commodity_batch_no_id);
			commodity_batch_no.attr("value", "");
			commodity_id.attr("id", commodity_id_id);
			commodity_id.attr("value", "");
			commodity_batch_no.attr("id", commodity_batch_no_id);
			commodity_batch_no.attr("value", "");
			commodity_manufacture.attr("id", commodity_manufacture_id);
			commodity_manufacture.attr("value", "");
			commodity_total_units.attr("id", commodity_total_units_id);
			commodity_total_units.attr("value", "");
			clone_datepicker.attr("id", clone_datepicker_id);	
			clone_datepicker.attr("value", "");	  
			 // remove the error class
            cloned_object.find("label.error").remove();      
			//insert the data
			cloned_object.insertAfter('#facility_stock_table tr:last');
	        // refresh the datepickers
			refreshDatePickers();
		});	
	$('.commodity_available_stock').on('keyup',function(){
   //get the value of the input
    var num=$(this).val();
    //get the object 
    var selector_object=$(this);
    var temp_data=send_data_to_the_temp_table(selector_object);	
    var commodity_unit_of_issue=selector_object.closest("tr").find('.commodity_unit_of_issue').val();
    var actual_unit_size=selector_object.closest("tr").find('.actual_units').val();	   
    var alert_message=check_if_the_form_has_been_filled_correctly(selector_object);
	if(isNaN(alert_message)){
	selector_object.val("");
	var notification='<ol>'+alert_message+'</ol>&nbsp;&nbsp;&nbsp;&nbsp;<span class="label label-danger">Before filling in the stock levels</span>';
			//hcmp custom dialog box
	dialog_box(notification,'<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>');
            //This event is fired immediately when the hide instance method has been called.
    $('#communication_dialog').on('hide.bs.modal', function (e) { selector_object.closest("tr").find('.desc').focus();	});			
    selector_object.closest("tr").find('.commodity_total_units').val();
	return;
	}
    // check if the user has inputed a decimal or character 
    if(!isNaN(num)){
    if(num.indexOf('.') > -1) {	
    //reset the text field and the message dialog box 
    selector_object.val("");
    //hcmp custom message dialog
    dialog_box("Decimals are not allowed",'<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>');
    //This event is fired immediately when the hide instance method has been called.
    $('#communication_dialog').on('hide.bs.modal', function (e) { selector_object.focus();	})
    return;}  }                       
    else {
     //reset the text field and the message dialog box 
    selector_object.val("");
    //hcmp custom message dialog
    dialog_box("Enter only numbers",'<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>');
    //This event is fired immediately when the hide instance method has been called.
    $('#communication_dialog').on('hide.bs.modal', function (e) { selector_object.focus();	})
    return; }
    // finally calculate the stock 
    calculate_actual_stock(actual_unit_size,commodity_unit_of_issue,num,".commodity_total_units",selector_object); 
    //update the record 
    var url = "<?php echo base_url().'stock/autosave_update_stock'?>";	  
    //save the infor 
    var temp_data=send_data_to_the_temp_table(selector_object);	
    ajax_simple_post_with_console_response(url, temp_data[0]);		     
    });  
    function send_data_to_the_temp_table(selector_object){
            	
			var data_ =$('option:selected', selector_object.closest("tr").find('.desc')).attr('special_data'); 
            var source_of_item=$('option:selected', selector_object.closest("tr").find('.source_of_item')).val(); 
	       	var data_array=data_.split("^");
			var unit_size=selector_object.closest("tr").find('.unit_size').val();
			var commodity_batch_noo=selector_object.closest("tr").find('.commodity_batch_no').val();
			var commodity_manufacture=selector_object.closest("tr").find('.commodity_manufacture').val();
			var clone_datepicker=selector_object.closest("tr").find('.clone_datepicker').val();
			var stock_level=selector_object.closest("tr").find('.commodity_available_stock').val();
			var unit_count=selector_object.closest("tr").find('.commodity_total_units').val();
			var commodity_unit_of_issue=selector_object.closest("tr").find('.commodity_unit_of_issue').val();
			var supplier=data_array[1];
			var data="&commodity_id="+data_array[0]+"&unit_size="
	          +unit_size+" &batch_no="+commodity_batch_noo+"&manuf="+commodity_manufacture+
	          "&expiry_date="+clone_datepicker+
	          "&stock_level="+stock_level+
	          "&total_units_count="+selector_object.closest("tr").find('.commodity_total_units').val()+
	          "&unit_issue="+commodity_unit_of_issue+
	          "&total_units="+data_array[4]+
	          "&source_of_item="+source_of_item+
	          "&supplier="+supplier;						
                    
	         return [data, data_ ,commodity_batch_noo,commodity_manufacture,clone_datepicker,stock_level,data_array[0]];
    }
    
    function check_if_the_form_has_been_filled_correctly(selector_object){
    	    var temp_data=send_data_to_the_temp_table(selector_object);
    	    var alert_message='';
			if(temp_data[1]==0){
				alert_message +="<li>Select a commodity first</li>";
			}			
			if(temp_data[2]==""){
				alert_message +="<li>Indicate batch No of the commodity</li>";
			}
			if(temp_data[3]==""){
				alert_message +="<li>Indicate Manufacturer of the commodity</li>";
			}
			if(temp_data[4]==''){
				alert_message +="<li>Indicate the expiry date of the commodity</li>";
			}
			if(temp_data[5]==''){
			alert_message +="<li>Indicate the stock level of the commodity</li>";
			}		
			return alert_message;
    }
    $('.remove').on('click',function(){
    var selector_object=$(this);
     //hcmp custom message dialog
    dialog_box("Are you sure you want to delete this record?",'<button type="button" class="btn btn-danger remove_record" >OK</button>'+
    '<button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>'); 
    $('.remove_record').on('click',function(){
    //url for deleting the row
    var url = "<?php echo base_url().'stock/delete_temp_autosave'?>";
    //get the data to delete the row
    var temp_data=send_data_to_the_temp_table(selector_object);    
	if(temp_data[6]!=0){
    //data to be used to delete the row
	var data="commodity_id="+temp_data[6]+"&commodity_batch_no="+temp_data[2];
	//delete the row on the temp table
     ajax_simple_post_with_console_response(url, data); }
     //remove the row
    selector_object.parent().parent().remove(); 
    $('#communication_dialog').modal('hide');
    })
    });						
/************save the data here*******************/
	$("#myform").validate();
	$('.save').button().click(function() {
	 confirm_if_the_user_wants_to_save_the_form("#myform"); 
     });});	
</script>

