 <div class="container-fluid" style="margin-top: 1%;">
      <div class="row row-offcanvas row-offcanvas-right" id="sidebar" >
      	<p class="pull-left visible-xs">
            <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Click to view Side Menu</button>
          </p>
        <div class="col-sm-3 col-md-2 sidebar-offcanvas"  id="bar" role="navigation" style="margin-left:0.5%">
           <div class="panel-group " id="accordion" style="padding: 0;">
                <!--To be removed once the redesign is done
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" id="notifications"><span class="glyphicon glyphicon-bullhorn">
                            </span>Notifications</a>
                        </h4>
                    </div>
                </div>-->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" id="stocking_levels"><span class="glyphicon glyphicon-sort-by-attributes">
                            </span>Stocking Levels</a>
                        </h4>
                    </div>
                 
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree" id="expiries"><span class="glyphicon glyphicon-trash">
                            </span>Expiries</a>
                        </h4>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour" id="consumption"><span class="glyphicon glyphicon-file">
                            </span>Consumption</a>
                        </h4>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFive" id="redistributions"><span class="glyphicon glyphicon-retweet">
                            </span>Redistributions</a>
                        </h4>
                    </div>
                </div>
                <!--<div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseSix" id="orders"><span class="glyphicon glyphicon-list-alt">
                            </span>Orders</a>
                        </h4>
                    </div>
                </div>-->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseSeven" id="program_reports"><span class="glyphicon glyphicon-folder-open">
                            </span>Program Reports</a>
                        </h4>
                    </div>
                </div>
                 <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseEight" id="system_usage"><span class="glyphicon glyphicon-sort">
                            </span>System Usage</a>
                        </h4>
                    </div>
                    
                </div>
            </div>
        </div>
      </div>
<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 " style="padding:0;border-radius: 0;margin-top: -2% ">
 <h1 class="page-header" style="margin-top: 2.4%;font-size: 1.6em;"></h1>
<div class="">
<div style="height: 100%;" id="notification"></div>
</div>
</div>
    </div>
    <script>
    	/**
 *@author Kariuki & Mureithi
 */
  $(document).ready(function () {
  	//default 
  $('.page-header').html('Stocking Levels');
  $('#stocking_levels').parent().parent().parent().addClass('active-panel');
  ajax_request_replace_div_content('reports/stock_level_dashboard',"#notification");
 
 
  $('[data-toggle=offcanvas]').click(function () {
    $('.row-offcanvas').toggleClass('active');
  });
  
  $(window).resize(function() {
    if (($(window).width() < 768))
    {
        $( ".col-md-2,.col-md-10" ).css( "position", "" );
    };
});
//expiries function
$("#notifications").on('click', function(){
$('.page-header').html('Notifications');
active_panel(this);
ajax_request_replace_div_content('reports/notification_dashboard',"#notification");
});
//expiries function
$("#expiries").on('click', function(){
$('.page-header').html('Expiries');
active_panel(this);
ajax_request_replace_div_content('reports/expiries_dashboard',"#notification");
});
//Notifications function
$("#notifications").on('click', function(){
active_panel(this);
 ajax_request_replace_div_content('reports/notification_dashboard',"#notification");
$('.page-header').html('Notifications');
});
//stocking_levels function
$("#stocking_levels").on('click', function(){
ajax_request_replace_div_content('reports/stock_level_dashboard',"#notification");
active_panel(this);
$('.page-header').html('Stocking Levels');
});
//Consumption function
$("#consumption").on('click', function(){
active_panel(this);
$('.page-header').html('Consumption');
//consumption_data_dashboard($commodity_id = null, $district_id = null, $facility_code=null, $option = null,$from = null,$to = null, $graph_type=null,$tracer = null) {
        
ajax_request_replace_div_content('reports/consumption_data_dashboard/NULL/NULL/NULL/NULL/NULL/NULL/NULL/1',"#notification");
});
//Redistributiions Functions
$("#redistributions").on('click', function(){
active_panel(this);
$('.page-header').html('Commodity Redistribution');
ajax_request_replace_div_content('reports/county_donation',"#notification");
});
//Orders
/*$("#orders").on('click', function(){
active_panel(this);
$('.page-header').html('Orders');
ajax_request_replace_div_content('reports/order_listing/subcounty/true',"#notification");
});*/
//Program Reports
$("#program_reports").on('click', function(){
active_panel(this);
$('.page-header').html('');
ajax_request_replace_div_content('divisional_reports/program_reports',"#notification");
});
//System Usage function
$("#system_usage").on('click', function(){
active_panel(this);
$('.page-header').html('System Usage');
ajax_request_replace_div_content('reports/get_sub_county_facility_mapping_data',"#notification");
});

//
     
});
</script>