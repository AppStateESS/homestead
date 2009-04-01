<div class="hms">
  <div class="box">
    <div class="box-title"><h2>On-campus Housing Re-application</h2></div>

    <div align="right">
        {LOGOUT_LINK}
    </div>

    <!-- BEGIN error_msg -->
    <span class="error">{ERROR_MSG}<br /></span>
    <!-- END error_msg -->
    
    <!-- BEGIN success_msg -->
    <span class="success">{SUCCESS_MSG}<br /></span>
    <!-- END success_msg -->

    Use the menu below the re-apply for on-campus housing. You must complete an application for each available term individually.

    <div class="box-content">
      <ul>
        <li>
          <h2>{LOTTERY_TERM} - {NEXT_TERM}</h2>
          <!-- BEGIN assigned -->
          {ASSIGNED}
          You have already been assigned for this term.<br />
          <!-- END assigned -->
          <!-- BEGIN eligible -->
          You are eligible to re-apply for on-campus housing for the {LOTTERY_TERM} - {NEXT_TERM} academic year. Please click the link below to apply.<br />
          {ENTRY_LINK}
          <!-- END eligible -->
          <!-- BEGIN applied -->
          {ALREADY_APPLIED}
          You have already re-applied for on-campus housing for this term. You will be notified by email if you are selected.<br />
          <!-- END applied -->
          <!-- BEGIN select -->
          You have been selected for re-application. You have until {EXPIRE_DATE} to confirm your room selection and request roommates. Please click the link below to choose your room.<br />
          {SELECT_LINK}
          <!-- END select -->
          <!-- BEGIN not_eligible -->
          {not_eligible}
          You are not eligible to re-apply for on-campus housing for the {LOTTERY_TERM} - {NEXT_TERM} academic year.<br />
          <!-- END not_eligible -->
          <!-- BEGIN too_soon -->
          Re-application for this term will begin on {BEGIN_DEADLINE}.
          <!-- END too_soon -->
          <!-- BEGIN too_late -->
          Re-application for this term ended on {END_DEADLINE}.
          <!-- END too_late -->
          <!-- BEGIN roommate_request -->
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
        </li>
        <li>
          <h2>{SUMMER_1_TERM}</h2>

          <!-- BEGIN summer1_assigned -->
          {SUMMER1_ASSIGNED}
          You have already been assigned for this term.<br />
          <!-- END summer1_assigned -->

          <!-- BEGIN summer1_applied -->
          {SUMMER1_APPLIED}
          You have already applied for on-campus housing for the {SUMMER_1_TERM} term.<br />
          <!-- END summer1_applied -->

          <!-- BEGIN summer1_too_early -->
          Re-application for on-campus housing for the {SUMMER_1_TERM} term will begin on {SUMMER1_START_DEADLINE}.<br />
          <!-- END summer1_too_early -->

          <!-- BEGIN summer1_too_late -->
          Re-application for on-campus housing for the {SUMMER_1_TERM} term ended on {SUMMER1_END_DEADLINE}.<br />
          <!-- END summer1_too_late -->

          <!-- BEGIN summer1_link -->
          You are eligible to re-apply for on-campus housing for the {SUMMER_1_TERM} term. Click the link below to apply.<br />
          {SUMMER1_LINK}
          <!-- END summer1_link -->
        </li>
        <li>
          <h2>{SUMMER_2_TERM}</h2>

          <!-- BEGIN summer2_assigned -->
          {SUMMER2_ASSIGNED}
          You have already been assigned for this term.<br />
          <!-- END summer2_assigned -->

          <!-- BEGIN summer2_applied -->
          {SUMMER2_APPLIED}
          You have already applied for on-campus housing for the {SUMMER_2_TERM} term.<br />
          <!-- END summer2_applied -->

          <!-- BEGIN summer2_too_early -->
          Re-application for on-campus housing for the {SUMMER_2_TERM} term will begin on {SUMMER2_START_DEADLINE}.<br />
          <!-- END summer2_too_early -->

          <!-- BEGIN summer2_too_late -->
          Re-application for on-campus housing for the {SUMMER_2_TERM} term ended on {SUMMER2_END_DEADLINE}.<br />
          <!-- END summer2_too_late -->

          <!-- BEGIN summer2_link -->
          You are eligible to re-apply for on-campus housing for the {SUMMER_2_TERM} term. Click the link below to apply.<br />
          {SUMMER2_LINK}
          <!-- END summer2_link -->
        </li>
      <ul>
    </div>
  </div>
</div>
