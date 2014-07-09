<div id="dialog"></div>
	<div class="alert alert-info" >
  <b>Below is the project status in the county</b>
</div>
	 <div id="temp"></div>
	<?php echo $data ?>
	<div>
	
	<div id="container"  style="height:60%; width: 50%; margin: 0 auto; float: left">
	</div>
	<div id="container_monthly"  style="height:60%; width: 50%; margin: 0 auto;float: left"></div>	
	</div>

<script>
$(document).ready(function() {

<?php  $header=""; $data_response=count(json_decode($category_data_monthly));  if($data_response>0): ?>
   $('#container').highcharts({
            	chart: {
                type: 'line',
                spacingBottom: 5 /* HERE */
            },
            title: {
                text: 'Daily Facility Access log for <?php echo $month." ".$year;?>',
                x: -20 //center
            },
            credits: { enabled:false},
            xAxis: {
                categories: <?php echo $category_data; ?>
            },
            yAxis: {
                title: {
                    text: '# of Facilities which loggin'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: ''
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: [
            
            <?php  
                  foreach($series_data as $key=>$raw_data):
					 $temp_array=array();
					 echo "{ name: '$key', data:";
					 
					  foreach($raw_data as $key_data):
						$temp_array=array_merge($temp_array,array((int)$key_data));
						  endforeach;
					  echo json_encode($temp_array)."},";
					  
				   endforeach;
            
              ?>
          ]
        });
   $('#container_monthly').highcharts({
   	chart: {
              
                type: 'line',
               spacingBottom: 5/* HERE */
            },
            title: {
                text: 'Monthly Facility Access log for <?php echo $year;?>',
                x: -20 //center
            },
            credits: { enabled:false},
            xAxis: {
                categories: <?php echo $category_data_monthly; ?>
            },
            yAxis: {
                title: {
                    text: '# of Facilities which loggin'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: ''
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: [
            
            <?php  
                  foreach($series_data_monthly as $key=>$raw_data):
					 $temp_array=array();
					 echo "{ name: '$key', data:";
					 
					  foreach($raw_data as $key_data):
						$temp_array=array_merge($temp_array,array((int)$key_data));
						  endforeach;
					  echo json_encode($temp_array)."},";
					  
				   endforeach;
            
              ?>
          ]
        });
         <?php else: ?>
		 var loading_icon="<?php echo base_url().'Images/no-record-found.png'; 
		 $header="<br><div align='center' class='label label-info '>Access Logs for  $month $year</div>" ?>";
		 $("#graph_div").html("<img style='margin-left:20%;' src="+loading_icon+">")
		  <?php endif; ?>
				
});
	</script>
	
	