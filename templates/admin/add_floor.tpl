{START_FORM}
<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <font color="red"><i>{ERROR}</i></font>
        <table>
            <tr>
                <th>Name of the Hall: </th><td>{HALL_NAME}</td>
            </tr>
            <tr>
                <th>Current number of floors: &nbsp;&nbsp;&nbsp;&nbsp;</th><td>{NUMBER_FLOORS}</td>
            </tr>
            <tr>
                <th>This floor number: </th><td>{FLOOR_NUMBER}</td>
            </tr>
            <tr>
                <th>Rooms per floor: </th><td>{ROOMS_PER_FLOOR}</td>
            </tr>
            <tr>
                <th>Number bedrooms per room:</th><td>{BEDROOMS_PER_ROOM}</td>
            </tr>
            <tr>
                <th>Number beds per bedroom:</th><td>{BEDS_PER_BEDROOM}</td>
            </tr>
            <tr>
                <th>Pricing Tier:</th><td>{PRICING_TIER}</td>
            </tr>
            <tr>
                <th>Use pricing tier:</th><td>{USE_PRICING_TIER}</td>
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
                <th>Is this floor online: </th><td>{IS_ONLINE_1} {IS_ONLINE_1_LABEL}</td>
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
