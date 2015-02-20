<?php

class SubmitRoommateProfileCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars()
    {
        return array('action'=>'SubmitRoommateProfile', 'term'=>$this->term);
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'RoommateProfile.php');

        $term = $context->get('term');

        # Check to see if a student already has a profile on file.
        # If so, pass the profile's id to the Student_Profile constructor
        # so it will load the current profile, and then update it.
        # Otherwise, create a new profile.
        $id = RoommateProfileFactory::checkForProfile(UserStatus::getUsername(), $term);

        if ($id !== FALSE){
            $profile = new RoommateProfile($id);
        } else {
            $profile = new RoommateProfile();
            $profile->setUsername(UserStatus::getUsername());
            $profile->set_date_submitted();
            $profile->setTerm($term);
        }

        # Alternate contact info
        $contact_array = array("alternate_email", "fb_link", "instagram_sn", "twitter_sn", "tumblr_sn", "kik_sn", "about_me");
		
		for ($x = 0; $x < count($contact_array); $x++)
		{
			if (isset($_REQUEST[$contact_array[$x]]) && $_REQUEST[$contact_array[$x]] != '')
			{
				$profile->set_text($contact_array[$x], $_REQUEST[$contact_array[$x]]);
			}
			else
			{
				$profile->set_text($contact_array[$x], '');
			}
		}

        # Hobbies check boxes
        $hobbies_array = array("arts_and_crafts", "books_and_reading", "cars", "church_activities", 
        "collecting", "computers_and_technology", "dancing", "fashion", "fine_arts", "gardening", 
        "games", "humor", "investing_personal_finance", "movies", "music", "outdoor_activities", 
        "pets_and_animals", "photography", "politics", "sports", "travel", "tv_shows", "volunteering", "writing", "rotc");
        
		for ($x = 0; $x < count($hobbies_array); $x++)
		{
			if (isset($_REQUEST['hobbies_checkbox'][$hobbies_array[$x]]))
			{
				$profile->set_checked($hobbies_array[$x]);
			}
			else
			{
				$profile->set_checked($hobbies_array[$x],0);
			}
		}

        # Music check boxes
        $music_array = array("alternative", "ambient", "beach", "bluegrass", "blues", "christian", "classical", 
		"classic_rock", "country", "electronic", "folk", "heavy_metal", "hip_hop", "house", "industrial", "jazz", 
		"popular_music", "progressive", "punk", "r_and_b", "rap", "reggae", "rock", "world_music");
		
		for ($x = 0; $x < count($music_array); $x++)
		{
			if (isset($_REQUEST['music_checkbox'][$music_array[$x]]))
			{
				$profile->set_checked($music_array[$x]);
			}
			else
			{
				$profile->set_checked($music_array[$x],0);
			}
		}
		
        # Study times
        $study_array = array("study_early_morning", "study_morning_afternoon", "study_afternoon_evening", "study_evening", "study_late_night");
        
        for ($x = 0; $x < count($study_array); $x++)
		{
			if (isset($_REQUEST['study_times'][$study_array[$x]]))
			{
				$profile->set_checked($study_array[$x]);
			}
			else
			{
				$profile->set_checked($study_array[$x],0);
			}
		}
		
        # Drop downs
        $drop_down_array = array("political_views", "major", "experience", 
        "sleep_time", "wakeup_time", "overnight_guests", "loudness", "cleanliness", "free_time");

		for ($x = 0; $x < count($drop_down_array); $x++)
		{
			if (isset($_REQUEST[$drop_down_array[$x]]) && $_REQUEST[$drop_down_array[$x]] != '')
			{
				$profile->set_text($drop_down_array[$x], $_REQUEST[$drop_down_array[$x]]);
			}
			else
			{
				$profile->set_text($drop_down_array[$x], '');
			}
		}
		
        # Spoken Languages
        $lang_array = array("arabic" , "bengali" , "chinese" , "english" , "french" , "german" , "hindi" , 
        "italian" , "japanese" , "javanese" , "korean" , "malay" , "marathi" , "portuguese" , "punjabi" , 
        "russian" , "tamil" , "telugu" , "vietnamese");
        
		for ($x = 0; $x < count($lang_array); $x++)
		{
			if (isset($_REQUEST['language_checkbox'][$lang_array[$x]]))
			{
				$profile->set_checked($lang_array[$x]);
			}
			else
			{
				$profile->set_checked($lang_array[$x], 0);
			}
		}
		
        $profile->save();

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your roommate profile was successfully created/updated.');
        $successCmd = CommandFactory::getCommand('ShowStudentMenu');
        $successCmd->redirect();
    }
}
?>
