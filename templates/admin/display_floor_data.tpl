{START_FORM}
<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <font color="red"><i>{ERROR}</i></font>
        <table>
            <tr>
                <th>Residence Hall: </th><td>{BUILDING}</td>
            </tr>
            <tr>
                <th>Floor Number: </th><td>{FLOOR}</td>
            </tr>
            <tr>
                <th>Number of Rooms: </th><td>{ROOMS}</td>
            </tr>
            <tr>
                <th>Number of Bedrooms per room: </th><td>{BEDROOMS_PER_ROOM}</td>
            </tr>
            <tr>
                <th>Number of Beds per bedroom: </th><td>{BEDS_PER_BEDROOM}</td>
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
                <th>Is online: </th><td>{IS_ONLINE_1} {IS_ONLINE_1_LABEL}</td>
            </tr>
            <tr>
                <th></th><td>{IS_ONLINE_2} {IS_ONLINE_2_LABEL}</td>
            </tr>
        </table>
        {SUBMIT}
    </div>
  </div>
</div>
{END_FORM}
