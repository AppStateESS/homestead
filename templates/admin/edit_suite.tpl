<div class="hms">
  <div class="box">
    <div class="{TITLE_CLASS}"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->
        <h2>Suite Properties</h2>
        {START_FORM}
        <table>
            <tr>
                <th>Hall Name:</th><td align="left">{HALL_NAME}</td>
            </tr>
            <tr>
                <th>Floor:</th><td align="left">{FLOOR_NUMBER}</td>
            </tr>
            <tr>
                <th>Gender: </th>
                <!-- BEGIN gender_message -->
                <td>{GENDER_MESSAGE}</td>
                <td>{GENDER_REASON}</td>
                <!-- END gender_message -->
                <!-- BEGIN gender_radio_buttons -->
                <td align="left">{GENDER_TYPE}</td>
                <!-- END gender_radio_button -->
            </tr>
        </table>
        {SUBMIT}
        <br /><br /><br />
        {ROOM_PAGER}
        <br /><br />
        {ASSIGNMENT_PAGER}
    </div>
  </div>
</div>
{END_FORM}
