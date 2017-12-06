<nav class="navbar navbar-default navbar-fixed">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navigation-example-2">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-left">
                <!-- Notifications
                <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-globe"></i>
                            <b class="caret"></b>
                            <span class="notification">5</span>
                      </a>
                      <ul class="dropdown-menu">
                        <li><a href="#">Notification 1</a></li>
                        <li><a href="#">Notification 2</a></li>
                        <li><a href="#">Notification 3</a></li>
                        <li><a href="#">Notification 4</a></li>
                        <li><a href="#">Another notification</a></li>
                      </ul>
                </li>
                -->

                <!-- BEGIN term_selector -->
                {TERM_SELECTOR}
                <!-- END term_selector -->

                <!-- BEGIN student_search -->
                <li>
                   <a href="">
                        <i class="fa fa-search"></i>
                    </a>
                </li>
                {STUDENT_SEARCH}
                <form class="navbar-form navbar-left" role="search">
                    <div class="form-group has-feedback">
                        <input type="text" id="studentSearch" class="form-control typeahead" name="studentSearchQuery" placeholder="Search" autocomplete="off">
                        <span id="student-search-spinner" class="fa fa-spinner fa-spin form-control-feedback" style="width:34px; height:auto; top:10px; right:-5px;" aria-hidden="true"></span>
                    </div>
                </form>
                <!-- END student_search -->

            </ul>

            <ul class="nav navbar-nav navbar-right">
                <!-- BEGIN userstatus -->
                {USER_STATUS_DROPDOWN}
                <!-- END userstatus -->

                <!-- BEGIN display_name -->
                <li><a href="#">{DISPLAY_NAME}</a></li>
                <!-- END display_name -->

                <li>
                    <a href="{SIGNOUT_URL}">
                        Log out
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
