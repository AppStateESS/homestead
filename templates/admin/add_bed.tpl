<h2><small>{TERM} &raquo; {HALL_NAME} &raquo; {FLOOR_NUMBER} &raquo; {ROOM_NUMBER}</small></h2>

{START_FORM}

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
              Add Bed
            </div>

            <p>
            </p>

            <div class="col-md-6">
              <h3 class="panel-title">
                <i class="fa fa-cog"></i>
                Properties
              </h3>
            </div>

            <div class="row"></div>

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

                <h3 class="panel-title">
                  <i class="fa fa-tags"></i>
                  General Features
                </h3>

                <div class="panel-body">
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                              {RA} Reserved for RA
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                              {RA_ROOMMATE} Hold empty for RA Roommate
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                              {INTERNATIONAL_RESERVED} International Reserved
                            </label>
                        </div>
                    </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success pull-right">
                      Submit
                    </button>
                </div>
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
