<div class="hms">
  <div class="box">
    <div class="title"> <h1>Residence Hall Application</h1><p>{RECEIVED_DATE}</p> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <font color="red">{ERROR_MSG}<br /></font>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <font color="green">{SUCCESS_MSG}<br /></font>
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
                <th colspan="2">Application Term(s)</th>
            </tr>
            <tr>
                <td><font color=red>{TERM_MSG}</font></td>
                <td></td>
            </tr>
            <tr>
                <td>{TERMS_0_LABEL}</td>
                <td>{TERMS_0}</td>
            </tr>
            <tr>
                <td>{TERMS_1_LABEL}</td>
                <td>{TERMS_1}</td>
            </tr>
            <tr>
                <td>{TERMS_2_LABEL}</td>
                <td>{TERMS_2}</td>
            </tr>
            <tr>
                <td>{TERMS_3_LABEL}</td>
                <td>{TERMS_3}</td>
            </tr>
            <tr>
                <th colspan="2">Demographic Information</th>
            <tr>
                <td>Name: </td><td align="left">{STUDENT_NAME}</td>
            </tr>
            <tr>
                <td>Gender: </td><td align="left">{GENDER}</td>
            </tr>
            <tr>
                <td>Student Status: </td><td align="left">{STUDENT_STATUS_LBL}</td>
            </tr>
            <tr>
                <td>Application Term: </td><td align="left">{ENTRY_TERM}</td>
            </tr>
            <tr>
                <td>Classification: </td><td align="left">{CLASSIFICATION_FOR_TERM_LBL}</td>
            </tr>
            <!-- BEGIN form -->
            <tr>
                <td>Cell Phone Number: </td><td align="left">({AREA_CODE})-{EXCHANGE}-{NUMBER}</td>
            </tr>
            <!-- END form -->
            <tr>
                <td></td>
                <td>{DO_NOT_CALL}<sub>Check here if you do not have or do not wish to provide your cellphone number.</sub></td>
            </tr>
            <!-- BEGIN review -->
            <tr>
                <td>Cell Phone Number: </td><td align="left">{CELLPHONE}</td>
            </tr>
            <!-- END review -->
            <tr>
                <th colspan="2">Meal Plan</th>
            </tr>
            <tr>
                <td>Meal Option: </td><td align="left">{MEAL_OPTION}</td>
            </tr>
            <tr>
                <th colspan="2">Preferences</th>
            </tr>
            <tr>
                <td>Lifestyle Option: </td><td align="left">{LIFESTYLE_OPTION}</td>
            </tr>
            <tr>
                <td>Preferred Bedtime: </td><td align="left">{PREFERRED_BEDTIME}</td>
            </tr>
            <tr>
                <td>Room Condition: </td><td align="left">{ROOM_CONDITION}</td>
            </tr>
            <tr>
                <th colspan="2">Special Needs Housing</th>
            </tr>
            <!-- BEGIN special_needs_text -->
            {SPECIAL_NEEDS_TEXT}
            <tr>
                <td colspan="2">The Department of Housing & Residence Life is committed to meeting the needs of all students to the best of its ability.<br /><br />
                
                Special needs housing requests will be reviewed individually with a commitment to providing housing that best meets the need of the student.  The Department of Housing & Residence Life takes these concerns very seriously and confidentiality will be maintained.<br /><br />
                
                Housing for special needs may be limited due to space availability.<br /><br />
                </td>
            </tr>
            <!-- END special_needs_text -->
            <tr>
                <td>Do you have any special needs?</td>
                <!-- BEGIN special_need -->
                <td>{SPECIAL_NEED}{SPECIAL_NEED_LABEL}</td>
                <!-- END special_need -->
                <!-- BEGIN special_needs_result -->
                <td>{SPECIAL_NEEDS_RESULT}</td>
                <!-- END special_needs_result -->
            </tr>
            <!-- BEGIN rlc_interest_1 -->
            <tr>
                <th colspan="2">Unique Housing Options</th>
            </tr>
            <tr>
                <td>Are you interested in a <a href="http://housing.appstate.edu/index.php?module=pagemaster&PAGE_user_op=view_page&PAGE_id=134" target="_blank">unique housing option</a>?</td>
                <td align="left">{RLC_INTEREST_1} {RLC_INTEREST_1_LABEL}&nbsp;{RLC_INTEREST_2} {RLC_INTEREST_2_LABEL}</td>
            </tr>
            <!-- END rlc_interest_1 -->
        </table>
        <br /><br />
        {SUBMIT}
        {REDO_BUTTON}
        {SUBMIT_APPLICATION}
        {END_FORM}
    </div>
  </div>
</div>
