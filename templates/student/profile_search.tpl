<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>Roommate Profile Search</h1> </div>
    <div class="box-content">
        Select the qualities and attributes below which you would like your roommate to posses. This tool only searches for <b>exact</b> matches
        to the information you supply below. If no results are found,
        you should try a broader search by selecting fewer attributes. If you enter an ASU user name, all other fields will be ignored.
        <br />
        <br />
        <!-- BEGIN search_form -->
        {START_FORM}
        <table cellspacing="2" cellpadding="3">
            <tr>
                <th colspan="2">1. Search By ASU Username</th>
            </tr>
            <tr>
                <td>{ASU_USERNAME_LABEL}</td>
                <td>{ASU_USERNAME}@appstate.edu</td>
            </tr>
            <tr>
                <th colspan="2">2. Music &amp; Hobbies</th>
            </tr>
            <tr>
                <td colspan="2">
                    <table width="100%" cellspacing="2" cellpadding="3">
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
                <th colspan="2">3. College Life</th>
            </tr>
            <tr valign="top">
                <td>{INTENDED_MAJOR_LABEL}</td>
                <td>{INTENDED_MAJOR}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{IMPORTANT_EXPERIENCE_LABEL}</td>
                <td>{IMPORTANT_EXPERIENCE}<br />&nbsp;</td>
            </tr>
            <tr>
                <th colspan="2">4. Daily Life</th>
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
        <!-- END search_form -->
    </div>
  </div>
</div>
