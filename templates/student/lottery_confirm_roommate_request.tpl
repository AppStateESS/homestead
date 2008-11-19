<div class="hms">
  <div class="box">
    <div class="header"> <h1>Confirm Roommate Request</h1> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->
 
        <p>
            To confirm that you would like to be roommates with <b>{REQUESTOR}</b> and the other possible roommates listed below in <b>{HALL_ROOM}</b> please type the words shown below in the text box provided and click the confirm button. Please be aware that empty beds will be made available for other students.
        </p>
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
        {START_FORM}
        <p>Meal plan: {MEAL_PLAN}</p>
        <p>{CAPTCHA}</p>
        {CONFIRM}
        {END_FORM}
    </div>
  </div>
</div>
