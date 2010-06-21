<h3>Room Change Request</h3>
{START_FORM}
<table>
  <tr>
    <th>{CELL_NUM_LABEL}
    </th>
    <td>{CELL_NUM} {CELL_OPT_OUT} <i style="color: #696969;">(or check the box to opt out)</i>
    </td>
  </tr>
  <tr>
    <th>Preferences
    </th>
    <td>TODO
    </td>
  </tr>
  <tr>
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
    $("#room_change_request_cell_opt_out").change(function(){
        if($(this).is(":checked")){
            $("#room_change_request_cell_num").attr('disabled', true);
        } else {
            $("#room_change_request_cell_num").attr('disabled', false);
        }
     });
});
</script>
