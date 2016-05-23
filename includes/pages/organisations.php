<?php
function initBrowseOrgsLayout($org_id=''){?>
	
	<div class="filtering">
		<span id="infotext" style="margin-left: 34px"></span>
		<form id="organisation_filter">
			<?php echo t('Name');?>: <input type="text" name="oname" id="oname" />
		</form>
	</div>
	<div id="OrganisationTableContainer" style="width: 600px;"></div>
		
	<script type="text/javascript">
		jQuery(document).ready(function($){
			
			function testOrganisationInput() {
				var filter = /^[a-z0-9+_.\s]+$/i;
				if (filter.test($("#oname").val()) || $("#oname").val()=="") {
					$("#oname").removeClass("error");
					$("#infotext").removeClass("error");
					$("#infotext").text("");
					return true;
				} else {
					$("#oname").addClass("error");
					$("#infotext").addClass("error");
					$("#infotext").text("Invalid character/s entered");
					return false;
				}
			}
	
			//Prepare jTable
			$("#OrganisationTableContainer").jtable({
				//title: "Table of organisations",
				paging: true,
				pageSize: 10,
				sorting: true,
				defaultSorting: "oname ASC",
				actions: {
					listAction: module_url + "actions/organisation_actions.php?action=list_organisations"
				},
				fields: {
					org_id: {
						key: true,
						create: false,
						edit: false,
						list: false
					},
					name: {
						title: Drupal.t('Organisation'),
						width: "30%",
						display: function (data) {
							return "<a title=\"View organisation details\" href=\"javascript:void(0);\" onclick=\"getOrganisationDetail("+
								data.record.org_id+")\">"+data.record.name+"</a>";
							},
					},
					url:{
						title: Drupal.t('More information'),
						width: "70%",
						display: function (data) {
							return "<a class='external' title=\"View organisation/project site\" target=\"_blank\" href=\""+data.record.url+"\" >"+data.record.url+"</a>";
							},
					}
				},
				/*
recordsLoaded: function(event, data) {
					$(".jtable-data-row").click(function() {
						var row_id = $(this).attr("data-record-key");
						var url = base_url + "dashboard/organisations/browse?org_id=" + row_id;
						document.location.href=url;
					});
				}
				*/
				
			});
			
			//Load org list from server on initial page load
			$("#OrganisationTableContainer").jtable("load", {<?php 
					echo ($org_id ? "orgid: $org_id": "");?>});
			$("#organisation_filter").submit(function(e){
				e.preventDefault();
				if(testOrganisationInput()){
					$("#OrganisationTableContainer").jtable("load", {
						oname: $("#oname").val()
					});
				}
			});
		});
	</script><?php 
}