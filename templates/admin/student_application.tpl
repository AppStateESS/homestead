<h2>{TERM} On-campus Housing Application</h2>
<p class="mark">{RECEIVED_DATE}</p>
<!-- BEGIN waiting_list -->
<p>Added to waiting list on: {WAITING_LIST_DATE}</p>
<!-- END waiting_list -->

<!-- BEGIN withdrawn -->
<div class="alert alert-danger"><strong>{WITHDRAWN}</strong></div>
<!-- END withdrawn -->
<!-- BEGIN review_msg -->
{REVIEW_MSG}
<p>Please review the information you entered. If you need to go back and make changes to your application click the 'modify application' button below. If the information you have entered is correct click the 'submit application' button.</p>
<!-- END review_msg -->
{START_FORM}

<h3>Demographic Information</h3>
<table class="table table-striped">
    <tr>
        <th class="col-xs-4">Name:</th><td align="left">{STUDENT_NAME}</td>
    </tr>
    <tr>
        <th class="col-xs-4">Gender:</th><td align="left">{GENDER}</td>
    </tr>
    <tr>
        <th class="col-xs-4">Student Status:</th><td align="left">{STUDENT_STATUS_LBL}</td>
    </tr>
    <tr>
        <th class="col-xs-4">Starting in:</th><td align="left">{ENTRY_TERM}</td>
    </tr>
    <tr>
        <th class="col-xs-4">Classification:</th><td align="left">{CLASSIFICATION_FOR_TERM_LBL}</td>
    </tr>
    <!-- BEGIN form -->
    <tr>
        <th class="col-xs-4">Your Cell Phone Number:</th><td align="left">({AREA_CODE})-{EXCHANGE}-{NUMBER}</td>
    </tr>
    <tr>
        <td></td>
        <td>{DO_NOT_CALL}<sub>Check here if you do not have or do not wish to provide your cellphone number.</sub></td>
    </tr>
    <!-- END form -->
    <!-- BEGIN review -->
    <tr>
        <th class="col-xs-4">Cell Phone Number:</th><td align="left">{CELLPHONE}</td>
    </tr>
    <!-- END review -->
</table>

<h3>Meal Plan</h3>
<table class="table table-striped">
    <th class="col-xs-4">Meal Option:</th><td align="left">{MEAL_OPTION}</td>
</table>

<h3>Preferences</h3>
<table class="table table-striped">
    <tr>
        <th class="col-xs-4">Lifestyle Option:</th><td align="left">{LIFESTYLE_OPTION}</td>
    </tr>
    <tr>
        <th class="col-xs-4">Preferred Bedtime:</th><td align="left">{PREFERRED_BEDTIME}</td>
    </tr>
    <tr>
        <th class="col-xs-4">Room Condition:</th><td align="left">{ROOM_CONDITION}</td>
    </tr>
    <!-- BEGIN room_type -->
    <tr>
        <th class="col-xs-4">Room Type:</th><td align="left">{ROOM_TYPE}</td>
    </tr>
    <!-- END room_type -->
</table>

<h3>Emergency Contact Information</h3>
<table class="table table-striped">
    <tr>
        <th class="col-xs-4">Emergency Contact Person Name:</th><td>{EMERGENCY_CONTACT_NAME}</td>
    </tr>
    <tr>
        <th class="col-xs-4">Relationship:</th><td>{EMERGENCY_CONTACT_RELATIONSHIP}</td>
    </tr>
    <tr>
        <th class="col-xs-4">Phone Number:</th><td>{EMERGENCY_CONTACT_PHONE}</td>
    </tr>
    <tr>
        <th class="col-xs-4">Email:</th><td>{EMERGENCY_CONTACT_EMAIL}</td>
    </tr>
</table>
<p class="well">Are there any medical conditions you have which our staff should be aware of? (This information will be kept confidential and will only be shared with the staff in your residence hall. However, this information <strong>may</strong> be disclosed to medical/emergency personnel in case of an emergency.)</p>
<p>{EMERGENCY_MEDICAL_CONDITION}</p>

<!-- BEGIN missing_person -->
<h3>Missing Person Information</h3>
<p>Federal law requires that we ask you to confidentially identify a person whom the University should contact if you are reported missing for more than 24 hours. Please list your contact person's information below:</p>
<table class="table table-striped">
    <tr>
        <th class="col-xs-4">Contact Person Name:</th><td>{MISSING_PERSON_NAME}</td>
    </tr>
    <tr>
        <th class="col-xs-4">Relationship:</th><td>{MISSING_PERSON_RELATIONSHIP}</td>
    </tr>
    <tr>
        <th class="col-xs-4">Phone Number:</th><td>{MISSING_PERSON_PHONE}</td>
    </tr>
    <tr>
        <th class="col-xs-4">Email:</th><td>{MISSING_PERSON_EMAIL}</td>
    </tr>
</table>
<!-- END missing_person -->

<!-- BEGIN special_needs_text -->
<h3>Special Needs Housing</h3>
<p>{SPECIAL_NEEDS_TEXT}</p>
<p>University Housing is committed to meeting the needs of all students to the best of its ability.</p>
<p>Special needs housing requests will be reviewed individually with a commitment to providing housing that best meets the needs of the student.  University Housing takes these concerns very seriously and confidentiality will be maintained. Housing for special needs may be limited due to space availability.</p>
<!-- END special_needs_text -->

<h4>Do you have any special needs?</h4>
<!-- BEGIN special_need -->
<p>{SPECIAL_NEED}{SPECIAL_NEED_LABEL}</p>
<!-- END special_need -->

<!-- BEGIN special_needs_result -->
<p>{SPECIAL_NEEDS_RESULT}</p>
<!-- END special_needs_result -->

<!-- BEGIN rlc_interest_1 -->
<h3>Residential Learning Communities</h3>
    <p>Are you interested in a <a href="http://housing.appstate.edu/rlc" target="_blank">Residential Learning Community</a> where you will live together with a group of students who share at least one similar interest?  Students in these communities report finding it easier to meet people and make friends. They also earn higher GPAs in their first semester than students not living in a Residential Learning Community.  For more information <a href="http://housing.appstate.edu/rlc">vist Residential Learning Communities website</a>.</p>
    <p>{RLC_INTEREST_1} {RLC_INTEREST_1_LABEL}&nbsp;{RLC_INTEREST_2} {RLC_INTEREST_2_LABEL}</p>
<!-- END rlc_interest_1 -->
{SUBMIT}
<!-- BEGIN redo_form -->
or {REDO_BUTTON}
<!-- END redo_form -->
{SUBMIT_APPLICATION}
{END_FORM}