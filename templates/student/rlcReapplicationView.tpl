<h1>Residential Learning Community Application</h1>
<h3 style="margin-left: 6em;margin-top:0">for Upperclassmen - {TERM}</h3>

{START_FORM}

<h2>Community Preference</h2>
<!-- BEGIN rlc_opt -->
<h3>
{RLC_OPT_1}{RLC_OPT_1_LABEL}<br />
{RLC_OPT_2}{RLC_OPT_2_LABEL}
</h3>
</p>
<!-- END rlc_opt -->

<!-- BEGIN community_preference -->
<p id="rlc_prefs" style="margin-left: 30px;">
Rank your Learning Community preferences. If your first choice is unavailable, your second and third choices will be considered.<br /><br />
{RLC_CHOICE_1_LABEL} {RLC_CHOICE_1}<br />
{RLC_CHOICE_2_LABEL} {RLC_CHOICE_2}<br />
{RLC_CHOICE_3_LABEL} {RLC_CHOICE_3}<br />
</p>
<!-- END community_preference -->

<h2 style="clear: left; margin-top: 35px;">Short Answer Section</h2>
<p>
Why do you want to be a member (or remain a member) of this Residential Learning Community? 
{WHY_THIS_RLC}
</p>

<p>
As a returning student, what do you hope to contribute and gain from this experience?
{CONTRIBUTE_GAIN}
</p>

<p>
{SUBMIT}
</p>
{END_FORM}

<script>

var rlc_opt_radio = $("#phpws_form_rlc_opt_continue").get();

// Only do this if the rlc radio buttons are actually a part of the page
if(rlc_opt_radio != ""){
	$("#rlc_prefs").hide();
	
	$("#phpws_form_rlc_opt_new").change(function(){
        if($(this).attr('checked')){
        	$("#rlc_prefs").slideDown();
        }
    });

    $("#phpws_form_rlc_opt_continue").change(function(){
        if($(this).attr('checked')){
            $("#rlc_prefs").slideUp();
        }
    });
}

</script>