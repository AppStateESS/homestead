<h1>{TERM} On-campus Housing Application</h1>

<p>{RECEIVED_DATE}</p>

<!-- BEGIN withdrawn -->
<div class="alert alert-danger">{WITHDRAWN}</div>
<!-- END withdrawn -->

<!-- BEGIN review_msg -->
{REVIEW_MSG}
Please review the information you entered. If you need to go back and make changes to your application click the 'modify application' button below. If the information you have entered is correct click the 'submit application' button.
<!-- END review_msg -->

{START_FORM}

<h3>Demographic Information</h3>
<table class="table table-striped">
    <tr>
        <th class="col-sm-5">Name:</th><td>{STUDENT_NAME}</td>
    </tr>
    <tr>
        <th>Gender:</th><td>{GENDER}</td>
    </tr>
    <tr>
        <th>Student Status:</th><td>{STUDENT_STATUS_LBL}</td>
    </tr>
    <tr>
        <th>Application Term:</th><td>{ENTRY_TERM}</td>
    </tr>
    <tr>
        <th>Classification:</th><td>{CLASSIFICATION_FOR_TERM_LBL}</td>
    </tr>
    <!-- BEGIN form -->
    <tr>
        <th>Cell Phone Number:</th><td><div class="form-inline">({AREA_CODE})-{EXCHANGE}-{NUMBER}</div> {DO_NOT_CALL} <label style="display:inline" class="small" for="phpws_form_do_not_call">Check here if you do not have or do not wish to provide your cellphone number.</label></td>
    </tr>
    <!-- END form -->
    <!-- BEGIN review -->
    <tr>
        <td>Cell Phone Number: </td>
        <td>{CELLPHONE}</td>
    </tr>
    <!-- END review -->
</table>
<!-- BEGIN meal_plan -->
<h3>Meal Plan</h3>
<table class="table table-striped">
    <tr>
        <th class="col-sm-5"><label for="phpws_form_meal_option">Meal Option:</label></th><td>{MEAL_OPTION}</td>
    </tr>
</table>
<!-- END meal_plan -->
<!-- BEGIN preferences -->
<h3>Preferences</h3>
<table class="table table-striped">
    <tr>
        <th class="col-sm-5"><label for="phpws_form_lifestyle_option">Lifestyle Option:</label></th><td>{LIFESTYLE_OPTION}</td>
    </tr>
    <tr>
        <th><label for="phpws_form_preferred_bedtime">Preferred Bedtime:</label></th><td>{PREFERRED_BEDTIME}</td>
    </tr>
    <tr>
        <th><label for="phpws_form_room_condition">Room Condition:</label></th><td>{ROOM_CONDITION}</td>
    </tr>
    <tr>
        <th><label for="phpws_form_room_condition">Smoking Preference:</label></th><td>{SMOKING_PREFERENCE}</td>
    </tr>
</table>
<!-- END preferences -->
<!-- BEGIN room_type -->
<h3>Room Type</h3>
<table class="table table-striped">
    <tr>
        <td class="col-sm-5">Preferred Room Type: </td><td>{ROOM_TYPE}</td>
    </tr>
</table>
<!-- END room_type -->

<h3>Emergency Contact Information</h3>
<table class="table table-striped">
    <tr>
        <th class="col-sm-5"><label for="phpws_form_emergency_contact_name">Emergency Contact Person Name</label><span class="required">*</span>:</th>
        <td>{EMERGENCY_CONTACT_NAME}</td>
    </tr>
    <tr>
        <th><label for="phpws_form_emergency_contact_relationship">Relationship</label><span class="required">*</span>:</th>
        <td>{EMERGENCY_CONTACT_RELATIONSHIP}</td>
    </tr>
    <tr>
        <th><label for="phpws_form_emergency_contact_phone">Phone Number</label><span class="required">*</span>:</th>
        <td>{EMERGENCY_CONTACT_PHONE}</td>
    </tr>
    <tr>
        <th><label for="phpws_form_emergency_contact_email">Email</label><span class="required">*</span>:</th>
        <td>{EMERGENCY_CONTACT_EMAIL}</td>
    </tr>
    <tr>
        <td colspan="2"><p>Are there any <em>emergency</em> medical conditions which our staff should be aware of?</p>
        <span class="help-block">
            In the event of a <em>medical emergency</em> within the residence halls we may disclose this information to <em>emergency personnel</em>. For example, severe <em>life-threatening</em> allergies to medications or foods. This information will be kept confidential and only shared on a need-to-know basis.
        </span>
            {EMERGENCY_MEDICAL_CONDITION}
        </td>
    </tr>
