<div class="hms">
  <div class="box">
    <div class="title"> <h1>Confirm Room & Roommates</h1> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->

        <p>Please confirm your room and roommate choices below.</p>

        <p>You will be assigned to:<br />
            <blockquote>
            <b>{ROOM}</b>
            </blockquote>
        </p>
        
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
        <p>Meal plan: {MEAL_PLAN}</p>
        {START_FORM}
        <p>To confirm your room and roommate selections please type the words shown in the image below in the text field provided. (If you cannot read the words, click the refresh button under the image to get new words.)</p>
        {CAPTCHA_IMAGE}<br /><br />
        {SUBMIT_FORM}
        {END_FORM}
    </div>
  </div>
</div>
