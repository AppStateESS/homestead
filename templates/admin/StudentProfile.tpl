<script>
var emailLogParams = {EMAIL_LOG_PARAMS};
var noteParamsStudent = '{USERNAME}';
</script>

<div class="col-md-8">
    <div class="card">
        <div class="content">
            <div class="row">
                <div class="col-md-12">
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
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
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

            <h2>Recent Notes</h2>
            <div id="note-box"></div>
            <!-- BEGIN notes -->
            <div class="profileHeader">{NOTE_PAGER}</div>
            <!-- END notes -->

            <!-- Email Log -->
            <h2>Email Log</h2>
            <div id="emailLogView"></div>

            <h2>Student Log</h2>
            <div class="profileHeader">{LOG_PAGER}</div>

        </div>
    </div>
</div>
<div class="col-md-4">
    <div class="card card-user">
        <div class="image">
            <img src="mod/hms/img/newland.jpg"/>
        </div>
        <div class="content">
            <div class="author">
                    <img class="avatar border-gray" src="mod/hms/img/Logo.png"/>
                    <h4 class="title">
                        {NAME}
                        <br>
                        <small>{BANNER_ID}</small>
                        <br>
                        <small>{USERNAME}</small>
                    <h4>
            </div>
            <p class="description text-center">
                Admission Decision: {ADMISSION_DECISION}<br>
                Application Term: {APPLICATION_TERM}<br>
                Class: {CLASS}<br>
                Type: {TYPE}<br>
                Level: {STUDENT_LEVEL}

            </p>
            <p class="description text-center">
                {GENDER}<br>
                {DOB}<br>
                International: {INTERNATIONAL}<br>
                <a href="mailto:{USERNAME}@appstate.edu">{USERNAME}@appstate.edu</a><br>
                 <!-- BEGIN phone_number -->
                {NUMBER}<br>
                <!-- END phone_number -->
                <br>
                <!-- BEGIN addresses -->
                <strong>{ADDR_TYPE}</strong><br>
                {ADDRESS_L1}<br>
                <!-- BEGIN subadd2 -->
                {ADDRESS_L2}<br>
                <!-- END subadd2 -->
                <!-- BEGIN subadd3 -->
                {ADDRESS_L3}<br>
                <!-- END subadd3 -->
                {CITY}, {STATE} {ZIP}<br>
                <!-- END addresses -->

            </p>
        </div>
    </div>
</div>

<script type="text/javascript" src="{vendor_bundle}"></script>
<script type="text/javascript" src="{email_bundle}"></script>
<script type="text/javascript" src="{note_bundle}"></script>
