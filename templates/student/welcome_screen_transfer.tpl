<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <h1>Welcome to Appalachian State University Housing!</h1>
        <br />
        <p>
            We see that you are a new transfer student admitted for the
            <strong>{ENTRY_TERM}</strong> semester and you will be living on-campus for the
            first time.
        </p>
        <br />
        <div class="alert alert-info">
            <h4><i class="fa fa-exclamation"></i> Transfer Housing Is Very Limited</h4>
            <p>
                On-campus housing is not guaranteed for transfer students and, due to limited space, very few transfer students are selected for on-campus housing. Transfer students are selected based on the date your housing application
                is received. You will be notified by mid-July if we are able to accommodate you on-campus.
            </p>
            <p>
                <br />
                We encourage you to explore off-campus housing options. We recommend <a class="alert-link" href="http://www.universityhighlands.com/">University Highlands</a> and the <a href="https://offcampus.appstate.edu/off-campus-housing" class="alert-link">Off-campus Housing Webiste</a>.
            </p>
        </div>
        <p class="lead">
            You may apply for housing for the following semesters:

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
