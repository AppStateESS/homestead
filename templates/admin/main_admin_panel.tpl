<h1>Housing Screen</h1>

<table width="100%" cellpadding="2" cellspacing="1" rows="3" cols="3" border="0">
    <tr>
        <td colspan="3">
            <!-- BEGIN student_lookup -->
            {BEGIN_FORM}
            {TERM_LABEL}{TERM}&nbsp;
            {STUDENT_ID_LABEL}{STUDENT_ID}&nbsp;
            {RESIDENCE_HALL_LOOKUP_LABEL}{RESIDENCE_HALL_LOOKUP}&nbsp;
            {ROOM_NUM_LOOKUP_LABEL}{ROOM_NUM_LOOKUP}&nbsp;
            {BED_NUM_LOOKUP_LABEL}{BED_NUM_LOOKUP}&nbsp;
            {LOOKUP_SUBMIT_LABEL}{LOOKUP_SUBMIT}&nbsp;
            {END_FORM}
            <!-- END student_lookup -->
        </td>
    </tr>
    <!-- BEGIN display_form -->
    {BEGIN_FORM}
    <tr>
        <td colspan="2">
            <!-- PERSONAL INFORMATION -->
            <fieldset><legend>Personal Information</legend>
                <table width="100%" cellpadding="1" rows="3" cols="2" border="0">
                    <tr>
                        <td rowspan="2">
                            <table rows="5" cols="2">
                                <tr>
                                    <td width="30%">{FIRST_NAME_LABEL}</td>
                                    <td align="left">{FIRST_NAME}</td>
                                </tr>
                                <tr>
                                    <td width="30%">{LAST_NAME_LABEL}</td>
                                    <td align="left">{LAST_NAME}</td>
                                </tr>
                                <tr>
                                    <td width="30%">{MIDDLE_INITIAL_LABEL}</td>
                                    <td align="left">{MIDDLE_INITIAL}</td>
                                </tr>
                                <tr>
                                    <td width="30%">{EMAIL_LABEL}</td>
                                    <td align="left">{EMAIL}</td>
                                </tr>
                                <tr>
                                    <td width="30%">{CELL_PHONE_LABEL}</td>
                                    <td align="left">{CELL_PHONE}</td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <fieldset><legend>Class Status</legend>
                                <table>
                                    <tr>
                                        <td>{CLASS_STATUS_2}{CLASS_STATUS_2_LABEL}</td>
                                        <td>{CLASS_STATUS_3}{CLASS_STATUS_3_LABEL}</td>
                                        <td>{CLASS_STATUS_4}{CLASS_STATUS_4_LABEL}</td>
                                        <td>{CLASS_STATUS_5}{CLASS_STATUS_5_LABEL}</td>
                                        <td>{CLASS_STATUS_6}{CLASS_STATUS_6_LABEL}</td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" cellpadding="2" rows="1" cols="2">
                                <tr>
                                    <td>
                                        <fieldset><legend>Student Type</legend>
                                            <table>
                                                <tr>
                                                    <td>{STUDENT_TYPE_1}</td><td align="left">{STUDENT_TYPE_1_LABEL}</td>
                                                </tr>
                                                <tr>
                                                    <td>{STUDENT_TYPE_2}</td><td align="left">{STUDENT_TYPE_2_LABEL}</td>
                                                </tr>
                                                <tr>
                                                    <td>{STUDENT_TYPE_3}</td><td align="left">{STUDENT_TYPE_3_LABEL}</td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                    </td>
                                    <td align="left">
                                        <fieldset><legend>Gender</legend>
                                            <table>
                                                <tr>
                                                    <td align="left">{GENDER_1}{GENDER_1_LABEL}</td>
                                                </tr>
                                                <tr>
                                                    <td align="left">{GENDER_2}{GENDER_2_LABEL}</td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {DOB_MONTH_LABEL}{DOB_MONTH}{DOB_DAY}{DOB_YEAR}
                        </td>
                        <td>
                            {APPLICATION_RECEIVED_MONTH_LABEL}
                            {APPLICATION_RECEIVED_MONTH}
                            {APPLICATION_RECEIVED_DAY}
                            {APPLICATION_RECEIVED_YEAR}
                        </td>
                    </tr>
                </table>
            </fieldset>
            <!-- END PERSONAL INFORMATION -->
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <!-- ASSIGNMENT INFORMATION -->
            <fieldset><legend>Assignment Information</legend>
                <table rows="3" cols="1">
                    <tr>
                        <td>
                            {ASSIGN_RESIDENCE_HALL_LABEL}{ASSIGN_RESIDENCE_HALL}&nbsp;
                            {ASSIGN_FLOOR_LABEL}{ASSIGN_FLOOR}&nbsp;
                            {ASSIGN_ROOM_NUM_LABEL}{ASSIGN_ROOM_NUM}&nbsp;
                            {ASSIGN_BED_NUM_LABEL}{ASSIGN_BED_NUM}&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {ASSIGN_PHONE_NUM_LABEL}{ASSIGN_PHONE_NUM}&nbsp;
                            {ASSIGN_ROOM_TYPE_LABEL}{ASSIGN_ROOM_TYPE}&nbsp;
                            {ASSIGN_MEAL_OPTION_LABEL}{ASSIGN_MEAL_OPTION}&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {ASSIGNED_BY_LABEL}{ASSIGNED_BY}&nbsp;
                            {ASSIGN_MONTH_LABEL}{ASSIGN_MONTH}{ASSIGN_DAY}{ASSIGN_YEAR}&nbsp;
                        </td>
                    </tr>
                </table>
            </fieldset>
            <!-- END ASSIGNMENT INFORMATION -->
        </td>
    </tr>
    <tr>
        <td>
            <!-- PREFERENCE INFORMATION -->
            <fieldset><legend>Preference Information</legend>
                <table>
                    <tr>
                        <td width="30%" align="left">{PREF_NEATNESS_LBL}</td>
                        <td align="left">{PREF_NEATNESS_1}{PREF_NEATNESS_1_LABEL}</td>
                        <td align="left">{PREF_NEATNESS_2}{PREF_NEATNESS_2_LABEL}</td>
                    </tr>
                    <tr>
                        <td width="30%" align="left">{PREF_BEDTIME_LBL}</td>
                        <td align="left">{PREF_BEDTIME_1}{PREF_BEDTIME_1_LABEL}</td>
                        <td align="left">{PREF_BEDTIME_2}{PREF_BEDTIME_2_LABEL}</td>
                    </tr>
                    <tr>
                        <td width="30%" align="left">{PREF_LIFESTYLE_LBL}</td>
                        <td align="left">{PREF_LIFESTYLE_1}{PREF_LIFESTYLE_1_LABEL}</td>
                        <td align="left">{PREF_LIFESTYLE_2}{PREF_LIFESTYLE_2_LABEL}</td>
                    </tr>
                </table>
            </fieldset>
            <!-- END PREFERENCE INFORMATION -->
        </td>
        <td>
            <!-- ROOMATE INFORMATION -->
            <fieldset><legend>Roomate Information</legend>
                <table rows="4" cols="2">
                    <tr>
                        <td width="40%">{ROOMATE_NAME_LABEL}</td>
                        <td align="left">{ROOMATE_NAME}</td>
                    </tr>
                    <tr>
                        <td width="40%">{ROOMATE_ID_LABEL}</td>
                        <td align="left">{ROOMATE_ID}</td>
                    </tr>
                    <tr>
                        <td width="40%">{ROOMATE_HOME_PHONE_LABEL}</td>
                        <td align="left">{ROOMATE_HOME_PHONE}</td>
                    </tr>
                    <tr>
                        <td width="40%">{PAIRED_BY_LABEL}</td>
                        <td align="left">{PAIRED_BY}</td>
                    </tr>
                </table>
            </fieldset>
            <!-- END ROOMATE INFORMATION -->
        </td>
    </tr>
    <tr>
        <td>
            <!-- DEPOSIT INFORMATION  -->
            <fieldset><legend>Deposit Info</legend>
                <table>
                    <tr>
                        <td>{DEPOSIT_MONTH_LABEL}</td><td align="left">{DEPOSIT_MONTH}</td><td>{DEPOSIT_DAY}</td><td>{DEPOSIT_YEAR}</td>
                    </tr>
                    <tr>
                        <td>{DEPOSIT_AMOUNT_LABEL}</td><td align="left">{DEPOSIT_AMOUNT}</td><td></td><td></td>
                    </tr>
                    <tr>
                        <td>{WAIVER_CHECK_LABEL}</td><td align="left">{WAIVER_CHECK}</td><td></td><td></td>
                    </tr>
                </table>
                <fieldset><legend>Forfeiture</legend>
                    <table>
                        <tr>
                            <td>{FORFEITURE_1}{FORFEITURE_1_LABEL}</td>
                        </tr>
                        <tr>
                            <td>{FORFEITURE_2}{FORFEITURE_2_LABEL}</td>
                        </tr>
                        <tr>
                            <td>{FORFEITURE_3}{FORFEITURE_3_LABEL}</td>
                        </tr>
                    </table>
                </fieldset>
            </fieldset>
            <!-- END DEPOSIT INFORMATION  -->
        </td>
        <td rowspan="2">
            <!-- WITHDRAWAL  -->
            <fieldset><legend>Withdrawal</legend>
                <table>
                    <tr>
                        <td>{WITHDRAWAL_1}</td><td align="left">{WITHDRAWAL_1_LABEL}</td>
                    </tr>
                    <tr>
                        <td>{WITHDRAWAL_2}</td><td align="left">{WITHDRAWAL_2_LABEL}</td>
                    </tr>
                    <tr>
                        <td>{WITHDRAWAL_3}</td><td align="left">{WITHDRAWAL_3_LABEL}</td>
                    </tr>
                    <tr>
                        <td>{WITHDRAWAL_4}</td><td align="left">{WITHDRAWAL_4_LABEL}</td>
                    </tr>
                    <tr>
                        <td>{WITHDRAWAL_5}</td><td align="left">{WITHDRAWAL_5_LABEL}</td>
                    </tr>
                    <tr>
                        <td>{WITHDRAWAL_6}</td><td align="left">{WITHDRAWAL_6_LABEL}</td>
                    </tr>
                    <tr>
                        <td>{WITHDRAWAL_7}</td><td align="left">{WITHDRAWAL_7_LABEL}</td>
                    </tr>
                </table>
            </fieldset>
            <!-- END WITHDRAWAL -->
        </td>
    </tr>
    {END_FORM}
    <!-- END display_form -->
</table>
