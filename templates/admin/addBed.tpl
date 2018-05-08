<h2><small>{TERM} &raquo; {HALL_NAME} &raquo; {FLOOR_NUMBER} &raquo; {ROOM_NUMBER}</small></h2>
<h1>Add a New Bed</h1>
{START_FORM}

<div class="row">
    <div class="col-md-6">
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

                <div class="checkbox">
                    <label>
                      {RA} Reserved for RA
                    </label>
                </div>

                <div class="checkbox">
                    <label>
                      {RA_ROOMMATE} Hold empty for RA Roommate
                    </label>
                </div>

                <div class="checkbox">
                    <label>
                      {INTERNATIONAL_RESERVED} International Reserved
                    </label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success float-right">
                      Submit
                    </button>
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
