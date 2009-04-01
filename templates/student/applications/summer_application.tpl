<div class="hms">
  <div class="box">
    <div class="title"> <h1>{TERM} Residence Hall Application</h1><p>{RECEIVED_DATE}</p> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->


        <!-- BEGIN withdrawn -->
        <font color="red"><b>{WITHDRAWN}</b></font>
        <!-- END withdrawn -->

        <!-- BEGIN review_msg -->
        {REVIEW_MSG}
        Please review the information you entered. If you need to go back and make changes to your application click the 'modify application' button below. If the information you have entered is correct click the 'submit application' button.
        <!-- END review_msg -->

        <!-- BEGIN menu_link -->
        {MENU_LINK}
        <!-- END menu_link -->

        {START_FORM}
        <table>
            <tr>
                <th colspan="2">Demographic Information</th>
            <tr>
                <td>Name: </td><td align="left">{NAME}</td>
            </tr>
            <tr>
                <td>Gender: </td><td align="left">{GENDER}</td>
            </tr>
            <tr>
                <td>Status: </td><td align="left">{TYPE} student</td>
            </tr>
            <tr>
                <td>Class:  </td><td align="left">{CLASS}</td>
            </tr>

            <!-- BEGIN form -->
            <tr>
                <td>Cell Phone Number: </td><td align="left">({AREA_CODE})-{EXCHANGE}-{NUMBER}</td>
            </tr>
            <tr>
                <td></td>
                <td>{DO_NOT_CALL}<sub>Check here if you do not have or do not wish to provide your cellphone number.</sub></td>
            </tr>
            <!-- END form -->
            <!-- BEGIN review -->
            <tr>
                <td>Cell Phone Number: </td><td align="left">{CELLPHONE}</td>
            </tr>
            <!-- END review -->

            <tr>
                <th colspan="2">Meal Plan</th>
            </tr>
            <tr>
                <td>Meal Option: </td><td align="left">Summer Meal Plan ($325.00 - Required)</td>
            </tr>
            <tr>
                <th colspan="2">Preferences</th>
            </tr>
            <tr>
                <td>Room Type: </td><td align="left">{ROOM_TYPE}</td>
            </tr>
            <tr>
                <th colspan="2">Special Needs Housing</th>
            </tr>
            <!-- BEGIN special_needs_text -->
            {SPECIAL_NEEDS_TEXT}
            <tr>
                <td colspan="2">The Department of Housing & Residence Life is committed to meeting the needs of all students to the best of its ability.<br /><br />
                
                Special needs housing requests will be reviewed individually with a commitment to providing housing that best meets the need of the student.  The Department of Housing & Residence Life takes these concerns very seriously and confidentiality will be maintained. Housing for special needs may be limited due to space availability.<br /><br />
                </td>
            </tr>
            <!-- END special_needs_text -->
            <tr>
                <td>Do you have any special needs?</td>
                <td>
                <!-- BEGIN special_need -->
                {SPECIAL_NEED}{SPECIAL_NEED_LABEL} <br />
                <!-- END special_need -->
                <!-- BEGIN special_needs_result -->
                {SPECIAL_NEEDS_RESULT} <br/ >
                <!-- END special_needs_result -->
                </td>
            </tr>
            <!-- BEGIN first_pass -->
            <tr>
                <th colspan="2">Submit & Continue</th>
            </tr>
            <tr>
                <td colspan="2">Click the button below to continue. You will be given the chance to confirm your information before your application is saved.
                <br /><br />
                {CONTINUE}
                </td>
            </tr>
            <!-- END first_pass -->
            <!-- BEGIN confirmation -->
            <tr>
                <th colspan="2">Submit Application</th>
            </tr>
            <tr>
                <td colspan="2">
                After confirming your information listed above, click the 'Submit' button below to confirm your application and submit it for processing.
                <br /><br />
                {SUBMIT}
                </td>
            </tr>
            <!-- END confirmation -->
        </table>
    </div>
  </div>
</div>
