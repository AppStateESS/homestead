
<h3>{ICON} <span class={STATUS}>Application</span></h3>


<div class="block-content">

    <div class="text-muted">Available: {DATES}</div>
    <p>
        <!-- BEGIN too_soon -->
    <p>The application for this term will be available on {BEGIN_DEADLINE}.</p>
    <!-- END too_soon -->

    <!-- BEGIN too_late -->
    <p>The deadline to apply for this term was {END_DEADLINE}.</p>
    <!-- END too_late -->

    <!-- BEGIN review_app -->
    <p>You have applied for on-campus housing for this term. You may {VIEW_APP}.</p>
    <!-- END review_app -->

    <!-- BEGIN edit_app -->
    <p>If you'd like to change your preferences, you may {NEW_APP}.</p>
    <!-- END edit_app -->

    <!-- BEGIN no_app -->
    <p>You have not applied for this term yet.</p>
    <a href="{APP_NOW}" class="btn btn-lg btn-success"><i class="fa fa-lg fa-arrow-circle-o-right"></i> Apply Now!</a>
    <!-- END no_app -->
</p>
</div>