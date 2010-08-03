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
    <th>Student's Reason</th>
    <td>{STUDENT_REASON}</td>
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
  <tr class="bed_selection">
    <th>{RESIDENCE_HALL_LABEL}
    </th>
    <td>{RESIDENCE_HALL}
    </td>
  </tr>
  <tr class="bed_selection">
    <th>{FLOOR_LABEL}
    </th>
    <td>{FLOOR}
    </td>
  </tr>
  <tr class="bed_selection">
    <th>{ROOM_LABEL}
    </th>
    <td>{ROOM}
    </td>
  </tr>
  <tr class="bed_selection">
    <th>{BED_LABEL}
    </th>
    <td>{BED}
    </td>
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
