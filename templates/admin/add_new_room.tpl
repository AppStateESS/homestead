<h2><small>{TERM} &raquo; {HALL_NAME} &raquo; {FLOOR_NUMBER_LINK}</small></h2>
<h2>Add Room to {FLOOR_NUMBER}</h2>

{START_FORM}

<div class="row">
        <!-- Settings Panel -->
        <div class="panel panel-default col-md-6">
            <div class="panel-heading">
                <h3 class="panel-title">New Room</h3>
            </div>
            <div class="panel-body">
              <label>
                <i class="fa fa-cog"></i>
                Settings
              </label>
              <p class="col-md-12">
                <div class="form-group">
                    <label class="col-md-6">
                      Room Number
                    </label>
                    <div class="col-md-6">
                      {ROOM_NUMBER}
                    </div>
                </div>
              </p>
              <p class="col-md-12">
                <div class="form-group">
                     <label class="col-md-6">
                       Default Gender
                      </label>
                     <div class="col-md-6">
                       {DEFAULT_GENDER}
                     </div>
                </div>
              </p>
              <p class="col-md-12">
                <div class="form-group">
                    <label class ="col-md-6" for="phpws_form_gender_type">
                      Gender
                    </label>
                    <div class="col-md-6">
                      {GENDER_TYPE}
                    </div>
                </div>
              </p>
              <p class="col-md-12">
                <div class="form-group">
                  <label class="col-md-6">
                    Residential Learning Community
                  </label>
                  <div class="col-md-6">
                    {RLC_RESERVED}
                  </div>
                </div>
              </p>
            </div>



            <!-- Room Reservation Panel -->
            <div class="panel-body">
              <div cass="row">
                <div class="col-md-6">
                  <label><i class="fa fa-tags"></i> Status</label>
                  <div class="checkbox">
                    {OFFLINE} {OFFLINE_LABEL}
                  </div>
                  <div class="checkbox">
                    {RESERVED} {RESERVED_LABEL}
                  </div>
                  <div class="checkbox">
                    {RA} {RA_LABEL}
                  </div>
                  <div class="checkbox">
                    {OVERFLOW}{OVERFLOW_LABEL}
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <label><i class="fa fa-list"></i> General Features</label>
                  <div class="checkbox">
                    {PRIVATE} {PRIVATE_LABEL}
                  </div>
                  <div class="checkbox">
                    {PARLOR} {PARLOR_LABEL}
                  </div>
                  <label><i class="fa fa-plus-square"></i> Medical Features</label>
                  <div class="checkbox">
                    {ADA} {ADA_LABEL}
                  </div>
                  <div class="checkbox">
                    {HEARING_IMPAIRED}{HEARING_IMPAIRED_LABEL}
                  </div>
                  <div class="checkbox">
                    {BATH_EN_SUITE}{BATH_EN_SUITE_LABEL}
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">Add Room</button>
            </div>

</div>

{END_FORM}
