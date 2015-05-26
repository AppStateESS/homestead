<h2>Residential Learning Community Reapplication</h2>


<div class="col-md-10">

	{START_FORM}

	<div class="row">
		<h3 class="col-md-4">
			Community Preference
		</h3>
	</div>

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

	<!-- BEGIN community_preference -->
	<div class="form-group">
		<div class="row">
			<p class="col-md-8">
				Rank your Learning Community preferences. If your first choice is unavailable,
				your second and third choices will be considered.
			</p>
		</div>

		<div class="row">
			<label class="col-md-3">
				{RLC_CHOICE_1_LABEL}
			</label>
			<div class="col-md-4 col-md-offset-2">
				{RLC_CHOICE_1}
			</div>
		</div>

		<div class="row">
			<label class="col-md-3">
				{RLC_CHOICE_2_LABEL}
			</label>
			<div class="col-md-4 col-md-offset-2">
				{RLC_CHOICE_2}
			</div>
		</div>

		<div class="row">
			<label class="col-md-3">
				{RLC_CHOICE_3_LABEL}
			</label>
			<div class="col-md-4 col-md-offset-2">
				{RLC_CHOICE_3}
			</div>
		</div>
	</div>
	<!-- END community_preference -->

	<div class="row">
		<h2>
			Short Answer Section
		</h2>
	</div>

	<div class="row">
		<p>
			Why do you want to be a member (or remain a member) of this Residential Learning Community?
		</p>
	</div>

	<div class="row">
		<div class="col-md-8">
			{WHY_THIS_RLC}
		</div>
	</div>

	<div class="row">
		<p>
			As a returning student, what do you hope to contribute and gain from this experience?
		</p>
	</div>

	<div class="row">
		<div class="col-md-8">
			{CONTRIBUTE_GAIN}
		</div>
	</div>

	<p>
	</p>

	<div class="row">
		<button type="submit" class="btn btn-lg btn-success col-md-offset-8">
			Continue
			<i class="fa fa-chevron-right"></i>
		</button>
	</div>


	{END_FORM}

</div>

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
