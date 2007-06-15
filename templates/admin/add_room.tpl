{START_FORM}
<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <font color="red"><i>{ERROR}</i></font><br />
        <br />
        <b>You are adding room {ROOM_NUMBER} to {HALL_NAME}, floor {FLOOR_NUMBER}.</b><br />
        <br />
        <table>
            <tr>
                <th>Name of the Hall: </th><td>{HALL_NAME}</td>
            </tr>
            <tr>
                <th>Floor number: </th><td>{FLOOR_NUMBER}</td>
            </tr>
            <tr>
                <th>Room Number: </th><td>{ROOM_NUMBER}</td>
            </tr>
            <tr>
                <th>Number bedrooms per room:</th><td>{BEDROOMS_PER_ROOM}</td>
            </tr>
            <tr>
                <th>Number beds per bedroom:</th><td>{BEDS_PER_BEDROOM}</td>
            </tr>
            <tr>
                <th>Pricing Tier: </th><td>{PRICING_TIER}</td>
            </tr>
            <tr>
                <th>Select a gender type: </th><td>{GENDER_TYPE_1} {GENDER_TYPE_1_LABEL}</td>
            </tr>
            <tr>
                <th></th><td>{GENDER_TYPE_2} {GENDER_TYPE_2_LABEL}</td>
            </tr>
            <tr>
                <th></th><td>{GENDER_TYPE_3} {GENDER_TYPE_3_LABEL}</td>
            </tr>
            <tr>
                <th>Reserved for freshmen: </th><td>{FRESHMAN_RESERVED_1} {FRESHMAN_RESERVED_1_LABEL}</td>
            </tr>
            <tr>
                <td></td><td>{FRESHMAN_RESERVED_2} {FRESHMAN_RESERVED_2_LABEL}</td>
            </tr>
            <tr>
                <th>Is this room online: </th><td>{IS_ONLINE_1} {IS_ONLINE_1_LABEL}</td>
            </tr>
            <tr>
                <th></th><td>{IS_ONLINE_2} {IS_ONLINE_2_LABEL}</td>
            </tr>
            <tr>
                <th>Room is a medical room: </th><td>{IS_MEDICAL_1} {IS_MEDICAL_1_LABEL} </td>
            </tr>
            <tr>
                <td></td><td>{IS_MEDICAL_2} {IS_MEDICAL_2_LABEL}</td>
            </tr>
            <tr>
                <th>Room is reserved: </th><td>{IS_RESERVED_1} {IS_RESERVED_1_LABEL}</td>
            </tr>
            <tr>
                <td></td><td>{IS_RESERVED_2} {IS_RESERVED_2_LABEL}</td>
            </tr>
             <tr>
                <th>RA Room: </th><td>{RA_ROOM_1} {RA_ROOM_1_LABEL}</td>
            </tr>
            <tr>
                <td></td><td>{RA_ROOM_2} {RA_ROOM_2_LABEL}</td>
            </tr>
            <tr>
                <th>Room is private: </th><td>{PRIVATE_ROOM_1} {PRIVATE_ROOM_1_LABEL}</td>
            </tr>
            <tr>
                <td></td><td>{PRIVATE_ROOM_2} {PRIVATE_ROOM_2_LABEL}</td>
            </tr>
            <tr>
                <th>Room is a lobby: </th><td>{IS_LOBBY_1} {IS_LOBBY_1_LABEL}</td>
            </tr>
            <tr>
                <td></td><td>{IS_LOBBY_2} {IS_LOBBY_2_LABEL}</td>
            </tr>
        </table>
        {SUBMIT}
    </div>
  </div>
</div>
{END_FORM}
