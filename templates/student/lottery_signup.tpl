<h1>{TERM} On-campus Housing Re-application</h1>

<div class="col-md-10">
<div class="row">
	<p>Welcome to Appalachian State University Housing. As a returning
	student you must re-apply and be selected in order to be guaranteed
	on-campus housing for {TERM}. Students will be selected at random. If
	you are selected you will be notified by email.
	</p>
</div>
<div class="row">
	<p><strong>For more information on how the selection process works, please
		read the <a href="http://housing.appstate.edu/reapp" target="_blank">housing
			 re-application FAQ</a>.</strong></p>
</div>

{START_FORM}

<div class="row">
	<h4>Contact Information</h4>
</div>
<div class="row">
	<p>
		We'll only use this information to contact you if we have a
		question about your application or to notify you if you receive a
		package (via UPS, FedEx, etc). This information will not be shared with
		anyone else.
	</p>
</div>
<div class="row">
	<label class="col-md-3">Cell Phone Number:</label>
	<div class="col-md-3 col-md-offset-3">
		{NUMBER}
	</div>
</div>
<div class="row">
	<div class="checkbox col-md-4 col-md-offset-6">
		<label>
			{DO_NOT_CALL}
			Check here if you do not have a cell phone or do
			not wish to provide your cell phone number.
		</label>
	</div>
</div>

<div class="row">
	<h4>Emergency Information</h4>
</div>
<div class="row">
	<label class="col-md-4">Emergency Contact Person Name:</label>
	<div class="col-md-3 col-md-offset-2">
		{EMERGENCY_CONTACT_NAME}
	</div>
</div>
<div class="row">
	<label class="col-md-3">Relationship:</label>
	<div class="col-md-3 col-md-offset-3">
		{EMERGENCY_CONTACT_RELATIONSHIP}
	</div>
</div>
<div class="row">
	<label class="col-md-3">Phone Number:</label>
	<div class="col-md-3 col-md-offset-3">
		{EMERGENCY_CONTACT_PHONE}
	</div>
</div>
<div class="row">
	<label class="col-md-3">Email:</label>
	<div class="col-md-3 col-md-offset-3">
		{EMERGENCY_CONTACT_EMAIL}
	</div>
</div>
<div class="row">
	<p class="col-md-5">Are there any medical conditions you have which our
		staff should be aware of? (This information will be kept confidential and
		will only be shared with the staff in your residence hall. However, this
		information <strong>may</strong> be disclosed to medical/emergency personnel
		in case of an emergency.)</p>
	<div class="col-md-5 col-md-offset-1">
		{EMERGENCY_MEDICAL_CONDITION}
	</div>
</div>

<div class="row">
	<h4>Missing Person Information</h4>
</div>
<div class="row">
	<p>Federal law requires that we ask you to confidentially identify a person
		whom the University should contact if you are reported missing for more
		than 24 hours. Please list your contact person's information below:
	</p>
</div>
<div class="row">
	<label class="col-md-3">Contact Person Name:</label>
	<div class="col-md-3 col-md-offset-3">
		{MISSING_PERSON_NAME}
	</div>
</div>
<div class="row">
	<label class="col-md-3">Relationship:</label>
	<div class="col-md-3 col-md-offset-3">
		{MISSING_PERSON_RELATIONSHIP}
	</div>
</div>
<div class="row">
	<label class="col-md-3">Phone Number:</label>
	<div class="col-md-3 col-md-offset-3">
		{MISSING_PERSON_PHONE}
	</div>
</div>
<div class="row">
	<label class="col-md-3">Email:</label>
	<div class="col-md-3 col-md-offset-3">
		{MISSING_PERSON_EMAIL}
	</div>
</div>

<div class="row">
	<h4>Meal Plan<h4>
</div>
<div class="row">
	<p>Please choose a meal plan. You'll have the opportunity to change
		 this again later (after you choose your room).</p>
</div>

<div class="row">
	<div class="col-md-3">
		{MEAL_PLAN_LABEL}
	</div>
	<div class="col-md-3 col-md-offset-3">
		{MEAL_PLAN}
	</div>
</div>

<div class="row">
	<h2>On-campus Housing Groups</h2>
</div>
<div class="row">
	<p>To re-apply for housing with a particular program on-campus,
select that group in the box below. If you are approved by the
group/program you apply for, then you will be administratively assigned
and may not be able to choose your room.</p>
</div>

<fieldset>
		<legend>
			Residential Learning Communities
		</legend>
		<div class="row">
			<label class="col-md-4">Would you like to apply to live in a Residential Learning Community</label>
			<div class="checkbox col-md-5 col-md-offset-2">
				<label>
					{RLC_INTEREST}
					{RLC_INTEREST_LABEL}
				</label>
			</div>
		</div>
