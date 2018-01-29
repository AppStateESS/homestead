<h3>{ICON} <span class={STATUS}>Re-Application</span></h3>

<div class="block-content">
  <div class="text-muted">Available: {DATES}</div>
  <small class="text-muted">Note: "Start Date" and "End Date" imply 12:01 AM on those dates. Re-application will be available all day on the selected "Start Date", but will <strong>not</strong> be available at all on the \
    "End Date".</small>
  <div>
    <!-- BEGIN eligible -->
    You are eligible to re-apply for on-campus housing for the {LOTTERY_TERM_1} - {NEXT_TERM_1} academic year. {ENTRY_LINK}
    <!-- END eligible -->

    <!-- BEGIN not_eligible -->
    You are not eligible to re-apply for on-campus housing for the {LOTTERY_TERM_2} - {NEXT_TERM_2} academic year.<br />
    <!-- END not_eligible -->

    <!-- BEGIN too_soon -->
    Re-application for this term will begin on {BEGIN_DEADLINE}.
    <!-- END too_soon -->

    <!-- BEGIN too_late -->
    Re-application for this term ended on {END_DEADLINE}.
    <!-- END too_late -->

    <!-- BEGIN applied -->
    {ALREADY_APPLIED}
    You have re-applied for on-campus housing for this term. You will be notified by email if you are selected.<br />
    <!-- END applied -->

    <!-- BEGIN select -->
    You have been selected for re-application. You have until {EXPIRE_DATE} to confirm your room selection and request roommates. Please click the link below to choose your room.<br />
    {SELECT_LINK}
    <!-- END select -->

    <!-- BEGIN hard_cap -->
    Sorry, re-application is now closed.
    {HARD_CAP}
    <!-- END hard_cap -->

    <!-- BEGIN assigned -->
    You have been re-assigned.<br />
    Please note <strong>this assignment is subject to change</strong> at anytime.  Your final room assignment will be sent to your Appalachian email in July.
    {ASSIGNED}
    <!-- END assigned -->

    <!-- BEGIN roommate_request -->
    <br />
    {ROOMMATE_REQUEST}
    You have one or more pending roommate requests. Click the name of the person who requested you to view the details of that request. You will then have the option to approve or deny the request.<br />
  <ul>
    <!-- BEGIN roommates -->
    <li>
      {ROOMMATE_LINK}
    </li>
    <!-- END roommates -->
  </ul>
  <!-- END roommate_request -->
  </div>
</div>
