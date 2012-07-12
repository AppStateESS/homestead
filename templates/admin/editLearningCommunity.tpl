<h1>Add/Edit a Learning Community</h1>

<!-- BEGIN community -->
<h2>{COMMUNITY}</h2>
<!-- END community -->

{START_FORM}
<fieldset>
  <legend>General Settings</legend>
  <table>
    <tr>
      <td>Community Name:&nbsp;</td>
      <td>{COMMUNITY_NAME}</td>
    </tr>
    <tr>
      <td>Abbreviation:</td>
      <td>{ABBREVIATION}</td>
    </tr>
    <tr>
      <td>Capacity:</td>
      <td>{CAPACITY}</td>
    </tr>
  </table>
</fieldset>

<br />

<fieldset>
  <legend>Move-in Times</legend>
  <div>{F_MOVEIN_TIME_LABEL}: {F_MOVEIN_TIME}</div>
  <div>{T_MOVEIN_TIME_LABEL}: {T_MOVEIN_TIME}</div>
  <div>{C_MOVEIN_TIME_LABEL}: {C_MOVEIN_TIME}</div>
</fieldset>

<br />

<fieldset>
  <legend>Student Types Allowed</legend>
  <table>
    <tr>
      <td>First-time Application Allowed Student Types:</td>
      <td>{STUDENT_TYPES} (comma separated list, i.e.: 'F,C,T')</td>
    </tr>
    <tr>
      <td>Re-application Allowed Student Types:</td>
      <td>{REAPPLICATION_STUDENT_TYPES} (comma separated list,
        i.e.: 'F,C,T')</td>
    </tr>
    <tr>
      <td>{MEMBERS_REAPPLY_LABEL}</td>
      <td>{MEMBERS_REAPPLY}</td>
    </tr>
  </table>
</fieldset>

<br />

<fieldset>
  <legend>Application Questions</legend>
  <table>
    <tr>
      <td>{FRESHMEN_QUESTION_LABEL}</td>
      <td>{FRESHMEN_QUESTION}</td>
    </tr>
    <tr>
      <td>{RETURNING_QUESTION_LABEL}</td>
      <td>{RETURNING_QUESTION}</td>
    </tr>
  </table>
</fieldset>

<br />

<fieldset>
  <legend>Terms &amp; Conditions</legend>
  <table>
    <tr>
      <p>This text will be included in the invitation email sent to accepted students.</p>
      <td>{TERMS_CONDITIONS}</td>
    </tr>
  </table>
</fieldset>
<br />
{SUBMIT} {END_FORM}
