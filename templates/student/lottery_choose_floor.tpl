<div class="hms">
  <div class="box">
    <div class="header"> <h1>{HALL} - Choose floor</h1> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <font color="red">{ERROR_MSG}<br /></font>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <font color="green">{SUCCESS_MSG}<br /></font>
        <!-- END success_msg -->
        <div style="float: right; height: 500; width: 300;">
           {EXTERIOR_IMAGE}<br /><br />
            <div style="border: 1px solid #AAAAAA;">
                <h2>Hall Features</h2>
                <ul>
                    <li>One</li>
                    <li>Two</li>
                    <li>Three</li>
                </ul>
            </div>
            <br /><br />
            {ROOM_PLAN_IMAGE}
            {MAP_IMAGE}<br />
            {OTHER_IMAGE}
        </div>
        
        <p>
        Please select a floor from the list below. Unavailable floors are shown in grey. Click the images to the right to view a larger version.
        </p>
        <ul>
        <!-- BEGIN floor_list -->
            <li style="color; {ROW_TEXT_COLOR}">{FLOOR}</li>
        <!-- END floor_list -->
        </ul>
    </div>
  </div>
</div>
