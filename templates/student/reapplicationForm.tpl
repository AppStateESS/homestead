<h1>{TERM} On-campus Housing Re-application</h1>

<div class="row">
    <div class="col-md-10">
        <p>Welcome to Appalachian State University Housing. As a returning
            student you must re-apply and be selected in order to be guaranteed
            on-campus housing for {TERM}. Students will be selected at random. If
            you are selected you will be notified by email.
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <p><strong>For more information on how the selection process works, please
            read the <a href="http://housing.appstate.edu/reapp" target="_blank">housing
                re-application FAQ</a>.</strong>
            </p>
    </div>
</div>

{START_FORM}

<div class="row">
    <div class="col-md-10">
        <h3>Contact Information</h3>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <p>
            We'll only use this information to contact you if we have a
            question about your application or to notify you if you receive a
            package (via UPS, FedEx, etc). This information will not be shared with
            anyone else.
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Cell Phone Number:</label>
            {NUMBER}
        </div>
        <div clas="form-group">
            {DO_NOT_CALL}
            Check here if you do not have a cell phone or do
            not wish to provide your cell phone number.
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <h3>Emergency Information</h3>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Emergency Contact Person Name:</label>
        {EMERGENCY_CONTACT_NAME}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Relationship:</label>
            {EMERGENCY_CONTACT_RELATIONSHIP}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Phone Number:</label>
            {EMERGENCY_CONTACT_PHONE}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Email:</label>
            {EMERGENCY_CONTACT_EMAIL}
        </div>
    </div>
</div>
<div class="row">
    <p class="col-md-10">Are there any medical conditions you have which our
        staff should be aware of? (This information will be kept confidential and
        will only be shared with the staff in your residence hall. However, this
        information <strong>may</strong> be disclosed to medical/emergency personnel
        in case of an emergency.)
    </p>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {EMERGENCY_MEDICAL_CONDITION}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <h3>Missing Person Information</h3>
    </div>
</div>
<div class="row">
    <div class="col-md-10">
        <p>Federal law requires that we ask you to confidentially identify a person
            whom the University should contact if you are reported missing for more
            than 24 hours. Please list your contact person's information below:
        </p>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Contact Person Name:</label>
            {MISSING_PERSON_NAME}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Relationship:</label>
            {MISSING_PERSON_RELATIONSHIP}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Phone Number:</label>
            {MISSING_PERSON_PHONE}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Email:</label>
            {MISSING_PERSON_EMAIL}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <h3>Meal Plan</h3>
    </div>
</div>
<div class="row">
    <div class="col-md-10">
        <p>Please choose a meal plan. You'll have the opportunity to change
            this again later (after you choose your room).</p>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Meal plan</label>
            {MEAL_PLAN}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <h3>Residential Learning Communities</h3>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Would you like to apply to live in a Residential Learning Community?</label>
            <div class="checkbox">
                <label>
                    {RLC_INTEREST}
                    {RLC_INTEREST_LABEL}
                </label>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <h3>Housing Accommodations</h3>

        <p>
            University Housing is committed to meeting the individual needs of all students to the best of our ability. Housing requests due to disabilities and gender related needs are taken seriously, thoroughly considered, and kept confidential.
        </p>
        <p>
            Students who need housing accommodations due to the impact of a disability (physical, medical, mental health, etc.) should contact the <a href="https://ods.appstate.edu/" target="_blank">Office of Disability Services</a>.
            Students who need housing accommodations due to gender related needs should contact the <a href="http://multicultural.appstate.edu/" target="_blank">Office of Multicultural Student Development</a>.
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <h3>Early Contract Release</h3>
        <p>Are you currently planning to apply for early contract release because
            you'll be leaving on-campus housing at the end of {FALL_TERM}?
        </p>
        <p>You may be released from the Housing Contract ealy, but only for an
            approved reason that's listed in the box below (e.g. graduation, student
            teaching, etc). This helps us in capacity planning; it
            <strong>does not</strong> commit you to leaving at the end of {FALL_TERM},
            nor does it release you from the Contract. There is a separate approval
            process later in the semester to confirm.
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {EARLY_RELEASE_LABEL}
            {EARLY_RELEASE}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <h3>Terms &amp; Conditions</h3>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="checkbox">
            <label>
                {DEPOSIT_CHECK}
                {DEPOSIT_CHECK_LABEL}
            </label>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 pull-right">
        <button type="submit" class="btn btn-success btn-lg">
            Continue <i class="fa fa-chevron-right"></i>
        </button>
    </div>
</div>

{END_FORM}
