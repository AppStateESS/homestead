<div class="row">
    <div class="col-md-10">
        <h1>Select A Roommate</h1>

        <div class="row">
            <div class="col-md-12">
                <p>
                    To request a roommate, provide his/her Appalachian email address below.
                    Your requested roommate will be sent an email inviting
                    him/her to confirm your request. We will send you an email when your
                    requested roommate accepts or declines your invitation.
                </p>

                <p>
                    It is <strong>NOT</strong> necessary for the person you are requesting to
                    also request you. They only need to accept your request in order for your
                    roommate pairing to be confirmed.
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="alert alert-info">
                    <h4><i class="fa fa-exclamation"></i> We Can't Guarantee Anything!</h4>
                    <p>
                        We cannot guarantee that you will be assigned with your
                        requested roommate. Roommate requests are honored as space allows.
                    </p>
                </div>

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
        </div>

        {START_FORM}

        <div class="row">
            <div class="col-md-4 col-md-offset-2">
                <div class="form-group">
                    <label for="{USERNAME_ID}">Roommate's ASU Email:</label>
                    <div class="input-group">
                        <input type="text" class="form-control input-lg" name="{USERNAME_NAME}" id="{USERNAME_ID}" value="{USERNAME_VALUE}" autofocus>
                        <div class="input-group-addon">@appstate.edu</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <a href="index.php" class="btn btn-default pull-left"><i class="fa fa-chevron-left"></i> Cancel</a>
                    <button type="submit" class="btn btn-success btn-lg pull-right">Send Request <i class="fa fa-chevron-right"></i></button>
                </div>
            </div>
        </div>

        {END_FORM}

    </div>
</div>
