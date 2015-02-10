{ICON}

<h3>
<div class={STATUS}>Residential Learning Community Self-Selection</div>
</h3>

<div class="block-content">
  <div class="availability-dates">Available: {DATES}</div>
  
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
    
    <!-- BEGIN assigned -->
    You have been assigned to {ASSIGNMENT} with {ASSIGNED_COMMUNITY_NAME}.<br />
    Please note this assignment is subject to change at anytime.  Your final room assignment will be sent to your Appalachian email in July.
    <!-- END assigned -->
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