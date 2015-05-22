<h1>{TERM} On-campus Housing Application </h1>
<p>{RECEIVED_DATE}</p>

<div class="col-md-12">
      <!-- BEGIN withdrawn -->
      <font color="red"><b>{WITHDRAWN}</b></font>
      <!-- END withdrawn -->
      <!-- BEGIN review_msg -->
      {REVIEW_MSG}
      Please review the information you entered. If you need to go back and make changes to your application click the 'modify application' button below. If the information you have entered is correct click the 'submit application' button.
      <!-- END review_msg -->
      {START_FORM}


      <div class="form-group col-md-10">
        <div class="row">
          <p><strong>Demographic Information</strong></p>
        </div>
        <div class="row">
          <label class="col-md-1">Name:</label>
          <p class="col-md-3 col-md-offset-5 text-right">{STUDENT_NAME}</p>
        </div>
        <div class="row">
          <label class="col-md-1">Gender:</label>
          <p class="col-md-3 col-md-offset-5 text-right">{GENDER}</p>
        </div>
        <div class="row">
          <label class="col-md-3">Student Status:</label>
          <p class="col-md-3 col-md-offset-3 text-right">{STUDENT_STATUS_LBL}</p>
        </div>
        <div class="row">
          <label class="col-md-3">Application Term:</label>
          <p class="col-md-3 col-md-offset-3 text-right">{ENTRY_TERM}</p>
        </div>
        <div class="row">
          <label class="col-md-3">Classification:</label>
          <p class="col-md-3 col-md-offset-3 text-right">{CLASSIFICATION_FOR_TERM_LBL}</p>
        </div>
        <!-- BEGIN form -->
        <div class="row">
          <label class="col-md-3">Cell Phone Number:</label>
          <div class="col-md-3 col-md-offset-3">
            {NUMBER}
          </div>
        </div>
        <div class="row">
          <div class="col-md-4 col-md-offset-6">
            <div class="checkbox">
              <label>
                {DO_NOT_CALL}
                Check here if you do not have or do not wish to provide your cellphone number.
              </label>
            </div>
          </div>
        </div>
        <!-- END form -->
        <!-- BEGIN review -->
        <div class="row">
          <label class="col-md-3">Cell Phone Number:</label>
          <p class="col-md-3 col-md-offset-3 text-right">{CELLPHONE}</p>
        </div>
        <!-- END review -->
        <!-- BEGIN meal_plan -->
        <div class="row">
          <p><strong>Meal Plan</strong></p>
        </div>
        <div class="row">
          <label class="col-md-2">Meal Option:</label>
          <div class="col-md-3 col-md-offset-4">{MEAL_OPTION}</div>
        </div>
        <!-- END meal_plan -->
        <!-- BEGIN preferences -->
        <div class="row">
          <p><strong>Preferences</strong></p>
        </div>
        <div class="row">
          <label class="col-md-3">Lifestyle Option:</label>
          <div class="col-md-3 col-md-offset-3">{LIFESTYLE_OPTION}</div>
        </div>
        <div class="row">
          <label class="col-md-3">Preffered Bedtime:</label>
          <div class="col-md-3 col-md-offset-3">{PREFERRED_BEDTIME}</div>
        </div>
        <div class="row">
          <label class="col-md-3">Room Condition:</label>
          <div class="col-md-3 col-md-offset-3">{ROOM_CONDITION}</div>
        </div>
        <!-- END preferences -->
        <!-- BEGIN room_type -->
        <div class="row">
          <p><strong>Room Type</strong></p>
        </div>
        <div class="row">
          <label class="col-md-3">Preferred Room Type:</label>
          <div class="col-md-2 col-md-offset-3">{ROOM_TYPE}</div>
        </div>
        <!-- END room_type -->

        <div class="row">
          <p><strong>Emergency Contact Information</strong></p>
        </div>
        <div class="row">
          <label class="col-md-3">Emergency Contact Person Name:</label>
          <div class="col-md-3 col-md-offset-3">{EMERGENCY_CONTACT_NAME}</div>
        </div>
        <div class="row">
          <label class="col-md-2">Relationship:</label>
          <div class="col-md-3 col-md-offset-4">{EMERGENCY_CONTACT_RELATIONSHIP}</div>
        </div>
        <div class="row">
          <label class="col-md-3">Phone Number:</label>
          <div class="col-md-3 col-md-offset-3">{EMERGENCY_CONTACT_PHONE}</div>
        </div>
        <div class="row">
          <label class="col-md-2">Email:</label>
          <div class="col-md-3 col-md-offset-4">{EMERGENCY_CONTACT_EMAIL}</div>
        </div>
        <div class="row">
          <label class="col-md-5">Are there any medical conditions you have which our
            staff should be aware of? (This information will be kept confidential and
            will only be shared with the staff in your residence hall. However, this
            information may be disclosed to medical/emergency personnel in case of an
            emergency.)</label>
          <div class="col-md-5 col-md-offset-1">{EMERGENCY_MEDICAL_CONDITION}</div>
        </div>

        <div class="row">
          <p><strong>Missing Person Information</p></strong>
        </div>
        <div class="row">
          <p>Federal law requires that we ask you to confidentially identify a person
            whom the University should contact if you are reported missing for more than
            24 hours. Please list your contact person's information below:</p>
        </div>
        <div class="row">
          <label class="col-md-3">Contact Person Name:</label>
          <div class="col-md-3 col-md-offset-3">{MISSING_PERSON_NAME}</div>
        </div>
        <div class="row">
          <label class="col-md-2">Relationship:</label>
          <div class="col-md-3 col-md-offset-4">{MISSING_PERSON_RELATIONSHIP}</div>
        </div>
        <div class="row">
          <label class="col-md-3">Phone Number:</label>
          <div class="col-md-3 col-md-offset-3">{MISSING_PERSON_PHONE}</div>
        </div>
        <div class="row">
          <label class="col-md-2">Email</label>
          <div class="col-md-3 col-md-offset-4">{MISSING_PERSON_EMAIL}</div>
        </div>

        <div class="row">
          <p><strong>Special Needs Housing</strong></p>
        </div>
        <!-- BEGIN special_needs_text -->
        <div class="row">
          {SPECIAL_NEEDS_TEXT}
        </div>
        <div class="row">
          <p>University Housing is committed to meeting the needs of all students to
            the best of its ability.</p>
          <p>Special needs housing requests will be reviewed individually with a
            commitment to providing housing that best meets the needs of the student.
            University Housing takes these concerns very seriously and confidentiality
            will be maintained. Housing for special needs may be limited due to space
            availability.</p>
        </div>
        <!-- END special_needs_text -->
        <div class="row">
          <p>Do you have any special needs?</p>
        </div>
          <!-- BEGIN special_need -->
        <div class="row">
          <div class="col-md-4 col-md-offset-6">
            <div class="checkbox">
              <label>
                {SPECIAL_NEED}{SPECIAL_NEED_LABEL}
              </label>
            </div>
          </div>
        </div>

        <!-- BEGIN special_needs_result -->
        <div class="row">
          <div class="col-md-4">
            {SPECIAL_NEEDS_RESULT}
          </div>
        </div>
        <!-- END special_needs_result -->

        <!-- BEGIN rlc_interest_1 -->
        <div class="row">
          <p><strong>Residential Learning Communities</strong></p>
        </div>
        <div class="row">
          <p>Are you interested in living in a
            <a href="http://housing.appstate.edu/rlc" target="_blank">
              Residential Learning Community
            </a>
             (RLC)?
          </p>
        </div>
        <div class="row">
          <div class="col-md-2 col-md-offset-6">
            <div class="radio">
              <label>
                {RLC_INTEREST_1}{RLC_INTEREST_1_LABEL_TEXT}
              </label>
            </div>
            <div class="radio">
              <label>
                {RLC_INTEREST_2}
                {RLC_INTEREST_2_LABEL_TEXT}
              </label>
            </div>
          </div>
        </div>
        <div class="row">
          <p>RLCs afford students a unique opportunity for an academic learning
            experience outside of the classroom.  Students participating in a learning
            community live together on the same floor of a residence hall and are often
            required to enroll in one or more linked courses which emphasize the theme
            of each specific community.  In addition, research shows that students who
            participate in a residential learning community have a higher GPA and enjoy
            a better college experience.</p>
          <p>Appalachian State University was ranked as a 2010 Best College for Learning
            Communities according to U.S. News &amp; World Report. We offer 17 options for
            students to choose from, including those focused on particular majors, and
            others with a focus on a particular student interest.  One of the best ways to
            develop strong friendships and succeed in college is to join a residential
            learning community. </p>
          <p>For more information visit the
            <a href="http://housing.appstate.edu/rlc" target="_blank">
              Residential Learning Communities website.
            </a>
          </p>
        </div>
        <!-- END rlc_interest_1 -->

        <!-- BEGIN rlc_submitted -->
        <div class="row">
          <p><strong>Residential Learning Communities</strong></p>
        </div>
        <div class="row>">
          <p>Are you interested in living in a
            <a href="http://housing.appstate.edu/rlc" target="_blank">
              Residential Learning Community
            </a>
            (RLC)?
          </p>
          <p>You have already submitted a separate Learning Community Application.
            Use the options on the main menu to view or edit your Learning Community
            Application.</p>
        </div>
        <!-- END rlc_submitted -->

        <!-- BEGIN rlc_review -->
        <div class="row">
          <p><strong>Residential Learning Communities</strong></p>
        </div>
        <div class="row">
          <p>Are you interested in living in a Residential Learning Community (RLC)?</p>
          <p>{RLC_REVIEW}</p>
        </div>
        <!-- END rlc_review -->

        <div class="row">
          <button type="submit" class="btn btn-success btn-lg">
            Continue
            <i class="fa fa-chevron-right"></i>
          </button>
          <!-- {SUBMIT} -->
          <!-- BEGIN redo_form -->
          or {REDO_BUTTON}
          <!-- END redo_form -->
          {SUBMIT_APPLICATION}
          {END_FORM}
        </div>



    </div>
</div>
