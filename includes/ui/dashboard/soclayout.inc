<?php
drupal_add_css(drupal_get_path('module', 'vals_soc') . '/includes/css/dashboard/css/dashboard.css', array(
      'type' => 'file',
      'group' => CSS_THEME
    ));

function initDashboardLayout(){
	
	global $base_url;
	echo '
		<div class="dashboard" id="main-dashboard">
  <div class="dashboard-head">
    <span>
      Semester of Code Project Member Dashboard
    </span>
  </div>

  <div class="block block-dashboard">
    <div class="column first">
        
          <div class="column-entry manage_proposals">
            <h4><a class="dashboard-link" href="'.$base_url.'/dashboard/proposals/overview" title="Overview of proposals">
            Overview of proposals per state</a>
            </h4>
            <p>Lists all the proposals submitted to the program divided in tabs per state.
            </p>
            <div>
	            <a class="dashboard-link" href="'.$base_url.'/dashboard/proposals/browse" title="Browse all proposals">
	            Browse all proposals</a>
            </div>
          </div>
    </div>
    
    <div class="column second">          
          <div class="column-entry manage_organizations">
            <h4>
              <a class="dashboard-link" href="'.$base_url.
                '/dashboard/members" title="Manage organizations">Manage organizations</a>
            </h4>
            <p>Manage organizations from current program. You can 
allocate slots for organizations, list mentors and administrators from 
various organizations</p>
          </div>
    </div><!-- column -->
</div>
			';
}

initDashboardLayout();