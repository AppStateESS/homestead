<script type="text/javascript">
//<![CDATA[

$().ready(function(){
    // Bind the onChange event
    $('#phpws_form_rooms_for_lottery').bind('change', function(){
        calculate_beds();
    });
    // calculate the initial value
    calculate_beds();
});

function calculate_beds()
{
$('#beds_for_lottery').text($('#phpws_form_beds_per_room').attr('value') * $('#phpws_form_rooms_for_lottery').attr('value') + " beds");
}

//]]>
</script>


<div class="hms">
  <div class="box">
    <div class="{TITLE_CLASS}"><h1>{TITLE}</h1></div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->
        <h2>Hall Properties</h2>
        {START_FORM}
        <table>
            <tr>
                <th>Hall name: </th><td align="left">{HALL_NAME}</td>
            </tr>
            <tr>
                <th>Number of floors: </th><td align="left">{NUMBER_OF_FLOORS}</td>
            </tr>
            <tr>
                <th>Number of rooms: </th><td align="left">{NUMBER_OF_ROOMS}</td>
            </tr>
            <tr>
                <th>Number of beds: </th><td align="left">{NUMBER_OF_BEDS}</td>
            </tr>
            <tr>
                <th>Number of online/non-overflow beds: </th><td align="left">{NUMBER_OF_BEDS_ONLINE}</td>
            </tr>
            <tr>
                <th>Number of assignees: </th><td align="left">{NUMBER_OF_ASSIGNEES}</td>
            </tr>
            <tr>
                <th>Gender: </th><td align="left">{GENDER_TYPE}</td>
            </tr>
            <tr>
                <th>Rooms for lottery: </th><td align="left">{ROOMS_FOR_LOTTERY} <span id="beds_for_lottery"></span></td>
            </tr>
            <tr>
                <th>Is online: </th><td align="left">{IS_ONLINE}</td>
            </tr>
            <tr>
                <th>Air Conditioned: </th><td align="left">{AIR_CONDITIONED}</td>
            </tr>
            <tr>
                <th>Meal plan required: </th><td align="left">{MEAL_PLAN_REQUIRED}</td>
            </tr>
            <tr>
                <th>Assignment Notifications: </th><td align="left">{ASSIGNMENT_NOTIFICATIONS}</td>
            </tr>
            <tr>
                <th>Exterior image: </th><td align"left">{EXTERIOR_IMG}</td>
            </tr>
            <tr>
                <th>Other image: </th><td align"left">{OTHER_IMG}</td>
            </tr>
            <tr>
                <th>Map image: </th><td align"left">{MAP_IMG}</td>
            </tr>
            <tr>
                <th>Room plan image: </th><td align"left">{ROOM_PLAN_IMG}</td>
            </tr>
        </table>
        {SUBMIT}
        {END_FORM}
        <br /><br />
        {FLOOR_PAGER}
    </div>
  </div>
</div>
