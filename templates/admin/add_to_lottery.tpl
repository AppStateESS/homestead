<h2>Add a student to the lottery</h2>
<br />
<!-- BEGIN error_msg -->
<span class="error">{ERROR_MSG}<br /></span>
<!-- END error_msg -->

<!-- BEGIN success_msg -->
<span class="success">{SUCCESS_MSG}<br /></span>
<!-- END success_msg -->
{START_FORM}
<table>
    <tr>
        <td>{ASU_USERNAME_LABEL}</td>
        <td>{ASU_USERNAME}</td>
    </tr>
    <tr>
        <td>{PHYSICAL_DISABILITY_LABEL}</td>
        <td>{PHYSICAL_DISABILITY}</td>
    </tr>
    <tr>
        <td>{PSYCH_DISABILITY_LABEL}</td>
        <td>{PSYCH_DISABILITY}</td>
    </tr>
    <tr>
        <td>{MEDICAL_NEED_LABEL}</td>
        <td>{MEDICAL_NEED}</td>
    </tr>
    <tr>
        <td>{GENDER_NEED_LABEL}</td>
        <td>{GENDER_NEED}</td>
    </tr>
    <tr>
        <td>{SPECIAL_INTEREST_LABEL}</td>
        <td>{SPECIAL_INTEREST}</td>
    <tr>
        <td></td>
        <td align=right>{ENTER_INTO_LOTTERY}</td>
    </tr>
</table>
{END_FORM}
