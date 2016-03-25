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
            <label class="col-md-4">
                Room Assignment:
            </label>
            <div class="col-md-5">
                {ASSIGNMENT}
            </div>
        </div>

        <div class="row">
            <label class="col-md-4">
                Move-in time:
            </label>
            <div class="col-md-5">
                {MOVE_IN_TIME}
            </div>
        </div>
        <!-- END assignment -->

        <!-- BEGIN no_assignment -->
        <div class="row">
            <label class="col-md-4">
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
            <label class="col-md-4">
                Roommate(s):
            </label>
            <div class="col-md-5">
                {ROOMMATE}
            </div>
        </div>
        <!-- END roommate -->

        <div class="row">
            <label class="col-md-4">
                Learning Community:
            </label>
            <div class="col-md-5">
                {RLC}
            </div>
        </div>

        <div class="row" style="margin-top: 30px">
            <div class="col-md-11">
                <div class="alert alert-info">
                    <h4>
                        <i class="fa fa-exclamation"></i>
                        Mail and Packages
                    </h4>
                    <p>
                        When shipping packages to a student, please use the appropriate mailing address format.
                        Please use the University Post Office P.O. Box when shipping packages via USPS.
                        However if the package is shipped via a private carrier such as UPS, Fedex, etc., then
                        you must ship them to the service desk in order to be able to pickup the package. For further information
                        visit the <strong><a href='https://housing.appstate.edu/packages'>Life on Campus</a></strong> section of the housing website.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <label class="col-md-5">
                P.O. Box (US Postal Service):
            </label>
            <div class="col-md-7">
                <!-- BEGIN po_empty_address -->
                    <p>{PO_EMPTY_ADDRESS}</p>
                <!-- END po_empty_address -->
                <!-- BEGIN po_address -->
                    <p>{PO_NAME}</p>
                    <p>{PO_STREET_ONE}</p>
                    <p>{PO_STREET_TWO}</p>
                    <p>{PO_STREET_THREE}</p>
                    <p>{PO_CITY}, {PO_STATE} {PO_ZIPCODE}</p>
                <!-- END po_address -->
            </div>
        </div>

        <div class="col-md-6">
            <label class="col-md-5">
                Private Carriers (UPS, Fedex, etc):
            </label>
            <div class="col-md-7">
                <!-- BEGIN unassigned -->
                    <p>{NO_ASSIGNMENT}</p>
                <!-- END unassigned -->
                <!-- BEGIN desk_address -->
                    <p>{DESK_NAME}</p>
                    <p>{DESK_CO_LABEL}</p>
                    <p>{DESK_STREET}</p>
                    <p>{DESK_CITY}, {DESK_STATE} {DESK_ZIPCODE}</p>
                <!-- END desk_address -->
            </div>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <a href="index.php" class="btn btn-default pull-left"><i class="fa fa-chevron-left"></i> Back</a>
    </div>
</div>
