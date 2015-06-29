<div class="row">
    <div class="col-md-10">
        <h1>Room Change Request</h1>


        <p class="lead">A few important notes first...</p>
        <ul>
            <li>Your request must be approved by your Residence Director (RD), the Residence Director of hall you are moving to, and by the University Housing Assignments Office.</li>
            <li><strong>Do not</strong> begin moving your belongings until you receive the final approval from University Housing.</li>
            <li>If you change rooms without approval you will be required to return to your assigned space. You may be denied the opportunity to participate in any other room changes for the academic year and will be assessed a $35 administrative charge.</li>
            <li><strong>Requests are granted based on available space and may be denied for any reason.</strong> We do not guarantee there any vacancies. Your RD will work with you to select a space from available vacancies.</li>
            <li><strong>The room fee may increase/decrease depending upon your selection of residence hall.</strong> Your student account will be billed accordingly.</li>
            <li>It may take several bussiness days to process your request. Requests submitted on Fridays may not be processed until the following business day.</li>
            <li>You will be notified via your ASU email address once your request is approved or denied.</li>
            <li>Once you receive your room change confirmation from the Housing Assignments Office, you must complete your move and be checked into your new assignment within 48 hours.</li>
        </ul>

        {START_FORM}
        <h3>Contact Info</h3>
        <p>Your RD and the Assignments Office will use this extra contact information (in addition to your ASU email address) to reach you in case there is a question regarding your request.</p>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {CELL_NUM_LABEL}
                    {CELL_NUM}
                    <div class="checkbox">
                        <label>
                            {CELL_OPT_OUT} <em class="text-muted">I don't want to provide a cellphone number</em>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <h3>Where to?</h3>

        <h4>{TYPE_1} {TYPE_1_LABEL}</h4>
        <div id="roomSwitch" style="margin-left:30px;">
            <h5>Hall Preferences</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {FIRST_CHOICE_LABEL}
                        {FIRST_CHOICE}
                    </div>
                    <div class="form-group">
                        {SECOND_CHOICE_LABEL}
                        {SECOND_CHOICE}
                    </div>
                </div>
            </div>
        </div>

        <h4>{TYPE_2} {TYPE_2_LABEL}</h4>
        <div id="roomSwap" style="margin-left:30px;">
            <p>Enter the ASU user name of the student you would like to switch rooms with.</p>
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        {SWAP_WITH_LABEL}:
                        <div class="input-group">
                            {SWAP_WITH}<span class="input-group-addon">@appstate.edu</span>
                        </div>
                        <span class="help-block"><strong>The person you want to switch will be sent an email asking them to sign in and confirm your request.</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <h3>Reason</h3>
        <p>Please provide a <strong>short</strong> explanation of why you would like to move to a different room. A few sentences are sufficient. You should also indicate any special circumstances (i.e. you want to switch rooms with a friend on your floor).</p>
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    {REASON}
                </div>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-success btn-lg">Submit Request</button>
        </div>
        {END_FORM}

    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    $("#room_change_request_cell_opt_out").change(function(){
        if($(this).is(":checked")){
            $("#room_change_request_cell_num").attr('disabled', true);
        } else {
            $("#room_change_request_cell_num").attr('disabled', false);
        }
     });

    $("#roomSwap").hide();
    $("#roomSwitch").hide();

    $("#room_change_request_type_switch").change(function(){
        if($(this).attr('checked', 'checked')){
            //$("#roomSwap").hide();
            //$("#roomSwitch").show();
            $("#roomSwap").slideUp();
            $("#roomSwitch").slideDown();
        }
    });

    $("#room_change_request_type_swap").change(function(){
        if($(this).attr('checked', 'checked')){
            //$("#roomSwitch").hide();
            //$("#roomSwap").show();
        	  $("#roomSwitch").slideUp();
            $("#roomSwap").slideDown();
        }
    });
});
</script>
