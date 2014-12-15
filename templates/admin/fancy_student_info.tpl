<div class="hms">
  <div class="box">
    <div class="box-content">
        <h1>{NAME} -- {BANNER_ID} -- {TERM}</h1>
        <h2>Login as this student [ {LOGIN_AS_STUDENT} ]</h2>

        <table class="profileHeader">
            <tr>
                <th><a id="demographics_toggle">[-]</a> Student Demographics</th>
            </tr>
           </table>
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
                        <th>Level</th>
                        <td>{STUDENT_LEVEL}</td>
                    </tr>
                    <tr>
                        <th>Admissions Decision</th>
                        <td>{ADMISSION_DECISION}</td>
                    </tr>
                    <tr>
                        <th>International</th>
                        <td>{INTERNATIONAL}</td>
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
                
                </table>
               </div>
        <br />

        <table  class="profileHeader">
            <tr>
                <th><a id="status_toggle">[-]</a> Housing Status</th>
            </tr>
        </table>
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
                            {ROOMMATE}<img class="roommate_request_icon" src="mod/hms/img/icons/check.png" />
                            </td>
                        <!-- END confirmed -->
                        <!-- BEGIN pending -->
                            <td class="warning">
                            {ROOMMATE}<img class="roommate_request_icon" src="mod/hms/img/icons/warning.png" />
                            </td>
                        <!-- END pending -->
                        <!-- BEGIN error_status -->
                            <td class="error">
                            {ROOMMATE}<img class="roommate_request_icon" src="mod/hms/img/icons/warning.png" />
                            </td>
                        <!-- END error_status -->
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
                        <th>Honors</th>
                        <td>{HONORS}</td>
                    </tr>
                    <tr>
                        <th>Teaching Fellow</th>
                        <td>{TEACHING_FELLOW}</td>
                    </tr>
                    <tr>
                        <th>Watauga Global Member</th>
                        <td>{WATAUGA}</td>
                    </tr>
                    <tr>
                        <th>Re-application Special Interest Group: </th>
                        <td>{SPECIAL_INTEREST}</td>
                    </tr>
                    <tr>
                    	<th>Freshmen Housing Waiver:</th>
                    	<td>{HOUSING_WAIVER}</td>
                    </tr>
                </table>
                </div>
        <h2>Applications</h2>
        {APPLICATIONS}
		
        <h2>Assignments</h2>
        {HISTORY}
        
        <h2>Check-in / Check-out</h2>
        {CHECKINS}
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
<div class="profileHeader">{NOTE_PAGER}</div>
<h1>Student Log</h1>
<div class="profileHeader">{LOG_PAGER}</div>
</center>
<!-- END notes -->