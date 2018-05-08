<h2><small>{TERM} &raquo; {HALL_NAME} &raquo; {FLOOR_NUMBER_LINK}</small></h2>
<h1>Add a New Room to {FLOOR_NUMBER}</h1>

{START_FORM}

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-cog"></i> Settings</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="{ROOM_NUMBER_ID}">Room Number</label>
                    {ROOM_NUMBER}
                </div>
                <div class="form-group">
                    <label for="{GENDER_TYPE_ID}">Gender</label>
                    {GENDER_TYPE}
                </div>
                <div class="form-group">
                    <label for="{DEFAULT_GENDER_ID}">Default Gender</label>
                    {DEFAULT_GENDER}
                </div>
                <div class="form-group">
                    <label for="{RLC_RESERVED_ID}">Residential Learning Community</label>
                    {RLC_RESERVED}
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success float-right">Add Room</button>
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
                <div class="checkbox">
                    <label>{OFFLINE} Offline</label>
                </div>
                <div class="checkbox">
                    <label>{RESERVED} Reserved</label>
                </div>
                <div class="checkbox">
                    <label>{RA} RA</label>
                </div>
                <div class="checkbox">
                    <label>{OVERFLOW} Overflow</label>
                </div>
                <div class="checkbox">
                    <label>{PRIVATE} Private</label>
                </div>
                <div class="checkbox">
                    <label>{PARLOR} Parlor</label>
                </div>
                <i class="fa fa-plus-square"></i> Medical
                <div class="checkbox">
                    <label>{ADA} ADA</label>
                </div>
                <div class="checkbox">
                    <label>{HEARING_IMPAIRED} Hearing Impaired</label>
                </div>
                <div class="checkbox">
                    <label>{BATH_EN_SUITE} Bath en Suite</label>
                </div>
            </div>
        </div>
    </div>
</div>

{END_FORM}
