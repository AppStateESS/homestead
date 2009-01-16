<div class="hms">
    <div class="box">
        <div class="box-title"><h2>Welcome to the Housing Management System</h2></div>
        <div class="box-content">
        Welcome to Appalachian State University Housing and Residence Life. As a returning student you must re-apply and be selected in order to be guaranteed on-campus housing for Fall 2009. Students will be selected at random. If you are selected you will be notified by email. For more information on how the selection process works, please read the <a href="http://housing.appstate.edu/" target="_blank">housing lottery FAQ</a>.<br />
        <br />
        Below you may (optionally) provide the ASU user names of up to three of your preferred roommates. <strong>Please note, we cannot guarantee that you will be assigned with your preferred roommates.</strong> Your roommate selection will be limited by your roommate(s)'s availability and space availability in the residence hall/room you choose at the time you are selected. However, we will notify the students you list below of your desire to be roommates and invite them to enter the housing lottery (if they have not already).<br /><br />
        <!-- BEGIN error_message -->
        <div style="color: red;">{ERROR_MESSAGE}</div><br />
        <!-- END error_message -->
        {START_FORM}
        <table>
            <tr>
                <th colspan="2">Contact Information</th>
            </tr>
            <tr>
                <td>Cell Phone Number: </td><td align="left">({AREA_CODE})-{EXCHANGE}-{NUMBER}</td>
            </tr>
            <tr>
                <td></td>
                <td>{DO_NOT_CALL}<sub>Check here if you do not have or do not wish to provide your cellphone number.</sub></td>
            </tr>
            <tr>
                <th colspan="2">Preferred Roommates</th>
            </tr>
            <tr>
                <td>First roommate: </td>
                <td>{ROOMMATE1}@appstate.edu</td>
            </tr>
            <tr>
                <td>Second roommate: </td>
                <td>{ROOMMATE2}@appstate.edu</td>
            </tr>
            <tr>
                <td>Third roommate: </td>
                <td>{ROOMMATE3}@appstate.edu</td>
            </tr>
            <tr>
                <th colspan="2">Special Interest Housing</th>
            </tr>
            <tr>
                <td colspan="2">To re-apply for housing with a particular program on-campus select that group in the box below. Each group/program may have a separate (on-line or paper-based) application to complete. If you are approved by the group/program you apply for then you will be administratively assigned and may not be able to choose your room.</td>
            </tr>
            <tr>
                <td>{SPECIAL_INTEREST_LABEL}</td>
                <td>{SPECIAL_INTEREST}</td>
            </tr>
            <tr>
                <th colspan="2">Special needs</th>
            </tr>
            <tr>
                <td colspan="2">{SPECIAL_NEED}&nbsp;{SPECIAL_NEED_LABEL}</td>
            </tr>
            <tr>
                <th colspan="2">Terms & Conditions</th>
            </tr>
            <tr>
                <td colspan="2">{TERMS_CHECK}{TERMS_CHECK_LABEL}</td>
            </tr>
        </table>
        <br />
        <br />
        {SUBMIT}
        {END_FORM}
        </div>
    </div>
</div>
