        <font color="red"><i>{MESSAGE}</i></font>
        {REDO} {NEWLINES}
        <!-- BEGIN application_form -->
        {START_FORM}
        <table>
            <tr>
                <th>Name: </th><td align="left">{STUDENT_NAME}</td>
            </tr>
            <tr>
                <th>Gender: </th><td align="left">{GENDER}</td>
            </tr>
            <tr>
                <th>Student Status: </th><td align="left">{STUDENT_STATUS_LBL}</td>
            </tr>
            <tr><th></th><td></td></tr>
            <tr>
                <th>Application Term: </th><td alighn="left">{ENTRY_TERM}</td>
            </tr>
            <tr><th></th><td></td></tr>
            <tr>
                <th>Classification: </th><td align="left">{CLASSIFICATION_FOR_TERM_LBL}</td>
            </tr>
            <tr><th></th><td></td></tr>
            <tr>
                <th>Meal Option: </th><td align="left">{MEAL_OPTION}</td>
            </tr>
            <tr><th></th><td></td></tr>
            <tr>
                <th>Lifestyle Option: </th><td align="left">{LIFESTYLE_OPTION}</td>
            </tr>
            <tr><th></th><td></td></tr>
            <tr>
                <th>Preferred Bedtime: </th><td align="left">{PREFERRED_BEDTIME}</td>
            </tr>
            <tr><th></th><td></td></tr>
            <tr>
                <th>Room Condition: </th><td align="left">{ROOM_CONDITION}</td>
            </tr>
            <!-- BEGIN rlc_interest_1 -->
            <tr><th> </th><td> </td></tr>
            <tr>
                <th>
                Do you have any special needs?<br />
                <div style="font-size: 9px">
                <a href="spec_needs.html" target="_blank">(More information)</a>
                <div>
                </th>
                <!-- BEGIN special_needs_1 -->
                <td>
                {SPECIAL_NEEDS_1}{SPECIAL_NEEDS_1_LABEL} <a href="spec_needs.html#physical" target="_blank">(more info)</a><br />
                {SPECIAL_NEEDS_2}{SPECIAL_NEEDS_2_LABEL} <a href="spec_needs.html#psych" target="_blank">(more info)</a><br />
                {SPECIAL_NEEDS_3}{SPECIAL_NEEDS_3_LABEL} <a href="spec_needs.html#medical" target="_blank">(more info)</a><br />
                {SPECIAL_NEEDS_4}{SPECIAL_NEEDS_4_LABEL} <a href="spec_needs.html#gender" target="_blank">(more info)</a><br />
                </td>
                <!-- END special_needs_1 -->
                <!-- BEGIN special_needs_result -->
                <td>
                {SPECIAL_NEEDS_RESULT}
                </td>
                <!-- END special_needs_result -->
            </tr>
            <tr><th> </th><td> </td></tr>
            <tr>
                <th>Are you interested in a <a href="http://housing.appstate.edu/index.php?module=pagemaster&PAGE_user_op=view_page&PAGE_id=134" target="_blank">unique housing option</a>?</th>
                <td align="left">{RLC_INTEREST_1} {RLC_INTEREST_1_LABEL}</td>
            </tr>
            <tr>
                <th></th><td align="left">{RLC_INTEREST_2} {RLC_INTEREST_2_LABEL}</td>
            </tr>
            <!-- END rlc_interest_1 -->
        </table>
        <br /><br />
        {SUBMIT}
        {END_FORM}
        <!-- END application_form --> 
