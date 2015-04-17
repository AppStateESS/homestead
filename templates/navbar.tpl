<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="index.php">Homestead</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
    
      <ul class="nav navbar-nav">
      	<!-- BEGIN term_selector -->
      	{TERM_SELECTOR}
      	<!-- END term_selector -->
      	
      	<!-- BEGIN student_search -->
      {STUDENT_SEARCH}
      <form class="navbar-form navbar-left" role="search">
        <div class="form-group">
          <input type="text" id="studentSearch" class="form-control typeahead" name="studentSearchQuery" placeholder="Search" autocomplete="off">
        </div>
      </form>
      <!-- END student_search -->
      	
      	<!-- BEGIN halls_link -->
        <li><a href="{HALL_VIEW}"><i class="fa fa-building"></i> Halls</a></li>
        <!-- END halls_link -->
        
        <!-- BEGIN reports_link -->
        <li><a href="{REPORT_LINK}"><i class="fa fa-bar-chart"></i> Reports</a></li>
        <!-- END halls_link -->
        
      </ul>
      
      <ul class="nav navbar-nav navbar-right">
      <!-- BEGIN dropdown -->
        {DROPDOWN}
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-cog"></i> Settings <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <!-- BEGIN SETTINGS -->
            <li>{LINK}</li>
            <!-- END SETTINGS -->
            <li class="divider"></li>
            
            <!-- BEGIN term -->
            <li><a href="{EDIT_TERM_URI}"><i class="fa fa-calendar"></i> Edit Terms</a></li>
            <!-- END term -->
            
            <!-- BEGIN activitylog -->
            <li><a href="{ACTIVITY_LOG_URI}"><i class="fa fa-list-ul"></i> Activity Log</a></li>
            <!-- END activitylog -->
            
            <li class="divider"></li>
            <li><a href="{STUDENT_VIEW_URI}"><i class="fa fa-user"></i> Switch to Student View</a></li> 
            
            <!-- BEGIN ctrlpanel -->
            <li class="divider"></li>
            <li><a href="{CTRL_PANEL_URI}"><i class="fa fa-wrench"></i> Control Panel</a></li>
            <!-- END ctrlpanel -->
          </ul>
        </li>
        <!-- END dropdown -->
        
        <!-- BEGIN fullname -->
        <li><a href="#">{FULL_NAME}</a></li>
        <!-- END fullname -->
        
        <!-- BEGIN signin -->
        <li><a href="{SIGNIN_URL}"><i class="fa fa-sign-in"></i> Sign in</a></li>
        <!-- END signin -->
        
        <!-- BEGIN signout -->
        <li><a href="{SIGNOUT_URL}"><i class="fa fa-sign-out"></i> Sign out</a></li>
        <!-- END signout -->
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
