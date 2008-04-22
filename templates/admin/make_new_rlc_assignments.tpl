<div class="hms">
    <div class="box">
        <div class="box-title"> <h1>{TITLE}</h1> </div>
        <div class="box-content">
            <!-- BEGIN success_msg -->
            <font color="green">{SUCCESS_MSG}<br /></font>
            <!-- END success_msg -->

            <!-- BEGIN error_msg -->
            <font color="red">{ERROR_MSG}<br /></font>
            <!-- END error_msg -->

            {SUMMARY}
            <h2>Applicants</h2>
            {ASSIGNMENTS_PAGER}
            <br />
            <h2>Application Export</h2>
            <h3>Sort: {DROPDOWN}</h3>
            <br />
            {START_FORM}
            {RLC_LIST}{SUBMIT}
            {END_FORM}
        </div>
    </div>
</div>
