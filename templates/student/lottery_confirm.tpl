<h2>Confirm Room & Roommates</h2>

<div class="col-md-12">

  <!-- BEGIN error_msg -->
    <span class="error">{ERROR_MSG}</span>
  <!-- END error_msg -->

  <!-- BEGIN success_msg -->
    <span class="success">{SUCCESS_MSG}</span>
  <!-- END success_msg -->

  <p>
    Please confirm your room and roommate choices below.
  </p>

  <p>
    You will be assigned to:
  </p>

  <div class="row">
    <div class="col-md-6 col-md-offset-1">
      <h3>
        {ROOM}
      </h3>
    </div>
  </div>

  <div class="row">
    <p class="col-md-8">
      The roommate(s) you have chosen will be sent an email to confirm your request.
      If confirmed, the people in your room will be:
    </p>
  </div>

  <div class="row">
    <label class="col-md-2">
      <u>Beds</u>
    </label>

  </div>

  <div class="row">
    <div class="col-md-5">
      <!-- BEGIN beds -->
      <div class="row">
        <label class="col-md-4">
          {BED_LABEL}:
        </label>
        <p class="col-md-6">
          {TEXT}
        </p>
      </div>
      <!-- END beds -->
    </div>
  </div>

  <div class="col-md-12">
    <div class="row">
      <label>
        <u>Meal plan</u>
      </label>
    </div>
    <div class="row">
      <p>
        {MEAL_PLAN}
      </p>
    </div>
  </div>



  <div class="row">
    <p class="col-md-8">
      To confirm your room and roommate selections please type the words shown in the image below in the text field provided. (If you cannot read the words, click the refresh button to get new words.)
    </p>
  </div>

  {START_FORM}

  <div class="row">
    <div class="col-md-3">
      {CAPTCHA_IMAGE}
    </div>
  </div>

  <button type="submit" class="btn btn-lg btn-success">
    Confirm
  </button>

  {END_FORM}

</div>
