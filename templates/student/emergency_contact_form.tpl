<div class="hms">
  <div class="box">
    <div class="title"> <h1>{TERM} Emergency Contact & Missing Person Information</h1><p>{RECEIVED_DATE}</p> </div>
    <div class="box-content">
        <!-- BEGIN withdrawn -->
        <font color="red"><b>{WITHDRAWN}</b></font>
        <!-- END withdrawn -->
        <!-- BEGIN review_msg -->
        {REVIEW_MSG}
        Please review the information you entered. If you need to go back and make changes, click the 'modify your information' link below. If the information you have entered is correct click the 'Confirm and Continue' button.
        <!-- END review_msg -->
        {START_FORM}
        <table>
            <tr>
                <th colspan="2">Demographic Information</th>
            </tr>
            <tr>
                <td>Name: </td><td align="left">{STUDENT_NAME}</td>
            </tr>
            <tr>
            	<th colspan="2">Emergency Contact Information</th>
            <tr>
            <tr>
            	<td>Emergency Contact Person Name:</td>
            	<td>{EMERGENCY_CONTACT_NAME}</td>
            </tr>
            <tr>
            	<td>Relationship:</td>
            	<td>{EMERGENCY_CONTACT_RELATIONSHIP}</td>
            </tr>
            <tr>
            	<td>Phone Number:</td>
            	<td>{EMERGENCY_CONTACT_PHONE}</td>
            </tr>
            <tr>
            	<td>Email:</td>
            	<td>{EMERGENCY_CONTACT_EMAIL}</td>
            </tr>
            <tr>
            	<td colspan="2">Are there any medical conditions you have which our staff should be aware of? (This information will be kept confidential and will only be shared with the staff in your residence hall. However, this information <strong>may</strong> be disclosed to medical/emergency personnel in case of an emergency.)</td>
            </tr>
            <tr>
            	<td colspan="2">{EMERGENCY_MEDICAL_CONDITION}</td>
            <tr>
            	<th colspan="2">Missing Person Information</th>
            </tr>
            <tr>
            	<td colspan="2">According to the recent update of the Higher Education Act, all schools are required to ask students who they wish the University to contact should they become missing. Please list your contact person's information below:</td>
            </tr>
            <tr>
            	<td>Contact Person Name:</td>
            	<td>{MISSING_PERSON_NAME}</td>
            </tr>
            <tr>
            	<td>Relationship:</td>
            	<td>{MISSING_PERSON_RELATIONSHIP}</td>
            </tr>
            <tr>
            	<td>Phone Number:</td>
            	<td>{MISSING_PERSON_PHONE}</td>
            </tr>
            <tr>
            	<td>Email:</td>
            	<td>{MISSING_PERSON_EMAIL}</td>
            </tr>
        </table>
        <br /><br />
        {SUBMIT}
        <!-- BEGIN redo_form -->
        or {REDO_BUTTON}
        <!-- END redo_form -->
        {SUBMIT_APPLICATION}
        {END_FORM}
    </div>
  </div>
</div>
