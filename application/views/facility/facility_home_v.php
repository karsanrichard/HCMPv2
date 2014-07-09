<div class="container" style="width: 96%; margin: auto;">
<div class="row">
	<div class="col-md-4">
		<div class="row">			
			<div class="col-md-12">
				<div class="panel panel-success">
      		<div class="panel-heading">
        		<h3 class="panel-title">Notification <span class="glyphicon glyphicon-bell"></span> </h3>
      		</div>
      <div class="panel-body">
    <?php if($facility_dashboard_notifications['facility_donations']>0): ?>
      	 <div style="height:auto; margin-bottom: 2px" class="warning message ">      	
        <h5>Inter Facility Donation</h5> 
        	<p>
			<a class="link" href="<?php echo base_url('issues/confirm_external_issue') ?>"><span class="badge"><?php 
				echo $facility_dashboard_notifications['facility_donations'];?></span> Items have been donated</a> 
			</p>
			 </div>
		  <?php endif; // Potential Expiries?>
   <?php if($facility_dashboard_notifications['facility_stock_count']==0): ?>
      	<div style="height:auto; margin-bottom: 2px" class="warning message ">      	
        <h5> 1) Set up facility stock</h5> 
        	<p>
			<a class="link" href="<?php echo base_url('stock/set_up_facility_stock') ?>">Select the Commodities which are used in the facility</a> 
			</p>
        </div>
        <div style="height:auto; margin-bottom: 2px" class="warning message ">      	
        <h5>2) No Stock (On First Run)</h5> 
        	<p>
	<a class="link" href="<?php echo base_url('stock/facility_stock_first_run/first_run') ?>">Please update your stock details</a> 
			</p>
        </div>
            <?php endif; // items_stocked_out_in_facility?>
         <?php if($facility_dashboard_notifications['actual_expiries']>0): ?>
        <div style="height:auto; margin-bottom: 2px" class="warning message ">       	
        <h5>Expired Commodities</h5>
        	<p>
			<a class="link" href="<?php echo base_url('reports') ?>"> <span class="badge"><?php 
				echo $facility_dashboard_notifications['actual_expiries'];?></span>Expired Commodities awaiting decommisioning.</a> 
			</p> 
        </div>
         <?php endif; // Actual Expiries?>
          <?php if($facility_dashboard_notifications['potential_expiries']>0): ?>
      	 <div style="height:auto; margin-bottom: 2px" class="warning message ">      	
        <h5>Potential Expiries</h5> 
        	<p>
			<a class="link" href="<?php echo base_url('reports') ?>"><span class="badge"><?php 
				echo $facility_dashboard_notifications['potential_expiries'];?></span>Commodities Expiring in the next 6 months</a> 
			</p>
			 </div>
		  <?php endif; // Potential Expiries?>
         <?php if($facility_dashboard_notifications['items_stocked_out_in_facility']>0): ?>
        <div style="height:auto; margin-bottom: 2px" class="warning message ">       	
        <h5>Stock Outs</h5>
        	<p>
			<a class="link" href="<?php echo base_url('reports/facility_stocked_out_items') ?>"> <span class="badge">
		<?php echo $facility_dashboard_notifications['items_stocked_out_in_facility'] ?></span>Item(s) are stocked out </a> 
			</p> 
        </div>
        <?php endif; // items_stocked_out_in_facility?>
        <?php if(array_key_exists('pending', $facility_dashboard_notifications['facility_order_count']) 
        && @$facility_dashboard_notifications['facility_order_count']['pending']>0): ?>
      	<div style="height:auto; margin-bottom: 2px" class="warning message ">      	
        	<h5>Orders Pending Approval by District Pharmacist</h5> 
        	<p>
			<a class="link" href="<?php echo base_url('reports/order_listing/facility') ?>"><span class="badge"><?php 
			echo $facility_dashboard_notifications['facility_order_count']['pending'] ?></span>Order(s) Pending.</a> 
			</p>
        </div>
        <?php endif; //pending
         if(array_key_exists('rejected', $facility_dashboard_notifications['facility_order_count']) 
         && @$facility_dashboard_notifications['facility_order_count']['rejected']>0): ?>
        <div style="height:auto; margin-bottom: 2px" class="warning message ">      	
        	<h5>Orders Rejected by District Pharmacist</h5> 
        	<p>
			<a class="link" href="<?php echo base_url('reports/order_listing/facility') ?>"><span class="badge"><?php 
			echo $facility_dashboard_notifications['facility_order_count']['rejected'] ?></span>Order(s) rejected</a> 
			</p>
        </div>
        <?php endif; //rejected
        if(array_key_exists('approved', $facility_dashboard_notifications['facility_order_count'])
		 && @$facility_dashboard_notifications['facility_order_count']['approved']>0): ?>
        <div style="height:auto; margin-bottom: 2px" class="warning message ">      	
        	<h5>Pending Dispatch</h5> 
        	<p>
			<a class="link" href="<?php echo base_url('reports/order_listing/facility') ?>"><span class="badge"><?php 
			echo $facility_dashboard_notifications['facility_order_count']['approved'] ?></span>Order(s) pending dispatch from KEMSA</a> 
			</p>
        </div>
         <?php endif; //approved?>
      </div>    
    </div>
	</div>
		</div>
	<div class="row">			
			<div class="col-md-12">				
			<div class="panel panel-success">
      		<div class="panel-heading">
        		<h3 class="panel-title">Actions <span class="glyphicon glyphicon-list-alt"></span></h3>
      </div>
      <div class="panel-body">

        <div style="height:auto; margin-bottom: 2px" class="issue message ">	 
        	<a href="<?php echo base_url("issues/index/internal") ?>"><h5>Issue Commodities to service points</h5></a>       	 
        </div>
        <div style="height:auto; margin-bottom: 2px" class="distribute message ">
        	<a href="<?php echo base_url('issues/index/external'); ?>"><h5>Redistribute Commodities to other facilities</h5></a>	 
        </div>
        
 		<div style="height:auto; margin-bottom: 2px" class="distribute message ">
        	<a href="<?php echo base_url('issues/confirm_external_issue')?>"><h5>Receive Commodities From Other Sources</h5></a>
        	 
        </div> 
        <div style="height:auto; margin-bottom: 2px;color: #428bca !important;" class="order message " id="order_tab">
            <h5>Orders</h5>
        </div>


         <div style="height:auto; margin-bottom: 2px" class="" id="order_hide">
            <a href="<?php echo base_url('reports/facility_transaction_data/1'); ?>"><h5>KEMSA</h5></a>
            <a href=""><h5>MEDS</h5></a> 
        </div>   

        <div style="height:auto; margin-bottom: 2px" class="reports message " id="order_tab">
          <a href="<?php echo base_url()."reports" ?>"><h5>Reports</h5></a>        
         
      </div>
          
         <div style="height:auto; margin-bottom: 2px" class="delivery message ">

        	<a href="<?php echo base_url('reports/order_listing/facility')?>"><h5>Update order delivery</h5></a> 
        	 </div>   	 
        <div style="height:auto; margin-bottom: 2px" class="order message ">
          <a href="<?php echo base_url("issues/add_service_points") ?>"><h5>Add Service Points </h5></a>          
        </div>

         <div style="height:auto; margin-bottom: 2px" class="reports message ">
          <a href="<?php echo base_url("reports") ?>"><h5>Reports</h5></a>        
        </div>
      </div>
        </div>      

      </div>    
    </div>
	</div>
 	</div>	
   <div class="col-md-8">
    <div class="panel panel-success">
      <div class="panel-heading">
        <h3 class="panel-title">Graph <span class="glyphicon glyphicon-stats" style=""></span><span class="glyphicon glyphicon-align-left" style="margin-left: 1%"></span></h3>
      </div>
      <div class="panel-body" style="overflow-y: auto">
        <div style="/*border: 1px solid #036;*/ ;" id="container"></div>
      </div>
    </div>
  </div>  	
	</div>	
	
</div>

<script>

	
   $(document).ready(function() {
       $('#order_hide').hide() 

       $('#order_tab').click(function(event) {
           /* Act on the event */
           $('#order_hide').toggle('slow')
       }); 

	
       <?php echo $facility_dashboard_notifications['faciliy_stock_graph'] ?>

    });
</script>