<div class="hms">
    <div class="title"> <h2>{TERM} On-campus Housing Application</h2></div>
    <p>{RECEIVED_DATE}</p>
    <!-- BEGIN waiting_list -->
    <p>Added to waiting list on: {WAITING_LIST_DATE}</p>
    <!-- END waiting_list -->

    <!-- BEGIN withdrawn -->
    <font color="red"><b>{WITHDRAWN}</b></font>
    <!-- END withdrawn -->
    <!-- BEGIN review_msg -->
    {REVIEW_MSG}
    Please review the information you entered. If you need to go back and make changes to your application click the 'modify application' button below. If the information you have entered is correct click the 'submit application' button.
    <!-- END review_msg -->
    {START_FORM}
    <table class="table table-striped">
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
            <td>Starting in: </td><td align="left">{ENTRY_TERM}</td>
        </tr>
        <tr>
            <td>Classification: </td><td align="left">{CLASSIFICATION_FOR_TERM_LBL}</td>
        </tr>
        <!-- BEGIN form -->
        <tr>
            <td>Your Cell Phone Number: </td><td align="left">({AREA_CODE})-{EXCHANGE}-{NUMBER}</td>
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
        <!-- BEGIN room_type -->
        <tr>
            <td>Room Type: </td><td align="left">{ROOM_TYPE}</td>
        </tr>
        <!-- END room_type -->

        <tr>
            <th colspan="2">Emergency Contact Information</th>
        <tr>
        <tr>
            <td>Emergency Contact Person Name:</td>
            <td>{EMERGENCY_CONTACT_NAME}</td>
        </tr>
        <tr>
            <td>Relationship:</td>
            <td>{EMERGENCY_CONTACT_RELATIONSHIP}</td>
        </tr>
        <tr>
            <td>Phone Number:</td>
            <td>{EMERGENCY_CONTACT_PHONE}</td>
        </tr>
        <tr>
            <td>Email:</td>
            <td>{EMERGENCY_CONTACT_EMAIL}</td>
        </tr>
        <tr>
            <td colspan="2">Are there any medical conditions you have which our staff should be aware of? (This information will be kept confidential and will only be shared with the staff in your residence hall. However, this information <strong>may</strong> be disclosed to medical/emergency personnel in case of an emergency.)</td>
        </tr>
        <tr>
            <td colspan="2">{EMERGENCY_MEDICAL_CONDITION}</td>
        <tr>
            <!-- BEGIN missing_person -->
            <th colspan="2">Missing Person Information</th>
        </tr>
        <tr>
            <td colspan="2">Federal law requires that we ask you to confidentially identify a person whom the University should contact if you are reported missing for more than 24 hours. Please list your contact person's information below:</td>
        </tr>
        <tr>
            <td>Contact Person Name:</td>
            <td>{MISSING_PERSON_NAME}</td>
        </tr>
        <tr>
            <td>Relationship:</td>
            <td>{MISSING_PERSON_RELATIONSHIP}</td>
        </tr>
        <tr>
            <td>Phone Number:</td>
            <td>{MISSING_PERSON_PHONE}</td>
        </tr>
        <tr>
            <td>Email:</td>
            <td>{MISSING_PERSON_EMAIL}</td>
        </tr>
        <!-- END missing_person -->
        <tr>
            <th colspan="2">Special Needs Housing</th>
        </tr>
        <!-- BEGIN special_needs_text -->
        {SPECIAL_NEEDS_TEXT}
        <tr>
            <td colspan="2">University Housing is committed to meeting the needs of all students to the best of its ability.<br /><br />

                Special needs housing requests will be reviewed individually with a commitment to providing housing that best meets the needs of the student.  University Housing takes these concerns very seriously and confidentiality will be maintained. Housing for special needs may be limited due to space availability.<br /><br />
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
            <th colspan="2">Residential Learning Communities</th>
        </tr>
        <tr>
            <td>Are you interested in a <a href="http://housing.appstate.edu/rlc" target="_blank">Residential Learning Community</a> where you will live together with a group of students who share at least one similar interest?  Students in these communities report finding it easier to meet people and make friends. They also earn higher GPAs in their first semester than students not living in a Residential Learning Community.  For more information <a href="http://housing.appstate.edu/rlc">vist Residential Learning Communities website</a>.</td>
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
