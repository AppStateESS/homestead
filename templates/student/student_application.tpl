<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <h1>{TERM} On-campus Housing Application </h1>

        <p>{RECEIVED_DATE}</p>

        <!-- BEGIN withdrawn -->
        <div class="alert alert-danger">{WITHDRAWN}</div>
        <!-- END withdrawn -->

        <!-- BEGIN spring_roommates -->
        {SPRING_ROOMMATE_NOTICE}
        <div class="alert alert-info">
            <p><i class="fa fa-exclamation"></i> Roommate requests are not available for the spring semester due to a lack of open spaces needed to meet these requests.</p>
        </div>
        <!-- END spring_roommates -->

        <!-- BEGIN review_msg -->
        <p>
            {REVIEW_MSG}
            Please review the information you entered. If you need to go back
            and make changes to your application click the 'modify application'
            button below. If the information you have entered is correct click
            the 'submit application' button.
        </p>
        <!-- END review_msg -->

        <!-- BEGIN summer_deposit -->
        {SUMMER_DEPOSIT_MSG}
        <div class="alert alert-info">
            <p><i class="fa fa-usd fa-2x"></i> <strong>Cancellation Fees are in Effect!</strong> We are now assigning students as applications are received.  You will be assessed a $75 cancellation fee if you cancel your space after completion of this application. <strong>Please do not complete this application if you are unsure of your summer housing needs</strong>.  We will continue to accept applications through the opening of summer school.</p>
        </div>
        <!-- END summer_deposit -->

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

        <h3>Preferences</h3>

        <div class="form-group">
            <label for="{MEAL_OPTION_ID}">Meal Plan:</label>
            <div class="row">

                <!-- BEGIN meal_plan -->
                <div class="col-md-3">
                    {MEAL_OPTION}
                </div>
                <!-- END meal_plan -->

                <!-- BEGIN meal_plan_exists -->
                <div class="col-md-6">
                    <p>{EXISTING_MEAL_PLAN}</p>
                    <p class="help-block">You meal plan has been choice has been sent to Food Services. Contact <a href="https://foodservices.appstate.edu/" target="_blank">Food Services</a> if you'd like to change it.</p>
                </div>
                <!-- END meal_plan_exists -->

            </div>
        </div>


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
            <label for="{PREFERRED_BEDTIME_ID}">Preferred Bedtime:</label>
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
            <div class="row">
                <div class="col-md-3">
                    {ROOM_TYPE}
                </div>
            </div>
        </div>
        <!-- END room_type -->

        <div class="form-group">
            <label for="{SMOKING_PREFERENCE_ID}">Do you smoke?</label>
            <div class="row">
                <div class="col-md-3">
                    {SMOKING_PREFERENCE}
                </div>
            </div>
        </div>

        <h3>Emergency Contact Information</h3>

        <div class="form-group required">
            <label for="{EMERGENCY_CONTACT_NAME_ID}">Parent / Guardian Name</label>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_CONTACT_NAME}
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="{EMERGENCY_CONTACT_RELATIONSHIP_ID}">Relationship</label>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_CONTACT_RELATIONSHIP}
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="{EMERGENCY_CONTACT_PHONE_ID}">Phone Number</label>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_CONTACT_PHONE}
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="{EMERGENCY_CONTACT_EMAIL_ID}">Email</label>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_CONTACT_EMAIL}
                    <div id="contact_status">
                    </div>
                </div>
            </div>
        </div>
        <h3>Emergency Medical Information</h3>
        <div class="form-group">
            <p>Are there any <em>emergency</em> medical conditions which our staff should be aware of?</p>
            <span class="help-block">
                In the event of a <em>medical emergency</em> within the residence halls we may disclose this information to <em>emergency personnel</em>. For example, severe <em>life-threatening</em> allergies to medications or foods. This information will be kept confidential and only shared on a need-to-know basis.
            </span>
            <div class="row">
                <div class="col-md-4">
                    <label for="{EMERGENCY_MEDICAL_CONDITION_ID}">Emergency Medical Conditions</label>
                    {EMERGENCY_MEDICAL_CONDITION}
                </div>
            </div>
        </div>

        <h3>Missing Person Information</h3>

        <p>If you are reported missing for more than 24 hours, federal law requires
            the University to contact someone. Please list your contact person’s
            information below.
        </p>
        <p class="text-muted">This information is kept confidential, but will be
            released to law enforcement if you are reported missing. The University
            will inform local law enforcement that you are missing within 24 hours of
            the report. <strong>Please note: </strong>If you are under 18 and not emancipated, the
            University must notify your parent/guardian within 24 hours that you
            are reported missing.
        </p>
        <div class="form-group required">
            <label for="{MISSING_PERSON_NAME_ID}">Contact Person Name</label>
            <div class="row">
                <div class="col-md-4">
                    {MISSING_PERSON_NAME}
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="{MISSING_PERSON_RELATIONSHIP_ID}">Relationship</label>
            <div class="row">
                <div class="col-md-4">
                    {MISSING_PERSON_RELATIONSHIP}
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="{MISSING_PERSON_PHONE_ID}">Phone Number</label>
            <div class="row">
                <div class="col-md-4">
                    {MISSING_PERSON_PHONE}
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="{MISSING_PERSON_EMAIL_ID}">Email</label>
            <div class="row">
                <div class="col-md-4">
                    {MISSING_PERSON_EMAIL}
                    <div id="missing_status">
                    </div>
                </div>
            </div>
        </div>

        <h3>Housing Accommodations</h3>

        <p>
            University Housing is committed to meeting the individual needs of all students to the best of our ability. Housing requests due to disabilities and gender related needs are taken seriously, thoroughly considered, and kept confidential.
        </p>
        <p>
            Students who need housing accommodations due to the impact of a disability (physical, medical, mental health, etc.) should contact the <a href="https://ods.appstate.edu/" target="_blank">Office of Disability Services</a>.
            Students who need housing accommodations due to gender related needs should contact the <a href="http://multicultural.appstate.edu/" target="_blank">Office of Multicultural Student Development</a>.
        </p>

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
