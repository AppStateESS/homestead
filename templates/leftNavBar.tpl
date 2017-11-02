<ul class="nav">
    <li class="active">
        <a href="#">
            <i class="fa fa-tachometer"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <!-- BEGIN assignments -->
    <li>
        <a href="{ASSIGNMENTS_URI}">
            <i class="fa fa-user"></i>
            <p>Assignments</p>
        </a>
    </li>
    <!-- END assignments -->

    <!-- BEGIN halls_link -->
    <li>
        <a href="{HALLS_URI}">
            <i class="fa fa-building"></i>
            <p>Halls</p>
        </a>
    </li>
    <!-- END halls_link -->

    <!-- BEGIN learning_communities -->
    <li>
        <a href="{RLC_URI}">
            <i class="fa fa-users"></i>
            <p>Communities</p>
        </a>
    </li>
    <!-- END learning_communities -->

    <!-- BEGIN messaging -->
    <li>
        <a href="{MESSAGING_URI}">
            <i class="fa fa-envelope"></i>
            <p>Messaging</p>
        </a>
    </li>
    <!-- END messaging -->

    <!-- BEGIN reports_link -->
    <li>
        <a href="{REPORTS_URI}">
            <i class="fa fa-bar-chart"></i>
            <p>Reports</p>
        </a>
    </li>
    <!-- END reports_link -->

    <!-- BEGIN service_desk -->
    <li>
        <a href="{SERVICE_DESK_URI}">
            <i class="fa fa-cogs"></i>
            <p>Service Desk</p>
        </a>
    </li>
    <!-- END service_desk -->

    <!-- BEGIN reapplication -->
    <li>
        <a href="{REAPPLICATION_URI}">
            <i class="fa fa-refresh"></i>
            <p>Re-application</p>
        </a>
    </li>
    <!-- END reapplication -->

    <li class="dropup settings-dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-cog"></i> Settings <span class="caret"></span></a>
      <ul class="dropdown-menu" role="menu">

        <!-- BEGIN term -->
        <li><a href="{EDIT_TERM_URI}"><i class="fa fa-calendar"></i> Edit Terms</a></li>
        <!-- END term -->

        <!-- BEGIN pulse -->
        <li><a href="{PULSE_URI}"><i class="fa fa-calendar-o"></i> Schedule processes</a></li>
        <!-- END pulse -->

        <!-- BEGIN activitylog -->
        <li><a href="{ACTIVITY_LOG_URI}"><i class="fa fa-list-ul"></i> Activity Log</a></li>
        <!-- END activitylog -->

        <!-- BEGIN ctrlpanel -->
        <li class="divider"></li>
        <li><a href="{CTRL_PANEL_URI}"><i class="fa fa-wrench"></i> Control Panel</a></li>
        <!-- END ctrlpanel -->
      </ul>
    </li>
</ul>
