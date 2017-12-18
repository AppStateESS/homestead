<h1>Roommate Profile Search</h1>

<div class="row">
    <div class="col-md-10">
        <p>We'll suggest your best roommate matches below, or use the other tabs to search by email address or specific preferences.</p>

        <div>
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#suggested" aria-controls="suggested" role="tab" data-toggle="tab">Suggested Roommates</a></li>
                <li role="presentation"><a href="#email" aria-controls="email" role="tab" data-toggle="tab">Search by Email Address</a></li>
                <li role="presentation"><a href="#preferences" aria-controls="preferences" role="tab" data-toggle="tab">Search by Preferences</a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="suggested">
                    <h3>Suggested Roommates</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div id="SuggestedRoommateList"></div>
                        </div>
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane" id="email">
                    <div class="row">
                        <div class="col-md-4">
                            <h3>
                                Search by ASU Email
                            </h3>
                            <form class="form-horizontal form-protected" autocomplete="on" id="phpws_form" action="index.php" method="get">
                                {HIDDEN_FIELDS}
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
                            </form>
                        </div>
                    </div>
                </div>


                <div role="tabpanel" class="tab-pane" id="preferences">
                    <div class="row">
                        <div class="col-md-12">
                            <h3>Search by Preferences</h3>
                            <p style="margin-top:2em;">Select the qualities below that you're looking for in a roommate.
                              This tool only searches for <b>exact</b> matches
                              to the information you supply below. If no results are found,
                              you should try a broader search by selecting fewer preferences.
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <form class="form-horizontal form-protected" autocomplete="on" id="phpws_form" action="index.php" method="get">
                                {HIDDEN_FIELDS}
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

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
window.term = '{TERM}';
</script>

<script type="text/javascript" src="{vendor_bundle}"></script>
<script type="text/javascript" src="{entry_bundle}"></script>
