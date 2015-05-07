<h3>{ICON} <span class={STATUS}>Update Emergency & Missing Persons Contact Info</span></h3>

<div class="block-content">
    <div class="text-muted">Available: {DATES}</div>
    <p>
        <!-- BEGIN no_app -->
        You have not applied for housing for this term, so you may not update your emergency contact and missing persons info. {NOT_APP}
        <!-- END no_app -->

        <!-- BEGIN too_early -->
        It's too early to update your emergency contact info. Changes will be allowed after {BEGIN_DEADLINE}.
        <!-- END too_early -->

        <!-- BEGIN too_late -->
        It's too late to update your emergency contact info. The deadline passed on {END_DEADLINE}.
        <!-- END too_late -->

        <!-- BEGIN new -->
        In case of an emergency, it's important for us to have your most up to date contact information. Help us keep you safe and {UPDATE_CONTACT} for this term.
        <!-- END new -->
    </p>
</div>
