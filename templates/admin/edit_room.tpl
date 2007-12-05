<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <font color="red">{ERROR_MSG}<br /></font>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <font color="green">{SUCCESS_MSG}<br /></font>
        <!-- END success_msg -->
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
                <th>Number of bedrooms: &nbsp;&nbsp;</th><td align="left">{NUMBER_OF_BEDROOMS}</td>
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
                <th>Is online: </th>
                <td align="left">{IS_ONLINE_1} {IS_ONLINE_1_LABEL}</td>
                <td align="left">{IS_ONLINE_2} {IS_ONLINE_2_LABEL}</td>
            </tr>
            <tr>
                <th>Is reserved: </th>
                <td align="left">{IS_RESERVED_1} {IS_RESERVED_1_LABEL}</td>
                <td align="left">{IS_RESERVED_2} {IS_RESERVED_2_LABEL} </td>
            </tr>
            <tr>
                <th>Reserved for RA: </th>
                <td>{RA_ROOM_1} {RA_ROOM_1_LABEL}</td>
                <td>{RA_ROOM_2} {RA_ROOM_2_LABEL}</td>
            </tr>
            <tr>
                <th>Private Room:</th>
                <td>{PRIVATE_ROOM_1} {PRIVATE_ROOM_1_LABEL}</td>
                <td>{PRIVATE_ROOM_2} {PRIVATE_ROOM_2_LABEL}</td>
            </tr>
            <tr>
                <th>Is medical: </th>
                <td align="left">{IS_MEDICAL_1} {IS_MEDICAL_1_LABEL}</td>
                <td align="left">{IS_MEDICAL_2} {IS_MEDICAL_2_LABEL} </td>
            </tr>
            <tr>
                <th>Is a Lobby:</th>
                <td>{IS_LOBBY_1} {IS_LOBBY_1_LABEL}</td>
                <td>{IS_LOBBY_2} {IS_LOBBY_2_LABEL}</td>
            </tr>
            <tr>
                <th>Is part of a suite: </th>
                <td align="left">{IS_IN_SUITE}</td>
            </tr>
            <!-- BEGIN suite -->
            <tr>
                <th>Other rooms in suite:</th>
                <td align="left">
                    <ul>
                <!-- BEGIN SUITE_ROOM_LIST -->
                    <li>{SUITE_ROOM}</li>
                <!-- END SUITE_ROOM_LIST -->
                    </ul>
                </td>
            </tr>
            <!-- END suite -->
        </table>
        <br />
        {SUBMIT}
        <br /><br />
        {ASSIGNMENT_PAGER}
    </div>
  </div>
</div>
{END_FORM}
