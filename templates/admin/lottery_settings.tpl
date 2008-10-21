<script>

function toggleState()
{
    if($('#phpws_form_phase_radio_single_phase').attr('checked') == true){
        // set all single phase text boxes to enabled
        $('.single_phase').attr('disabled', false);

        // set all multi-phase text boxes to disabled
        $('.multi_phase').attr('disabled', true);
    }else{
        $('.single_phase').attr('disabled', true);
        $('.multi_phase').attr('disabled', false);
    }
}

$(document).ready(function() {
    $('.lotterystate').bind('change', toggleState);
});

</script>

<div class="hms">
  <div class="box">
    <div class="title"> <h1>Lottery Settings</h1> </div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <font color="red">{ERROR_MSG}<br /></font>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <font color="green">{SUCCESS_MSG}<br /></font>
        <!-- END success_msg -->
        {START_FORM}
        <table>
            <tr>
                <th>Lottery term: </th><td align="left">{LOTTERY_TERM}</td>
            </tr>
            <tr>
                <th colspan="4">Lottery type & limits</th>
            </tr>
            <tr>
                <td colspan="2">{PHASE_RADIO_1_LABEL}: {PHASE_RADIO_1}</td>
                <td colspan="2">{PHASE_RADIO_2_LABEL}: {PHASE_RADIO_2}</td>
            </tr>
            <tr>
                <td>Percent sophomore: </td><td align="left">{LOTTERY_PER_SOPH}</td>
                <td>Max sophomore invites: </td><td align="left">{LOTTERY_MAX_SOPH}</td>
            </tr>
            <tr>
                <td>Percent junior: </td><td align="left">{LOTTERY_PER_JR}</td>
                <td>Max junior invites: </td><td align="left">{LOTTERY_MAX_JR}</td>
            </tr>
            <tr>
                <td>Percent senior: </td><td align="left">{LOTTERY_PER_SENIOR}</td>
                <td>Max senior invites: </td><td align="left">{LOTTERY_MAX_SENIOR}</td>
            </tr>
            <tr>
                <th colspan="4">Estimated Outcome</th>
            </tr>
            <tr>
                <td>Sophomores: </td>
                <td colspan="3">blah</td>
            </tr>
            <tr>
                <td>Juniors: </td>
                <td colspan="3">blah</td>
            </tr>
            <tr>
                <td>Seniors: </td>
                <td colspan="3">blah</td>
            </tr>
        </table>
        <br />
        {SUBMIT}
        {END_FORM}
    </div>
  </div>
</div>
