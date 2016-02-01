<h2>{NAME} - {BANNER_ID} <small class="text-muted">{TERM}</small></h2>

<div class="row">
    <div class="col-md-8">
        <div class="form-group pull-right">
        <div class="dropdown">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
            <i class="fa fa-cog"></i> Options
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
              <!-- BEGIN login-as-student -->
              <li role="presentation"><a role="menuitem" tabindex="-1" href="{LOGIN_AS_STUDENT_URI}"><i class="fa fa-sign-in"></i> Login as Student</a></li>
              <!-- BEGIN login-as-student -->
          </ul>
        </div>
        </div>

        <table class="table">
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
                <p>
                <strong>{ADDR_TYPE}</strong><br />
                {ADDRESS_L1}<br />
                <!-- BEGIN subadd2 -->{ADDRESS_L2}<br /><!-- END subadd2 -->
                <!-- BEGIN subadd3 -->{ADDRESS_L3}<br /><!-- END subadd3 -->
                {CITY}, {STATE} {ZIP}</p>
                <!-- END addresses -->
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <table class="table">
            <tr>
                <th>Assigned:</th>
                <td>
                    <!-- BEGIN not-assigned -->
                    <span class="text-danger" style="margin-top : 5px">Not assigned</span>
                    <a href="{NOT_ASSIGNED}" class="btn btn-xs btn-success"><i class="fa fa-plus"></i> Assign Student</a>
                    <!-- END not-assigned -->
                    <!-- BEGIN assignment -->
                    {ASSIGNMENT}
                    <!-- END assignment -->
                </td>
            </tr>
            <tr>
                <th>Roommate(s):</th>
                <!-- BEGIN confirmed -->
                <td class="success">
                    {ROOMMATE} <i class="fa fa-check fa-2x"></i>
                </td>
                <!-- END confirmed -->
                <!-- BEGIN pending -->
                <td class="warning">
                    {ROOMMATE} <i class="fa fa-warning fa-2x"></i>
                </td>
                <!-- END pending -->
                <!-- BEGIN error_status -->
                <td class="error">
                    {ROOMMATE} <i class="fa fa-warning fa-2x"></i>
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
                <th>RLC:</th>
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
</div>

<h2>Applications</h2>
{APPLICATIONS}

<h2>Assignments</h2>
{HISTORY}

<h2>Check-in / Check-out</h2>
{CHECKINS}

<div id="note_dialog" title="Enter a note for: {FIRST_NAME} {MIDDLE_NAME} {LAST_NAME}">
{START_FORM}
{NOTE}
<br>
{SUBMIT}
{END_FORM}
</div>

<!-- BEGIN notes -->
<h2>Recent Notes</h2>
[<a id=add_note>Add a note</a>]
<div class="profileHeader">{NOTE_PAGER}</div>
<h2>Student Log</h2>
<div class="profileHeader">{LOG_PAGER}</div>
<!-- END notes -->
