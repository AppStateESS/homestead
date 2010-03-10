<?php

class RoommateProfileView extends View {

    private $student;
    private $profile;
    
    public function __construct(Student $student, RoommateProfile $profile = NULL)
    {
        $this->student = $student;
        $this->profile = $profile;
    }
    
    public function show()
    {
        require(PHPWS_SOURCE_DIR . 'mod/hms/inc/profile_options.php');

        $tpl = array();
        $profile_form = new PHPWS_Form('profile_form');
        $profile_form->useRowRepeat();

        $none_given = '<span style="color:#CCC;">none given</span>';
         
        /***** Contact Info *****/
        $tpl['TITLE'] = $this->student->getName() . '\'s Profile';
        $tpl['EMAIL_ADDRESS'] = "<a href=\"mailto:{$this->student->getUsername()}@appstate.edu\">{$this->student->getUsername()}@appstate.edu</a>";

        $tpl['ALTERNATE_EMAIL_LABEL'] = 'Alternate email: ';
        $alt_email = $this->profile->get_alternate_email();
        if(!empty($alt_email)){
            $tpl['ALTERNATE_EMAIL'] = "<a href=\"mailto:$alt_email\">$alt_email</a>";
        }else{
            $tpl['ALTERNATE_EMAIL'] = $none_given;
        }

        $tpl['AIM_SN_LABEL'] = 'AIM screen name: ';
        $aim_sn = $this->profile->get_aim_sn();
        if(!empty($aim_sn)){
            $tpl['AIM_SN'] = $aim_sn;
        }else{
            $tpl['AIM_SN'] = $none_given;
        }
        
        $tpl['YAHOO_SN_LABEL'] = 'Yahoo! screen name: ';
        $yahoo_sn = $this->profile->get_yahoo_sn();
        if(!empty($yahoo_sn)){
            $tpl['YAHOO_SN'] = $yahoo_sn;
        }else{
            $tpl['YAHOO_SN'] = $none_given;
        }

        $tpl['MSN_SN_LABEL'] = 'MSN screen name: ';
        $msn_sn = $this->profile->get_msn_sn();
        if(!empty($msn_sn)){
            $tpl['MSN_SN'] = $msn_sn;
        }else{
            $tpl['MSN_SN'] = $none_given;
        }

        /***** About Me *****/
        $profile_form->addCheck('hobbies_checkbox',$hobbies);
        $profile_form->setLabel('hobbies_checkbox',$hobbies_labels);
        $profile_form->setDisabled('hobbies_checkbox');
        $tpl['HOBBIES_CHECKBOX_QUESTION'] = 'My Hobbies and Interests: ';

        # set matches on hobby check boxes
        $hobbies_matches = RoommateProfile::get_hobbies_matches($this->profile);
        $profile_form->setMatch('hobbies_checkbox',$hobbies_matches);

        $profile_form->addCheck('music_checkbox',$music);
        $profile_form->setLabel('music_checkbox',$music_labels);
        $profile_form->setDisabled('music_checkbox');
        $tpl['MUSIC_CHECKBOX_QUESTION'] = 'My Music Preferences: ';

        # set matches on music check boxes
        $music_matches = RoommateProfile::get_music_matches($this->profile);
        $profile_form->setMatch('music_checkbox',$music_matches);

        $tpl['POLITICAL_VIEWS_DROPBOX_LABEL'] = 'Political views: ';
        $tpl['POLITICAL_VIEWS_DROPBOX'] = $political_views[$this->profile->get_political_view()];

        /***** College Life *****/
        $tpl['INTENDED_MAJOR_LABEL'] = 'Intended major: ';
        $tpl['INTENDED_MAJOR'] = $majors[$this->profile->get_major()];

        $tpl['IMPORTANT_EXPERIENCE_LABEL'] = 'I fee the most important part of my college experience is: ';
        $tpl['IMPORTANT_EXPERIENCE'] = $experiences[$this->profile->get_experience()];

        /***** Daily Life *****/
        $tpl['SLEEP_TIME_LABEL']       = 'I generally go to sleep: ';
        $tpl['SLEEP_TIME']             = $sleep_times[$this->profile->get_sleep_time()];
        $tpl['WAKEUP_TIME_LABEL']      = 'I generally wake up: ';
        $tpl['WAKEUP_TIME']            = $wakeup_times[$this->profile->get_wakeup_time()];
        $tpl['OVERNIGHT_GUESTS_LABEL'] = 'I plan on hosting overnight guests: ';
        $tpl['OVERNIGHT_GUESTS']       = $overnight_guests[$this->profile->get_overnight_guests()];
        $tpl['LOUDNESS_LABEL']         = 'In my daily activities: ';
        $tpl['LOUDNESS']               = $loudness[$this->profile->get_loudness()];
        $tpl['CLEANLINESS_LABEL']      = 'I would describe myself as: ';
        $tpl['CLEANLINESS']            = $cleanliness[$this->profile->get_cleanliness()];
        $tpl['FREE_TIME_LABEL']        = 'If I have free time I would rather: ';
        $tpl['FREE_TIME']              = $free_time[$this->profile->get_free_time()];

        $profile_form->addCheck('study_times',$study_times);
        $profile_form->setLabel('study_times',$study_times_labels);
        $profile_form->setDisabled('study_times');
        $tpl['STUDY_TIMES_QUESTION'] = 'I prefer to study: ';
        # set matches on study times check boxes here, set disabled
        $study_matches = RoommateProfile::get_study_matches($this->profile);
        $profile_form->setMatch('study_times',$study_matches);

        $profile_form->mergeTemplate($tpl);
        $tpl = $profile_form->getTemplate();

        return PHPWS_Template::process($tpl,'hms','student/profile_form.tpl');
    }
}

?>