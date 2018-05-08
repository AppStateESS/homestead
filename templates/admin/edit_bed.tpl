<h2><small>{TERM} &raquo; {HALL_NAME} &raquo; {FLOOR_NUMBER} &raquo; {ROOM_NUMBER_LINK}</small></h2>
<h1 style="display: inline-block; margin-right: 15px">Room {ROOM_NUMBER} - Bed {BED_LABEL}</h1>

<!-- BEGIN bath -->
<h3 style="display:inline-block"><span class="badge badge-danger">{ROOM_CHANGE_RESERVED}</span></h3>
<!-- END bath -->

{START_FORM}
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Assignment</h3>
            </div>
            <!-- BEGIN reserve_link -->
            <div class="panel-body">
                <p>Currently reserved for a {RESERVE_LINK}</p>
            </div>
            <!-- END reserve_link -->
            <!-- BEGIN assigned_to -->
            <div class="panel-body">
                <p>Currently assigned to {ASSIGNED_TO}</p>
            </div>
            <!-- END assigned_to -->
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-cog"></i> Settings</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="{BEDROOM_LABEL_ID}">Bedroom Label</label>
                    {BEDROOM_LABEL}
                </div>
                <div class="form-group">
                    <label for="{PHONE_NUMBER_ID}">Phone Number</label>
                    {PHONE_NUMBER}
                    <span class="help-block">Last four digits <strong>only</strong> after 828-266-####</span>
                </div>

                <!-- BEGIN bedletter -->
                <div class="form-group">
                    <label for="{BED_LETTER_ID}">Bed Letter</label>
                    {BED_LETTER}
                </div>
                <!-- END bedletter -->


                <div class="form-group">
                    <label for="{BANNER_ID_ID}">Banner Bed ID</label>
                    <div class="input-group">
                        <span class="input-group-addon">{HALL_ABBR}</span>
                        {BANNER_ID}
                    </div>
                </div>

                <div class="form-group">
                    <button class="btn btn-success float-right">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-tags"></i> Status</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <div class="checkbox">
                        <label>{RA} Reserved for RA</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox">
                        <label>{RA_ROOMMATE} Hold empty for RA Roommate</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox">
                        <label>{INTERNATIONAL_RESERVED} International Reserved</label>
                    </div>
                </div>

                <div class="form-group">
                    <button class="btn btn-success float-right">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

{END_FORM}
<div class="row">
    <div class="col-md-8">
        {HISTORY}
    </div>
</div>
