{START_FORM}
<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <font color="red"><i>{ERROR}</i></font>
        <table>
            <tr>
                <td>You have requested: </td><td>{FIRST_NAME} {LAST_NAME}</td>
            </tr>
            <tr>
                <td>&nbsp;</td><td></td>
            </tr>
            <tr>
                <td>That person's ASU email address is: &nbsp;&nbsp;&nbsp;&nbsp;</td><td>{USERNAME}@appstate.edu</td>
            </tr>
            <tr>
                <td>&nbsp;</td><td></td>
            </tr>
            <tr>
                <td>Is this correct?</td><td></td>
            </tr>
        </table>
        <br />
        {SUBMIT} {CANCEL}
    </div>
  </div>
</div>
{END_FORM}
