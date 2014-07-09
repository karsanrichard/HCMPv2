<div class="panel-group " id="accordion" style="padding: 0;">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" id="stocking_levels"><span class="glyphicon glyphicon-sort-by-attributes">
                            </span>Stocking Levels</a>
                        </h4>
                    </div>
                   <div id="collapseTwo" class="panel-collapse collapse">
                        <div class="panel-body">
                            <table class="table">
                                <tr>
                                    <td>
                  <a href="<?php echo base_url("reports") ?>">Actual Stocks</a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div> 
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a  data-parent="#accordion" href="<?php echo base_url("reports/county_expiries") ?>" id="expiries"><span class="glyphicon glyphicon-trash">
                            </span>Expiries</a>
                        </h4>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a  data-toggle="collapse" data-parent="#accordion" href="#collapsec" id="consumption"><span class="glyphicon glyphicon-cutlery">
                            </span>Consumption</a>
                        </h4>
                    </div>
                     <div id="collapsec" class="panel-collapse collapse">
                        <div class="panel-body">
                            <table class="table">
                                <tr>
                                    <td>
                  <a href="<?php echo base_url("reports/county_consumption") ?>">County Consumption</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                  <a href="#" id="bin_card">Bin Card/ Stock control card</a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div> 
                </div>
                 <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour" id="consumption"><span class="glyphicon glyphicon-retweet">
                            </span>Donations</a>
                        </h4>
                    </div>
                </div>
                 <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a  data-parent="#accordion" href="<?php echo base_url("reports/order_listing/subcounty/true"); ?>" id="consumption"><span class="glyphicon glyphicon-list-alt">
                            </span>Orders</a>
                        </h4>
                    </div>
                </div>
                      <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-parent="#accordion" href="<?php echo base_url("reports/program_reports"); ?>" id="program_reports"><span class="glyphicon glyphicon-folder-open">
                            </span>Program Reports</a>
                        </h4>
                    </div>
                </div>
                      <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-parent="#accordion" href="<?php echo base_url("reports/get_sub_county_facility_mapping_data"); ?>" id="system_usage"><span class="glyphicon glyphicon-sort">
                            </span>System Usage</a>
                        </h4>
                    </div>
                </div>               
            </div>
<script>
   $("#bin_card").on('click', function() {
	var body_content='<select id="facility_code" name="facility_code" class="form-control"><option value="0">--Select Facility Name--</option>'+
                    '<?php	$facilities=Facilities::get_facilities_online_per_district($this -> session -> userdata('county_id'));
                    foreach($facilities as $facility):
						    $facility_code=$facility['facility_code'];
							 $facility_name=$facility['facility_name']; ?>?>'+					
						'<option <?php echo 'value="'.$facility_code.'">'.$facility_name ;?></option><?php endforeach;?>';
   //hcmp custom message dialog
    dialog_box(body_content,
    '<button type="button" class="btn btn-primary order_for_them" >View Their Bin Card</button>'
    +'<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>'); 
    $(".order_for_them").on('click', function() {
    var facility_code=$('#facility_code').val();
    if(facility_code==0){
    alert("Please select a Facility First");
    	
    }else{
     window.location="<?php echo site_url('reports/stock_control');?>/"+facility_code;		
    }
   	
    });
		
	})
</script>
            
