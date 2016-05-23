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
      
      Admin Dashboard
    </span>
  </div>

  <div class="block block-dashboard">
    <div class="column first">
        
          <div class="column-entry lookup_profile">
            <h4>
              <a class="" href="https://vals-melange2.appspot.com/gsoc/admin/lookup/google/gsoc2014" title="Lookup profile">Lookup profile</a>
              
            </h4>
            <p>Lookup profile of mentor or student from various program.</p>
            
          </div>
        
          <div class="column-entry allocate_slots">
            <h4>
              <a class="" href="https://vals-melange2.appspot.com/gsoc/admin/slots/google/gsoc2014" title="Allocate slots">Allocate slots</a>
              
            </h4>
            <p>Allocate slots (number of acceptable projects) per organization</p>
            
          </div>
        
          <div class="column-entry slots_transfer">
            <h4>
              <a class="" href="https://vals-melange2.appspot.com/gsoc/admin/slots/transfer/google/gsoc2014" title="Slots transfer">Slots transfer</a>
              
            </h4>
            <p>Transfer slots for organizations</p>
            
          </div>
        
          <div class="column-entry duplicates">
            <h4>
              <a class="" href="https://vals-melange2.appspot.com/gsoc/duplicates/google/gsoc2014" title="Duplicates">Duplicates</a>
              
            </h4>
            <p>Calculate how many duplicate proposals, students that have accepted proposals more than one</p>
            
          </div>
        
          <div class="column-entry accept_proposals">
            <h4>
              <a class=""  href="https://vals-melange2.appspot.com/gsoc/accept_proposals/google/gsoc2014" title="Bulk accept proposals and send acceptance/rejection emails">Bulk accept proposals and send acceptance/rejection emails</a>
            </h4>
            <p>Start proposals into projects conversion</p>
            
          </div>
        
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
            
            <!-- 
              <a class="" href="https://vals-melange2.appspot.com/gsoc/admin/proposals/accept/google/gsoc2014" title="Proposals submitted">Proposals submitted</a>
              -->
          </div>
        
          <div class="column-entry withdraw_projects">
            <h4>
              <a class="" href="javascript:void(0);" onclick="ajaxCall(\'project\', \'eval_projects\', \'content\');"
               title="Accept/withdraw projects">Accept/withdraw projects</a>
              <!-- href="https://vals-melange2.appspot.com/gsoc/withdraw_projects/google/gsoc2014" -->
            </h4>
            <p>Withdraw accepted projects or accept withdrawn projects</p>
            
          </div>
        
    </div>
    
    <div class="column">
          <div class="column-entry participants">
            <h4>
              <a class="dashboard-link" href="#participants" title="Participants">Participants</a>
            </h4>
            <p>List of all participants in this program.</p>
              <div>
                <a href="https://vals-melange2.appspot.com/gsoc/admin/list/mentors/google/gsoc2014" title="List mentors and admins">List mentors and admins</a>
                |
                <a href="https://vals-melange2.appspot.com/gsoc/admin/students/google/gsoc2014" title="List students">List students</a>
              </div>
          </div>
        
          <div class="column-entry students">
            <h4>
              <a class="dashboard-link" href="#students" title="Students">Students</a>
            </h4>
            <p>Manage all the Students projects.</p>
              <div>
                <a href="https://vals-melange2.appspot.com/gsoc/admin/all_projects/google/gsoc2014" title="View All Projects">View All Projects</a>
                 |
                
                  
                    <a href="https://vals-melange2.appspot.com/gsoc/admin/manage_projects/google/gsoc2014" title="Manage Projects">Manage Projects</a>
                  
                  
                
              </div>
            
          </div>
        
          <div class="column-entry manage_organizations">
            <h4>
              <a class="dashboard-link" href="'.$base_url.'/dashboard/members" title="Manage organizations">Manage organizations</a>
              
            </h4>
            <p>Manage organizations from current program. You can 
allocate slots for organizations, list mentors and administrators from 
various organizations</p>
            
              <div>
                
                  
                    <a href="https://vals-melange2.appspot.com/gsoc/org/application/edit/google/gsoc2014" title="Edit organization application">Edit organization application</a>
                  
                  |
                
                  
                    <a href="https://vals-melange2.appspot.com/gsoc/org/application/preview/google/gsoc2014" title="Preview organization application">Preview organization application</a>
                  
                  |
                
                  
                    <a href="https://vals-melange2.appspot.com/gsoc/org/application/list/google/gsoc2014" title="Submitted organization applications">Submitted organization applications</a>
                  
                  
                
              </div>
            
          </div>
        
          <div class="column-entry evaluations">
            <h4>
              <a class="dashboard-link" href="#evaluations" title="Evaluations">Evaluations</a>
              
            </h4>
            <p>Send reminder, evaluation group, create, edit, view evaluations for mentors and students</p>
            
              <div>
                
                  
                    <a href="https://vals-melange2.appspot.com/gsoc/admin/survey_reminder/google/gsoc2014" title="Send reminder">Send reminder</a>
                  
                  |
                
                  
                    <a class="dashboard-link" href="#mentor_evaluations" title="Mentor Evaluations">Mentor Evaluations</a>
                  
                  |
                
                  
                    <a class="dashboard-link" href="#student_evaluations" title="Student Evaluations">Student Evaluations</a>
                  
                  |
                
                  
                    <a class="dashboard-link" href="#evaluation_group" title="Evaluation Group">Evaluation Group</a>
                  
                  
                
              </div>
            
          </div>
        
          <div class="column-entry program_settings">
            <h4>
              <a class="dashboard-link" href="'.$base_url.'/dashboard/program_settings" title="Program settings">Program settings</a>
              
            </h4>
            <p>Edit program settings and timeline</p>
            
              <div>
                    <a href="'.$base_url.'/dashboard/program_settings/timeline" title="Edit timeline">Edit timeline</a>
                  |
                    <a href="'.$base_url.'/dashboard/program_settings/messages" title="Edit messages">Edit messages</a>
              |
                    <a href="'.$base_url.'/dashboard/program_settings/frontpage" title="Edit frontpage">Edit messages</a>
              </div>
            
          </div>
        
          <div class="column-entry shipment_tracking">
            <h4>
              <a class="dashboard-link" href="#shipment_tracking" title="Tracking Information">Tracking Information</a>
              
            </h4>
            <p>Shipment tracking for students</p>
            
              <div>
                
                  
                    <a class="dashboard-link" href="#shipment_infos" title="Shipment Information">Shipment Information</a>
                  
                  |
                
                  
                    <a href="https://vals-melange2.appspot.com/gsoc/admin/shipment_tracking/sync/google/gsoc2014" title="Sync Data">Sync Data</a>
                  
                  
                
              </div>
            
          </div>
        
    </div>
    

    
  </div>
</div>
			';
}

initDashboardLayout();