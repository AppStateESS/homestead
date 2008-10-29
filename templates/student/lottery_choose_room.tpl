<div class="hms">
  <div class="box">
    <div class="header"> <h1>{HALL_FLOOR} - Choose room</h1> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->
        <div style="float: right; width: 300px">
           {FLOOR_PLAN_IMAGE} 
        </div>
        <p>
        Choose a room from the list below by clicking on its room number. Rooms which are unavailable to you are shown in grey. Click the floor plan image to the right to see a larger version.
        </p>
        <table cellpadding="1" cellspacing="3" style="text-align: center">
            <tr>
                <th>Room</th>
                <th style="padding-left: 10px;">Available beds</th>
                <th style="padding-left: 10px;"># of Beds</th>
            </tr>
        <!-- BEGIN room_list -->
                <tr style="color: {ROW_TEXT_COLOR};">
                <td>{ROOM_NUM}</td>
                <td>{AVAIL_BEDS}</td>
                <td>{NUM_BEDS}</td>
            </tr>
        <!-- END room_list -->
        </table>
    </div>
  </div>
</div>
