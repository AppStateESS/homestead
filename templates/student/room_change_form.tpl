<h2>Room Change Request</h2>

<p>
This form allows you to request to move to a different room. Your request must be approved by the Residence Director (RD) for your residence hall and by the University Housing Assignments office. Requests are granted based on available space and may be denied for any reason.
</p>

<p>
You will be notified via your ASU email address when your request is approved or denied. Do not begin moving your belongings until you receive the final approval from University Housing. 
</p>

{START_FORM}
<h3>Contact Info</h3>
<p>Your RD and the assignments office will use this extra contact information (in addition to your ASU email address) to reach you in case there is a question regarding your request.</p>
<p>
{CELL_NUM_LABEL}: {CELL_NUM} {CELL_OPT_OUT} <i style="color: #696969;">(or check the box to opt out)</i>
</p>

<h3>Hall Preferences</h3>
<p>Your RD will take these preferences into consideration when finding an available room for you to move to.</p>

<table>
    <tr>
        <td>{FIRST_CHOICE_LABEL}: </td>
        <td>{FIRST_CHOICE}</td>
    </tr>
    <tr>
        <td>{SECOND_CHOICE_LABEL}:</td>
        <td>{SECOND_CHOICE}</td>
    </tr>
</table>


<h3>Reason</h3>
<p>In the box below, please provide a <em>short</em> explanation of why you would like to move to a different room. A few sentences is sufficient. You should also indicate any special circumstances (i.e. you want to switch rooms with a friend on your floor).</p>
{REASON}

<p>{SUBMIT}</p>
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
