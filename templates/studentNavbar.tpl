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
      <a class="navbar-brand" href="index.php" title="Homestead">Homestead</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

      <ul class="nav navbar-nav">

      </ul>

      <ul class="nav navbar-nav navbar-right">
        <!-- BEGIN userstatus -->
        {USER_STATUS_DROPDOWN}
        <!-- END userstatus -->

        <!-- BEGIN display_name -->
        <li><a href="#">{DISPLAY_NAME}</a></li>
        <!-- END display_name -->

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
