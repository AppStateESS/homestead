<h2>{TERM} Emergency Contact & Missing Person Information</h2><p>{RECEIVED_DATE}</p>

<!-- BEGIN withdrawn -->
  <font color="red"><b>{WITHDRAWN}</b></font>
<!-- END withdrawn -->

<!-- BEGIN review_msg -->
  {REVIEW_MSG}
  Please review the information you entered. If you need to go back and make changes, click the 'modify your information' link below. If the information you have entered is correct click the 'Confirm and Continue' button.
<!-- END review_msg -->

{START_FORM}

<div class="col-md-10">
  <div class="row">
    <h3>
      Demographic Information
    </h3>
  </div>
  <div class="row">
    <label class="col-md-3">
      Name:
    </label>
    <div class="col-md-3 col-md-offset-3 text-left">
      {STUDENT_NAME}
    </div>
  </div>
  <br />
  <div class="row">
    <label class="col-md-3">
      Cell Phone:
    </label>
    <div class="col-md-3 col-md-offset-3">
        {CELL_PHONE}
    </div>
  </div>

  <div class="row">
    <h3>
      Emergency Contact Information
    </h3>

  </div>

  <div class="row">
    <label class="col-md-4">
      Emergency Contact Person Name:
    </label>
    <div class="col-md-3 col-md-offset-2">
      {EMERGENCY_CONTACT_NAME}
    </div>
  </div>
  <br />
  <div class="row">
    <label class="col-md-4">
      Emergency Contact Relationship:
    </label>
    <div class="col-md-3 col-md-offset-2">
      {EMERGENCY_CONTACT_RELATIONSHIP}
    </div>
  </div>
  <br />
  <div class="row">
    <label class="col-md-4">
      Emergency Contact Phone Number:
    </label>
    <div class="col-md-3 col-md-offset-2">
      {EMERGENCY_CONTACT_PHONE}
    </div>
  </div>
  <br />
  <div class="row">
    <label class="col-md-4">
      Emergency Contact Email:
    </label>
    <div class="col-md-3 col-md-offset-2">
      {EMERGENCY_CONTACT_EMAIL}
    </div>
  </div>
  <br />
  <div class="row">
    <p class="col-md-9">
      Are there any medical conditions you have which our staff should be aware
      of? (This information will be kept confidential and will only be shared
      with the staff in your residence hall. However, this information
      <strong>may</strong> be disclosed to medical/emergency personnel in case
      of an emergency.)
    </p>
  </div>

  <div class="row">
    <div class="col-md-9">
      {EMERGENCY_MEDICAL_CONDITION}
    </div>
  </div>

  <div class="row">
    <h3>
      Missing Person Information
    <h3>
  </div>

  <div class="row">
    <p class="col-md-9">
      Federal law requires that we ask you to confidentially identify a person
      whom the University should contact if you are reported missing for more
      than 24 hours. Please list your contact person's information below:
    </p>
  </div>
  <br />
  <div class="row">
    <label class="col-md-3">
      Contact Person Name:
    </label>
    <div class="col-md-3 col-md-offset-3">
      {MISSING_PERSON_NAME}
    </div>
  </div>
  <br />
  <div class="row">
    <label class="col-md-3">
      Contact Person Relationship:
    </label>
    <div class="col-md-3 col-md-offset-3">
      {MISSING_PERSON_RELATIONSHIP}
    </div>
  </div>
  <br />
  <div class="row">
    <label class="col-md-4">
      Contact Person Phone Number:
    </label>
    <div class="col-md-3 col-md-offset-2">
      {MISSING_PERSON_PHONE}
    </div>
  </div>
  <br />
  <div class="row">
    <label class="col-md-3">
      Contact Person Email:
    </label>
    <div class="col-md-3 col-md-offset-3">
      {MISSING_PERSON_EMAIL}
    </div>
  </div>
  <br />
  <p>
  </p>

  <div class="row">
    <div class="form-group col-md-9">
      <a href="index.php" class="btn btn-lg btn-danger">
        <i class="fa fa-chevron-left"></i>
          Cancel
      </a>

      <button type="submit" class="btn btn-lg btn-success pull-right">
        <i class="fa fa-save"></i>
        Update
      </button>
      <!-- BEGIN redo_form -->
      or {REDO_BUTTON}
      <!-- END redo_form -->
      {SUBMIT_APPLICATION}
      {END_FORM}
    </div>
  </div>

</div>
