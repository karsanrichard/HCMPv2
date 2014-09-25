<div id="tabs">
	<a id="trend_tab" href="<?php echo base_url().'rtk_management/rtk_manager'; ?>" data-tab="1" class="tab">National Trend</a>
	<a id="users_tab" href="<?php echo base_url().'rtk_management/rtk_manager_users'; ?>" data-tab="2" class="tab">Users</a>
	<a id="messaging_tab" href="<?php echo base_url().'rtk_management/rtk_manager_messages'; ?>" data-tab="1" class="tab">Messaging</a>
	<a id="settings_tab" href="<?php echo base_url().'rtk_management/rtk_manager_settings'; ?>" data-tab="2" class="tab">Settings</a>
	<a id="settings_tab" href="<?php echo base_url().'rtk_management/rtk_manager_activity'; ?>" data-tab="2" class="tab">Activity</a>
</div>


<style type="text/css">
	.tab {
		float: left;
		display: block;
		padding: 10px 20px;
		text-decoration: none;
		border-radius: 5px 5px 0 0;
		background: #F9F9F9;
		color: #777;		
	}
	#tabs a,#switch_tab a{		
		text-decoration: none;
		font-style: normal;		
	}
	#tabs a:hover,#switch_tab:hover{
		border-radius: 5px 5px 0 0;
		background: #CCCCCC;
	}
	.tab_switch {
		float: right;
		display: block;
		padding: 10px 20px;
		text-decoration: none;
		border-radius: 5px 5px 0 0;
		background: #F9F9F9;
		color: #777;
	}
	.active_tab{
		border-bottom: 4px solid #009933;
		float: left;
		display: block;
		padding: 10px 20px;
		text-decoration: none;
		border-radius: 5px 5px 0 0;
		background: #ECECEC;
		color: #777;		
	}


</style>
