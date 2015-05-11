<?php

class RoommateProfileView extends hms\View {

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

        $majors = hms\MajorFactory::getMajorsList();

        $tpl = array();
        $profile_form = new PHPWS_Form('profile_form');
        $profile_form->useRowRepeat();

        $none_given = '<span style="color:#CCC;">none given</span>';

        /***** Contact Info *****/
        $tpl['TITLE'] = $this->student->getName() . '\'s Profile';
        $tpl['EMAIL_ADDRESS'] = "<a href=\"mailto:{$this->student->getUsername()}@appstate.edu\">{$this->student->getUsername()}@appstate.edu</a>";

        $tpl['ALTERNATE_EMAIL_LABEL'] = 'Alternate email: ';
        $alt_email = $this->profile->get_text('alternate_email');
        if(!empty($alt_email)){
            $tpl['ALTERNATE_EMAIL'] = "<a href=\"mailto:$alt_email\">$alt_email</a>";
        }else{
            $tpl['ALTERNATE_EMAIL'] = $none_given;
        }

        $tpl['FB_LINK_LABEL'] = 'Facebook link: ';
        $fb_link = $this->profile->get_text('fb_link');
        if(!empty($fb_link)){
        	$tpl['FB_LINK'] = $fb_link;
        }else{
        	$tpl['FB_LINK'] = $none_given;
        }

        $tpl['INSTAGRAM_SN_LABEL'] = 'Instagram username: ';
        $instagram_sn = $this->profile->get_text('instagram_sn');
        if(!empty($instagram_sn)){
            $tpl['INSTAGRAM_SN'] = $instagram_sn;
        }else{
            $tpl['INSTAGRAM_SN'] = $none_given;
        }

        $tpl['TWITTER_SN_LABEL'] = 'Twitter username: ';
        $twitter_sn = $this->profile->get_text('twitter_sn');
        if(!empty($twitter_sn)){
            $tpl['TWITTER_SN'] = $twitter_sn;
        }else{
            $tpl['TWITTER_SN'] = $none_given;
        }

        $tpl['TUMBLR_SN_LABEL'] = 'Tumblr username: ';
        $tumblr_sn = $this->profile->get_text('tumblr_sn');
        if(!empty($tumblr_sn)){
            $tpl['TUMBLR_SN'] = $tumblr_sn;
        }else{
            $tpl['TUMBLR_SN'] = $none_given;
        }

        $tpl['KIK_SN_LABEL'] = 'Kik username: ';
        $kik_sn = $this->profile->get_text('kik_sn');
        if(!empty($kik_sn)){
        	$tpl['KIK_SN'] = $kik_sn;
        }else{
        	$tpl['KIK_SN'] = $none_given;
        }

        $tpl['ABOUT_ME_LABEL'] = '4. Additional information: ';
        $about_me = $this->profile->get_text('about_me');
        if(!empty($about_me)){
        	$tpl['ABOUT_ME'] = $about_me;
        }else{
        	$tpl['ABOUT_ME'] = $none_given;
        }

        /***** About Me *****/
        $profile_form->addCheck('hobbies_checkbox',$hobbies);
        $profile_form->setLabel('hobbies_checkbox',$hobbies_labels);
        $profile_form->setDisabled('hobbies_checkbox');
        $tpl['HOBBIES_CHECKBOX_QUESTION'] = 'My Hobbies and Interests: ';

        # set matches on hobby check boxes
        $hobbies_matches = RoommateProfileFactory::get_hobbies_matches($this->profile);
        $profile_form->setMatch('hobbies_checkbox',$hobbies_matches);

        $profile_form->addCheck('music_checkbox',$music);
        $profile_form->setLabel('music_checkbox',$music_labels);
        $profile_form->setDisabled('music_checkbox');
        $tpl['MUSIC_CHECKBOX_QUESTION'] = 'My Music Preferences: ';

        # set matches on music check boxes
        $music_matches = RoommateProfileFactory::get_music_matches($this->profile);
        $profile_form->setMatch('music_checkbox',$music_matches);

        $profile_form->addCheck('language_checkbox',$language);
        $profile_form->setLabel('language_checkbox',$language_labels);
        $profile_form->setDisabled('language_checkbox');
        $tpl['LANGUAGE_CHECKBOX_QUESTION'] = 'Languages I speak: ';

        # set matches on language check boxes
        $language_matches = RoommateProfileFactory::get_language_matches($this->profile);
        $profile_form->setMatch('language_checkbox',$language_matches);

        $tpl['POLITICAL_VIEWS_LABEL'] = 'Political views: ';
        $tpl['POLITICAL_VIEWS'] = $political_views[$this->profile->get_text('political_views')];

        /***** College Life *****/
        $tpl['MAJOR_LABEL'] = 'Intended major: ';
        $tpl['MAJOR'] = $majors[$this->profile->get_text('major')];

        $tpl['EXPERIENCE_LABEL'] = 'I feel the most important part of my college experience is: ';
        $tpl['EXPERIENCE'] = $experiences[$this->profile->get_text('experience')];

        /***** Daily Life *****/
        $tpl['SLEEP_TIME_LABEL']       = 'I generally go to sleep: ';
        $tpl['SLEEP_TIME']             = $sleep_times[$this->profile->get_text('sleep_time')];
        $tpl['WAKEUP_TIME_LABEL']      = 'I generally wake up: ';
        $tpl['WAKEUP_TIME']            = $wakeup_times[$this->profile->get_text('wakeup_time')];
        $tpl['OVERNIGHT_GUESTS_LABEL'] = 'I plan on hosting overnight guests: ';
        $tpl['OVERNIGHT_GUESTS']       = $overnight_guests[$this->profile->get_text('overnight_guests')];
        $tpl['LOUDNESS_LABEL']         = 'In my daily activities: ';
        $tpl['LOUDNESS']               = $loudness[$this->profile->get_text('loudness')];
        $tpl['CLEANLINESS_LABEL']      = 'I would describe myself as: ';
        $tpl['CLEANLINESS']            = $cleanliness[$this->profile->get_text('cleanliness')];
        $tpl['FREE_TIME_LABEL']        = 'If I have free time I would rather: ';
        $tpl['FREE_TIME']              = $free_time[$this->profile->get_text('free_time')];

        $profile_form->addCheck('study_times',$study_times);
        $profile_form->setLabel('study_times',$study_times_labels);
        $profile_form->setDisabled('study_times');
        $tpl['STUDY_TIMES_QUESTION'] = 'I prefer to study: ';
        # set matches on study times check boxes here, set disabled
        $study_matches = RoommateProfileFactory::get_study_matches($this->profile);
        $profile_form->setMatch('study_times',$study_matches);

        $profile_form->mergeTemplate($tpl);
        $tpl = $profile_form->getTemplate();


        Layout::addPageTitle("Roommate Profile");

        return PHPWS_Template::process($tpl,'hms','student/profile_form.tpl');
    }
}

?>
