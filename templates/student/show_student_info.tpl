<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        {MENU_LINK}<br /><br />
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR}</span><br />
        <!-- END error_msg -->
        <table cellspacing="8" cellpadding="1">
            <tr>
                <td>Full Name: </td><td>{FIRST_NAME} {MIDDLE_NAME} {LAST_NAME}</td>
            </tr>
            <tr>
                <td>ASU email address: &nbsp;&nbsp;&nbsp;&nbsp;</td><td>{USERNAME}@appstate.edu</td>
            </tr>
            <tr>
                <td>Banner id: &nbsp;&nbsp;&nbsp;&nbsp;</td><td>{BANNER_ID}</td>
            </tr>
            <tr>
                <td>Gender: </td><td>{GENDER}</td>
            </tr>
            <tr>
                <td>Date of Birth: </td><td>{DOB}</td>
            </tr>
            <tr>
                <td>Type: </td><td>{TYPE}</td>
            </tr>
            <tr>
                <td>Class: </td><td>{CLASS}</td>
            </tr>
            <tr>
                <td>Phone Number: </td><td>({PHONE_AC}) {PHONE_NUMBER}</td>
            </tr>
            <!-- BEGIN pr_address -->
            <tr>
                <td>Permanent address: </td><td>{PR_ADDRESS_L1}</td>
            </tr>
            <!-- BEGIN pr_address_2 -->
            <tr>
                <td></td><td>{PR_ADDRESS_L2}</td>
            </tr>
            <!-- END pr_address_2 -->
            <!-- BEGIN pr_address_3 -->
            <tr>
                <td></td><td>{PR_ADDRESS_L3}</td>
            </tr>
            <!-- END pr_address_3 -->
            <tr>
                <td></td><td>{PR_ADDRESS_CITY}, {PR_ADDRESS_STATE} {PR_ADDRESS_ZIP}</td>
            </tr>
            <!-- END pr_address -->
            <!-- BEGIN ps_address -->
            <tr>
                <td>Student address: </td><td>{PS_ADDRESS_L1}</td>
            </tr>
            <!-- BEGIN ps_address_2 -->
            <tr>
                <td></td><td>{PS_ADDRESS_L2}</td>
            </tr>
            <!-- END ps_address_2 -->
            <!-- BEGIN ps_address_3 -->
            <tr>
                <td></td><td>{PS_ADDRESS_L3}</td>
            </tr>
            <!-- END ps_address_3 -->
            <tr>
                <td></td><td>{PS_ADDRESS_CITY}, {PS_ADDRESS_STATE} {PS_ADDRESS_ZIP}</td>
            </tr>
            <!-- END ps_address -->
            <tr>
                <td>Application Status:</td><td>{APPLICATION}</td>
            </tr>
            <tr>
                <td>Roommate Status:</td><td>{ROOMMATE}</td>
            </tr>
            <tr>
                <td>Assigned To: </td><td>{ROOM_ASSIGNMENT}</td>
            </tr>
            <tr>
                <td>Meal Option: </td><td>{MEAL_PLAN}</td>
            </tr>
            <tr>
                <td>RLC Status: </td><td>{RLC_STATUS}</td>
            </tr>
            {LOGIN_AS_STUDENT}
        </table>
    </div>
  </div>
</div>


