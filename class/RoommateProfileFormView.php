<?php

class RoommateProfileFormView extends Homestead\View{

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
            $profile_form->setMatch('hobbies_checkbox',RoommateProfileFactory::get_hobbies_matches($this->profile));
        }

        $profile_form->addCheck('music_checkbox',$music);
        $profile_form->setLabel('music_checkbox',$music_labels);
        $template['MUSIC_CHECKBOX_QUESTION'] = 'My Music Preferences (check all that apply):';
        if($profile_exists){
            $profile_form->setMatch('music_checkbox',RoommateProfileFactory::get_music_matches($this->profile));
        }

        $profile_form->addCheck('language_checkbox',$language);
        $profile_form->setLabel('language_checkbox',$language_labels);
        $template['LANGUAGE_CHECKBOX_QUESTION'] = 'I can speak (check all that apply):';
        if($profile_exists){
            $profile_form->setMatch('language_checkbox',RoommateProfileFactory::get_language_matches($this->profile));
        }

        $profile_form->addDropBox('political_views',$political_views);
        $profile_form->setLabel('political_views','I consider myself: ');
        if($profile_exists){
            $profile_form->setMatch('political_views',$this->profile->get_text('political_views'));
        }

        $profile_form->addText('alternate_email');
        $profile_form->setLabel('alternate_email','Alternate email: ');
        if($profile_exists){
            $profile_form->setValue('alternate_email',$this->profile->get_text('alternate_email'));
        }
        
        $profile_form->addText('fb_link');
        $profile_form->setLabel('fb_link','Facebook link:');
        if($profile_exists) {
        	$profile_form->setValue('fb_link',$this->profile->get_text('fb_link'));
        }

        $profile_form->addText('instagram_sn');
        $profile_form->setLabel('instagram_sn','Instagram username: ');
        if($profile_exists){
            $profile_form->setValue('instagram_sn',$this->profile->get_text('instagram_sn'));
        }

        $profile_form->addText('twitter_sn');
        $profile_form->setLabel('twitter_sn','Twitter username:');
        if($profile_exists){
            $profile_form->setValue('twitter_sn',$this->profile->get_text('twitter_sn'));
        }
        
        $profile_form->addText('tumblr_sn');
        $profile_form->setLabel('tumblr_sn','Tumblr username:');
        if($profile_exists){
            $profile_form->setValue('tumblr_sn',$this->profile->get_text('tumblr_sn'));
        }
        
        $profile_form->addText('kik_sn');
        $profile_form->setLabel('kik_sn','Kik username:');
        if($profile_exists){
            $profile_form->setValue('kik_sn',$this->profile->get_text('kik_sn'));
        }
        
        $profile_form->addTextArea('about_me');
        $profile_form->setLabel('about_me','4. Additional information: ');
        $profile_form->setCols('about_me',50);
        $profile_form->setRows('about_me',15);
   		//$profile_form->setMaxSize('about_me',4096);
        if($profile_exists){
            $profile_form->setValue('about_me',$this->profile->get_text('about_me'));
        }

        /***** College Life *****/
        $profile_form->addDropBox('major',$majors);
        $profile_form->setLabel('major','My intended academic major: ');
        if($profile_exists){
            $profile_form->setMatch('major',$this->profile->get_text('major'));
        }

        $profile_form->addDropBox('experience',$experiences);
        $profile_form->setLabel('experience','I feel the following is the most important part of my college experience: ');
        if($profile_exists){
            $profile_form->setMatch('experience',$this->profile->get_text('experience'));
        }

        /***** My Daily Life *****/
        $profile_form->addDropBox('sleep_time',$sleep_times);
        $profile_form->setLabel('sleep_time','I generally go to sleep: ');
        if($profile_exists){
            $profile_form->setMatch('sleep_time',$this->profile->get_text('sleep_time'));
        }

        $profile_form->addDropBox('wakeup_time',$wakeup_times);
        $profile_form->setLabel('wakeup_time','I generally wake up: ');
        if($profile_exists){
            $profile_form->setMatch('wakeup_time',$this->profile->get_text('wakeup_time'));
        }

        $profile_form->addDropBox('overnight_guests',$overnight_guests);
        $profile_form->setLabel('overnight_guests','I plan on hosting overnight guests: ');
        if($profile_exists){
            $profile_form->setMatch('overnight_guests',$this->profile->get_text('overnight_guests'));
        }

        $profile_form->addDropBox('loudness',$loudness);
        $profile_form->setLabel('loudness','In my daily activities (music, conversations, etc.): ');
        if($profile_exists){
            $profile_form->setMatch('loudness',$this->profile->get_text('loudness'));
        }

        $profile_form->addDropBox('cleanliness',$cleanliness);
        $profile_form->setLabel('cleanliness','I would describe myself as: ');
        if($profile_exists){
            $profile_form->setMatch('cleanliness',$this->profile->get_text('cleanliness'));
        }

        $profile_form->addCheck('study_times',$study_times);
        $profile_form->setLabel('study_times',$study_times_labels);
        $template['STUDY_TIMES_QUESTION'] = 'I prefer to study (check all that apply):';
        if($profile_exists){
            $profile_form->setMatch('study_times',RoommateProfileFactory::get_study_matches($this->profile));
        }

        $profile_form->addDropBox('free_time',$free_time);
        $profile_form->setLabel('free_time','If I have free time I would rather: ');
        if($profile_exists){
            $profile_form->setMatch('free_time', $this->profile->get_text('free_time'));
        }

        $profile_form->addSubmit('Submit');

        $profile_form->mergeTemplate($template);
        $template = $profile_form->getTemplate();

        Layout::addPageTitle("Roommate Profile Form");
        javascript('jquery');
        return PHPWS_Template::process($template,'hms','student/profile_form.tpl');
    }
}

?>
