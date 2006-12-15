{START_FORM}
<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <font color="red"><i>{MESSAGE}</i></font>
        {REDO} {NEWLINES}
        <table>
            <tr>
                <th>First Name: </th><td>{FIRST_NAME}</td>
            </tr>
            <tr>
                <th>Last Name: </th><td>{LAST_NAME}</td>
            </tr>
            <tr>
                <th>ASU E-mail: </th><td>{ASU_EMAIL_ADDRESS}@appstate.edu</td>
            </tr>
            <tr><th></th><td></td></tr>
            <tr>
                <th>Classification for </th><td></td>
            </tr>
            <tr><th></th><td></td></tr>
            <tr>
                <th>Application Term: </th><td>{CLASSIFICATION_FOR_TERM}</td>
            </tr>
            <tr><th></th><td></td></tr>
            <tr>
                <th>Lifestyle Option: </th><td>{LIFESTYLE_OPTION}</td>
            </tr>
            <tr><th></th><td></td></tr>
            <tr>
                <th>Preferred Bedtime: </th><td>{PREFERRED_BEDTIME}</td>
            </tr>
            <tr><th></th><td></td></tr>
            <tr>
                <th>Room Condition: </th><td>{ROOM_CONDITION}</td>
            </tr>
            <tr><th> </th><td> </td></tr>
            <tr>
                <th>In a relationship: </th><td>{RELATIONSHIP}</td>
            </tr>
            <tr><th> </th><td> </td></tr>
            <tr>
                <th>Currently employed: </th><td>{EMPLOYED}</td>
            </tr>
            <tr><th> </th><td> </td></tr>
            <tr>
                <th>Are you a member of</th><td></td>
            </tr>
            <tr>
                <th>a Residential Learning Community? &nbsp;&nbsp;&nbsp;&nbsp;</th><td>{RLC_INTEREST_1} {RLC_INTEREST_1_LABEL}</td>
            </tr>
            <tr>
                <th></th><td>{RLC_INTEREST_2} {RLC_INTEREST_2_LABEL}</td>
            </tr>
        </table>
        <br /><br />
        {SUBMIT}
    </div>
  </div>
</div>
{END_FORM}
