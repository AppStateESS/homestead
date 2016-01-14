<h2>Confirm Room &amp; Roommates</h2>

<div class="row">
    <div class="col-md-12">

        <p>Please confirm your room and roommate choices below.</p>

        <p>You will be assigned to</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-md-offset-1">
        <h3>{ROOM}</h3>
    </div>
</div>

<div class="row">
    <p class="col-md-8">
        The roommate(s) you have chosen will be sent an email to confirm your request.
        If confirmed, the people in your room will be:
    </p>
</div>

<!-- BEGIN beds -->
<div class="row">
    <div class="col-md-2">
        <strong>{BED_LABEL}:</strong>
    </div>
    <div class="col-md-4">
        <p>{TEXT}</p>
    </div>
</div>
<!-- END beds -->

<div class="row" style="margin-top:2em;">
    <div class="col-md-10">
        <p><strong>Meal plan:</strong> {MEAL_PLAN}</p>
    </div>
</div>

<div class="row" style="margin-top:2em;">
    <p class="col-md-8">
        To confirm your room and roommate selections please type the words shown in the image below in the text field provided. (If you cannot read the words, click the refresh button to get new words.)
    </p>
</div>

{START_FORM}

<div class="row">
    <div class="col-md-3">
        {CAPTCHA_IMAGE}
        <button type="submit" class="btn btn-lg btn-success">
            Confirm This Room &amp; Invite Roommates
        </button>
    </div>
</div>

{END_FORM}
