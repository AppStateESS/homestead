<div class="hms">
  <div class="box">
    <h1>Verify Your Housing Status</h1>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <font color="red">{ERROR_MSG}<br /></font>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <font color="green">{SUCCESS_MSG}<br /></font>
        <!-- END success_msg -->

        <font color="red"><b>Note: This information is not final and is subject to change.</b></font>
        The information displayed below only represents your current status within the Housing Management System and is listed for
        your convienince only. The room assignment, Learning Community assignment and
        other information displayed below are subject to change.<br /><br />
        
        <table>
                <!-- BEGIN assignment -->
            <tr>
                <th rowspan="4">Room assignment:</th>
            </tr>
                <tr><td align="left">{ASSIGNMENT}</td></tr>
                <tr><td align="left">Room phone number: 828-266-{ROOM_PHONE}</td></tr>
                <tr><td align="left">Move-in time: {MOVE_IN_TIME}</td></tr>
                <!-- END assignment -->
                <!-- BEGIN no_assignment -->
                <td align="left">{NO_ASSIGNMENT}</td>
                <!-- END no_assignment -->
            <tr>
                <th>Roommate:</th><td align="left">{ROOMMATE}</td>
            </tr>
            <tr>
                <th>Learning Community:</th><td align="left">{RLC}</td>
            </tr>
        </table>
        <br />
        {MENU_LINK}
    </div>
  </div>
</div>
