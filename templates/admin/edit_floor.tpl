<div class="hms">
  <div class="box">
    <div class="{TITLE_CLASS}"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->
        <h2>Floor Properties</h2>
        {START_FORM}
        <table>
            <tr>
                <th>Hall Name:</th><td align="left">{HALL_NAME}</td>
            </tr>
            <tr>
                <th>Floor: </th><td align="left">{FLOOR_NUMBER}</td>
            </tr>
            <tr>
                <th>Number of rooms: &nbsp;&nbsp;</th><td align="left">{NUMBER_OF_ROOMS}</td>
            </tr>
            <tr>
                <th>Number of beds: </th><td>{NUMBER_OF_BEDS}</td>
            </tr>
            <tr>
                <th>Number of occupants: </th><td>{NUMBER_OF_ASSIGNEES}</td>
            </tr>
            <tr>
                <th>Gender type: </th>
                <!-- BEGIN gender_radio_buttons -->
                <td align="left">{GENDER_TYPE}</td>
                <!-- END gender_radio_button -->
            </tr>
            <tr>
                <th>Is online: </th>
                <td align="left">{IS_ONLINE}</td>
            </tr>
            <tr>
                <th>Freshmen Move-in Time: </th><td>{FT_MOVEIN_TIME}</td>
            </tr>
            <tr>
                <th>Returning Move-in Time: </th><td>{RT_MOVEIN_TIME}</td>
            </tr>
            <tr>
                <th>Reserved for RLC: </th><td>{FLOOR_RLC_ID}</td>
            </tr>
            <tr>
                <th>Floor plan:</th><td>{FILE_MANAGER}</td>
            </tr>
        </table>
        <br />
        {SUBMIT_FORM}
        {END_FORM}
        <br /><br />
        {ROOM_PAGER}
    </div>
  </div>
</div>
