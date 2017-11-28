<div class="row">
  <div class="col-md-8">
      <div class="col-md-10 col-xs-12 pull-left hall-banner">
          <img src={EXTERIOR_IMG_PIC} width="100%" height="200px"></img>
          <h1><span>{TITLE} <small>{TERM}</small></span></h1>
      </div>
    {START_FORM}

    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN offline -->
            {OFFLINE}
            <h3><span class="label label-danger">Offline</span></h3>
            <!-- END offline -->
        </div>
    </div>

    <div class="row" style="margin-top:2em;">
        <div class="col-md-2 text-center">
            Floors<p class="lead">{NUMBER_OF_FLOORS}</p>
        </div>
        <div class="col-md-2 text-center">
            Rooms<p class="lead">{NUMBER_OF_ROOMS}</p>
        </div>
        <div class="col-md-2 text-center">
            Beds<p class="lead">{NUMBER_OF_BEDS}</p>
        </div>
        <div class="col-md-2 text-center">
            Nominal beds<p class="lead">{NUMBER_OF_BEDS_ONLINE}</p>
        </div>
        <div class="col-md-2 text-center">
            Residents<p class="lead">{NUMBER_OF_ASSIGNEES}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10">
            <div role="tabpanel">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" style="margin-bottom: 15px;" role="tablist">
                    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Floors</a></li>
                    <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Settings</a></li>
                    <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">Images</a></li>
                    <li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Roles</a></li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="home">
                        {FLOOR_PAGER}
                    </div>
                    <div role="tabpanel" class="tab-pane" id="profile">
                        {START_FORM}
                        <input type="hidden" name="tab" value="settings">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="{HALL_NAME_ID}">Name</label>
                                    {HALL_NAME}
                                </div>
                                <div class="form-group">
                                    <label for="{GENDER_TYPE_ID}">Gender</label>
                                    {GENDER_TYPE}
                                </div>

                                <div class="checkbox">
                                    <label>
                                        {IS_ONLINE} Is online
                                    </label>
                                </div>

                                <div class="checkbox">
                                    <label>
                                        {AIR_CONDITIONED} Air Conditioned
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        {MEAL_PLAN_REQUIRED} Meal plan required
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        {ASSIGNMENT_NOTIFICATIONS} Assignment Notifications
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label for="{PACKAGE_DESK_ID}">
                                        Package Desk
                                    </label>
                                    {PACKAGE_DESK}
                                </div>

                                <button type="submit" class="btn btn-success pull-right">Save</button>
                            </div>
                        </div>
                        {END_FORM}
                    </div>
                    <div role="tabpanel" class="tab-pane" id="messages">
                        {START_FORM}
                        <input type="hidden" name="tab" value="images">
                        Exterior image: {EXTERIOR_IMG}
                        Other image: {OTHER_IMG}
                        Map image: {MAP_IMG}
                        Room plan image: {ROOM_PLAN_IMG}

                        <button type="submit" class="btn btn-success">Save</button>
                        {END_FORM}
                    </div>
                    <div role="tabpanel" class="tab-pane" id="settings">
                        {ROLE_EDITOR}
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>
