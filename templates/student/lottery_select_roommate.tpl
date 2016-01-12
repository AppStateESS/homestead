<h2>{ROOM} - Select Your Roommates</h2>
<div class="col-md-9">
    <p>
      Your Appalachian State AppalNet user name has been entered in the first free bed in the room below. You may invite eligible roommates for each additional vacant bed by entering their Appalachian State AppalNet user name.
    </p>

    <p>
      The beds in {ROOM}:
    </p>

    {START_FORM}
    <div class="row">
      <label class="col-md-2">
        Beds
      </label>
      <label class="col-md-3 col-md-offset-2">
        Roommates
      </label>
    </div>

    <!-- BEGIN beds -->
    <div class="row">
      <p class="col-md-12">
        <div class="col-md-1">
          {BED_LABEL}:
        </div>
        <div class="col-md-5 col-md-offset-2">
          <div class="input-group">
            {TEXT}
          <div class="input-group-addon">@appstate.edu</div>
          </div>
        </div>
      </p>
    </div>
    <!-- END beds -->

    <p></p>

    <p>
      Please choose a meal plan. <b>Note: </b>Most residence halls require you
      to choose a meal plan. If your chosen residence hall does not require a
      meal plan, then a 'None' option will be available in drop down box below.
    </p>

    <div class="row">
      <label class="col-md-3">
        Meal plan:
      </label>
      <div class="col-md-4">
        {MEAL_PLAN}
      </div>
    </div>

    <p></p>

    <div class="row">
      <div class="col-md-12">
        <button type="submit" class="btn btn-lg btn-success">
          Review Roommate & Room Selection
        </button>
      </div>
    </div>


    {END_FORM}
</div>

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
