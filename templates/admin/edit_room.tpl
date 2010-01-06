<div class="hms">
  <div class="box">
    <div class="{TITLE_CLASS}"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <h2>Room Properties</h2>
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
                <th>Pricing Tier: </th><td>{PRICING_TIER}</td>
            </tr>
            <tr>
                <th>Number of beds: </th><td>{NUMBER_OF_BEDS}</td>
            </tr>
            <tr>
                <th>Number of occupants: </th><td>{NUMBER_OF_ASSIGNEES}</td>
            </tr>
            <tr>
                <th>Gender type: </th>
                <!-- BEGIN gender_message -->
                <td>{GENDER_MESSAGE}</td>
                <td>{GENDER_REASON}</td>
                <!-- END gender_message -->
                <!-- BEGIN gender_radio_buttons -->
                <td align="left">{GENDER_TYPE}</td>
                <!-- END gender_radio_button -->
            </tr>
            <tr>
                <th>Default Gender: </th>
                <td>{DEFAULT_GENDER}</td>
            </tr>
            <tr>
                <th>Is online: </th>
                <td align="left">{IS_ONLINE}</td> 
            </tr>
            <tr>
                <th>Is reserved: </th>
                <td align="left">{IS_RESERVED} {IS_RESERVED_LABEL}</td>
            </tr>
            <tr>
                <th>Reserved for RA: </th>
                <td>{RA_ROOM} {RA_ROOM_LABEL}</td>
            </tr>
            <tr>
                <th>Private Room:</th>
                <td>{PRIVATE_ROOM} {PRIVATE_ROOM_LABEL}</td>
            </tr>
            <tr>
                <th>Is medical: </th>
                <td align="left">{IS_MEDICAL} {IS_MEDICAL_LABEL}</td>
            </tr>
            <tr>
                <th>Is an Overflow Room:</th>
                <td>{IS_OVERFLOW} {IS_OVERFLOW_LABEL}</td>
            </tr>
        </table>
        {SUBMIT}
        {END_FORM}
        <br /><br />
        {BED_PAGER}
    </div>
  </div>
</div>
