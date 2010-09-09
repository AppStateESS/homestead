<h3>Approve or Deny Room Change Request</h3>
{START_FORM}
<table>
  <tr>
    <td>Name:</td>
    <td>{FULLNAME}</td>
  </tr>
  <tr>
    <td>Username:</td>
    <td>{USERNAME}</td>
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
    <td>Student's Reason:</td>
    <td>{STUDENT_REASON}</td>
  </tr>
  <tr>
    <td>Student's Preferred Halls:</td>
    <td>
        <!-- BEGIN preferences -->
        <b>{PREFERENCE}</b><br />
        <!-- END preferences -->
    </td>
  </tr>
  <tr>
    <td>Action:</td>
    <td>{APPROVE_DENY_1} {APPROVE_DENY_1_LABEL} {APPROVE_DENY_2} {APPROVE_DENY_2_LABEL}</td>
  </tr>
  <tr id="reason_row">
    <td>{REASON_LABEL}</td>
    <td>{REASON}</td>
  </tr>
  <tr class="bed_selection">
    <td>{RESIDENCE_HALL_LABEL}</td>
    <td>{RESIDENCE_HALL}</td>
  </tr>
  <tr class="bed_selection">
    <td>{FLOOR_LABEL}</td>
    <td>{FLOOR}</td>
  </tr>
  <tr class="bed_selection">
    <td>{ROOM_LABEL}</td>
    <td>{ROOM}</td>
  </tr>
  <tr class="bed_selection">
    <td>{BED_LABEL}</td>
    <td>{BED}</td>
  </tr>
</table>
{SUBMIT_BUTTON}
{END_FORM}

<script type="text/javascript">
$(document).ready(function(){
    $("#phpws_form_approve_deny_approve").click(function(){
        $("#reason_row").hide();
        $(".bed_selection").show();
    });
    $("#phpws_form_approve_deny_deny").click(function(){
        $("#reason_row").show();
        $(".bed_selection").hide();
    });
    $("#reason_row").hide();
    $(".bed_selection").hide();
});
</script>
