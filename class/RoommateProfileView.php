<?php

class RoommateProfileView extends View {

    // TODO clean up the old copy-pasted code

    public function show()
    {
        require(PHPWS_SOURCE_DIR . 'mod/hms/inc/profile_options.php');

        $id = HMS_Student_Profile::check_for_profile($username);

        if(PEAR::isError($id)){
            # db error
            PHPWS_Error::log($id);
            $template['MESSAGE'] = "Sorry, there was an error working with the database. Please contact Housing and Residence Life if you need assistance.";
            return PHPWS_Template::process($template, 'hms', 'student/student_success_failure_message.tpl');
        }elseif($id !== FALSE){
            # profile found
            $profile = new HMS_Student_Profile($id);
        }else{
            # No profile found
            $template['MESSAGE'] = "No profile found for $username.";
            return PHPWS_Template::process($template, 'hms', 'student/student_success_failure_message.tpl');
        }

        $template = array();
        $profile_form = &new PHPWS_Form('profile_form');
        $profile_form->useRowRepeat();

        $none_given = '<span style="color:#CCC;">none given</span>';
         
        /***** Contact Info *****/
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        $template['TITLE'] = HMS_SOAP::get_name($username) . '\'s Profile';
        $template['EMAIL_ADDRESS'] = "<a href=\"mailto:$username@appstate.edu\">$username@appstate.edu</a>";

        $template['ALTERNATE_EMAIL_LABEL'] = 'Alternate email: ';
        $alt_email = $profile->get_alternate_email();
        if(isset($alt_email)){
            $template['ALTERNATE_EMAIL'] = "<a href=\"mailto:$alt_email\">$alt_email</a>";
        }else{
            $template['ALTERNATE_EMAIL'] = $none_given;
        }

        $template['AIM_SN_LABEL'] = 'AIM screen name: ';
        $aim_sn = $profile->get_aim_sn();
        if(isset($aim_sn)){
            $template['AIM_SN'] = $aim_sn;
        }else{
            $template['AIM_SN'] = $none_given;
        }

        $template['YAHOO_SN_LABEL'] = 'Yahoo! screen name: ';
        $yahoo_sn = $profile->get_yahoo_sn();
        if(isset($yahoo_sn)){
            $template['YAHOO_SN'] = $yahoo_sn;
        }else{
            $template['YAHOO_SN'] = $none_given;
        }

        $template['MSN_SN_LABEL'] = 'MSN screen name: ';
        $msn_sn = $profile->get_msn_sn();
        if(isset($msn_sn)){
            $template['MSN_SN'] = $msn_sn;
        }else{
            $template['MSN_SN'] = $none_given;
        }

        /***** About Me *****/
        $profile_form->addCheck('hobbies_checkbox',$hobbies);
        $profile_form->setLabel('hobbies_checkbox',$hobbies_labels);
        $profile_form->setDisabled('hobbies_checkbox');
        $template['HOBBIES_CHECKBOX_QUESTION'] = 'My Hobbies and Interests: ';

        # set matches on hobby check boxes
        $hobbies_matches = HMS_Student_Profile::get_hobbies_matches($profile);
        $profile_form->setMatch('hobbies_checkbox',$hobbies_matches);

        $profile_form->addCheck('music_checkbox',$music);
        $profile_form->setLabel('music_checkbox',$music_labels);
        $profile_form->setDisabled('music_checkbox');
        $template['MUSIC_CHECKBOX_QUESTION'] = 'My Music Preferences: ';

        # set matches on music check boxes
        $music_matches = HMS_Student_Profile::get_music_matches($profile);
        $profile_form->setMatch('music_checkbox',$music_matches);

        $template['POLITICAL_VIEWS_DROPBOX_LABEL'] = 'Political views: ';
        $template['POLITICAL_VIEWS_DROPBOX'] = $political_views[$profile->get_political_view()];

        /***** College Life *****/
        $template['INTENDED_MAJOR_LABEL'] = 'Intended major: ';
        $template['INTENDED_MAJOR'] = $majors[$profile->get_major()];

        $template['IMPORTANT_EXPERIENCE_LABEL'] = 'I fee the most important part of my college experience is: ';
        $template['IMPORTANT_EXPERIENCE'] = $experiences[$profile->get_experience()];

        /***** Daily Life *****/
        $template['SLEEP_TIME_LABEL']       = 'I generally go to sleep: ';
        $template['SLEEP_TIME']             = $sleep_times[$profile->get_sleep_time()];
        $template['WAKEUP_TIME_LABEL']      = 'I generally wake up: ';
        $template['WAKEUP_TIME']            = $wakeup_times[$profile->get_wakeup_time()];
        $template['OVERNIGHT_GUESTS_LABEL'] = 'I plan on hosting overnight guests: ';
        $template['OVERNIGHT_GUESTS']       = $overnight_guests[$profile->get_overnight_guests()];
        $template['LOUDNESS_LABEL']         = 'In my daily activities: ';
        $template['LOUDNESS']               = $loudness[$profile->get_loudness()];
        $template['CLEANLINESS_LABEL']      = 'I would describe myself as: ';
        $template['CLEANLINESS']            = $cleanliness[$profile->get_cleanliness()];
        $template['FREE_TIME_LABEL']        = 'If I have free time I would rather: ';
        $template['FREE_TIME']              = $free_time[$profile->get_free_time()];

        $profile_form->addCheck('study_times',$study_times);
        $profile_form->setLabel('study_times',$study_times_labels);
        $profile_form->setDisabled('study_times');
        $template['STUDY_TIMES_QUESTION'] = 'I prefer to study: ';
        # set matches on study times check boxes here, set disabled
        $study_matches = HMS_Student_Profile::get_study_matches($profile);
        $profile_form->setMatch('study_times',$study_matches);

        $profile_form->mergeTemplate($template);
        $template = $profile_form->getTemplate();

        return PHPWS_Template::process($template,'hms','student/profile_form.tpl');
    }
}

?>