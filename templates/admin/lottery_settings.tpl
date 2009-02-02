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
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
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
        </table>
        <table>
            <tr>
                <th colspan="5">Estimated Outcome</th>
            </tr>
            <tr>
                <th>Class</th>
                <th>Minimum</th>
                <th>Average</th>
                <th>Maximum</th>
                <th>Most Frequent</th>
            </tr>
            <tr>
                <td>Sophomores: </td>
                <td>{soph_min}</td>
                <td>{soph_avg}</td>
                <td>{soph_max}</td>
                <td>{soph_mode}</td>
            </tr>
            <tr>
                <td>Juniors: </td>
                <td>{jr_min}</td>
                <td>{jr_avg}</td>
                <td>{jr_max}</td>
                <td>{jr_mode}</td>
            </tr>
            <tr>
                <td>Seniors: </td>
                <td>{sr_min}</td>
                <td>{sr_avg}</td>
                <td>{sr_max}</td>
                <td>{sr_mode}</td>
            </tr>
        </table>
        <br />
        {SUBMIT}
        {END_FORM}
    </div>
  </div>
</div>
