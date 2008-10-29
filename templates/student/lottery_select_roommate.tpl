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

<div class="hms">
  <div class="box">
    <div class="title"> <h1>{ROOM} - Select Your Roommates</h1> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->

        <p>
        Your Appalachian State AppalNet user name has been entered in the first free bed in the room below. The availability of your preferred roommates is also indicated. You may invite eligible roommates for each additional vacant bed by selecting "invite roommate" link or entering their Appalachian State AppalNet user name.
        </p>

        Your preferred roommates:<br />
        <table>
            <tr>
                <th>Name</th>
                <th>Status</th>
            </tr>
            <!-- BEGIN no_roommates -->
            {NO_ROOMMATES}
            <tr>
                <td colspan="2">You did not specify any preferred roommates.</td>
            </tr>
            <!-- END no_roommates -->
            <!-- BEGIN roommate_status -->
            <tr>
                <td>{NAME}</td>
                <td>{STATUS}</td>
            </tr>
            <!-- END roommate_status -->
        </table>
        <br />

        The beds in {ROOM}:<br />
        {START_FORM}
        <table>
            <tr>
                <th>Bedroom</th>
                <th>Bed</th>
                <th>Roommate</th>
            </tr>
            <!-- BEGIN beds -->
            <tr>
                <td>{BEDROOM_LETTER}</td>
                <td>{BED_LETTER}</td>
                <td>{TEXT}</td>
            </tr>
            <!-- END beds -->
        </table>
        <br />
        <p>Please choose a meal plan. <b>Note: </b>Most residence halls require you to choose a meal plan. If your chosen residence hall does not require a meal plan, then a 'None' option will be available in drop down box below.</p>
        <p>Meal plan: {MEAL_PLAN}</p>
        {SUBMIT_FORM}
        {END_FORM}
    </div>
  </div>
</div>
