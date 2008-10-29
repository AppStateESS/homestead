{START_FORM}
<div class="hms">
  <div class="box">
    <div class="{TITLE_CLASS}"><h1>{TITLE}</h1></div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->
        
        {MESSAGE}<br /><br />
        <table>
            <tr>
                <th align="left">ASU Email:</th><td>{USERNAME}@appstate.edu</td>
            </tr>
            <tr>
                <th align="left">{RESIDENCE_HALL_LABEL}</th>
                <td>{RESIDENCE_HALL}</td>
            </tr>
                <th align="left">{FLOOR_LABEL}</th>
                <td>{FLOOR}</td>
            <tr>
                <th align="left">{ROOM_LABEL}</th>
                <td>{ROOM}</td>
            </tr>
            <tr id="bed_row" style="{BED_STYLE}">
                <th align="left">{BED_LABEL}</th>
                <td>{BED}</td>
            </tr>
            <tr id="link_row" style="{LINK_STYLE}">
                <td>&nbsp;</td>
                <td><a href="javascript:showBedDrop();">Show bed</a></td>
            </tr>
            <tr>
                <th align="left">{MEAL_PLAN_LABEL}</th>
                <td>{MEAL_PLAN}</td>
            </tr>
        </table>
        <p>Note: </p>
        {NOTE}
        <br />
        {SUBMIT}
    </div>
  </div>
</div>
{END_FORM}