</fieldset>

<!-- BEGIN greek -->

<fieldset >
	<legend>
		Greek Letter Organizations
	</legend>

		<div class="checkbox">
			<label>
				{SORORITY_CHECK}
				{SORORITY_CHECK_LABEL}
			</label>
		</div>
	<div id="sorority_options">
		<div class="row">
			<label class="col-md-4">{SORORITY_DROP_LABEL}</label>
			<div class="col-md-3 col-md-offset-2">
				{SORORITY_DROP}
			</div>
		</div>
		<div class="row">
			<label class="col-md-4">
				Would you like to live in the Appalachian Panhellenic Hall?
			</label>
			<div class="radio col-md-5 col-md-offset-2">
				<label>
					{SORORITY_PREF_1}{SORORITY_PREF_1_LABEL}
				</label>
				<label>
					{SORORITY_PREF_2}{SORORITY_PREF_2_LABEL}
				</label>
			</div>
		</div>
	</div>
</fieldset>

<!-- END greek -->

<!-- BEGIN watauga -->
<fieldset>
	<legend>
			Watauga Global
	</legend>
	<div class="row">
		<label class="col-md-4">
			Would you like to live with other Watauga Global students?
		</label>
		<div class="radio col-md-6 col-md-offset-2">
			<label>
				{WG_PREF_1}{WG_PREF_1_LABEL}
			</label>
			<label>
				{WG_PREF_2}{WG_PREF_2_LABEL}
			</label>
		</div>
	</div>
</fieldset>
<!-- END watauga -->

<!-- BEGIN honors -->

<fieldset>
	<legend>
		The Honors College Program
	</legend>
	<div class="row">
		<label class="col-md-4">
			Would you like to live in Honors Housing?
		</label>
		<div class="radio col-md-5 col-md-offset-2">
			<label>
				{HONORS_PREF_1}{HONORS_PREF_1_LABEL}
			</label>
			<label>
				{HONORS_PREF_2}{HONORS_PREF_2_LABEL}
			</label>
		</div>
	</div>
</fieldset>
<!-- END honors -->


<div class="row">
	<h2>Special needs</h2>
</div>
<div class="row">
	<p>University Housing is committed to meeting the needs of all students to
		the best of its ability.
	</p>
</div>
<div class="row">
	<p>Special needs housing requests will be reviewed individually with a
		commitment to providing housing that best meets the needs of the student.
		University Housing takes these concerns very seriously and confidentiality
		will be maintained. Housing for special needs may be limited due to space
		availability.
	</p>
</div>
<div class="row">
	<label class="col-md-4">Do you have any special needs?</label>

	<div class="checkbox col-md-5 col-md-offset-2">
		<label>
			{SPECIAL_NEED}
			{SPECIAL_NEED_LABEL} (Includes physical, psychological, medical, and gender needs.)
		</label>
	</div>
</div>

<div class="row">
	<h2>Early Contract Release</h2>
</div>
<div class="row">
	<p>Are you currently planning to apply for early contract release because
		you'll be leaving on-campus housing at the end of {FALL_TERM}?
	</p>
</div>
<div class="row">
	<p>You may be released from the Housing Contract ealy, but only for an
		approved reason that's listed in the box below (e.g. graduation, student
		teaching, etc). This helps us in capacity planning; it
		<strong>does not</strong> commit you to leaving at the end of {FALL_TERM},
		nor does it release you from the Contract. There is a separate approval
		process later in the semester to confirm.
	</p>
</div>
<div class="row">
	<div class="col-md-4">
		{EARLY_RELEASE_LABEL}
	</div>
	<div class="col-md-4 col-md-offset-2">
		{EARLY_RELEASE}
	</div>
</div>

<div class="row">
	<h2>Terms & Conditions</h2>
</div>
<div class="row">
	<div class="checkbox">
		<label>
			{DEPOSIT_CHECK}
			{DEPOSIT_CHECK_LABEL}
		</label>
	</div>
</div>

<div class="row">
	<button type="submit" class="btn btn-success btn-lg">
		Continue
		<i class="fa fa-chevron-right"></i>
	</button>
</div>

{END_FORM}

</div>

<script>
	$(document).ready(
			function() {
				// Bind the event for the sorority checkbox
				$('#phpws_form_sorority_check_sorority_check').bind('change',
						function() {
							$('#sorority_options').toggle('fast');
						});
				// Collapse the sorority checkbox by default
				$('#sorority_options').hide();

				// Create the button style
				$("#phpws_form_submit_form").button();
			});
</script>
