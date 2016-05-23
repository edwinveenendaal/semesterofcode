<?php
function getAcceptedProjectResponse(){
	$content = t('You have now chosen your project, congratulations!');
	$content .= "<br/><br/>";
	$content .= t("You can now optionally complete an agreement between you, your supervisor and your project mentor. ");
	$content .= t("Your supervisor and mentor will be able to tell you what is required if an agreement is needed.");
	$content .= "<br/><br/>";
	$content .= t("You can access your accepted project details ");
	$content .= "<a href='"._WEB_URL. "/dashboard/projects/mine'>".t('here')."</a>.";
	$content .= "<br/>";
	$content .= t("Or in the future by using the dashboard and clicking the 'My project' link.");
	return $content;
}

function initBrowseProjectOffersLayout(){
	global $base_url;
	$org_id=0;
	if(isset($_GET['organisation'])){
		$org_id = $_GET['organisation'];
	}

	echo '<div id="baktoprops"><a href=" '.$base_url.'/dashboard">'.t('Back to dashboard').'</a></div>';
	echo '<h2>'.t('Here you can select which of your project offers you wish to accept').'</h2>';
?>
		<div class="filtering" style="width: 800px;">
			<span id="infotext" style="margin-left: 34px"></span>
			<form id="proposal_filter">
		        <?php echo t('Filter by Organisation');?>:
		        <?php // echo t('Organisations');?>
		        <select id="organisation" name="organisation">
					<option <?php echo  (! $org_id) ? 'selected="selected"': ''; ?>
					value="0"><?php echo t('All Organisations');?></option><?php
					$result = Organisations::getInstance()->getOrganisationsLite();
					foreach ($result as $record) {
						$selected = ($record->org_id == $org_id ? 'selected="selected" ' : '');
						echo '<option ' .$selected.'value="'.$record->org_id.'">'.$record->name.'</option>';
					}?>
				</select>
			</form>
		</div>

		<div id="TableContainer" style="width: 800px;"></div>
		<script type="text/javascript">
	
				jQuery(document).ready(function($){
					window.view_settings = {};
	
					function loadFilteredProposals(){
						$("#TableContainer").jtable("load", {
							organisation: $("#organisation").val(),
						});
					}
	
				    //Prepare jTable
					$("#TableContainer").jtable({
						paging: true,
						pageSize: 10,
						sorting: true,
						defaultSorting: "pid ASC",
						actions: {
							listAction: module_url + "actions/proposal_actions.php?action=list_my_offers"
						},
						fields: {
							pid: {
								key: true,
		    					create: false,
		    					edit: false,
		    					list: false
							},
							pr_title: {
								title: "Project",
								width: "49%",
								display: function (data) {
									return "<a title=\"View proposal details\" href=\"javascript:void(0);\" onclick=\"getProposalDetail("+data.record.proposal_id+")\">"
											+ data.record.pr_title+"</a>";
									},
							},
							o_name: {
								title: "Organisation",
								width: "35%",
								display: function (data){return data.record.o_name;}
							},
							accept_col : {
								width: "6%",
		    					title: "Accept",
								sorting: false,
		    					display: function (data) {
									return "<a title=\"Accept this offer\" href=\"javascript:void(0);\" "+
										"onclick=\"acceptProjectOffer("+data.record.proposal_id+",'"+data.record.pr_title+"', "+data.record.pid+")\">"+
											"<span class=\"ui-icon ui-icon-star\">accept</span></a>";
		    					},
	
		    					create: false,
		    					edit: false
							},
	
						},
					});
	
					//Load proposal list from server on initial page load
					loadFilteredProposals();
	
					$("#organisation").change(function(e) {
		           		e.preventDefault();
		           		loadFilteredProposals();
		        	});
	
					$("#proposal_filter").submit(function(e){
						e.preventDefault();
						loadFilteredProposals()
					});
	
				});
			</script><?php
}