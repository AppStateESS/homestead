<h2>{ROOM} - Select Your Roommates</h2>
<div class="row">
    <div class="col-md-9">
        <p>
            Your AppalNet user name has been entered in the first free bed in the room below. You may invite eligible roommates for each additional vacant bed by entering their Appalachian State AppalNet user name.
        </p>
    </div>
</div>

{START_FORM}
<div class="row">
    <div class="col-md-9">
        <div class="row">
            <div class="col-md-2">
                <strong>Beds</strong>
            </div>
            <div class="col-md-3">
                <strong>Roommates</strong>
            </div>
        </div>

        <!-- BEGIN beds -->
        <div class="row" style="margin-top:1em;">
            <div class="col-md-2">
                {BED_LABEL}:
            </div>
            <div class="col-md-5">
                <div class="input-group">
                    {TEXT}
                    <div class="input-group-addon">@appstate.edu</div>
                </div>
            </div>
        </div>
        <!-- END beds -->
    </div>
</div>

<div class="row">
    <div class="col-md-9">
        <p style="margin-top:2em;">
            Please choose a meal plan. <b>Note: </b>Most residence halls require you
            to choose a meal plan. If your chosen residence hall does not require a
            meal plan, then a 'None' option will be available in drop down box below.
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Meal plan:</label>
            {MEAL_PLAN}
        </div>
    </div>
</div>


<div class="row" style="margin-top:2em;">
    <div class="col-md-12">
        <button type="submit" class="btn btn-lg btn-success">
            Review Roommate &amp; Room Selection
        </button>
    </div>
</div>


{END_FORM}

<script type="text/javascript">
//<![CDATA[

function choose_roommate(roommate_username)
{
    var boxes = $('.roommate_entry');

    for(var i = 0; i < boxes.length; i++){
        if(boxes[i].value == ""){
            boxes[i].value = roommate_username;
            break;
        }
    }
}

//]]>
</script>
