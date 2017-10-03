<div class="row">
    <div class="col-md-12">
        <h1>Contact University Housing</h1>

        <h2><small>We're here to help with any questions or comments.</small></h2>
        <hr>

        <h3>Need help with On-campus Housing?</h3>
        <p>You've come to the right place! The form below sends an email to a real human being on our support staff.</p>

        <h4>Why am I seeing this page?</h4>
        <p>
           <ul>
              <li>You clicked a "Contact a Human" button.</li>
              <li>Homestead detected a problem with the data we have on-file for you.</li>
          </ul>
      </p>

      <hr>
  </div>
</div>


<div class="row">
    <div class="col-md-6">
        <p>Please complete the form below. Someone from our support staff will contact you soon!</p>
        {START_FORM}
        <div class="form-group">
            {NAME_LABEL}
            <input type="text" class="form-control" id="{NAME_ID}" name="{NAME_NAME}" autofocus>
        </div>

        <div class="form-group">
            {EMAIL_LABEL}
            <input type="email" class="form-control" id="{EMAIL_ID}" name="{EMAIL_NAME}">
        </div>

        <div class="form-group">
            {PHONE_LABEL}
            <input type="phone" class="form-control" id="{PHONE_ID}" name="{PHONE_NAME}">
        </div>

        <div class="form-group">
            {STYPE_LABEL}
            <select class="form-control" id="{STYPE_ID}" name="{STYPE_NAME}">
                <option value="F">New Freshmen</option>
                <option value="T">Transfer</option>
                <option value="C">Returning</option>
            </select>
        </div>

        <div class="form-group">
            {COMMENTS_LABEL}
            <p class="help-block">Provide us with as much essential information as possible.</p>
            <textarea class="form-control" id="{COMMENTS_ID}" name="{COMMENTS_NAME}" rows="5"></textarea>
        </div>

        <div class="form-group">
            <button class="btn btn-success" type="submit">Submit</button>
        </div>

        {END_FORM}
    </div>
</div>
