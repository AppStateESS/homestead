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
      Bedroom
    </label>

  </div>

  <!-- BEGIN beds -->
  <div class="row">
    <label class="col-md-2">
      {BEDROOM_LETTER}:
    </label>
    <p class="col-md-3">
      {TEXT}
    </p>
  </div>
  <!-- END beds -->

  <div class="row">
    <label class="col-md-2">
      Meal plan:
    </label>
    <p class="col-md-3">
      {MEAL_PLAN}
    </p>
  </div>



  <p>To confirm your room and roommate selections please type the words shown in the image below in the text field provided. (If you cannot read the words, click the refresh button under the image to get new words.)</p>

  {START_FORM}

  <div class="row">
    <div class="col-md-3">
      {CAPTCHA_IMAGE}
    </div>
  </div>

  {SUBMIT_FORM}
  {END_FORM}

</div>
