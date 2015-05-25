<h1>Roommate Profile Search</h1> </div>

<div class="col-md-10">
  <div class="row">
    <p>Select the qualities and attributes below which you would like your
      roommate to posses. This tool only searches for <b>exact</b> matches
      to the information you supply below. If no results are found,
      you should try a broader search by selecting fewer attributes. If you enter
      an ASU user name, all other fields will be ignored.
    </p>
  </div>

  <!-- BEGIN search_form -->

  {START_FORM}

  <div class="row">
    <h3>
      Search by ASU User name
    </h3>
  </div>

  <div class="row">
    <label class="col-md-3">
      {ASU_USERNAME_LABEL}
    </label>
    <div class="input-group col-md-4 col-md-offset-6">
      {ASU_USERNAME}
      <div class="input-group-addon">
        @appstate.edu
      </div>
    </div>
  </div>

  <div class="row">
    <button type="submit" class="btn btn-info btn-lg">
      <i class="fa fa-search"></i>
      Search
    </button>
  </div>

  <div class="row">
    <div class="col-md-offset-5">
      <h3> -- OR -- </h3>
    </div>
  </div>

  <div class="row">
    <h3>Search By Preferences</h3>
  </div>

  <div class="row">
    <h4 class="col-md-4">Music &amp; Hobbies</h4>
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
      <h4>
        College Life
      </h4>
    </div>

    <div class="row">
      <label class="col-md-4">
        {POLITICAL_VIEWS_DROPBOX_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-2">
        {POLITICAL_VIEWS_DROPBOX}
      </div>
    </div>

    <div class="row">
      <label class="col-md-4">
        {INTENDED_MAJOR_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-2">
        {INTENDED_MAJOR}
      </div>
    </div>

    <div class="row">
      <label class="col-md-4">
        {IMPORTANT_EXPERIENCE_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-2">
        {IMPORTANT_EXPERIENCE}
      </div>
    </div>

    <div class="row">
      <h4>
        Daily Life
      </h4>
    </div>

    <div class="row">
      <label class="col-md-4">
        {SLEEP_TIME_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-2">
        {SLEEP_TIME}
      </div>
    </div>

    <div class="row">
      <label class="col-md-4">
        {WAKEUP_TIME_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-2">
        {WAKEUP_TIME}
      </div>
    </div>

    <div class="row">
      <label class="col-md-4">
        {OVERNIGHT_GUESTS_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-2">
        {OVERNIGHT_GUESTS}
      </div>
    </div>

    <div class="row">
      <label class="col-md-4">
        {LOUDNESS_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-2">
        {LOUDNESS}
      </div>
    </div>

    <div class="row">
      <label class="col-md-4">
        {CLEANLINESS_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-2">
        {CLEANLINESS}
      </div>
    </div>

    <div class="row">
      <label class="col-md-4">
        {STUDY_TIMES_QUESTION}
      </label>
    </div>

    <div class="checkbox col-md-offset-1">
      <!-- BEGIN study_times_repeat -->
      <div class="row">
        {STUDY_TIMES}{STUDY_TIMES_LABEL}
      </div>
      <!-- END study_times_repeat -->
    </div>


    <div class="row">
      <label class="col-md-4">
        {FREE_TIME_LABEL}
      </label>
      <div class="col-md-4 col-md-offset-2">
        {FREE_TIME}
      </div>
    </div>

    <div class="row">
      <button type="submit" class="btn btn-info btn-lg">
        <i class="fa fa-search"></i>
        Search
      </button>
    </div>
</div>
