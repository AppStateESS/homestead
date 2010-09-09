<h3>Approve or Deny Room Change Request</h3>
{START_FORM}
<table>
  <tr>
    <td>Name:
    </td>
    <td>{FULLNAME}
    </td>
  </tr>
  <tr>
    <td>Username:
    </td>
    <td>{USERNAME}
    </td>
  </tr>
  <tr>
    <td>Banner ID:
    </td>
    <td>{BANNER_ID}
    </td>
  </tr>
  <tr>
    <td>Phone number:</td>
    <td>{NUMBER}</td>
  </tr>
  <tr>
    <td>Current Assignment:</td>
    <td>{CURR_ASSIGN}</td>
  </tr>
  <tr>
  <tr>
    <td>Student's Reason:</td>
    <td>{STUDENT_REASON}</td>
  </tr>
  <tr>
    <td>Selected Bed:</td>
    <td>{BED}</td>
  </tr>
  <tr>
    <td>Approve or Deny</td>
    <td>
      <!-- BEGIN radio_approve -->
      {APPROVE_DENY_1} {APPROVE_DENY_1_LABEL} {APPROVE_DENY_2} {APPROVE_DENY_2_LABEL}
      <!-- END radio_approve -->
      <!-- BEGIN check_deny -->
      {APPROVE_DENY_LABEL} {APPROVE_DENY}
      <!-- END check_deny -->
    </td>
  </tr>
  <tr id="reason_row">
    <td>{REASON_LABEL}
    </td>
    <td>{REASON}
    </td>
  </tr>
</table>
{SUBMIT}
{END_FORM}

<script type="text/javascript">
$(document).ready(function(){
    $("#room_change_approval_approve_deny_approve").click(function(){
        $("#reason_row").hide();
    });
    $("#room_change_approval_approve_deny_deny").click(function(){
        $("#reason_row").show();
    });
    $("#reason_row").hide();
});
</script>
