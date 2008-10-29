<div class="hms">
    <div class="box">
        <div class="box-title"> <h1>{TITLE}</h1> </div>
        <div class="box-content">
            <!-- BEGIN success_msg -->
            <span class="success">{SUCCESS_MSG}<br /></span>
            <!-- END success_msg -->

            <!-- BEGIN error_msg -->
            <span class="error">{ERROR_MSG}<br /></span>
            <!-- END error_msg -->

            {SUMMARY}
            <h2>Applicants</h2>
            <h3>Sort: {DROPDOWN}</h3>
            {ASSIGNMENTS_PAGER}
            <br />
            <h2>Application Export</h2>
            <br />
            {START_FORM}
            {RLC_LIST}{SUBMIT}
            {END_FORM}
        </div>
    </div>
</div>
