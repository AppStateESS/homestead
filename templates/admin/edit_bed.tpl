<div class="hms">
  <div class="box">
    <div class="{TITLE_CLASS}"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <font color="red">{ERROR_MSG}<br /></font>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <font color="green">{SUCCESS_MSG}<br /></font>
        <!-- END success_msg -->
        <h2>Bed Properties</h2>
        {START_FORM}
        <table>
            <tr>
                <th>Hall Name:</th><td align="left">{HALL_NAME}</td>
            </tr>
            <tr>
                <th>Floor: </th><td align="left">{FLOOR_NUMBER}</td>
            </tr>
            <tr>
                <th>Room Number: </th><td align="left">{ROOM_NUMBER}</td>
            </tr>
            <tr>
                <th>Bedroom Label: </th><td>{BEDROOM_LABEL}</td>
            </tr>
            <tr>
                <th>Bed Letter: </th><td>{BED_LETTER}</td>
            </tr>
            <tr>
                <th>Phone Number: </th><td>828-266-{PHONE_NUMBER}</td>
            </tr>
            <tr>
                <th>Banner ID:</th><td>{BANNER_ID}</td>
            </tr>
            <tr>
                <th>RA Bed: </th><td>{RA_BED}</td>
            </tr>
            <tr>
                <th>Assigned to:</th><td>{ASSIGNED_TO}</td>
            </tr>
        </table>
        {SUBMIT}
        {END_FORM}
    </div>
  </div>
</div>
