<div class="hms">
  <div class="box">
    <div class="header"><h1>Congratulations</h1></div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->
        <div style="float: right; height: 500px; width: 300px">
            <img src="images/hms/llc01.jpg">
            <br /><br />
            <div style="border: 1px solid #AAAAAA">
                <h2 style="background-color: #E3E3E3; color: #222222; margin: 0; padding: 6px 5px;">Housing Facts</h2>
                <ul>
                    <li>There are 21 residence halls.</li>
                    <li>ASU can house 5622 students total.</li>
                    <li>Residence Life employs approx. 125 resident assistants each semester.</li>
                </ul>
            </div>
        </div>
        <p>
        Congratulations, you have been selected for reapplication for {TERM}!
        </p>
        <p>
        You may select any room which is currently available. Browse available rooms by selecting a residence hall below.
        </p>
        <ul>
        <!-- BEGIN hall_list -->
            <li style="color: {ROW_TEXT_COLOR}">{HALL_NAME}</li>
        <!-- END hall_list -->
        </ul>
    </div>
  </div>
</div>
