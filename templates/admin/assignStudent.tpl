{START_FORM}

<h1>Assign Student <small>{TERM}</small></h1>

<div class="row">
    <div class="col-md-4">

        <label for="{USERNAME_ID}">ASU Email:</label>
        <div class="input-group">
            {USERNAME}
            <span class="input-group-addon" id="basic-addon2">@appstate.edu</span>
        </div>

        <div class="form-group">
            {RESIDENCE_HALL_LABEL}
            {RESIDENCE_HALL}
        </div>

        <div class="form-group">
            {FLOOR_LABEL}
            {FLOOR}
        </div>

        <div class="form-group">
            {ROOM_LABEL}
            {ROOM}
        </div>

        <div class="form-group">
            {BED_LABEL}
            {BED}
        </div>

        <div>
            <p class="help-block">
                <a href="javascript:showBedDrop();">Show bed</a>
            </p>
        </div>

        <div class="form-group">
            {MEAL_PLAN_LABEL}
            {MEAL_PLAN}
        </div>

        <div class="form-group">
            {ASSIGNMENT_TYPE_LABEL}
            {ASSIGNMENT_TYPE}
        </div>

        <div class="form-group">
            <label for="">Note: </label>
            {NOTE}
        </div>

        <button type="submit" class="btn btn-success">Assign</button>

    </div>
</div>

{END_FORM}
