{START_FORM}
<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <font color="red"><i>{ERROR}</i><br /></font>
        This action will disband the following roommates:<br /><br />
        <table align="center">
            <tr>
                <th>First roommate:</th><td>{FIRST_ROOMMATE}</td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><th>Second roommate:</th><td>{SECOND_ROOMMATE}</td>
            </tr>
            <tr>
                <th>Full Name:</th><td align="left">{FIRST_ROOMMATE_NAME}</td><td></td><th>Full Name:</th><td align="left">{SECOND_ROOMMATE_NAME}</td>
            </tr>
            <tr>
                <th>Send email: </th><td>{EMAIL_FIRST_ROOMMATE}</td><td></td><th>Send email:</th><td>{EMAIL_SECOND_ROOMMATE}</td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td></td><td></td>
            </tr>
            <tr>
                <th>Third roommate:</th><td>{THIRD_ROOMMATE}</td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td<th>Fourth roommate:</th><td>{FOURTH_ROOMMATE}</td>
            </tr>
            <tr>
                <th>Full Name:</th><td align="left">{THIRD_ROOMMATE_NAME}</td><td></td><th>Full Name:</th><td align="left">{FOURTH_ROOMMATE_NAME}</td>
            </tr>
            <tr>
                <th>Send email: </th><td>{EMAIL_THIRD_ROOMMATE}</td><td></td><th>Send email:</th><td>{EMAIL_FOURTH_ROOMMATE}</td>
            </tr>
        </table>
        <br /><br />
        {SUBMIT}
    </div>
  </div>
</div>
{END_FORM}
