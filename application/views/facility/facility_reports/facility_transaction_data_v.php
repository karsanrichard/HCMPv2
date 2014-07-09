<div class="container" style="width: 96%; margin: auto;">
	<?php echo form_open('orders/facility_order'); ?>
 <table width="100%" border="0" class="row-fluid table table-hover table-bordered table-update"  id="example">
	<thead>
		<tr>
                        <th>Category</th>
                        <th>Commodity&nbsp;Name</th>
                        <th>Commodity&nbsp;Code</th>
                        <th>Unit&nbsp;Size</th>
                        <th>Opening&nbsp;Balance</th>
                        <th>Total&nbsp;Receipts</th>
                        <th>Total&nbsp;issues</th>
                        <th>Adjustments(-ve)</th>
                        <th>Adjustments(+ve)</th>
                        <th>Losses</th>
                        <th>Days out of stock</th>   
                        <th>Closing Stock</th>
		</tr>
	</thead>
	<tbody>
<?php 
    foreach($facility_stock_data as $facility_stock_data):
		foreach($facility_stock_data->commodity_detail as $item):
		foreach($item->sub_category_data as $sub_category):
		endforeach;		
		echo "<tr>
		<td>$sub_category->sub_category_name</td>
		<td>$item->commodity_name</td>
		<td>$item->commodity_code</td>
		<td>$item->unit_size</td>
		<td>$facility_stock_data->opening_balance</td>
		<td>$facility_stock_data->total_receipts</td>
		<td>$facility_stock_data->total_issues</td>
		<td>$facility_stock_data->adjustmentnve</td>
		<td>$facility_stock_data->adjustmentpve</td>
		<td>$facility_stock_data->losses</td>
		<td>$facility_stock_data->days_out_of_stock</td>
		<td>$facility_stock_data->closing_stock</td>
		</tr>
		";
		endforeach;
	endforeach;
 ?>
</tbody>
</table> 
<hr />
<div class="container-fluid">
<div style="float: right">
<button class="btn btn-success" type="submit"><span class="glyphicon glyphicon-open"></span>Proceed to Order from KEMSA</button></div>
<?php echo form_close();?>
</div>
</div>
<script>
$(document).ready(function() {
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
		}
	} );
	$('#example_filter label input').addClass('form-control');
	$('#example_length label select').addClass('form-control');
});
</script>