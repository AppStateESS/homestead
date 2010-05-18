<?php

class RoommateProfileFormView extends View {

    private $profile;
    private $term;
    
    public function __construct(RoommateProfile $profile = NULL, $term){
        $this->profile  = $profile;
        $this->term     = $term;
    }

    public function show()
    {
        require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/profile_options.php');
        PHPWS_Core::initModClass('hms','RoommateProfile.php');

        $template = array();

        $template['TITLE'] = 'My Profile';

        if(isset($this->profile) && !is_null($this->profile)){
            $profile_exists = TRUE;
        }else{
            $profile_exists = FALSE;
        }
        
        $submitCmd = CommandFactory::getCommand('SubmitRoommateProfile');
        $submitCmd->setTerm($this->term);
        
        $profile_form = new PHPWS_Form('profile_form');
        $profile_form->useRowRepeat();
        
        $submitCmd->initForm($profile_form);

        /***** About Me *****/
        $profile_form->addCheck('hobbies_checkbox',$hobbies);
        $profile_form->setLabel('hobbies_checkbox',$hobbies_labels);
        //test($profile_form,1);
        $template['HOBBIES_CHECKBOX_QUESTION'] = 'My Hobbies and Interests (check all that apply):';
        if($profile_exists){
            $profile_form->setMatch('hobbies_checkbox',RoommateProfile::get_hobbies_matches($this->profile));
        }

        $profile_form->addCheck('music_checkbox',$music);
        $profile_form->setLabel('music_checkbox',$music_labels);
        $template['MUSIC_CHECKBOX_QUESTION'] = 'My Music Preferences (check all that apply):';
        if($profile_exists){
            $profile_form->setMatch('music_checkbox',RoommateProfile::get_music_matches($this->profile));
        }

        $profile_form->addDropBox('political_views_dropbox',$political_views);
        $profile_form->setLabel('political_views_dropbox','I consider myself: ');
        if($profile_exists){
            $profile_form->setMatch('political_views_dropbox',$this->profile->get_political_view());
        }

        $profile_form->addText('alternate_email');
        $profile_form->setLabel('alternate_email','Alternate email: ');
        if($profile_exists){
            $profile_form->setValue('alternate_email',$this->profile->get_alternate_email());
        }

        $profile_form->addText('aim_sn');
        $profile_form->setLabel('aim_sn','AIM screen name:');
        if($profile_exists){
            $profile_form->setValue('aim_sn',$this->profile->get_aim_sn());
        }

        $profile_form->addText('yahoo_sn');
        $profile_form->setLabel('yahoo_sn','Yahoo! screen name: ');
        if($profile_exists){
            $profile_form->setValue('yahoo_sn',$this->profile->get_yahoo_sn());
        }

        $profile_form->addText('msn_sn');
        $profile_form->setLabel('msn_sn','MSN Screen name:');
        if($profile_exists){
            $profile_form->setValue('msn_sn',$this->profile->get_msn_sn());
        }

        /***** College Life *****/
        $profile_form->addDropBox('intended_major',$majors);
        $profile_form->setLabel('intended_major','My intended academic major: ');
        if($profile_exists){
            $profile_form->setMatch('intended_major',$this->profile->get_major());
        }

        $profile_form->addDropBox('important_experience',$experiences);
        $profile_form->setLabel('important_experience','I feel the following is the most important part of my college experience: ');
        if($profile_exists){
            $profile_form->setMatch('important_experience',$this->profile->get_experience());
        }

        /***** My Daily Life *****/
        $profile_form->addDropBox('sleep_time',$sleep_times);
        $profile_form->setLabel('sleep_time','I generally go to sleep: ');
        if($profile_exists){
            $profile_form->setMatch('sleep_time',$this->profile->get_sleep_time());
        }

        $profile_form->addDropBox('wakeup_time',$wakeup_times);
        $profile_form->setLabel('wakeup_time','I generally wake up: ');
        if($profile_exists){
            $profile_form->setMatch('wakeup_time',$this->profile->get_wakeup_time());
        }

        $profile_form->addDropBox('overnight_guests',$overnight_guests);
        $profile_form->setLabel('overnight_guests','I plan on hosting overnight guests: ');
        if($profile_exists){
            $profile_form->setMatch('overnight_guests',$this->profile->get_overnight_guests());
        }

        $profile_form->addDropBox('loudness',$loudness);
        $profile_form->setLabel('loudness','In my daily activities (music, conversations, etc.): ');
        if($profile_exists){
            $profile_form->setMatch('loudness',$this->profile->get_loudness());
        }

        $profile_form->addDropBox('cleanliness',$cleanliness);
        $profile_form->setLabel('cleanliness','I would describe myself as: ');
        if($profile_exists){
            $profile_form->setMatch('cleanliness',$this->profile->get_cleanliness());
        }

        $profile_form->addCheck('study_times',$study_times);
        $profile_form->setLabel('study_times',$study_times_labels);
        $template['STUDY_TIMES_QUESTION'] = 'I prefer to study (check all that apply):';
        if($profile_exists){
            $profile_form->setMatch('study_times',RoommateProfile::get_study_matches($this->profile));
        }

        $profile_form->addDropBox('free_time',$free_time);
        $profile_form->setLabel('free_time','If I have free time I would rather: ');
        if($profile_exists){
            $profile_form->setMatch('free_time', $this->profile->get_free_time());
        }

        $profile_form->addSubmit('Submit');

        $profile_form->mergeTemplate($template);
        $template = $profile_form->getTemplate();

        Layout::addPageTitle("Roommate Profile Form");

        return PHPWS_Template::process($template,'hms','student/profile_form.tpl');
    }
}

?>