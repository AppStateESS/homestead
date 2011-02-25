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
        <p>
        Your Appalachian State AppalNet user name has been entered in the first free bed in the room below. You may invite eligible roommates for each additional vacant bed by entering their Appalachian State AppalNet user name.
        </p>

        <br />

        The beds in {ROOM}:<br />
        {START_FORM}
        <table>
            <tr>
                <th>Bedroom</th>
                <th>Roommate</th>
            </tr>
            <!-- BEGIN beds -->
            <tr>
                <td>{BEDROOM_LETTER}</td>
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
