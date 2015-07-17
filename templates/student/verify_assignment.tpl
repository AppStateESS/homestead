<h2>Verify Your Housing Status</h2>

<div class="row">
    <div class="col-md-10">

        <div class="row">
            <div class="col-md-11">
                <div class="alert alert-info">
                    <h4>
                        <i class="fa fa-exclamation"></i>
                        This information is not final and is subject to change.
                    </h4>
                    <p>
                        The information displayed below only represents your current status within the Housing Management System and is listed for
                        your convenience only. Your room assignment, Learning Community assignment and
                        other information displayed below are subject to change.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- BEGIN assignment -->
        <div class="row">
            <label class="col-md-3">
                Room Assignment:
            </label>
            <div class="col-md-5">
                {ASSIGNMENT}
            </div>
        </div>

        <div class="row">
            <label class="col-md-3">
                Move-in time:
            </label>
            <div class="col-md-5">
                {MOVE_IN_TIME}
            </div>
        </div>
        <!-- END assignment -->

        <!-- BEGIN no_assignment -->
        <div class="row">
            <label class="col-md-3">
                Room Assignment:
            </label>
            <div class="col-md-5">
                <p>
                    {NO_ASSIGNMENT}
                </p>
            </div>
        </div>
        <!-- END no_assignment -->

        <!-- BEGIN roommate -->
        <div class="row">
            <label class="col-md-3">
                Roommate(s):
            </label>
            <div class="col-md-5">
                {ROOMMATE}
            </div>
        </div>
        <!-- END roommate -->

        <div class="row">
            <label class="col-md-3">
                Learning Community:
            </label>
            <div class="col-md-5">
                {RLC}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <a href="index.php" class="btn btn-default pull-left"><i class="fa fa-chevron-left"></i> Back</a>
    </div>
</div>
