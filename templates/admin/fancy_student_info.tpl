<div class="hms">
  <div class="box">
    <div class="box-content">
        <h1>{NAME} -- {BANNER_ID} -- {TERM}</h1>
        <h2>Login as this student [ {LOGIN_AS_STUDENT} ]</h2>

        <!-- BEGIN success_msg -->
            <div class="success">{SUCCESS}</div><br />
        <!-- END success_msg -->

        <!-- BEGIN error_msg -->
            <div class="error">{ERROR}</div><br />
        <!-- END error_msg -->

        <table>
            <tr>
                <th><a id="demographics_toggle">[-]</a>Student Demographics</th>
            </tr>
            <tr>
            <td rowspan="6">
            <div id="student_demographics">
                <table cellspacing="3" cellpadding="2">
                    <tr>
                        <th>ASU Email Address:</th>
                        <td><a href="mailto:{USERNAME}@appstate.edu">{USERNAME}@appstate.edu</a></td>
                    </tr> 

                    <tr>
                        <th>Gender</th>
                        <td>{GENDER}</td>
                    </tr>
                    
                    <tr>
                        <th>Birthday</th>
                        <td>{DOB}</td>                    
                    </tr>
                    
                    <tr>
                        <th>Type</th>
                        <td>{TYPE}</td>
                    </tr>
                    <!-- BEGIN application_term -->
                    <tr>
                        <th>Application Term:</th>
                        <td>{APPLICATION_TERM}</td>
                    </tr>
                    <!-- END application_term -->
                    <tr>
                        <th>Class</th>
                        <td>{CLASS}</td>
                    </tr>
                    <tr>
                        <th>Phone Number</th>
                        <td>
                    <!-- BEGIN phone_number -->
                        {NUMBER}<br />
                    <!-- END phone_number -->
                        </td>
                    </tr>
                    
                    <tr>
                       <th>Addresses</th>
                        <td>
                            <!-- BEGIN addresses -->
                            	{ADDR_TYPE}<br />
                            	{ADDRESS_L1}<br />
                            	{ADDRESS_L2}<br />
                            	{ADDRESS_L3}<br />
                            	{CITY}, {STATE} {ZIP}<br /><br />
                            <!-- END addresses -->
                            </ul>
                        </td>
                </div>
                </table>
                </td>
            </tr>
        </table>
        <br>

        <table>
            <tr>
                <th><a id="status_toggle">[-]</a>Housing Status</th>
            </tr>
            <tr>
                <td>
                <div id="housing_status">
                <table>
                    <tr>
                        <th>Assigned:</th>
                        <td>{ASSIGNMENT}</td>
                    </tr>
                    <tr>
                        <th>Roommate(s):</th>
                        <!-- BEGIN confirmed -->
                            <td class="success">
                            {ROOMMATE}<img class="roommate_request_icon" src="images/mod/hms/icons/check.png" />
                            <td>
                        <!-- END confirmed -->
                        <!-- BEGIN pending -->
                            <td class="warning">
                            {ROOMMATE}<img class="roommate_request_icon" src="images/mod/hms/icons/warning.png" />
                            <td>
                        <!-- END pending -->
                        <!-- BEGIN no_bed_available -->
                            <td class="error">
                            {ROOMMATE}<img class="roommate_request_icon" src="images/mod/hms/icons/warning.png" />
                            <td>
                        <!-- END no_bed_available -->
                        
                    </tr>
                    <tr>
                        <!-- BEGIN assigned -->
                        <tr>
                            <td></td>
                            <td>{ROOMMATE}</td>
                        </tr>
                        <!-- END assigned -->
                    </tr>
                    <tr>
                        <th>RLC:</td>
                        <td>{RLC_STATUS}</td>
                    </tr>
                    <tr>
                        <th>Special Interest Group: </th>
                        <td>{SPECIAL_INTEREST}</td>
                    </tr>
                </table>
                </div>
                </td>
            </tr>
        </table>
        <br /><br />
        <table>
            <tr>
                <th><a id="application_toggle">[-]</a>Applications</th>
            </tr>
            <tr>
                <td>{REPORT_APPLICATION}</td>
            </tr>
            <tr>
                <td>
                    <div id="applications">
                    <table>
                        <tr>
                            <th>Term</th>
                            <th>Type</th>
                            <th>Cell phone #</th>
                            <th>Meal plan</th>
                            <th>Cleanliness</th>
                            <th>Bedtime</th>
                            <th>Actions</th>
                        </tr>
                        <!-- BEGIN APPLICATIONS -->
                        <tr>
                            <td>{term}</td>
                            <td>{type}</td>
                            <td>{cell_phone}</td>
                            <td>{meal_plan}</td>
                            <td>{clean}</td>
                            <td>{bedtime}</td>
                            <td>{actions}</td>
                        </tr>
                        <!-- END APPLICATIONS -->
                        <!-- BEGIN no_apps -->
                        <tr>
                            <td colspan="5">{APPLICATIONS_EMPTY}</td>
                        </tr>
                        <!-- END no_apps -->
                    </table>
                    </div>
                </td>
            </tr>
        </table>

        </div>
    </div>
</div>
<div id="note_dialog" title="Enter a note for: {FIRST_NAME} {MIDDLE_NAME} {LAST_NAME}">
{START_FORM}
{NOTE}
<br>
{SUBMIT}
{END_FORM}
</div>
<!-- BEGIN notes -->
<center>
<h1>Recent Notes</h1>
[<a id=add_note>Add a note</a>]
{NOTE_PAGER}
<h1>Student Log</h1>
{LOG_PAGER}
</center>
<!-- END notes -->