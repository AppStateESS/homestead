<h2>Select A Roommate</h2>

<div class="col-md-9">

  <!-- BEGIN error_msg -->
    <div class="error">
      <img style="vertical-align:middle;" src="mod/hms/img/tango/process-stop.png">
        {ERROR_MSG}
    </div>
  <!-- END error_msg -->

  <!-- BEGIN success_msg -->
    <span class="success">
      {SUCCESS_MSG}
    </span>
  <!-- END success_msg -->

  <div class="row">
    <p>
      To request a roommate please provide his/her Appalachian State University
      email address below. Your requested roommate will be sent an email inviting
      him/her to confirm your request. You will also be sent an email confirmation
      that your request has been made. You will receive another email when your
      requested roommate accepts or rejects your invitation.
    </p>
  </div>

  <div class="row">
    <p>
      It is <strong>NOT</strong> necessary for the person you are requesting to
      also request you. They only need to accept your request in order for your
      roommate pairing to be confirmed.
    </p>
  </div>

  <div class="row">
    <div class="alert alert-info">
      <h4>
        <i class="fa fa-exclamation"></i>
        Please note, we cannot guarantee that you will be assigned with your
        requested roommate. Roommate requests will be honored where space allows.
      </h4>
    </div>
  </div>

  <div class="row">
    <div class="alert alert-info">
      <h4>
        <i class="fa fa-exclamation"></i>
        Residential Learning Communities and Common Interest Housing members
      </h4>
      <p>
        If you have been accepted as a member of a Residential Learning Community
        or a Common Interest Housing group you will only be able to choose a
        roommate who has also been accepted into the same group.
      </p>
    </div>
  </div>

  <div class="row">
    <div class="alert alert-info">
      <h4>
        <i class="fa fa-exclamation"></i>
        Honors College and Watauga Global Community members
      </h4>
      <p>
        If you are a member of The Honors College or Watauga Global Community
        your roommate request may not be honored if your roommate is not also a
        member of the same organization.
      </p>
    </div>
  </div>

  {START_FORM}

  <div class="row">
    <div class="form-group">
      <label class="col-md-3">
        ASU Email:
      </label>
      <div class="input-group col-md-5 col-md-offset-7">
          {USERNAME}
          <div class="input-group-addon">
            @appstate.edu
          </div>
      </div>
    </div>

    <div class="form-group">
      <a href="index.php" class="btn btn-danger btn-lg pull-left">
        <i class="fa fa-chevron-left"></i>
        Cancel
      </a>
      <button type="submit" class="btn btn-success btn-lg pull-right">
        Continue
        <i class="fa fa-chevron-right"></i>
      </button>
    </div>
  </div>

  {END_FORM}

</div>
