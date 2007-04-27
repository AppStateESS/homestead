<div class="hms">
  <div class="box">
    <div class="box-title"><h1>My Profile</h1></div>
    <div class="box-content">
        <font color="red"><i>{MESSAGE}</i></font><br>
        <!-- BEGIN rlc_form -->
        {START_FORM}
        <table cellspacing="2" cellpadding="3">
            <tr>
                <th colspan="2">1. About Me</th>
            </tr>
            <tr>
                <td colspan="2">
                    <table width="100%" cellspacing="2" cellpadding="3" rows="1" cols="2">
                        <tr>
                            <td valign="top" width="50%">{HOBBIES_CHECKBOX_QUESTION}<br />
                            <!-- BEGIN hobbies_checkbox_repeat -->
                              {HOBBIES_CHECKBOX}{HOBBIES_CHECKBOX_LABEL}<br>
                            <!-- END hobbies_checkbox_repeat -->
                            </td>
                            <td valign="top" width="50%">{MUSIC_CHECKBOX_QUESTION}<br />
                            <!-- BEGIN music_checkbox_repeat -->
                              {MUSIC_CHECKBOX}{MUSIC_CHECKBOX_LABEL}<br>
                            <!-- END music_checkbox_repeat -->
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr valign="top">
                <td>{POLITICAL_VIEWS_DROPBOX_LABEL}</td>
                <td>{POLITICAL_VIEWS_DROPBOX}<br />&nbsp;</td>
            </tr>
            <tr>
                <th colspan="2">2. College Life</th>
            </tr>
            <tr valign="top">
                <td>{INTENDED_MAJOR_LABEL}</td>
                <td>{INTENDED_MAJOR}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{IMPORTANT_EXPERIENCE_LABEL}</td>
                <td>{IMPORTANT_EXPERIENCE}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{ALTERNATE_EMAIL_LABEL}</td>
                <td>{ALTERNATE_EMAIL}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{AIM_SN_LABEL}</td>
                <td>{AIM_SN}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{YAHOO_SN_LABEL}</td>
                <td>{YAHOO_SN}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{MSN_SN_LABEL}</td>
                <td>{MSN_SN}<br />&nbsp;</td>
            </tr>
            <tr>
                <th colspan="2">3. My Daily Life</th>
            </tr>
            <tr valign="top">
                <td>{SLEEP_TIME_LABEL}</td>
                <td>{SLEEP_TIME}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{WAKEUP_TIME_LABEL}</td>
                <td>{WAKEUP_TIME}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{OVERNIGHT_GUESTS_LABEL}</td>
                <td>{OVERNIGHT_GUESTS}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{LOUDNESS_LABEL}</td>
                <td>{LOUDNESS}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{CLEANLINESS_LABEL}</td>
                <td>{CLEANLINESS}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{STUDY_TIMES_QUESTION}</td>
                <td>
                <!-- BEGIN study_times_repeat -->
                  {STUDY_TIMES}{STUDY_TIMES_LABEL}<br />
                <!-- END study_times_repeat -->
                &nbsp;
                </td>
            </tr>
            <tr valign="top">
                <td>{FREE_TIME_LABEL}</td>
                <td>{FREE_TIME}<br />&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2">{SUBMIT}</td>
            </tr>
        </table>
        {END_FORM}
        <!-- END rlc_form -->
    </div>
  </div>
</div>
