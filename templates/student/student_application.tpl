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
            </tr>
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
            <!-- BEGIN meal_plan -->
            <tr>
                <th colspan="2">Meal Plan</th>
            </tr>
            <tr>
                <td>Meal Option: </td><td align="left">{MEAL_OPTION}</td>
            </tr>
            <!-- END meal_plan -->
            <!-- BEGIN preferences -->
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
            <!-- END preferences -->
            <!-- BEGIN room_type -->
            <tr>
                <th colspan="2">Room Type</th>
            </tr>
            <tr>
                <td>Preferred Room Type: </td><td align="left">{ROOM_TYPE}</td>
            </tr>
            <!-- END room_type -->
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
                <td>
                    <p>Are you interested in living in a <a href="http://housing.appstate.edu/pagesmith/29" target="_blank">Residential Learning Community</a> (RLC)?</p>                  
                    <p>RLCs afford students a unique opportunity for an academic learning experience outside of the classroom.  Students participating in a learning community live together on the same floor of a residence hall and are often required to enroll in one or more linked courses which emphasize the theme of each specific community.  In addition, research shows that students who participate in a residential learning community have a higher GPA and enjoy a better college experience.</p>
                    <p>Appalachian State University was ranked as a 2010 Best College for Learning Communities according to U.S. News & World Report. We offer 17 options for students to choose from, including those focused on particular majors, and others with a focus on a particular student interest.  One of the best ways to develop strong friendships and succeed in college is to join a residential learning community. </p>
                    <p>For more information visit the <a href="http://housing.appstate.edu/pagesmith/29" target="_blank">Residential Learning Communities website.</a></p>
                </td>
                <td align="left">{RLC_INTEREST_1} {RLC_INTEREST_1_LABEL}&nbsp;{RLC_INTEREST_2} {RLC_INTEREST_2_LABEL}</td>
            </tr>
            <!-- END rlc_interest_1 -->
            
            <!-- BEGIN rlc_submitted -->
            {RLC_SUBMITTED}
            <tr>
                <th colspan="3">Unique Housing Options</th>
            </tr>
            <tr>
                <td>
                    <p>Are you interested in living in a <a href="http://housing.appstate.edu/pagesmith/29" target="_blank">Residential Learning Community</a> (RLC)?</p>
                    <p>You have already submitted a separate Learning Community Application. Use the options on the main menu to view or edit your Learning Community Application.</p>                  
                </td>
            </tr>
            <!-- END rlc_submitted -->
            
            <!-- BEGIN rlc_review -->
            <tr>
            <tr>
                <th colspan="2">Unique Housing Options</th>
            </tr>
                <td>
                    <p>Are you interested in living in a Residential Learning Community (RLC)?</p>
                </td>
                <td>
                    <p>{RLC_REVIEW}</p>
                </td>
            </tr>
            <!-- END rlc_review -->
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
