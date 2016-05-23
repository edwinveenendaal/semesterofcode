<?php
function initBrowseInstitutesLayout($inst_id=''){?>
	
	<div class="filtering">
		<span id="infotext" style="margin-left: 34px"></span>
		<form id="organisation_filter">
			<?php echo t('Name');?>: <input type="text" name="iname" id="iname" />
		</form>
	</div>
	<div id="InstituteTableContainer" style="width: 600px;"></div>
		
	<script type="text/javascript">
		jQuery(document).ready(function($){
			
			function testInstituteInput() {
				var filter = /^[a-z0-9+_.\s]+$/i;
				if (filter.test($("#iname").val()) || $("#iname").val()=="") {
					$("#iname").removeClass("error");
					$("#infotext").removeClass("error");
					$("#infotext").text("");
					return true;
				} else {
					$("#iname").addClass("error");
					$("#infotext").addClass("error");
					$("#infotext").text("Invalid character/s entered");
					return false;
				}
			}
	
			//Prepare jTable
			$("#InstituteTableContainer").jtable({
				//title: "Table of institutes",
				paging: true,
				pageSize: 10,
				sorting: true,
				defaultSorting: "iname ASC",
				actions: {
					listAction: module_url + "actions/institute_actions.php?action=list"
				},
				fields: {
					inst_id: {
						key: true,
						create: false,
						edit: false,
						list: false
					},
					name: {
						title: Drupal.t('Institute'),
						width: "30%",
						display: function (data) {
							return "<a title=\"View institute details\" href=\"javascript:void(0);\" onclick=\"getInstituteDetail("+
								data.record.inst_id+")\">"+data.record.name+"</a>";
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
			$("#InstituteTableContainer").jtable("load", {<?php 
					echo ($inst_id ? "instid: $inst_id": "");?>});
			$("#organisation_filter").submit(function(e){
				e.preventDefault();
				if(testInstituteInput()){
					$("#InstituteTableContainer").jtable("load", {
						iname: $("#iname").val()
					});
				}
			});
		});
	</script><?php 
}