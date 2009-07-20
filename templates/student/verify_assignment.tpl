<div class="hms">
  <div class="box">
    <h1>Verify Your Housing Status</h1>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->

        <font color="red"><b>Note: This information is not final and is subject to change.</b></font>
        The information displayed below only represents your current status within the Housing Management System and is listed for
        your convenience only. The room assignment, Learning Community assignment and
        other information displayed below are subject to change.<br /><br />
        
        <table>
                <!-- BEGIN assignment -->
            <tr>
                <th rowspan="4">Room assignment:</th>
            </tr>
                <tr><td align="left">{ASSIGNMENT}</td></tr>
                <tr><td align="left">Move-in time: {MOVE_IN_TIME}</td></tr>
                <!-- END assignment -->
                <!-- BEGIN no_assignment -->
                <td align="left">{NO_ASSIGNMENT}</td>
                <!-- END no_assignment -->
            <tr>
                <!-- BEGIN roommate -->
                <th>Roommate:</th><td align="left">{ROOMMATE}</td>
                <!-- END roommate -->
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
