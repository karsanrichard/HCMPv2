<h1 class="page-header" style="margin: 0;font-size: 1.6em;">Stock Level</h1>
<style>
	.filter{
	width: 100%;

	border-bottom: 1px solid #DDD3ED;
	margin:auto;
	padding-bottom: 1em;	
	}
	.graph_content{
	width: 100%;
	height:auto;
	-webkit-box-shadow: 1px 1px 1px 1px #DDD3ED;
    box-shadow: 1px 1px 1px 1px #DDD3ED;
	margin:auto;	
	}
</style>
<div class="alert alert-info" style="width: 100%">
  <b>Below are the Stocking Levels in the County </b> :Select filter Options
</div>
<div class="filter row">
<form class="form-inline" role="form">
<select id="commodity_filter" class="form-control col-md-3">
<option value="NULL">Select Commodity</option>
<?php
foreach($c_data as $data):
		$commodity_name=$data['commodity_name'];	
		$commodity_id=$data['id'];
		echo "<option value='$commodity_id'>$commodity_name</option>";
endforeach;
?>
</select>	
	<select id="district_filter" class="form-control col-md-2">
<option selected="selected" value="NULL">Select Sub-county</option>
<?php
foreach($district_data as $district_):
		$district_id=$district_->id;
		$district_name=$district_->district;	
		echo "<option value='$district_id'>$district_name</option>";
endforeach;
?>
</select> 
<select id="facility_filter" class="form-control col-md-2">
<option value="NULL">Select facility</option>
</select>	
<select id="plot_value_filter" class="form-control col-md-2">
<option selected="selected" value="NULL">Select Plot value</option>
<option value="packs">Packs</option>
<option value="units">Units</option>
<option value="ksh">KSH</option>
</select>
<div class="col-md-1">
<button class="btn btn-sm btn-success filter" id="filter" name="filter"><span class="glyphicon glyphicon-filter"></span>Filter</button> 
</div> 
</form>
</div>

<div class="graph_content">	
</div>
<script>	
	$(document).ready(function() {
		$("#facility_filter").hide();
		$("#district_filter").change(function() {
		var option_value=$(this).val();
		
		if(option_value=='NULL'){
		$("#facility_filter").hide('slow');	
		}
		else{
var drop_down='';
 var hcmp_facility_api = "<?php echo base_url(); ?>/reports/get_facility_json_data/"+$("#district_filter").val();
  $.getJSON( hcmp_facility_api ,function( json ) {
     $("#facility_filter").html('<option value="NULL" selected="selected">--select facility--</option>');
      $.each(json, function( key, val ) {
      	drop_down +="<option value='"+json[key]["facility_code"]+"'>"+json[key]["facility_name"]+"</option>";	
      });
      $("#facility_filter").append(drop_down);
    });
		$("#facility_filter").show('slow');		
		}
		});		
		$("#filter").on('click',function(e) {
		e.preventDefault();	
        var url_ = "reports/get_county_stock_level_new/"+
        $("#commodity_filter").val()+
        "/NULL/"+$("#district_filter").val()+
        "/"+ $("#facility_filter").val()+
        "/"+$("#plot_value_filter").val()+
        "/"+'table_data';	
		ajax_request_replace_div_content(url_,'.graph_content');	
          });

		});
</script>