<style>
	.filter{
	width: 100%;
	/*border: 1px solid black;*/
	margin:auto;
	padding-bottom: 2em;	
	}
	.graph_content{
	padding-top: 2em;
	width: 100%;
	height:400px;
	-webkit-box-shadow: 1px 1px 1px 1px #DDD3ED;
box-shadow: 1px 1px 1px 1px #DDD3ED;
	margin:auto;	
	}
	.input-small{
 		width: 100px !important;
 	}
 	.input-small_{
 		width: 230px !important;
 	}
</style>
<div class="alert alert-info" style="width: 100%">
  <b>Below is the consumption Level in the County </b> :Select filter Options
</div>
<div class="filter row">
<form class="form-inline" role="form">
<select id="commodity_filter" class="form-control input-small_ col-md-2">
<option value="NULL">Select Commodity</option>
<option value="ALL">All</option>
<?php
foreach($c_data as $data):
		$commodity_name=$data['commodity_name'];	
		$commodity_id=$data['id'];
		echo "<option value='$commodity_id'>$commodity_name</option>";
endforeach;
?>
</select>	
	<select id="district_filter" class="form-control col-md-1">
<option selected="selected" value="NULL">Select Sub-county</option>
<option value="ALL">All</option>
<?php
foreach($district_data as $district_):
		$district_id=$district_->id;
		$district_name=$district_->district;	
		echo "<option value='$district_id'>$district_name</option>";
endforeach;
?>
</select> 
<select id="facility_filter" class="form-control col-md-1">
<option value="NULL">Select facility</option>
</select>
<input type="text" name="from"  id="from" 
placeholder="FROM" class="form-control input-small col-md-1 clone_datepicker_normal_limit_today" />
<input type="text" name="to"  id="to" placeholder="TO" class="form-control input-small col-md-1 clone_datepicker_normal_limit_today" />		
<select id="plot_value_filter" class="form-control col-md-1">
<option selected="selected" value="NULL">Select Plot value</option>
<option value="packs">Packs</option>
<option value="units">Units</option>
<option value="ksh">KSH</option>
</select>
<div class="col-md-1">
<button class="btn btn-sm btn-success filter_"><span class="glyphicon glyphicon-filter"></span>Filter</button> 
</div> 
<div class="col-md-1">
<button class="btn btn-sm btn-success download"><span class="glyphicon glyphicon-save"></span>Download</button> 
</div>
</form>
</div>
<div class="graph_content">	
</div>
<script>
	$(document).ready(function() {
		json_obj = { "url" : "assets/img/calendar.gif'",};
var baseUrl=json_obj.url;
	  //	-- Datepicker	limit today		
	$(".clone_datepicker_normal_limit_today").datepicker({
    maxDate: new Date(),				
	dateFormat: 'd M yy', 
	changeMonth: true,
	changeYear: true,
	buttonImage: baseUrl,       });	
	    
		$("#facility_filter").hide();
		$("#district_filter").change(function() {
		var option_value=$(this).val();		
		if(option_value=='NULL' || option_value=='ALL'){
		$("#facility_filter").hide('slow');	
		}
		else{
var drop_down='';
 var hcmp_facility_api = "<?php echo base_url(); ?>/reports/get_facility_json_data/"+$("#district_filter").val();
  $.getJSON( hcmp_facility_api ,function( json ) {
     $("#facility_filter").html('<option value="NULL" selected="selected">--select facility--</option>'+
     '<option value="ALL">ALL</option>');
      $.each(json, function( key, val ) {
      	drop_down +="<option value='"+json[key]["facility_code"]+"'>"+json[key]["facility_name"]+"</option>";	
      });
      $("#facility_filter").append(drop_down);
    });
		$("#facility_filter").show('slow');		
		}

		});		
		$(".filter_").on('click',function(e) {
		e.preventDefault();	
		var from =$("#from").val();
		var to =$("#to").val();
		if(from==''){
		from="NULL";	
		}
		if(to==''){
		to="NULL";	
		}
        var url_ = "reports/consumption_stats_graph/"+
        $("#commodity_filter").val()+
        "/"+$("#district_filter").val()+
        "/"+$("#facility_filter").val()+
        "/"+ $("#plot_value_filter").val()+
        "/"+encodeURI(from)+
        "/"+encodeURI(to);
		ajax_request_replace_div_content(url_,'.graph_content');	
          });
          $(".download").on('click',function(e) {
		e.preventDefault();	
		var from =$("#from").val();
		var to =$("#to").val();
		if(from==''){
		from="NULL";	
		}
		if(to==''){
		to="NULL";	
		}
        var url_ = "reports/consumption_stats_graph/"+
        $("#commodity_filter").val()+
        "/"+$("#district_filter").val()+
        "/"+$("#facility_filter").val()+
        "/"+ $("#plot_value_filter").val()+
        "/"+encodeURI(from)+
        "/"+encodeURI(to)+
       "/csv_data";	
		 window.open(url+url_ ,'_blank');	
          });

		});
</script>