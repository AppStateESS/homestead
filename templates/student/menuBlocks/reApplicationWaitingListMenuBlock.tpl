{ICON}


<h3>
<div class={STATUS}>Waiting List</div>
</h3>

<div class="block-content">

<div class="availability-dates">Available: {DATES}</div>


<!-- BEGIN too_soon -->
<p>If you have not selected a room by {BEGIN_DEADLINE} you may sign-up for the Re-application Waiting List.</p>
<!-- END too_soon -->

<!-- BEGIN too_late -->
<p>It is too late to sign-up for the waiting list. The deadline passed on {END_DEADLINE}.</p>
<!-- END too_late -->

<!-- BEGIN did_not_apply -->
{DID_NOT_APPLY}
<p>You are not eligible for the on-campus waiting list.</p>
<!-- END did_not_apply -->

<!-- BEGIN signed_up -->
{SIGNED_UP}
<p>You have applied for the On-campus Housing Re-application Waiting List.</p>

<p>You are number {POSITION} of {TOTAL} students on the waiting list. This will be updated as other students are added and removed from the waiting list, so you can come back to check your place on the list. We will notify you by mid-March whether on-campus housing has become available or not.</p>
<!-- END signed_up -->

<!-- BEGIN apply -->
You may {APPLY_LINK}.
<!-- END apply -->
</div>
