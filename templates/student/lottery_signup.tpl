<script>
	$(document).ready(
			function() {
				// Bind the event for the sorority checkbox
				$('#phpws_form_sorority_check_sorority_check').bind('change',
						function() {
							$('#sorority_options').toggle('fast');
						});
				// Collapse the sorority checkbox by default
				$('#sorority_options').hide();
				
				// Create the button style
				$("#phpws_form_submit_form").button();
			});
</script>

<h1>{TERM} On-campus Housing Re-application</h1>
<p>Welcome to Appalachian State University Housing. As a returning
student you must re-apply and be selected in order to be guaranteed
on-campus housing for {TERM}. Students will be selected at random. If
you are selected you will be notified by email.
<p>
<p><strong>For more information on how the selection
process works, please read the <a
	href="https://hms.appstate.edu/webpage/3" target="_blank">housing
re-application FAQ</a>.</strong></p>

{START_FORM}

<h2>Contact Information</h2>
<p>We'll only use this information to contact you if we have a
question about your application or to notify you if you receive a
package (via UPS, FedEx, etc). This information will not be shared with
anyone else.</p>
<p>Cell Phone Number: ({AREA_CODE})-{EXCHANGE}-{NUMBER}<br />
{DO_NOT_CALL}<sub>Check here if you do not have a cell phone or do
not wish to provide your cell phone number.</sub></p>

<h2>Meal Plan</h2>
<p>Please choose a meal plan. You'll have the opportunity to change
this again later (after you choose your room).</p>
<p>{MEAL_PLAN_LABEL}{MEAL_PLAN}</p>

<h2>On-campus Housing Groups</h2>
<p>To re-apply for housing with a particular program on-campus,
select that group in the box below. If you are approved by the
group/program you apply for, then you will be administratively assigned
and may not be able to choose your room.</p>
<div>
<fieldset style="width: 65%;"><legend>Residential Learning
Communities</legend> {RLC_INTEREST}{RLC_INTEREST_LABEL}</fieldset>
</div>

<!-- BEGIN greek -->
<div>
<fieldset style="width: 65%;"><legend>Greek Letter
Organizations</legend> {SORORITY_CHECK}{SORORITY_CHECK_LABEL}
<div id="sorority_options" style="padding-left: 30px;">
{SORORITY_DROP_LABEL}&nbsp;{SORORITY_DROP}<br />
{SORORITY_PREF_1}{SORORITY_PREF_1_LABEL}<br />
{SORORITY_PREF_2}{SORORITY_PREF_2_LABEL}</div>
</fieldset>
</div>
<!-- END greek -->

<!-- BEGIN teaching -->
<div>
<fieldset style="width: 65%;"><legend>Teaching
Fellows</legend> {TF_PREF_1}{TF_PREF_1_LABEL}<br />
{TF_PREF_2}{TF_PREF_2_LABEL}</fieldset>
</div>
<!-- END teaching -->

<!-- BEGIN watauga -->
<div>
<fieldset style="width: 65%;"><legend>Watauga Global</legend>
{WG_PREF_1}{WG_PREF_1_LABEL}<br />
{WG_PREF_2}{WG_PREF_2_LABEL}</fieldset>
</div>
<!-- END watauga -->

<!-- BEGIN honors -->
<div>
<fieldset style="width: 65%;"><legend>Heltzer Honors
Program</legend> {HONORS_PREF_1}{HONORS_PREF_1_LABEL}<br />
{HONORS_PREF_2}{HONORS_PREF_2_LABEL}</fieldset>
</div>
<!-- END honors -->

<h2>Special needs</h2>
<p>{SPECIAL_NEED}&nbsp;{SPECIAL_NEED_LABEL}</p>

<h2>Terms & Conditions</h2>
<p>{DEPOSIT_CHECK}{DEPOSIT_CHECK_LABEL}</p>

<br />

{SUBMIT} {END_FORM}
