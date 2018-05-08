<div class="row">
    <div class="col-md-10">
        <h1>{TERM} On-campus Housing Application </h1>
        <h2>Open Waiting List Application</h2>

        <p>As a returning student who did not, or was not eligible to
        	re-apply, you may apply to the On-campus Housing Open Waiting List. If
        	you are selected to receive on-campus housing you will be notified by
        	email and/or telephone.</p>
    </div>
</div>

{START_FORM}

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="{WAITLIST_REASON_ID}">{WAITLIST_REASON_LABEL_TEXT}</label>
            {WAITLIST_REASON}
        </div>

        <div class="form-group">
            <label for="{ONCAMPUS_REASON_ID}">{ONCAMPUS_REASON_LABEL_TEXT}</label>
            {ONCAMPUS_REASON}
        </div>

        <div class="form-group">
            <label for="{ONCAMPUS_OTHER_REASON_ID}">{ONCAMPUS_OTHER_REASON_LABEL_TEXT}</label>
            {ONCAMPUS_OTHER_REASON}
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
		$('#phpws_form_oncampus_other_reason').attr('disabled', true);

		// Set event listeners
		$("#phpws_form_oncampus_reason").bind("change", function(){
            selectedVal = $('#phpws_form_oncampus_reason').val();
            if(selectedVal == 'other'){
                $('#phpws_form_oncampus_other_reason').attr('disabled', false);
            } else {
                $('#phpws_form_oncampus_other_reason').attr('disabled', true);
                $('#phpws_form_oncampus_other_reason').val('');
            }
        });
	});
</script>

<div class="row">
    <div class="col-md-3">
        <h3>Preferences</h3>
        <div class="form-group">
            <label for="{MEAL_OPTION_ID}">Meal Plan:</label>
            {MEAL_OPTION}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <div class="form-group">
            <label for="{NUMBER_ID}">Cell Phone Number:</label>
            <span class="help-block">We'll only use this to contact you if we have a question about your application, or when you have a package delivered.</span>
            <div class="row">
                <div class="col-md-3">
                    {NUMBER}
                </div>

            </div>
            <div class="checkbox">
                <label class="text-muted">
                    {DO_NOT_CALL}
                    Check here if do not wish to provide your cellphone number.
                </label><br />
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <h3>Emergency Contact Information</h3>

        <div class="form-group required">
            <label for="{EMERGENCY_CONTACT_NAME_ID}">Parent / Guardian Name</label>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_CONTACT_NAME}
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="{EMERGENCY_CONTACT_RELATIONSHIP_ID}">Relationship</label>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_CONTACT_RELATIONSHIP}
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="{EMERGENCY_CONTACT_PHONE_ID}">Phone Number</label>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_CONTACT_PHONE}
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="{EMERGENCY_CONTACT_EMAIL_ID}">Email</label>
            <div class="row">
                <div class="col-md-4">
                    {EMERGENCY_CONTACT_EMAIL}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <h3>Emergency Medical Information</h3>
        <div class="form-group">
            <p>Are there any <em>emergency</em> medical conditions which our staff should be aware of?</p>
            <span class="help-block">
                In the event of a <em>medical emergency</em> within the residence halls we may disclose this information to <em>emergency personnel</em>. For example, severe <em>life-threatening</em> allergies to medications or foods. This information will be kept confidential and only shared on a need-to-know basis.
            </span>
            <div class="row">
                <div class="col-md-4">
                    <label for="{EMERGENCY_MEDICAL_CONDITION_ID}">Emergency Medical Conditions</label>
                    {EMERGENCY_MEDICAL_CONDITION}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <h3>Missing Person Information</h3>

        <p>If you are reported missing for more than 24 hours, federal law requires
            the University to contact someone. Please list your contact person’s
            information below.
        </p>
        <p class="text-muted">This information is kept confidential, but will be
            released to law enforcement if you are reported missing. The University
            will inform local law enforcement that you are missing within 24 hours of
            the report. <strong>Please note: </strong>If you are under 18 and not emancipated, the
            University must notify your parent/guardian within 24 hours that you
            are reported missing.
        </p>
        <div class="form-group required">
            <label for="{MISSING_PERSON_NAME_ID}">Contact Person Name</label>
            <div class="row">
                <div class="col-md-4">
                    {MISSING_PERSON_NAME}
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="{MISSING_PERSON_RELATIONSHIP_ID}">Relationship</label>
            <div class="row">
                <div class="col-md-4">
                    {MISSING_PERSON_RELATIONSHIP}
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="{MISSING_PERSON_PHONE_ID}">Phone Number</label>
            <div class="row">
                <div class="col-md-4">
                    {MISSING_PERSON_PHONE}
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="{MISSING_PERSON_EMAIL_ID}">Email</label>
            <div class="row">
                <div class="col-md-4">
                    {MISSING_PERSON_EMAIL}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-10">
        <h3>Housing Accommodations</h3>

        <p>
            University Housing is committed to meeting the individual needs of all students to the best of our ability. Housing requests due to disabilities and gender related needs are taken seriously, thoroughly considered, and kept confidential.
        </p>
        <p>
            Students who need housing accommodations due to the impact of a disability (physical, medical, mental health, etc.) should contact the <a href="https://ods.appstate.edu/" target="_blank">Office of Disability Services</a>.
            Students who need housing accommodations due to gender related needs should contact the <a href="http://multicultural.appstate.edu/" target="_blank">Office of Multicultural Student Development</a>.
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <h3>Terms &amp; Conditions</h3>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="checkbox">
            <label>
                {DEPOSIT_CHECK}
                {DEPOSIT_CHECK_LABEL}
            </label>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 float-right">
        <button type="submit" class="btn btn-success btn-lg">
            Submit Waiting List Application <i class="fa fa-chevron-right"></i>
        </button>
    </div>
</div>

{END_FORM}
