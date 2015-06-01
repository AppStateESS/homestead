<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <h1>{TERM} On-campus Housing Application </h1>

        <p>{RECEIVED_DATE}</p>

        <!-- BEGIN withdrawn -->
        <div class="alert alert-danger">{WITHDRAWN}</div>
        <!-- END withdrawn -->

        <!-- BEGIN review_msg -->
        <p>
            {REVIEW_MSG}
            Please review the information you entered. If you need to go back and make changes to your application click the 'modify application' button below. If the information you have entered is correct click the 'submit application' button.
        </p>
        <!-- END review_msg -->

        <h3>Demographic Information</h3>

        <form class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-2">Name:</label>
                <div class="col-sm-4"><p class="form-static-control">{STUDENT_NAME}</p></div>
            </div>

            <div class="form-group">
                <label class="col-sm-2">Gender:</label>
                <div class="col-sm-4"><p class="form-static-control">{GENDER}</p></div>
            </div>

            <div class="form-group">
                <label class="col-sm-2">Student Status:</label>
                <div class="col-sm-4"><p class="form-static-control">{STUDENT_STATUS_LBL}</p></div>
            </div>

            <div class="form-group">
                <label class="col-sm-2">Starting at ASU in:</label>
                <div class="col-sm-4"><p class="form-static-control">{ENTRY_TERM}</p></div>
            </div>

            <div class="form-group">
                <label class="col-sm-2">Class:</label>
                <div class="col-sm-4"><p class="form-static-control">{CLASSIFICATION_FOR_TERM_LBL}</p></div>
            </div>
        </form>
        <hr>
        {START_FORM}
        <!-- BEGIN form -->
        <div class="form-group">
            <label for="{NUMBER_ID}">Cell Phone Number:</label>
            <span class="help-block">We'll only use this to contact you if we have a question about your application, or when you have a package delivered.</span>
            <div class="row">
                <div class="col-md-3">
                    {NUMBER}
                </div>
            </div>
            <div class="checkbox">
                <label class="text-muted">
                    {DO_NOT_CALL}
                    Check here if do not wish to provide your cellphone number.
                </label><br />
            </div>
        </div>
        <!-- END form -->

        <!-- BEGIN review -->
        <div class="form-group">
            <label>Cell Phone Number:</label>
            <p>{CELLPHONE}</p>
        </div>
        <!-- END review -->

        <!-- BEGIN meal_plan -->
        <h3>Preferences</h3>
        <div class="form-group">
            <label for="{MEAL_OPTION_ID}">Meal Plan:</label>
            <div class="row">
                <div class="col-md-3">
                    {MEAL_OPTION}
                </div>
            </div>
        </div>
        <!-- END meal_plan -->
        <!-- BEGIN preferences -->
        <div class="form-group">
            <label for="{LIFESTYLE_OPTION_ID}">Lifestyle Option:</label>
            <div class="row">
                <div class="col-md-3">
                    {LIFESTYLE_OPTION}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="{PREFERRED_BEDTIME_ID}">Preffered Bedtime:</label>
            <div class="row">
                <div class="col-md-3">
                    {PREFERRED_BEDTIME}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label  for="{ROOM_CONDITION_ID}">Room Condition:</label>
            <div class="row">
                <div class="col-md-3">
                    {ROOM_CONDITION}
                </div>
            </div>
        </div>
        <!-- END preferences -->

        <!-- BEGIN room_type -->
        <div class="form-group">
            <label for="{ROOM_TYPE_ID}">Preferred Room Type:</label>
            {ROOM_TYPE}
        </div>
        <!-- END room_type -->

        <h3>Emergency Contact Information</h3>

        <div class="form-group">
            <label for="{EMERGENCY_CONTACT_NAME_ID}">Parent / Guardian Name:</label>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_CONTACT_NAME}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="{EMERGENCY_CONTACT_RELATIONSHIP_ID}">Relationship:</label>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_CONTACT_RELATIONSHIP}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="{EMERGENCY_CONTACT_PHONE_ID}">Phone Number:</label>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_CONTACT_PHONE}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="{EMERGENCY_CONTACT_EMAIL_ID}">Email:</label>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_CONTACT_EMAIL}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="{EMERGENCY_MEDICAL_CONDITION_ID}">Medical Conditions</label>
            <p>Are there any medical conditions you have which our staff should be aware of?</p>
            <span class="help-block">
                These should be <em>life-threatening</em> conditions you'd want us to share with first responders. For example, <em>severe</em> allergies to food or medicines.
            </span>
            <span class="help-block">
                (This information will be kept confidential and
                will only be shared with the staff in your residence hall. However, this
                information may be disclosed to emergency medical personnel in case of an
                emergency.)
            </span>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_MEDICAL_CONDITION}
                </div>
            </div>
        </div>

        <h3>Missing Person Information</h3>

        <p>Federal law requires that we ask you to confidentially identify a person
            whom the University should contact if you are reported missing for more than
            24 hours. Please list your contact person's information below:
        </p>
        <div class="form-group">
            <label for="{MISSING_PERSON_NAME_ID}">Contact Person Name:</label>
            <div class="row">
                <div class="col-md-4">
                    {MISSING_PERSON_NAME}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="{MISSING_PERSON_RELATIONSHIP_ID}">Relationship:</label>
            <div class="row">
                <div class="col-md-4">
                    {MISSING_PERSON_RELATIONSHIP}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="{MISSING_PERSON_PHONE_ID}">Phone Number:</label>
            <div class="row">
                <div class="col-md-4">
                    {MISSING_PERSON_PHONE}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="{MISSING_PERSON_EMAIL_ID}">Email</label>
            <div class="row">
                <div class="col-md-4">
                    {MISSING_PERSON_EMAIL}
                </div>
            </div>
        </div>

        <h3>Special Needs Housing</h3>

        <!-- BEGIN special_needs_text -->
        {SPECIAL_NEEDS_TEXT}
        <p>
            University Housing is committed to meeting the needs of all students to
            the best of its ability.
        </p>
        <p>
            Special needs housing requests will be reviewed individually with a
            commitment to providing housing that best meets the needs of each student.
            University Housing takes these concerns very seriously and confidentiality
            will be maintained. Housing for special needs may be limited due to space
            availability.
        </p>
        <p>Do you have any special needs?</p>
        <!-- END special_needs_text -->

        <!-- BEGIN special_need -->
        <div class="row">
            <div class="col-md-5 col-md-offset-1">
                <div class="checkbox">
                    <label>
                        {SPECIAL_NEED}{SPECIAL_NEED_LABEL}
                    </label>
                </div>
            </div>
        </div>
        <!-- END special_need -->


        <!-- BEGIN special_needs_result -->
        <p>
            {SPECIAL_NEEDS_RESULT}
        </p>
        <!-- END special_needs_result -->

                <!-- BEGIN rlc_interest_1 -->
        <h3>Residential Learning Communities</h3>
        <p>
            Are you interested in living in a
            <a href="http://housing.appstate.edu/rlc" target="_blank">Residential Learning Community</a>
            (RLC)?
        </p>

        <div class="row">
            <div class="col-md-2 col-md-offset-1">
                <div class="radio">
                    <label>
                        {RLC_INTEREST_2}
                        {RLC_INTEREST_2_LABEL_TEXT}
                    </label>
                </div>
                <div class="radio">
                    <label>
                        {RLC_INTEREST_1}{RLC_INTEREST_1_LABEL_TEXT}
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <p class="text-muted">
                    RLCs afford students a unique opportunity for an academic learning
                    experience outside of the classroom.  Students participating in a learning
                    community live together on the same floor of a residence hall and are often
                    required to enroll in one or more linked courses which emphasize the theme
                    of each specific community.  In addition, research shows that students who
                    participate in a residential learning community have a higher GPA and enjoy
                    a better college experience.
                </p>
                <p class="text-muted">
                    Appalachian State University was ranked as a 2010 Best College for Learning
                    Communities according to U.S. News &amp; World Report. We offer 17 options for
                    students to choose from, including those focused on particular majors, and
                    others with a focus on a particular student interest.  One of the best ways to
                    develop strong friendships and succeed in college is to join a residential
                    learning community.
                </p>
                <p class="text-muted">
                    For more information visit the
                    <a href="http://housing.appstate.edu/rlc" target="_blank">Residential Learning Communities website.</a>
                </p>
            </div>
        </div>
        <!-- END rlc_interest_1 -->

        <!-- BEGIN rlc_submitted -->
        <h3>Residential Learning Communities</h3>
        <div class="row>">
            <p>
                Are you interested in living in a
                <a href="http://housing.appstate.edu/rlc" target="_blank">Residential Learning Community</a>
                (RLC)?
            </p>
            <p>
                You have already submitted a separate Learning Community Application.
                Use the options on the main menu to view or edit your Learning Community
                Application.
            </p>
        </div>
        <!-- END rlc_submitted -->

        <!-- BEGIN rlc_review -->
        <h3>Residential Learning Communities</h3>
        <div class="row">
            <div class="col-md-12">
                <p>Are you interested in living in a Residential Learning Community (RLC)?</p>
                <p>{RLC_REVIEW}</p>
            </div>
        </div>
        <!-- END rlc_review -->

        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN continue_btn -->
                {CONTINUE_BTN}
                <button type="submit" class="btn btn-success btn-lg pull-right">Continue <i class="fa fa-chevron-right"></i></button>
                <!-- END continue_btn -->

                <!-- BEGIN redo_form -->
                <a href="{REDO_BUTTON}" class="btn btn-default"><i class="fa fa-chevron-left"></i> Back</a>
                <!-- END redo_form -->

                <!-- BEGIN confirmapp -->
                {CONFIRM_BTN}
                <button type="submit" class="btn btn-success btn-lg pull-right">Confirm &amp; Continue <i class="fa fa-chevron-right"></i></button>
                <!-- END confirmapp -->
        </div>

        {END_FORM}
    </div>
</div>
