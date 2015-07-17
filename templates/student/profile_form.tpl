<h1>{TITLE}</h1>

<form class="form-horizontal form-protected" autocomplete="on" id="profile_form" action="index.php" method="get">
    {HIDDEN_FIELDS}


    <div class="row">
        <div class="col-md-5">
            <h3>About Me</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <label>{HOBBIES_CHECKBOX_QUESTION}</label>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <!-- BEGIN hobbies_checkbox_repeat -->
                    <div class="checkbox">
                        <label>
                            {HOBBIES_CHECKBOX}{HOBBIES_CHECKBOX_LABEL}
                        </label>
                    </div>
                    <!-- END hobbies_checkbox_repeat -->
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <label>{MUSIC_CHECKBOX_QUESTION}</label>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <!-- BEGIN music_checkbox_repeat -->
                    <div class="checkbox">
                        <label>
                            {MUSIC_CHECKBOX}{MUSIC_CHECKBOX_LABEL}
                        </label>
                    </div>
                    <!-- END music_checkbox_repeat -->
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <label>{LANGUAGE_CHECKBOX_QUESTION}</label>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <!-- BEGIN language_checkbox_repeat -->
                    <div class="checkbox">
                        <label>
                            {LANGUAGE_CHECKBOX}{LANGUAGE_CHECKBOX_LABEL}
                        </label>
                    </div>
                    <!-- END language_checkbox_repeat -->
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10">
            <h4>Contact Info &amp; Social Media</h4>

            <div class="form-group">
                <label for="{ALTERNATE_EMAIL_ID}" class="col-sm-4 control-label">{ALTERNATE_EMAIL_LABEL}</label>
                <div class="col-sm-5">
                    {ALTERNATE_EMAIL}
                </div>
            </div>

            <div class="form-group">
                <label for="{FB_LINK_ID}" class="col-sm-4 control-label">{FB_LINK_LABEL}</label>
                <div class="col-sm-5">
                    {FB_LINK}
                </div>
            </div>

            <div class="form-group">
                <label for="{INSTAGRAM_SN_ID}" class="col-sm-4 control-label">{INSTAGRAM_SN_LABEL}</label>
                <div class="col-sm-5">
                    {INSTAGRAM_SN}
                </div>
            </div>

            <div class="form-group">
                <label for="{TWITTER_SN_ID}" class="col-sm-4 control-label">{TWITTER_SN_LABEL}</label>
                <div class="col-sm-5">
                    {TWITTER_SN}
                </div>
            </div>

            <div class="form-group">
                <label for="{TUMBLR_SN_ID}" class="col-sm-4 control-label">{TUMBLR_SN_LABEL}</label>
                <div class="col-sm-5">
                    {TUMBLR_SN}
                </div>
            </div>

            <div class="form-group">
                <label for="{KIK_SN_ID}" class="col-sm-4 control-label">{KIK_SN_LABEL}</label>
                <div class="col-sm-5">
                    {KIK_SN}
                </div>
            </div>

            <h4>College Life</h4>

            <div class="form-group">
                <label for="{POLITICAL_VIEWS_ID}" class="col-sm-4 control-label">{POLITICAL_VIEWS_LABEL}</label>
                <div class="col-sm-5">
                    {POLITICAL_VIEWS}
                </div>
            </div>

            <div class="form-group">
                <label for="{MAJOR_ID}" class="col-sm-4 control-label">{MAJOR_LABEL}</label>
                <div class="col-sm-5">
                    {MAJOR}
                </div>
            </div>

            <div class="form-group">
                <label for="{EXPERIENCE_ID}" class="col-sm-4 control-label">{EXPERIENCE_LABEL}</label>
                <div class="col-sm-5">
                    {EXPERIENCE}
                </div>
            </div>

            <h4>Daily Life</h4>

            <div class="form-group">
                <label for="{SLEEP_TIME_ID}" class="col-sm-4 control-label">{SLEEP_TIME_LABEL}</label>
                <div class="col-sm-5">
                    {SLEEP_TIME}
                </div>
            </div>

            <div class="form-group">
                <label for="{WAKEUP_TIME_ID}" class="col-sm-4 control-label">{WAKEUP_TIME_LABEL}</label>
                <div class="col-sm-5">
                    {WAKEUP_TIME}
                </div>
            </div>

            <div class="form-group">
                <label for="{OVERNIGHT_GUESTS_ID}" class="col-sm-4 control-label">{OVERNIGHT_GUESTS_LABEL}</label>
                <div class="col-sm-5">
                    {OVERNIGHT_GUESTS}
                </div>
            </div>

            <div class="form-group">
                <label for="{LOUDNESS_ID}" class="col-sm-4 control-label">{LOUDNESS_LABEL}</label>
                <div class="col-sm-5">
                    {LOUDNESS}
                </div>
            </div>

            <div class="form-group">
                <label for="{CLEANLINESS_ID}" class="col-sm-4 control-label">{CLEANLINESS_LABEL}</label>
                <div class="col-sm-5">
                    {CLEANLINESS}
                </div>
            </div>

            <label class="col-sm-4 col-sm-offset-2">{STUDY_TIMES_QUESTION}</label>
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-8">
                    <!-- BEGIN study_times_repeat -->
                    <div class="checkbox">
                        <label>
                            {STUDY_TIMES}{STUDY_TIMES_LABEL}
                        </label>
                    </div>
                    <!-- END study_times_repeat -->
                </div>
            </div>

            <div class="form-group">
                <label for="{FREE_TIME_ID}" class="col-sm-4 control-label">{FREE_TIME_LABEL}</label>
                <div class="col-sm-5">
                    {FREE_TIME}
                </div>
            </div>

            <div class="form-group">
                <label for="{ABOUT_ME_ID}" class="col-sm-4 control-label">{ABOUT_ME_LABEL}</label>
                <div class="col-sm-5">
                    <span id="wrdcnt" class=""></span>
                    {ABOUT_ME}
                </div>
            </div>

            <!-- BEGIN save-btn -->
            {SAVE_BTN}
            <div class="form-group pull-right">
                <button type="submit" class="btn btn-success btn-lg">Save Profile</button>
            </div>
            <!-- END save-btn -->
        </div>
    </div>
</form>

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
