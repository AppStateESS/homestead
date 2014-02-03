{ICON}

<h3>
<div class={STATUS}>Room Change Request</div>
</h3>

<div class="block-content">

<div class="availability-dates">Available: {DATES}</div>

<p>
<!-- BEGIN no_assignment -->
You are not currently assigned, so you may not request a room change. {NOT_ASSIGNED}
<!-- END no_assignment -->

<!-- BEGIN too_early -->
It's too early to request a room change. Room changes will be allowed after {BEGIN_DEADLINE}.
<!-- END too_early -->

<!-- BEGIN too_late -->
It's too late to request a room change. The deadline passed on {END_DEADLINE}.
<!-- END too_late -->

<!-- BEGIN pending -->
Your room change request has been submitted. Once it is approved, you will be notified via an email to your ASU email account. {PENDING}
<!-- END pending -->

<!-- BEGIN approval -->
You have a room change request that needs your approval. {APPROVAL_CMD}
<!-- END approval -->

<!-- BEGIN new -->
You may {NEW_REQUEST}.
<!-- END new -->
</p>

</div>
