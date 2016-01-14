<h2>{HALL_FLOOR}</h2>

<div class="row">
    <div class="col-md-7">
        <p>Choose a room from the list below by clicking on its room number. Rooms which are unavailable to you are shown in grey. Click the floor plan image to the right to see a larger version.</p>
        <table class="table table-striped table-hover">
            <tr>
                <th>Room</th>
                <th>Available beds</th>
                <th># of Beds</th>
            </tr>
            <!-- BEGIN room_list -->
            <tr class="{ROW_TEXT_COLOR}">
                <td>{ROOM_NUM} {ADA} {HEARING_IMPAIRED} {BATH_EN_SUITE}</td>
                <td>{AVAIL_BEDS}</td>
                <td>{NUM_BEDS}</td>
            </tr>
            <!-- END room_list -->
        </table>
    </div>
    <div class="col-md-5">
        {FLOOR_PLAN_IMAGE}
        <div class="panel panel-default">
            <div class="panel-body">
                <p class="col-md-12">
                    <i class="fa fa-wheelchair"></i> indicates a room is ADA compliant.
                </p>
                <p class="col-md-12">
                    <i class="fa fa-bell-slash"></i> indicates a room is equipped for the hearing impaired.
                </p>
                <p class="col-md-12">
                    <i class="fa fa-female">|</i><i class="fa fa-male"></i> indicates that a room has a bath en suite.
                </p>
            </div>
        </div>
    </div>
</div>
