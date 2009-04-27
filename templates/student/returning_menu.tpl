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
          <h1>{LOTTERY_TERM} - {NEXT_TERM}</h1>
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
          <h1>{SUMMER_1_TERM}</h1>

          <ul>
            <li><h2>Application</h2>
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
            <li><h2>Roommate Request</h2>
              <!-- BEGIN summer1_roommate_not_applied -->{SUMMER1_ROOMMATE_NOT_APPLIED}
                You need apply for on-campus housing for this term first.
              <!-- END summer1_roommate_not_applied -->
              <!-- BEGIN summer1_roommate_too_early -->
                Roommate selection will be available for this term on {SUMMER1_ROOMMATE_START_DATE}.
              <!-- END summer1_roommate_too_early -->
              <!-- BEGIN summer1_roommate_too_late -->
                Roommate selection for this term ended on {SUMMER1_ROOMMATE_END_DATE}.
              <!-- END summer1_roommate_too_late -->
              <!-- BEGIN summer1_roommate_pending -->
                You have requested {SUMMER1_PENDING_ROOMMATE_NAME} as your roommate and are awaiting his/her confirmation.
              <!-- END summer1_roommate_pending -->
              <!-- BEGIN summer1_roommate_confirmed -->
                {SUMMER1_CONFIRMED_ROOMMATE_NAME} has confirmed your roommate request.
              <!-- END summer1_roommate_confirmed -->
              <!-- BEGIN summer1_roommate_request_link -->
                You may request a roommate for this term by clicking on the link below.<br />
                {SUMMER1_ROOMMATE_REQUEST_LINK}<br />
              <!-- END summer1_roommate_request_link -->
              <!-- BEGIN summer1_roommate_requests -->
                You have one or more pending roommate requests. Click the name of the person who requested you to view the details of that request. You will then have the option to approve or deny the request.<br />
                {SUMMER1_ROOMMATE_REQUESTS}
              <!-- END summer1_roommate_requests -->

            </li>
            <li><h2>Assignment</h2>
              <!-- BEGIN summer1_not_assigned -->{SUMMER1_NOT_ASSIGNED}
                You have not been assigned to a room for this term.
              <!-- END summer1_not_assigned -->
                
              <!-- BEGIN summer1_assigned -->
              You have been assigned to {SUMMER1_ASSIGNED}.<br />
              <strong>Note:</strong> This information is updated frequently and is subject to change. Your listed assignment may not be final.
              <!-- END summer1_assigned -->
            </li>
          </ul>
        </li>
        <li>
          <h1>{SUMMER_2_TERM}</h1>
          <ul>
            <li><h2>Application</h2>
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
            <li><h2>Roommate Request</h2>
              <!-- BEGIN summer2_roommate_not_applied -->{SUMMER2_ROOMMATE_NOT_APPLIED}
                You need apply for on-campus housing for this term first.
              <!-- END summer2_roommate_not_applied -->
              <!-- BEGIN summer2_roommate_too_early -->
                Roommate selection will be available for this term on {SUMMER2_ROOMMATE_START_DATE}.
              <!-- END summer2_roommate_too_early -->
              <!-- BEGIN summer2_roommate_too_late -->
                Roommate selection for this term ended on {SUMMER2_ROOMMATE_END_DATE}.
              <!-- END summer2_roommate_too_late -->
              <!-- BEGIN summer2_roommate_pending -->
                You have requested {SUMMER2_PENDING_ROOMMATE_NAME} as your roommate and are awaiting his/her confirmation.
              <!-- END summer2_roommate_pending -->
              <!-- BEGIN summer2_roommate_confirmed -->
                {SUMMER2_CONFIRMED_ROOMMATE_NAME} has confirmed your roommate request.
              <!-- END summer2_roommate_confirmed -->
              <!-- BEGIN summer2_roommate_request_link -->
                You may request a roommate for this term by clicking on the link below.<br />
                {SUMMER2_ROOMMATE_REQUEST_LINK}<br />
              <!-- END summer2_roommate_request_link -->
              <!-- BEGIN summer2_roommate_requests -->
                You have one or more pending roommate requests. Click the name of the person who requested you to view the details of that request. You will then have the option to approve or deny the request.<br />
                {SUMMER2_ROOMMATE_REQUESTS}
              <!-- END summer2_roommate_requests -->
            </li>
            <li><h2>Assignment</h2>
              <!-- BEGIN summer2_not_assigned -->{SUMMER2_NOT_ASSIGNED}
                You have not been assigned to a room for this term.
              <!-- END summer2_not_assigned -->

              <!-- BEGIN summer2_assigned -->
              You have been assigned to {SUMMER2_ASSIGNED}.<br />
              <strong>Note:</strong> This information is updated frequently and is subject to change. Your listed assignment may not be final.
              <!-- END summer2_assigned -->
            </li>
          </ul>
        </li>
      <ul>
    </div>
  </div>
</div>
