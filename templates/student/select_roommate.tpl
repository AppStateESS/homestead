<div class="hms">
    <div class="box">
        <div class="box-title"><h2>Select A Roommate</h2></div>
        <div class="box-content">

            <!-- BEGIN error_msg -->
            <div class="error">
                <img style="vertical-align:middle;" src="images/mod/hms/tango/process-stop.png">
                {ERROR_MSG}
            </div>
            <br />
            <!-- END error_msg -->

            <!-- BEGIN success_msg -->
            <span class="success">
                {SUCCESS_MSG}
            </span>
            <br /><br />
            <!-- END success_msg -->

        
            To request a roommate please provide his/her Appalachian State University email address below. Your requested roommate will be sent an email inviting him/her to confirm your request. You will also be sent an email confirmation that your request has been made. You will receive another email when your requested roommate accepts or rejects your invitation.<br /><br />

            It is <b>NOT</b> necessary for the person you are requesting to also request you. They only need to accept your request in order for your roommate pairing to be confirmed..<br /><br />

            <div class="note">
                <img style="vertical-align:middle;" src="images/mod/hms/tango/emblem-important.png">
                <b>Note: </b>If you have been accepted as a member of a Residential Learning Community or a Common Interest Housing group you will only be able to choose a roommate who has also been accepted into the same group.
            </div>
            <br />

            <div class="note">
                <img style="vertical-align:middle;" src="images/mod/hms/tango/emblem-important.png">
            <b>Note: </b>If you are a member of The Honors College or Watauga Global Community your roommate request may not be honored if your roommate is not also a member of the same organization.</div>
            <br/>
            
            <div class="note">
                <img style="vertical-align:middle;" src="images/mod/hms/tango/emblem-important.png">
            <b>Please note, we cannot guarantee that you will be assigned with your requested roommate. Roommate requests will be honored where space allows.</b></div>

            <br />
            
            {START_FORM}
            <table>
                <tr>
                    <th align="left">ASU Email:</th><td>{USERNAME}@appstate.edu</td>
                </tr>
            </table>
        <br /><br />
        {SUBMIT} {CANCEL}
        {END_FORM}
        </div>
    </div>
</div>
