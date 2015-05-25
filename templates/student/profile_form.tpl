<h1>{TITLE}</h1>

    {MENU_LINK}

<font color="red">
  <i>
    {MESSAGE}
  </i>
</font>

<!-- BEGIN rlc_form -->

{START_FORM}


<div class="row">
  <div class="col-md-10">
    <div class="row">
      <h3>
        About Me
      </h3>
    </div>

    <div class="row">
      <label class="col-md-4">
        {HOBBIES_CHECKBOX_QUESTION}
      </label>
    </div>

    <div class="checkbox col-md-offset-1">
      <!-- BEGIN hobbies_checkbox_repeat -->
        <div class="row">
          {HOBBIES_CHECKBOX}{HOBBIES_CHECKBOX_LABEL}
        </div>
      <!-- END hobbies_checkbox_repeat -->
    </div>

    <div class="row">
      <label class="col-md-4">
        {MUSIC_CHECKBOX_QUESTION}
      </label>
    </div>

    <div class="checkbox col-md-offset-1">
      <!-- BEGIN music_checkbox_repeat -->
        <div class="row">
          {MUSIC_CHECKBOX}{MUSIC_CHECKBOX_LABEL}
        </div>
      <!-- END music_checkbox_repeat -->
    </div>

    <div class="row">
      <label class="col-md-4">
        {LANGUAGE_CHECKBOX_QUESTION}
      </label>
    </div>

    <div class="checkbox col-md-offset-1">
      <!-- BEGIN language_checkbox_repeat -->
        <div class="row">
          {LANGUAGE_CHECKBOX}{LANGUAGE_CHECKBOX_LABEL}
        </div>
      <!-- END language_checkbox_repeat -->
    </div>

    <div class="row">
      <label class="col-md-3">
        {POLITICAL_VIEWS_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {POLITICAL_VIEWS}
      </div>
    </div>

    <div class="row">
      <h3>
        College Life
      </h3>
    </div>

    <div class="row">
      <label class="col-md-3">
        {MAJOR_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {MAJOR}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {EXPERIENCE_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {EXPERIENCE}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {ALTERNATE_EMAIL_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {ALTERNATE_EMAIL}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {FB_LINK_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {FB_LINK}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {INSTAGRAM_SN_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {INSTAGRAM_SN}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {TWITTER_SN_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {TWITTER_SN}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {TUMBLR_SN_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {TUMBLR_SN}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {KIK_SN_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {KIK_SN}
      </div>
    </div>

    <div class="row">
      <h3>
        My Daily Life
      </h3>
    </div>

    <div class="row">
      <label class="col-md-3">
        {SLEEP_TIME_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {SLEEP_TIME}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {WAKEUP_TIME_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {WAKEUP_TIME}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {OVERNIGHT_GUESTS_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {OVERNIGHT_GUESTS}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {LOUDNESS_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {LOUDNESS}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {CLEANLINESS_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {CLEANLINESS}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {STUDY_TIMES_QUESTION}
      </label>
    </div>

    <div class="row">
      <div class="checkbox col-md-4 col-md-offset-1">
        <!-- BEGIN study_times_repeat -->
          <div class="row">
            {STUDY_TIMES}{STUDY_TIMES_LABEL}
          </div>
        <!-- END study_times_repeat -->
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {FREE_TIME_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-3">
        {FREE_TIME}
      </div>
    </div>

    <div class="row">
      <label class="col-md-3">
        {ABOUT_ME_LABEL}
      </label>
    </div>

    <div class="row">
      <div id="wrdcnt" class="col-md-3"></div>
    </div>

    <div class="row">
      <div class="col-md-9">
        {ABOUT_ME}
      </div>
    </div>

    <p></p>

  </div>
</div>



<div class="row">
  <button type="submit" class="btn btn-success btn-lg col-md-1">
    Submit
  </button>
</div>


{END_FORM}

<!-- END rlc_form -->

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
