<div class="hms">
  <div class="box">
    <div class="box-content">
        <h1>{FIRST_NAME} {MIDDLE_NAME} {LAST_NAME} -- {BANNER_ID}</h1><h2>Login as this student [ {LOGIN_AS_STUDENT} ]</h2>
        <br>
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
                    
                    <tr>
                        <th>Class</th>
                        <td>{CLASS}</td>
                    </tr>
                    
                    <tr>
                        <th>Phone Number</th>
                        <td>({PHONE_AC}) {PHONE_NUMBER}</td>
                    </tr>
                    
                    <tr>
                        <th>Cellphone</th>
                        <td>{CELLPHONE}</td>
                    </tr>
                    
                    <tr>
                       <th>Addresses</th>
                        <td>
                            <!-- BEGIN pr_address -->
                                Permanent address:<br />
                                {PR_ADDRESS_L1}<br />
                                
                                <!-- BEGIN pr_address_2 -->
                                {PR_ADDRESS_L2}<br />
                                <!-- END pr_address_2 -->
                                <!-- BEGIN pr_address_3 -->
                                {PR_ADDRESS_L3}<br />
                                <!-- END pr_address_3 -->

                                {PR_ADDRESS_CITY}, {PR_ADDRESS_STATE} {PR_ADDRESS_ZIP}<br />
                                <!-- END pr_address -->
                                <!-- BEGIN address_space -->
                                {ADDRESS_SPACE}<br />
                                <!-- END  address_space -->
                                <!-- BEGIN ps_address -->
                                Student address:<br />
                                {PS_ADDRESS_L1}<br />

                                <!-- BEGIN ps_address_2 -->
                                {PS_ADDRESS_L2}<br />
                                <!-- END ps_address_2 -->

                                <!-- BEGIN ps_address_3 -->
                                {PS_ADDRESS_L3}<br />
                                <!-- END ps_address_3 -->
                                {PS_ADDRESS_CITY}, {PS_ADDRESS_STATE} {PS_ADDRESS_ZIP}<br />
                            <!-- END ps_address -->
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
                    <!-- BEGIN application_term -->
                    <tr>
                        <th>Application Term:</th>
                        <td>{APPLICATION_TERM} ({HUMAN_PARSEABLE_APPLICATION_TERM})</td>
                    </tr>
                    <!-- END application_term -->
                    <tr>
                        <th>Have Application:</th>
                        <td>{APPLICATION_RECEIVED}  {APPLICATION}
                    </tr>
                    <tr>
                        <th>Assigned:</th>
                        <td>{ASSIGNED}  [{ROOM_ASSIGNMENT}]</td>
                    </tr>
                    <tr>
                        <th>Roommate:</th>
                        <td>{ROOMMATE}</td>
                    </tr>
                    <!-- BEGIN requested_roommate -->
                    <tr>
                        <td></td>
                        <td>{REQUESTED_ROOMMATE}</td>
                    </tr>
                    <!-- END requested_roommate -->
                    <tr>
                        <th>RLC:</td>
                        <td>{RLC_STATUS}</td>
                    </tr>
                    <tr>
                        <th>Meal Option:</th>
                        <td>{MEAL_PLAN}</td>
                    </tr>
                </table>
                </div>
                </td>
            </tr>
        </table>
        </div>
    </div>
</div>
<div class="flora" id="note_dialog" title="Enter a note for: {FIRST_NAME} {MIDDLE_NAME} {LAST_NAME}">
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
