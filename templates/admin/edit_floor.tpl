<div class="row">
    <div class="col-md-8">
        <h2><small>{TERM} &raquo; {HALL_NAME}</small></h2>
        <h1>{FLOOR_NUMBER} Floor</h1>

        <!-- BEGIN offline -->
        <h3 style="display:inline-block"><span class="label label-danger">{OFFLINE_ATTRIB}</span></h3>
        <!-- END offline -->

        <!-- BEGIN rlc -->
        <h3 style="display:inline-block"><span class="label label-info">{RLC_NAME}</span></h3>
        <!-- END rlc -->
    </div>
</div>

<div class="row" style="margin-top:2em;">
    <div class="col-md-2 text-center">
        Rooms<p class="lead">{NUMBER_OF_ROOMS}</p>
    </div>
    <div class="col-md-2 text-center">
        Beds<p class="lead">{NUMBER_OF_BEDS}</p>
    </div>
    <div class="col-md-2 text-center">
        Nominal beds<p class="lead">{NOMINAL_BEDS}</p>
    </div>
    <div class="col-md-2 text-center">
        Residents<p class="lead">{NUMBER_OF_ASSIGNEES}</p>
    </div>
</div>


<div class="row">
    <div class="col-md-8">
        <div role="tabpanel">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" style="margin-bottom: 15px;" role="tablist">
                <li role="presentation" class="active"><a href="#floors-tab" aria-controls="floors-tab" role="tab" data-toggle="tab">Rooms</a></li>
                <li role="presentation"><a href="#settings-tab" aria-controls="settings-tab" role="tab" data-toggle="tab">Settings</a></li>
                <li role="presentation"><a href="#images-tab" aria-controls="images-tab" role="tab" data-toggle="tab">Images</a></li>
                <li role="presentation"><a href="#roles-tab" aria-controls="roles-tab" role="tab" data-toggle="tab">Roles</a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <!-- Floor list tab -->
                <div role="tabpanel" class="tab-pane active" id="floors-tab">
                    <div id="static_pager">
                      {STATIC_ROOM_PAGER}
                    </div>
                </div>
                <!-- Settings tab -->
                <div role="tabpanel" class="tab-pane" id="settings-tab">
                    {START_FORM}
                    <div class="row">
                        <div class="col-md-7">
                            <div class="checkbox">
                                <label for="{IS_ONLINE_ID}">
                                    {IS_ONLINE} Is online
                                </label>
                            </div>

                            <div class="form-group">
                                <label for="{GENDER_TYPE_ID}">Gender type</label>
                                <!-- BEGIN gender_radio_buttons -->
                                {GENDER_TYPE}
                                <!-- END gender_radio_button -->
                            </div>

                            <div class="form-group">
                                <label for="{F_MOVEIN_TIME_ID}">Freshmen Move-in Time</label>
                                {F_MOVEIN_TIME}
                            </div>

                            <div class="form-group">
                                <label for="{T_MOVEIN_TIME_ID}">Transfer Move-in Time</label>
                                {T_MOVEIN_TIME}
                            </div>

                            <div class="form-group">
                                <label for="{RT_MOVEIN_TIME_ID}">Returning Move-in Time</label>
                                {RT_MOVEIN_TIME}
                            </div>

                            <div class="form-group">
                                <label for="{FLOOR_RLC_ID_ID}">Reserved for RLC</label>
                                {FLOOR_RLC_ID}
                            </div>

                            <div class="form-group">
                                <button class="btn btn-success pull-right">Save</button>
                            </div>
                        </div>
                    </div>
                    {END_FORM}
                </div>
                <!-- Images tab -->
                <div role="tabpanel" class="tab-pane" id="images-tab">
                    Floor plan: {FILE_MANAGER}
                    <div class="form-group">
                        <button class="btn btn-success pull-right">Save</button>
                    </div>
                </div>
                <!-- Roles Tab -->
                <div role="tabpanel" class="tab-pane" id="roles-tab">
                    <div id="roles">
                    {ROLE_EDITOR}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
