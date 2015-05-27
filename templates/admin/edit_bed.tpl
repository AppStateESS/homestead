<h2>Edit Bed - {HALL_NAME}, Room {ROOM_NUMBER}, Bed {BED_LABEL}</h2>

<div class="alert alert-info">
    <p>{TERM} &raquo; {HALL_NAME} &raquo; {FLOOR_NUMBER} &raquo; {ROOM_NUMBER_LINK}</p>
</div>

{START_FORM}
<div class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-xs-2" for="phpws_form_bedroom_label">Bedroom Label</label>
        <div class="col-xs-3">{BEDROOM_LABEL}</div>
    </div>
</div>
<div class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-xs-2" for="phpws_form_phone_number">Phone Number</label>
        <div class="col-xs-4">{PHONE_NUMBER}<p><small>Last four digits <strong>only</strong> after 828-266-####</small></p></div>
    </div>
</div>

<!-- BEGIN bedletter -->
<div class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-xs-2" for="phpws_form_bed_letter">Bed Letter</label>
        <div class="col-xs-10 form-inline">{BED_LETTER}</div>
    </div>
</div>
<!-- END bedletter -->


<div class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-xs-2" for="phpws_form_banner_id">Banner Bed ID</label>
        <div class="col-xs-10 form-inline">{HALL_ABBR} {BANNER_ID}</div>
    </div>
</div>

<div class="form-group">
    <div class="checkbox col-xs-offset-2"">
        <label>{RA} Reserved for RA</label>
    </div>
</div>

<div class="form-group">
    <div class="checkbox col-xs-offset-2">
        <label>{RA_ROOMMATE} Hold empty for RA Roommate</label>
    </div>
</div>
<div class="form-group">
    <div class="checkbox col-xs-offset-2"">
        <label>{INTERNATIONAL_RESERVED} International Reserved</label>
    </div>
</div>


<button class="btn btn-primary btn-lg"><i class="fa fa-floppy-o"></i> Save bed</button>
{END_FORM}
<hr />
<div class="alert alert-info">
    <p>Currently assigned to {ASSIGNED_TO}</p>
</div>
{HISTORY}