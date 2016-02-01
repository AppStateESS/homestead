<h3>{ICON} <span class={STATUS}>Residential Learning Community Self-Selection</span></h3>

<div class="block-content">
  <div class="text-muted">Available: {DATES}</div>

  <p>
    <!-- BEGIN too_soon -->
    Residential Learning Community re-application for this term will begin on {BEGIN_DEADLINE}.
    <!-- END too_soon -->

    <!-- BEGIN too_late -->
    Residential Learning Community re-application for this term ended on {END_DEADLINE}.
    <!-- END too_late -->

    <!-- BEGIN select_room -->
    You have been invited to join {INVITED_COMMUNITY_NAME}. You may {SELECT_LINK}.
    <!-- END select_room -->

    <!-- BEGIN self-assigned -->
    You have been assigned to {SELF_ASSIGNMENT} with {ASSIGNED_COMMUNITY_NAME}.<br />
    Please note this assignment is subject to change at anytime.  Your final room assignment will be sent to your Appalachian email in July.
    <!-- END self-assigned -->

    <!-- BEGIN assigned -->
    You have been assigned.<br />
    Please note this assignment is subject to change at anytime.  Your final room assignment will be sent to your Appalachian email in July.
    {ASSIGNMENT}
    <!-- END assigned -->

    <!-- BEGIN not_eligible -->
    {NOT_ELIGIBLE}
    You are not eligible for RLC self-selection because you are not a member of a Learning Community.
    <!-- END not_eligible -->
  </p>

  <!-- BEGIN roommate_request -->
  <p>
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
  </p>

</div>
