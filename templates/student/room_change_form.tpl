<h2>Room Change Request</h2>

<p>
This form allows you to request to move to a different room. Your request must be approved by the Residence Director (RD) for your residence hall and by the University Housing Assignments office. Requests are granted based on available space and may be denied for any reason.
Students completing unauthorized room changes will be required to return to their assigned space, may be denied the opportunity to participate in any other room changes for the academic year, and will be assessed a $35 administrative charge.
</p>

<p>
You will be notified via your ASU email address when your request is approved or denied. <strong>Do not</strong> begin moving your belongings until you receive the final approval from University Housing.
Once your receive your room change confirmation from the Housing Assignments Office, you must complete your move and be checked into your new assignment within 48 hours. 
</p>

<p>
<strong>Note:</strong> Requests submitted on Fridays will not be processed until the following Monday.
</p>

{START_FORM}
<h3>Contact Info</h3>
<p>Your RD and the assignments office will use this extra contact information (in addition to your ASU email address) to reach you in case there is a question regarding your request.</p>
<p>
{CELL_NUM_LABEL}: {CELL_NUM} {CELL_OPT_OUT} <i style="color: #696969;">(or check the box to opt out)</i>
</p>

<h3>Hall Preferences</h3>
<p>
<ul>
    <li>You are not guaranteed a space in either of your preferred halls.</li>
    <li>Room changes are subject to space availability. Your RD will work with you to select a space from available vacancies.</li>
    <li><strong>The room fee may increase/decrease depending upon your selection of residence hall.</strong></li>
</ul>
</p>

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

<h3>Room Swap</h3>
<p>If you would like to switch rooms with another student, enter their ASU email address below.</p>
<p>
{SWAP_WITH_LABEL}:
{SWAP_WITH}@appstate.edu
</p>

<h3>Reason</h3>
<p>In the box below, please provide a <em>short</em> explanation of why you would like to move to a different room. A few sentences are sufficient. You should also indicate any special circumstances (i.e. you want to switch rooms with a friend on your floor).</p>
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