</table>

<h3>Missing Person Information</h3>
<table class="table table-striped">
    <tr>
        <td colspan="2">Federal law requires that we ask you to confidentially identify a person whom the University should contact if you are reported missing for more than 24 hours. Please list your contact person's information below:</td>
    </tr>
    <tr>
        <td class="col-sm-5">Contact Person Name<span class="required">*</span>:</td>
        <td>{MISSING_PERSON_NAME}</td>
    </tr>
    <tr>
        <td>Relationship<span class="required">*</span>:</td>
        <td>{MISSING_PERSON_RELATIONSHIP}</td>
    </tr>
    <tr>
        <td>Phone Number<span class="required">*</span>:</td>
        <td>{MISSING_PERSON_PHONE}</td>
    </tr>
    <tr>
        <td>Email<span class="required">*</span>:</td>
        <td>{MISSING_PERSON_EMAIL}</td>
    </tr>
</table>

<h3>Housing Accommodations</h3>

<p>
    University Housing is committed to meeting the individual needs of all students to the best of our ability. Housing requests due to disabilities and gender related needs are taken seriously, thoroughly considered, and kept confidential.
</p>
<p>
    Students who need housing accommodations due to the impact of a disability (physical, medical, etc.) should contact the <a href="https://ods.appstate.edu/" target="_blank">Office of Disability Services</a>.
    Students who need housing accommodations due to gender related needs should contact the <a href="http://multicultural.appstate.edu/" target="_blank">Office of Multicultural Student Development</a>.
</p>

<table class="table table-striped">
    <!-- BEGIN rlc_interest_1 -->
    <tr>
        <th colspan="2">Residential Learning Communities</th>
    </tr>
    <tr>
        <td>
            <p>Are you interested in living in a <a href="http://housing.appstate.edu/rlc" target="_blank">Residential Learning Community</a> (RLC)?</p>
        </td>
        <td>{RLC_INTEREST_1} {RLC_INTEREST_1_LABEL}&nbsp;{RLC_INTEREST_2} {RLC_INTEREST_2_LABEL}</td>
    </tr>
    <tr>
        <td colspan="2">
            <p>RLCs afford students a unique opportunity for an academic learning experience outside of the classroom.  Students participating in a learning community live together on the same floor of a residence hall and are often required to enroll in one or more linked courses which emphasize the theme of each specific community.  In addition, research shows that students who participate in a residential learning community have a higher GPA and enjoy a better college experience.</p>
            <p>Appalachian State University was ranked as a 2010 Best College for Learning Communities according to U.S. News &amp; World Report. We offer 17 options for students to choose from, including those focused on particular majors, and others with a focus on a particular student interest.  One of the best ways to develop strong friendships and succeed in college is to join a residential learning community. </p>
            <p>For more information visit the <a href="http://housing.appstate.edu/rlc" target="_blank">Residential Learning Communities website.</a></p>
        </td>
    </tr>
    <!-- END rlc_interest_1 -->
    <!-- BEGIN rlc_submitted -->
    {RLC_SUBMITTED}
    <tr>
        <th colspan="3">Residential Learning Communities</th>
    </tr>
    <tr>
        <td>
            <p>Are you interested in living in a <a href="http://housing.appstate.edu/rlc" target="_blank">Residential Learning Community</a> (RLC)?</p>
            <p>You have already submitted a separate Learning Community Application. Use the options on the main menu to view or edit your Learning Community Application.</p>
        </td>
    </tr>
    <!-- END rlc_submitted -->
    <!-- BEGIN rlc_review -->
    <tr>
        <th colspan="2">Residential Learning Communities</th>
    </tr>
    <tr>
        <td>
            <p>Are you interested in living in a Residential Learning Community (RLC)?</p>
        </td>
        <td>
            <p>{RLC_REVIEW}</p>
        </td>
    </tr>
    <!-- END rlc_review -->
</table>
            <button class="btn btn-lg btn-primary">Continue <i class="fa fa-arrow-right"></i></button>
<!-- BEGIN redo_form -->
or {REDO_BUTTON}
<!-- END redo_form -->
{SUBMIT_APPLICATION}
{END_FORM}
