<?php

class RoommateProfileSearchForm extends hms\View {

    private $term;

    public function __construct($term)
    {
        $this->term = $term;
    }

    public function show()
    {
        $_SESSION['profile_search_use_session'] = FALSE;
        require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/profile_options.php');

        # Overwrite the first element of each array so it reads "No Preference" instead of "Prefer not to say".
        $no_pref = 'No Preference';
        $political_views[0]  = $no_pref;
        $majors[0]           = $no_pref;
        $experiences[0]      = $no_pref;
        $sleep_times[0]      = $no_pref;
        $wakeup_times[0]     = $no_pref;
        $overnight_guests[0] = $no_pref;
        $loudness[0]         = $no_pref;
        $cleanliness[0]      = $no_pref;
        $free_time[0]        = $no_pref;

        $tags = array();

        $submitCmd = CommandFactory::getCommand('RoommateProfileSearch');
        $submitCmd->setTerm($this->term);

        $form = new PHPWS_Form();
        $submitCmd->initForm($form);
        $form->setMethod('get');
        $form->useRowRepeat();

        $form->addText('asu_username');
        $form->setLabel('asu_username','ASU Username: ');
        $form->addCssClass('asu_username', 'form-control');
        $form->setExtra('asu_username', 'autofocus');

        $form->addCheck('hobbies_checkbox',$hobbies);
        $form->setLabel('hobbies_checkbox',$hobbies_labels);
        $tags['HOBBIES_CHECKBOX_QUESTION'] = 'Hobbies and Interests (check all that apply):';


        $form->addCheck('music_checkbox',$music);
        $form->setLabel('music_checkbox',$music_labels);
        $tags['MUSIC_CHECKBOX_QUESTION'] = 'Music Preferences (check all that apply):';

        $form->addCheck('language_checkbox', $language);
        $form->setLabel('language_checkbox', $language_labels);
        $tags['LANGUAGE_CHECKBOX_QUESTION'] = 'Spoken Languages (check all that apply):';

        $form->addDropBox('political_views_dropbox',$political_views);
        $form->setLabel('political_views_dropbox','His/her political view: ');
        $form->addCssClass('political_views_dropbox', 'form-control');

        /***** College Life *****/
        $form->addDropBox('intended_major',$majors);
        $form->setLabel('intended_major','His/her academic major: ');
        $form->addCssClass('intended_major', 'form-control');

        $form->addDropBox('important_experience',$experiences);
        $form->setLabel('important_experience','The following is the most important part of his/her college experience: ');
        $form->addCssClass('important_experience', 'form-control');

        /***** Daily Life *****/
        $form->addDropBox('sleep_time',$sleep_times);
        $form->setLabel('sleep_time','He/she generally goes to sleep: ');
        $form->addCssClass('sleep_time', 'form-control');

        $form->addDropBox('wakeup_time',$wakeup_times);
        $form->setLabel('wakeup_time','He/she generally wakes up: ');
        $form->addCssClass('wakeup_time', 'form-control');

        $form->addDropBox('overnight_guests',$overnight_guests);
        $form->setLabel('overnight_guests','He/she plans on hosting overnight guests: ');
        $form->addCssClass('overnight_guests', 'form-control');

        $form->addDropBox('loudness',$loudness);
        $form->setLabel('loudness','In his/her daily activities (music, conversations, etc.): ');
        $form->addCssClass('loudness', 'form-control');

        $form->addDropBox('cleanliness',$cleanliness);
        $form->setLabel('cleanliness','He/she could be described as: ');
        $form->addCssClass('cleanliness', 'form-control');

        $tags['STUDY_TIMES_QUESTION'] = 'He/she prefers to study (check all that apply):';
        $form->addCheck('study_times',$study_times);
        $form->setLabel('study_times',$study_times_labels);

        $form->addDropBox('free_time',$free_time);
        $form->setLabel('free_time','If he/she has free time he/she would rather: ');
        $form->addCssClass('free_time', 'form-control');

        $form->addSubmit('Search');

        $form->mergeTemplate($tags);
        $tags = $form->getTemplate();

        Layout::addPageTitle("Roommate Profile Search");

        return PHPWS_Template::process($tags,'hms','student/profile_search.tpl');
    }
}
