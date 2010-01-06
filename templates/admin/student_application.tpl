<div class="hms">
  <div class="box">
    <div class="title"> <h1>{TERM} On-campus Housing Application</h1><p>{RECEIVED_DATE}</p> </div>
    <div class="box-content">
        
        <!-- BEGIN withdrawn -->
        <font color="red"><b>{WITHDRAWN}</b></font>
        <!-- END withdrawn -->
        <!-- BEGIN review_msg -->
        {REVIEW_MSG}
        Please review the information you entered. If you need to go back and make changes to your application click the 'modify application' button below. If the information you have entered is correct click the 'submit application' button.
        <!-- END review_msg -->
        {START_FORM}
        <table>
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
                
                Special needs housing requests will be reviewed individually with a commitment to providing housing that best meets the needs of the student.  The Department of Housing & Residence Life takes these concerns very seriously and confidentiality will be maintained. Housing for special needs may be limited due to space availability.<br /><br />
                </td>
            </tr>
            <!-- END special_needs_text -->
            <tr>
                <td>Do you have any special needs? <br/ >
                <!-- BEGIN special_need -->
                {SPECIAL_NEED}{SPECIAL_NEED_LABEL} <br />
                <!-- END special_need -->
                <!-- BEGIN special_needs_result -->
                {SPECIAL_NEEDS_RESULT} <br/ >
                <!-- END special_needs_result -->
                </td>
            </tr>
            <!-- BEGIN rlc_interest_1 -->
            <tr>
                <th colspan="2">Unique Housing Options</th>
            </tr>
            <tr>
                <td>Are you interested in a <a href="http://housing.appstate.edu/pagesmith/29" target="_blank">Unique Housing Option</a> where you will live together with a group of students who share at least one similar interest?  Students in these communities report finding it easier to meet people and make friends. They also earn higher GPAs in their first semester than students not living in a unique housing option.  For more information <a href="http://housing.appstate.edu/pagesmith/29">vist Unique Housing Options website</a>.</td>
                <td align="left">{RLC_INTEREST_1} {RLC_INTEREST_1_LABEL}&nbsp;{RLC_INTEREST_2} {RLC_INTEREST_2_LABEL}</td>
            </tr>
            <!-- END rlc_interest_1 -->
        </table>
        <br /><br />
        {SUBMIT}
        <!-- BEGIN redo_form -->
        or {REDO_BUTTON}
        <!-- END redo_form -->
        {SUBMIT_APPLICATION}
        {END_FORM}
    </div>
  </div>
</div>
