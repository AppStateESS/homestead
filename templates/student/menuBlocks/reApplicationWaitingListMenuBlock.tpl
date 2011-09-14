{ICON}


<h3>
<div class={STATUS}>Waiting List</div>
</h3>

<div class="block-content">

<div class="availability-dates">Available: {DATES}</div>

<p>
<!-- BEGIN too_soon -->
If you have not selected a room by {BEGIN_DEADLINE} you will be automatically placed on the housing waiting list. Any time after {BEGIN_DEADLINE} you will be able to opt-out of this waiting list if you have found other off-campus housing.
<!-- END too_soon -->

<!-- BEGIN too_late -->
It is too late to opt-out of the waiting list. The deadline passed on {END_DEADLINE}.
<!-- END too_late -->

<!-- BEGIN opted_out -->
{OPTED_OUT}
You have opted-out of the re-application waiting list.
<!-- END opted_out -->

<!-- BEGIN did_not_apply -->
{DID_NOT_APPLY}
You are not eligible for the on-campus waiting list.
<!-- END did_not_apply -->

<!-- BEGIN opt_out -->
<p>You have been automatically placed you on the on-campus housing waiting list.</p>

<p>You are number {POSITION} of {TOTAL} students on the waiting list. (This will be updated as students are assigned and/or opt-out of the waiting list.)</p>

<p>If you have found other off-campus housing, you may {OPT_OUT_LINK}.</p>
<!-- END opt_out -->
</p>
</div>
