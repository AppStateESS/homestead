<script>
function CountWords (this_field) 
{
      var char_count = this_field.prop('value').length;
      var fullStr = this_field.prop('value') + " ";
      var initial_whitespace_rExp = /^[^A-Za-z0-9]+/gi;
      var left_trimmedStr = fullStr.replace(initial_whitespace_rExp, "");
      var non_alphanumerics_rExp = rExp = /[^A-Za-z0-9]+/gi;
      var cleanedStr = left_trimmedStr.replace(non_alphanumerics_rExp, " ");
      var splitString = cleanedStr.split(" ");
      var word_count = splitString.length -1;
      var words_left = 500 - (splitString.length - 1);
      if (fullStr.length <1) {
            word_count = 0;
      }
      if (words_left == 1)
      {
      	wordOrWords = " word ";
      }
      else 
      {
      	wordOrWords = " words ";
      }
      str_words_left = String(words_left)

      if (words_left < 0)
      {
      	var formatted = "<span style='color:#ff0000'>" + str_words_left + "</span>";
      }
      else
      {
      	var formatted = str_words_left;
      }
      var retstring = formatted + wordOrWords + "remaining."
      document.getElementById('wrdcnt').innerHTML=retstring;
      
}
$().ready(function (){
	CountWords($("#profile_form_about_me"));
	$("#profile_form_about_me").keydown(function(){
		CountWords($("#profile_form_about_me"));
	});
});
</script>

<div class="hms">
  <div class="box">
    <div class="box-title"><h1>{TITLE}</h1></div>
    <div class="box-content">
    {MENU_LINK}
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
                        <tr>
                          <td valign="top" width="50%">{LANGUAGE_CHECKBOX_QUESTION}<br />
                            <!-- BEGIN language_checkbox_repeat -->
                            {LANGUAGE_CHECKBOX}{LANGUAGE_CHECKBOX_LABEL}<br>
                            <!-- END language_checkbox_repeat -->
                          </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr valign="top">
                <td>{POLITICAL_VIEWS_LABEL}</td>
                <td>{POLITICAL_VIEWS}<br />&nbsp;</td>
            </tr>
            <tr>
                <th colspan="2">2. College Life</th>
            </tr>
            <tr valign="top">
                <td>{MAJOR_LABEL}</td>
                <td>{MAJOR}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{EXPERIENCE_LABEL}</td>
                <td>{EXPERIENCE}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{ALTERNATE_EMAIL_LABEL}</td>
                <td>{ALTERNATE_EMAIL}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
            	<td>{FB_LINK_LABEL}</td>
                <td>{FB_LINK}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{INSTAGRAM_SN_LABEL}</td>
                <td>{INSTAGRAM_SN}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{TWITTER_SN_LABEL}</td>
                <td>{TWITTER_SN}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{TUMBLR_SN_LABEL}</td>
                <td>{TUMBLR_SN}<br />&nbsp;</td>
            </tr>
            <tr valign="top">
                <td>{KIK_SN_LABEL}</td>
                <td>{KIK_SN}<br />&nbsp;</td>
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
            <tr valign="top">
                <td>{ABOUT_ME_LABEL}</td>
                <td><div id="wrdcnt"></div>{ABOUT_ME}</td>
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

