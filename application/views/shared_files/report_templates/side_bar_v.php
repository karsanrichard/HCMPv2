<?php ?>

<div class="panel-group " id="accordion" style="padding: 0;">
                
                
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseone"><span class="glyphicon">
                            </span>Expiries</a>
                        </h4>
                    </div>
                    <div id="collapseone" class="panel-collapse collapse in ">
                        <div class="panel-body">
                            <table class="table">
                                <tr>
                                    <td>
                                        <a href="<?php echo base_url().'reports' ?>">Potential Expiries</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a href="<?php echo base_url().'reports/expiries' ?>">Expired</a> <span class="label label-info"></span>
                                    </td>
                                </tr>
                                
                            </table>
                        </div>
                    </div>
                </div>
                <!--Divisional Reports Accordion-->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapsetwo"><span class="glyphicon glyphicon-file">
                            </span>Divisional Reports</a>
                        </h4>
                    </div>
                    <div id="collapsetwo" class="panel-collapse collapse">
                        <div class="panel-body">
                            <table class="table">
                                <tr>
                                    <td>
                                        <span class="glyphicon glyphicon-usd"></span><a href="<?php echo base_url().'divisional_reports/view_malaria_report'?>">Malaria Reports</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="glyphicon glyphicon-user"></span><a href="<?php echo base_url().'divisional_reports/view_TB_report'?>">TB Reports</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="glyphicon glyphicon-tasks"></span><a href="<?php echo base_url().'divisional_reports/view_RH_report'?>">Reproductive Health Reports</a>
                                    </td>
                                </tr>
                                
                            </table>
                        </div>
                    </div>
                </div>
                <!--Submit Divisional Reports-->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapsethree"><span class="glyphicon glyphicon-file">
                            </span>Submit Divisional Reports</a>
                        </h4>
                    </div>
                    <div id="collapsethree" class="panel-collapse collapse ">
                        <div class="panel-body">
                            <table class="table">
                                <tr>
                                    <td>
                                        <span class="glyphicon glyphicon-usd"></span><a href="<?php echo base_url().'divisional_reports/malaria_report'?>">Malaria Reports</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="glyphicon glyphicon-user"></span><a href="<?php echo base_url().'divisional_reports/TB_report'?>">TB Reports</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="glyphicon glyphicon-tasks"></span><a href="<?php echo base_url().'divisional_reports/RH_report'?>">Reproductive Health Reports</a>
                                    </td>
                                </tr>
                                
                            </table>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour"><span class="glyphicon glyphicon-screenshot">
                            </span>Facility Statistics</a>
                        </h4>
                    </div>
                    <div id="collapseFour" class="panel-collapse collapse in">
                        <div class="panel-body">
                            <table class="table">
                                <tr>
                                    <td>
                                        <span class="glyphicon glyphicon-remove-sign"></span><a href="<?php echo base_url().'reports/load_expiries'?>" >Expiries</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="glyphicon glyphicon-usd"></span><a href="<?php echo base_url().'reports/load_cost_of_orders'?>" >Cost of Orders</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="glyphicon glyphicon-cutlery"></span><a href="<?php echo base_url().'reports/consumption'?>" >Consumption</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="glyphicon glyphicon-user"></span><a href="#">User Statistics</a>
                                    </td>
                                </tr>
                                
                            </table>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour"><span class="glyphicon glyphicon-file">
                            </span>Other Reports</a>
                        </h4>
                    </div>
                    <div id="collapseFour" class="panel-collapse collapse">
                        <div class="panel-body">
                            <table class="table">
                                <tr>
                                    <td>
                                        <span class="glyphicon ">
                                        </span><a href="<?php echo base_url().'reports/order_report' ?>">Order Report</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="glyphicon ">
                                        </span><a href="<?php echo base_url().'reports/stock_control' ?>">Stock Control Card</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="glyphicon">
                                        </span><a href="<?php echo base_url().'reports/commodities_issue' ?>">Commodities Issued</a>
                                    </td>
                                </tr>
                                
                            </table>
                        </div>
                    </div>
                </div>


            </div>
<?php?>