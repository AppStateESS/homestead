{START_FORM}
<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <font color="red"><i>{ERROR}</i></font>
        <table>
            <tr>
                <th>Residence Hall:</th><td>{BUILDING}</td>
            </tr>
            <tr>
                <th>Floor Number:</th><td>{FLOOR}</td>
            </tr>
            <tr>
                <th>Number of Rooms:</th><td>{ROOMS}</td>
            </tr>
            <tr>
                <th>Number of Bedrooms:</th><td>{NUMBER_OF_BEDROOMS}</td>
            </tr>
            <tr>
                <th>Number of Beds:</th><td>{NUMBER_OF_BEDS}</td>
            </tr>
            <tr>
                <th>Select a gender type: </th><td>{GENDER_TYPE}</td>
            </tr>
            <tr>
                <th>Is online: </th>
                <td>{IS_ONLINE_1} {IS_ONLINE_1_LABEL}</td>
                <td>{IS_ONLINE_2} {IS_ONLINE_2_LABEL}</td>
            </tr>
            <tr>
                <th>Freshman/Transfer Move-in Time: </th>
                <td>{FT_MOVEIN}</td>
            </tr>
            <tr>
                <th>Continuing Student Move-in Time: </th>
                <td>{C_MOVEIN}</td>
            </tr>
        </table>
        {SUBMIT}
    </div>
  </div>
</div>
{END_FORM}
