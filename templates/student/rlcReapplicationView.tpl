<h2>Residential Learning Community Reapplication</h2>

{START_FORM}

<div class="row">
	<h3 class="col-md-4">
		Community Preference
	</h3>
</div>

<div class="row">
    <div class="col-md-8">
    	<!-- BEGIN rlc_opt -->
    	<div class="radio">
    		<label>
    			{RLC_OPT_1}{RLC_OPT_1_LABEL}
    		</label>
    		<label>
    			{RLC_OPT_2}{RLC_OPT_2_LABEL}
    		</label>
    	</div>
    	<!-- END rlc_opt -->
    </div>
</div>

<!-- BEGIN community_preference -->
<div class="row">
    <div class="col-md-8">
        <p>
            Rank your Learning Community preferences. If your first choice is unavailable,
            your second and third choices will be considered.
        </p>
    </div>
</div>


<div class="row">
    <div class="col-md-4">
		<div class="form-group">
			<label>{RLC_CHOICE_1_LABEL_TEXT}</label>
			{RLC_CHOICE_1}
		</div>

		<div class="form-group">
			<label>{RLC_CHOICE_2_LABEL_TEXT}</label>
            {RLC_CHOICE_2}
		</div>

		<div class="form-group">
			<label>{RLC_CHOICE_3_LABEL_TEXT}</label>
			{RLC_CHOICE_3}
		</div>
    </div>
</div>
<!-- END community_preference -->

<div class="row">
    <div class="col-md-4">
	    <h3>Short Answer Section</h3>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
    	<p>
    		Why do you want to be a member (or remain a member) of this Residential Learning Community?
    	</p>
    </div>
</div>

<div class="row">
	<div class="col-md-8">
		{WHY_THIS_RLC}
	</div>
</div>

<div class="row">
    <div class="col-md-8">
	    <p>As a returning student, what do you hope to contribute and gain from this experience?</p>
    </div>
</div>

<div class="row">
	<div class="col-md-8">
		{CONTRIBUTE_GAIN}
	</div>
</div>

<div class="row">
    <div class="col-md-4 col-md-offset-8">
	    <button type="submit" class="btn btn-lg btn-success">Continue <i class="fa fa-chevron-right"></i></button>
    </div>
</div>

{END_FORM}

<script type="text/javascript">

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
