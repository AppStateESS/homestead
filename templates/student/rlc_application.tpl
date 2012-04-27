{MENU_LINK}
<h1>Learning Community Application</h1>

<!-- BEGIN rlc_list -->
<div style="float: right;" class="rounded-box">
  <div class="boxheader">
    <h2 style="padding: 2px;">Assignment</h2>
  </div>
  <div style="padding: 3px;">
    {START_FORM} {RLC_LIST}<br /> {APPROVE} &nbsp; {DENY_APP}
    {END_FORM}
  </div>
</div>
<!-- END rlc_list -->

<h3>{FULL_NAME}</h3>

<table>
  <tr>
    <td>Term:</td>
    <td>{TERM}</td>
  </tr>
  <tr>
    <td>Student Type:</td>
    <td>{STUDENT_TYPE}</td>
  </tr>
  <tr>
    <td>Application Type:</td>
    <td>{APPLICATION_TYPE}</td>
  </tr>
  <tr>
    <td>RLC Preferences:</td>
    <td>
      <ol style="margin-top: -10px;">
        <li>{FIRST_CHOICE}</li>
        <!-- BEGIN second_choice -->
        <li>{SECOND_CHOICE}</li>
        <!-- END second_choice -->
        <!-- BEGIN third_choice -->
        <li>{THIRD_CHOICE}</li>
        <!-- END third_choice -->
      </ol>
    </td>
  </tr>
</table>

<div style="margin-top: 1em;">
  <strong>Specific communites chosen because:</strong>
</div>
<div>{WHY_SPECIFIC}</div>

<div style="margin-top: 1em;">
  <strong>Strengths and weaknesses:</strong>
</div>
<div>{STRENGTHS_AND_WEAKNESSES}</div>

<div style="margin-top: 1em;">
  <strong>Chose {FIRST_CHOICE} because:</strong>
</div>
<div>{WHY_FIRST_CHOICE}</div>

<!-- BEGIN second -->
<div style="margin-top: 1em;">
  <strong>Chose {SECOND_CHOICE} because:</strong>
</div>
<div>{WHY_SECOND_CHOICE}</div>
<!-- END second -->

<!-- BEGIN third -->
<div style="margin-top: 1em;">
  <strong>Chose {THIRD_CHOICE} because:</strong>
</div>
<div>{WHY_THIRD_CHOICE}</div>
<!-- END third -->
