<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <h1>Welcome to Appalachian State University Housing!</h1>
        <br />
        <p>
            We see that you are a new freshmen admitted for the
            <strong>{ENTRY_TERM}</strong> semester and you will be living on-campus for the
            first time.
        </p>
        <br />
        <p class="lead">
            You are required to apply for housing for the following semesters:

            <ul>
                <!-- BEGIN REQUIRED_TERMS -->
    	       <li>{REQ_TERM}</li>
                <!-- END REQUIRED_TERMS -->
            </ul>
        </p>
        <br /><br />
        <p class="text-muted">
            If you are not a new freshman or if this will not be your first time living on campus, then please {CONTACT_LINK}.
        </p>

        <p class="text-muted">
            Once you have completed the required applications, you may then be eligible
            to apply for non-required semesters.
        </p>

        {START_FORM}
        <div class="form-group">
            <button type="submit" class="btn btn-lg btn-success pull-right">Start My Application <i class="fa fa-chevron-right"></i></button>
        </div>
        {END_FORM}
    </div>
</div>
