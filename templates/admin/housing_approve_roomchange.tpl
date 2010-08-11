<h3>Approve or Deny Room Change Request</h3>
{START_FORM}
<table>
  <tr>
    <th>Name:
    </th>
    <td>{FULLNAME}
    </td>
  </tr>
  <tr>
    <th>Username:
    </th>
    <td>{USERNAME}
    </td>
  </tr>
  <tr>
    <th>Student's Reason:</th>
    <td>{STUDENT_REASON}</td>
  </tr>
  <tr>
    <th>Selected Bed:</th>
    <td>{BED}</td>
  </tr>
  <tr>
    <th>Approve or Deny
    </th>
    <td>{APPROVE_DENY_1} {APPROVE_DENY_1_LABEL} {APPROVE_DENY_2} {APPROVE_DENY_2_LABEL}
    </td>
  </tr>
  <tr id="reason_row">
    <th>{REASON_LABEL}
    </th>
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
