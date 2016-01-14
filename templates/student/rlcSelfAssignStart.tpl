<h2>Accept Learning Community Invitation</h2>

<div class="row">
	<p class="col-md-9">
		You have been invited to the <strong>{COMMUNITY_NAME}</strong> Residential Learning Community for {TERM}.
	</p>
</div>

{START_FORM}

<div class="row">
	<div class="col-md-8 col-md-offset-1">
		<div class="radio">
            <label>
			    {ACCEPTANCE_1} {ACCEPTANCE_1_LABEL_TEXT}
            </label>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-7 col-md-offset-2">
		<div class="checkbox">
            <label>
			    {TERMS_COND}
                I agree to the terms and conditions for this learning community. I agree to the terms of the Residence Hall License Contract. I understand &amp; acknowledge that if I cancel my License Contract my student account will be charged <strong>$250</strong>.
            </label>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-8 col-md-offset-1">
		<div class="radio">
            <label>
			    {ACCEPTANCE_2} {ACCEPTANCE_2_LABEL_TEXT}
            </label>
		</div>
	</div>
</div>

<div class="row">
	<p class="col-md-9">
		If you decline this invitation, you will still be eligible to re-apply for
		on-campus housing and potentially be randomly invited for on-campus housing
		outside of this community.
	</p>
</div>

<h3>Contact Information</h3>
<div class="row">
    <div class="col-md-8">
        <p class="help-block">
            We'll only use your phone number to contact you for University Housing related
            business or if you receive a package (via UPS, FedEx, etc).
            This information will not be shared with anyone else.
        </p>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="{CELLPHONE_ID}">Cell phone number:</label>
		    {CELLPHONE}
        </div>
	</div>
</div>

<div class="row">
    <div class="cold-md-6 col-md-offset-1">
	    <div class="checkbox">
		    <label class="text-muted">
			    {DO_NOT_CALL}
			    Check here if you do not have or do not wish to provide your cellphone number.
		    </label>
        </div>
	</div>
</div>

<h3>Emergency Information</h3>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="{EMERGENCY_CONTACT_NAME_ID}">Emergency Contact Person Name</label>
            {EMERGENCY_CONTACT_NAME}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="{EMERGENCY_CONTACT_RELATIONSHIP_ID}">Relationship</label>
            {EMERGENCY_CONTACT_RELATIONSHIP}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="{EMERGENCY_CONTACT_PHONE_ID}">Phone Number</label>
            {EMERGENCY_CONTACT_PHONE}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="{EMERGENCY_CONTACT_EMAIL_ID}">Email Address</label>
            {EMERGENCY_CONTACT_EMAIL}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-9">
    	<p>
    		Are there any medical conditions you have which our staff should be
    		aware of? (This information will be kept confidential and will only be
    		shared with the staff in your residence hall. However, this information
    		<strong>may</strong> be disclosed to medical/emergency personnel in
    		case of an emergency.)
    	</p>
    </div>
</div>

<div class="row">
	<div class="col-md-5">
		{EMERGENCY_MEDICAL_CONDITION}
	</div>
</div>

<h3>Missing Person Information</h3>

<div class="row">
    <div class="col-md-9">
        <p class="help-block">
    		Federal law requires that we ask you to confidentially identify a person
    		whom the University should contact if you are reported missing for more than
    		24 hours. Please list your contact person's information below:
    	</p>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
	        <label for="{MISSING_PERSON_NAME_ID}">Contact Person Name:</label>
		    {MISSING_PERSON_NAME}
        </div>
	</div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
	        <label for="{MISSING_PERSON_RELATIONSHIP_ID}">Relationship:</label>
		    {MISSING_PERSON_RELATIONSHIP}
        </div>
	</div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="{MISSING_PERSON_PHONE_ID}">Phone Number:</label>
	        {MISSING_PERSON_PHONE}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
	        <label for="{MISSING_PERSON_EMAIL_ID}">Email:</label>
            {MISSING_PERSON_EMAIL}
        </div>
	</div>
</div>

<div class="row">
	<div class="col-md-2">
		<button type="submit" class="btn btn-lg btn-success">
			Submit
		</button>
	</div>
</div>

{END_FORM}
