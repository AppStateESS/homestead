<h3>{ICON} <span class={STATUS}>Add Room Damages</span></h3>

<div class="block-content">
    <div class="text-muted">Available: {DATES}</div>

<p>
<!-- BEGIN too_late -->
It's too late to set room damages. You only have 48 hours after checking in, and the deadline passed on <strong>{END_DEADLINE}</strong>.
<!-- END too_late -->

<!-- BEGIN new -->
You may {NEW_REQUEST}.  You have only have 48 hours after checking in to complete this.
<!-- END new -->

<!-- BEGIN not_checkedin -->
{NO_CHECKIN}
<!-- END not_checkedin -->
</p>

</div>
