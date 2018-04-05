<div class="row">
  <div class="col-md-12">
	<h1><i class="fa fa-bar-chart"></i> {NAME}</h1>

	<div class="panel panel-default">
	  <div class="panel-body">
	  	<!-- BEGIN reportDesc -->
	    <p class="lead">{REPORT_DESC}</p>
	    <!-- END reportDesc -->
	    
	    <!-- BEGIN noDesc -->{NO_DESC}
	    <p class="text-muted">No description available.</p>
	    <!-- END noDesc -->
	    
	    <!-- BEGIN last-run-relative -->
	    <p>Last executed {LAST_RUN_RELATIVE} by <em>{LAST_RUN_USER}</em>.</p>
	    <!-- END last-run-relative -->
	    
	    <!-- BEGIN never-run -->{NEVER_RUN}
	    <p class="text-muted">This report has never been executed.</p>
	    <!-- END never-run -->
	  </div>
	</div>
  </div>
</div>

<div class="row">
  <div class="col-md-9">
    <div class="panel panel-default">
      <div class="panel-heading"><h4><i class="fa fa-archive"></i> Archived Results</h4></div>
      <div class="panel-body">
        {RESULTS_PAGER}
      </div>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="panel panel-default">
      <div class="panel-heading"><h4><i class="fa fa-cogs"></i> Schedule &amp; Execute</h4></div>
      <div class="panel-body">
        <ul>
	      <!-- BEGIN run-now -->
	      <li>{RUN_NOW}</li>
	      <!-- END run-now -->
	      <!-- BEGIN run-now-diabled -->
	      {RUN_NOW_DISABLED}
	      <li class="text-muted">Run now not allowed</li>
	      <!-- END run-now-diabled -->
	
	      <!-- BEGIN run-bg -->
	      <li>{RUN_BACKGROUND}</li>
	      <!-- END run-bg -->
	      <!-- BEGIN run-bg-disabled -->
	      {RUN_BACKGROUND_DISABLED}
	      <li class="text-muted">Background execution not allowed</li>
	      <!-- END run-bg-disabled -->
	
	      <!-- BEGIN run-schedule -->
	      <li>{RUN_SCHEDULE}</li>
	      <!-- END run-schedule -->
	      <!-- BEGIN run-schedule-disabled -->
	      {RUN_SCHEDULE_DISABLED}
	      <li class="text-muted">Scheduled execution not allowed</li>
	      <!-- END run-schedule-disabled -->
    	</ul>
      </div>
    </div> <!-- end panel -->
    
    <div class="panel panel-default">
      <div class="panel-heading"><h4><i class="far fa-clock"></i> Pending</h4></div>
      <div class="panel-body">
        {SCHEDULE_PAGER}
      </div>
    </div> <!-- end panel -->
  </div> <!-- end col-md-3 -->
</div> <!-- end row -->
