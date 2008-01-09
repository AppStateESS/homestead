<div class="hms">
  <div class="box">
    <div class="{TITLE_CLASS}"><h1>{TITLE}</h1></div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <font color="red">{ERROR_MSG}<br /></font>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <font color="green">{SUCCESS_MSG}<br /></font>
        <!-- END success_msg -->
        <h2>Hall Properties</h2>
        {START_FORM}
        <table>
            <tr>
                <th>Hall name: </th><td align="left">{HALL_NAME}</td>
            </tr>
            <tr>
                <th>Number of floors: </th><td align="left">{NUMBER_OF_FLOORS}</td>
            </tr>
            <tr>
                <th>Number of rooms: </th><td align="left">{NUMBER_OF_ROOMS}</td>
            </tr>
            <tr>
                <th>Number of beds: </th><td align="left">{NUMBER_OF_BEDS}</td>
            </tr>
            <tr>
                <th>Number of assignees: </th><td align="left">{NUMBER_OF_ASSIGNEES}</td>
            </tr>
            <tr>
                <th>Gender: </th><td align="left">{GENDER_TYPE}</td>
            </tr>
            <tr>
                <th>Is online: </th><td align="left">{IS_ONLINE}</td>
            </tr>
            <tr>
                <th>Air Conditioned: </th><td align="left">{AIR_CONDITIONED}</td>
            </tr>
        </table>
        {SUBMIT}
        {END_FORM}
        <br /><br />
        {FLOOR_PAGER}
    </div>
  </div>
</div>
