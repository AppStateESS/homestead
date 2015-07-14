<h1>Roommate Profile Search</h1>

<div class="row">
    <div class="col-md-10">
        <p>Select the qualities and attributes below which you would like your
          roommate to posses. This tool only searches for <b>exact</b> matches
          to the information you supply below. If no results are found,
          you should try a broader search by selecting fewer attributes. If you enter
          an ASU user name, all other fields will be ignored.
        </p>
    </div>
</div>

  <!-- BEGIN search_form -->

<form class="form-horizontal form-protected" autocomplete="on" id="phpws_form" action="index.php" method="get">
    {HIDDEN_FIELDS}

    <div class="row">
        <div class="col-md-4">
            <h3>
                Search by ASU Email
            </h3>
            {ASU_USERNAME_LABEL}
            <div class="form-group">
                <div class="input-group">
                    {ASU_USERNAME}
                    <div class="input-group-addon">@appstate.edu</div>
                </div>
            </div>
            <div class="form-group pull-right">
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-search"></i> Search
                </button>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-offset-3">
            <h3> -- OR -- </h3>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <h3>Search By Preferences</h3>
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
            <h4>College Life</h4>

            <div class="form-group">
                <label for="{POLITICAL_VIEWS_DROPBOX_ID}" class="col-sm-3 control-label">{POLITICAL_VIEWS_DROPBOX_LABEL}</label>
                <div class="col-sm-5">
                    {POLITICAL_VIEWS_DROPBOX}
                </div>
            </div>

            <div class="form-group">
                <label for="{INTENDED_MAJOR_ID}" class="col-sm-3 control-label">{INTENDED_MAJOR_LABEL}</label>
                <div class="col-sm-5">
                    {INTENDED_MAJOR}
                </div>
            </div>

            <div class="form-group">
                <label for="{IMPORTANT_EXPERIENCE_ID}" class="col-sm-3 control-label">{IMPORTANT_EXPERIENCE_LABEL}</label>
                <div class="col-sm-5">
                    {IMPORTANT_EXPERIENCE}
                </div>
            </div>

            <h4>Daily Life</h4>

            <div class="form-group">
                <label for="{SLEEP_TIME_ID}" class="col-sm-3 control-label">{SLEEP_TIME_LABEL}</label>
                <div class="col-sm-5">
                    {SLEEP_TIME}
                </div>
            </div>

            <div class="form-group">
                <label for="{WAKEUP_TIME_ID}" class="col-sm-3 control-label">{WAKEUP_TIME_LABEL}</label>
                <div class="col-sm-5">
                    {WAKEUP_TIME}
                </div>
            </div>

            <div class="form-group">
                <label for="{OVERNIGHT_GUESTS_ID}" class="col-sm-3 control-label">{OVERNIGHT_GUESTS_LABEL}</label>
                <div class="col-sm-5">
                    {OVERNIGHT_GUESTS}
                </div>
            </div>

            <div class="form-group">
                <label for="{LOUDNESS_ID}" class="col-sm-3 control-label">{LOUDNESS_LABEL}</label>
                <div class="col-sm-5">
                    {LOUDNESS}
                </div>
            </div>

            <div class="form-group">
                <label for="{CLEANLINESS_ID}" class="col-sm-3 control-label">{CLEANLINESS_LABEL}</label>
                <div class="col-sm-5">
                    {CLEANLINESS}
                </div>
            </div>

            <label>{STUDY_TIMES_QUESTION}</label>
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
                <label for="{FREE_TIME_ID}" class="col-sm-3 control-label">{FREE_TIME_LABEL}</label>
                <div class="col-sm-5">
                    {FREE_TIME}
                </div>
            </div>

            <div class="form-group pull-right">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa fa-search"></i> Search
                </button>
            </div>
        </div>
    </div>
</form>
