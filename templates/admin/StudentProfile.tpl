<script>
var emailLogParams = {EMAIL_LOG_PARAMS};
var noteParamsStudent = '{USERNAME}';
var userActivity = '{USER_ACTIVITY}';
</script>

<div class="col-md-4 col-md-push-8">
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
                        <small>{USERNAME} <a href="mailto:{USERNAME}@appstate.edu"><i class="fa fa-envelope-o"></i></a></small>
                        <br>
                         <!-- BEGIN phone_number -->
                        <small>{NUMBER}</small><br>
                        <!-- END phone_number -->
                    <h4>
            </div>
            <p class="description text-center">
                {GENDER}<br>
                {DOB}<br>
                Application Term: {APPLICATION_TERM}<br>
                Class: {CLASS}<br>
                Type: {TYPE}<br>
                Level: {STUDENT_LEVEL}<br>
                Admission Decision: {ADMISSION_DECISION}<br>
            </p>
            <p class="description text-center">
                <span class="label label-info {INTERNATIONAL}">International</span>
                <span class="label label-info {HONORS}">Honors</span>
                <span class="label label-info {TEACHING_FELLOW}">Teaching Fellow</span>
                <span class="label label-info {WATAUGA}">Watauga Global Member</span>
                <span class="label label-info {SPECIAL_INTEREST_SHOW}">{SPECIAL_INTEREST}</span>
                <span class="label label-info {HOUSING_WAIVER}">Freshmen Housing Waiver</span>
                <br>
            </p>
            <p class="description text-center">
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

<div class="col-md-8 col-md-pull-4">
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
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="header">
            <h2>Applications</h2>
        </div>
        <div class="content">
            {APPLICATIONS}
        </div>
    </div>
    <div class="card">
        <div class="header">
            <h2>Assignments</h2>
        </div>
        <div class="content">
            {HISTORY}
        </div>
    </div>
    <div class="card">
        <div class="header">
            <h2>Check-in / Check-out</h2>
        </div>
        <div class="content">
            {CHECKINS}
        </div>
    </div>
    <div class="card">
        <div class="header">
            <h2>Recent Notes</h2>
        </div>
        <div class="content">
            <div id="note-box"></div>
            <!-- BEGIN notes -->
            <div class="profileHeader">{NOTE_PAGER}</div>
            <!-- END notes -->
        </div>
    </div>
    <div class="card">
        <div class="header">
            <h2>Email Log</h2>
        </div>
        <div class="content">
            <!-- Email Log -->
            <div id="emailLogView"></div>
        </div>
    </div>
    <div class="card">
        <div class="header">
            <h2>Student Log</h2>
        </div>
        <div class="content">
            <div class="profileHeader">{LOG_PAGER}</div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{vendor_bundle}"></script>
<script type="text/javascript" src="{email_bundle}"></script>
<script type="text/javascript" src="{note_bundle}"></script>
