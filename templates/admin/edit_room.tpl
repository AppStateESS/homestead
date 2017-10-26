<h2><small>{TERM} &raquo; {HALL_NAME} &raquo; {FLOOR_NUMBER}</small></h2>
<h1 style="display: inline-block; margin-right: 15px">Room {ROOM}</h1>

<!-- BEGIN offline -->
<h3 style="display:inline-block"><span class="label label-danger">{OFFLINE_ATTRIB}</span></h3>
<!-- END offline -->

<!-- BEGIN reserved -->
<h3 style="display:inline-block"><span class="label label-warning">{RESERVED_ATTRIB}</span></h3>
<!-- END reserved -->

<!-- BEGIN private -->
<h3 style="display:inline-block"><span class="label label-warning">{PRIVATE_ATTRIB}</span></h3>
<!-- END private -->

<!-- BEGIN parlor -->
<h3 style="display:inline-block"><span class="label label-warning">{PARLOR_ATTRIB}</span></h3>
<!-- END parlor -->

<!-- BEGIN overflow -->
<h3 style="display:inline-block"><span class="label label-warning">{OVERFLOW_ATTRIB}</span></h3>
<!-- END overflow -->

<!-- BEGIN RA -->
<h3 style="display:inline-block"><span class="label label-info">{RA_ATTRIB}</span></h3>
<!-- END RA -->

<!-- BEGIN ada -->
<h3 style="display:inline-block"><span class="label label-default">{ADA_ATTRIB}</span></h3>
<!-- END ada -->

<!-- BEGIN hearing -->
<h3 style="display:inline-block"><span class="label label-default">{HEARING_ATTRIB}</span></h3>
<!-- END hearing -->

<!-- BEGIN bath -->
<h3 style="display:inline-block"><span class="label label-default">{BATHENSUITE_ATTRIB}</span></h3>
<!-- END bath -->

{START_FORM}

<div class="row">
    <!-- Bed / Student list panel -->
    <div class="col-md-7">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-bed"></i> Beds</h3>
            </div>
            <div class="panel-body">
                <span class="pull-right">{NUMBER_OF_ASSIGNEES} of {NUMBER_OF_BEDS} occupied</span>
                {BED_PAGER}
            </div>
        </div>

        <!-- Settings Panel -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-cog"></i> Settings</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    {ROOM_NUMBER_LABEL} {ROOM_NUMBER}
                </div>
                <div class="form-group">
                    <label for="phpws_form_gender_type">Gender</label>
                    <!-- BEGIN gender_message -->
                    {GENDER_MESSAGE} {GENDER_REASON}
                    <!-- END gender_message -->
                    {GENDER_TYPE}
                </div>
                <div class="form-group">
                    {DEFAULT_GENDER_LABEL} {DEFAULT_GENDER}
                </div>
                <div class="form-group">
                    {RLC_RESERVED_LABEL}{RLC_RESERVED}
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success"><i class="fa fa-disk"></i> Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Room Reservation Panel -->
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-tags"></i> Status</h3>
            </div>
            <div class="panel-body">
                {OFFLINE} {OFFLINE_LABEL}<br />
                {RA} {RA_LABEL}<br />
                {PRIVATE} {PRIVATE_LABEL}<br />
                {OVERFLOW}{OVERFLOW_LABEL}<br />
                {PARLOR} {PARLOR_LABEL}<br />
                <strong>Medical</strong>
                <div style="margin-left: 15px;">
                    {ADA} {ADA_LABEL}<br />
                    {HEARING_IMPAIRED}{HEARING_IMPAIRED_LABEL}<br />
                    {BATH_EN_SUITE}{BATH_EN_SUITE_LABEL}<br />
                </div>
                <div class="form-group">
                    <label>Reserved</label>
                    <select id="phpws_form_reserved_reason" class="form-control" name="reserved_reason">
                        <option value="none">None</option>
                        <optgroup label="Athletics">
                            <!-- BEGIN ATHLETICS_OPTIONS -->
                                <option value="{VALUE}">{NAME}</option>
                            <!-- END ATHLETICS_OPTIONS -->
                        </optgroup>
                        <optgroup label="Special Needs">
                            <!-- BEGIN SPECIAL_NEEDS_OPTIONS -->
                                <option value="{VALUE}">{NAME}</option>
                            <!-- END SPECIAL_NEEDS_OPTIONS -->
                        </optgroup>
                        <optgroup label="Scholars/Organizations">
                            <!-- BEGIN SCHOLARS_OPTIONS -->
                                <option value="{VALUE}">{NAME}</option>
                            <!-- END SCHOLARS_OPTIONS -->
                        </optgroup>
                        <optgroup label="Miscellaneous">
                            <!-- BEGIN MISC_OPTIONS -->
                                <option value="{VALUE}">{NAME}</option>
                            <!-- END MISC_OPTIONS -->
                        </optgroup>
                    </select>
                </div>
                <div class="form-group">
                    <label>Reserved Notes</label>
                    <input id="phpws_form_reserved_notes" class="form-control" name="reserved_notes"></input>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success"><i class="fa fa-disk"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

{END_FORM}


<div class="row">
    <div class="col-md-7">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-wrench"></i> Damages</h3>
            </div>
            <div class="panel-body">
                <div id="RoomDamages"></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    phpws_form_reserved_reason.value = '{CURRENT_REASON}';
    phpws_form_reserved_notes.value = '{RESERVED_NOTES}';
</script>

<div id="addDamageDialog"></div>

<script type ="text/javascript">
  var roomPersistentId = '{ROOM_PERSISTENT_ID}';
  var term             = '{TERM}';
</script>

<script type="text/javascript" src="{vendor_bundle}"></script>
<script type="text/javascript" src="{entry_bundle}"></script>
