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
        $id = RoommateProfile::checkForProfile(UserStatus::getUsername(), $term);

        if($id !== FALSE){
            $profile = new RoommateProfile($id);
        }else{
            $profile = new RoommateProfile();
            $profile->setUsername(UserStatus::getUsername());
            $profile->set_date_submitted();
            $profile->setTerm($term);
        }

        /*
         * I know the following code still uses $_REQUEST. I don't see any real advantage
         * (at the present time) to taking the time to switch it all...
         */

        # Alternate contact info
        if(isset($_REQUEST['alternate_email']) && $_REQUEST['alternate_email'] != ''){
            $profile->set_alternate_email($_REQUEST['alternate_email']);
        }

        if(isset($_REQUEST['aim_sn']) && $_REQUEST['aim_sn'] != ''){
            $profile->set_aim_sn($_REQUEST['aim_sn']);
        }

        if(isset($_REQUEST['yahoo_sn']) && $_REQUEST['yahoo_sn'] != ''){
            $profile->set_yahoo_sn($_REQUEST['yahoo_sn']);
        }

        if(isset($_REQUEST['msn_sn']) && $_REQUEST['msn_sn'] != ''){
            $profile->set_msn_sn($_REQUEST['msn_sn']);
        }

        # Hobbies check boxes
        if(isset($_REQUEST['hobbies_checkbox']['arts_and_crafts'])){
            $profile->set_arts_and_crafts();
        }

        if(isset($_REQUEST['hobbies_checkbox']['books_and_reading'])){
            $profile->set_books_and_reading();
        }

        if(isset($_REQUEST['hobbies_checkbox']['cars'])){
            $profile->set_cars();
        }

        if(isset($_REQUEST['hobbies_checkbox']['church_activities'])){
            $profile->set_church_activities();
        }

        if(isset($_REQUEST['hobbies_checkbox']['collecting'])){
            $profile->set_collecting();
        }

        if(isset($_REQUEST['hobbies_checkbox']['computers_and_technology'])){
            $profile->set_computers_and_technology();
        }

        if(isset($_REQUEST['hobbies_checkbox']['dancing'])){
            $profile->set_dancing();
        }

        if(isset($_REQUEST['hobbies_checkbox']['fashion'])){
            $profile->set_fashion();
        }

        if(isset($_REQUEST['hobbies_checkbox']['fine_arts'])){
            $profile->set_fine_arts();
        }

        if(isset($_REQUEST['hobbies_checkbox']['gardening'])){
            $profile->set_gardening();
        }

        if(isset($_REQUEST['hobbies_checkbox']['games'])){
            $profile->set_games();
        }

        if(isset($_REQUEST['hobbies_checkbox']['humor'])){
            $profile->set_humor();
        }

        if(isset($_REQUEST['hobbies_checkbox']['investing_personal_finance'])){
            $profile->set_investing_personal_finance();
        }

        if(isset($_REQUEST['hobbies_checkbox']['movies'])){
            $profile->set_movies();
        }

        if(isset($_REQUEST['hobbies_checkbox']['music'])){
            $profile->set_music();
        }

        if(isset($_REQUEST['hobbies_checkbox']['outdoor_activities'])){
            $profile->set_outdoor_activities();
        }

        if(isset($_REQUEST['hobbies_checkbox']['pets_and_animals'])){
            $profile->set_pets_and_animals();
        }

        if(isset($_REQUEST['hobbies_checkbox']['photography'])){
            $profile->set_photography();
        }

        if(isset($_REQUEST['hobbies_checkbox']['politics'])){
            $profile->set_politics();
        }

        if(isset($_REQUEST['hobbies_checkbox']['sports'])){
            $profile->set_sports();
        }

        if(isset($_REQUEST['hobbies_checkbox']['travel'])){
            $profile->set_travel();
        }

        if(isset($_REQUEST['hobbies_checkbox']['tv_shows'])){
            $profile->set_tv_shows();
        }

        if(isset($_REQUEST['hobbies_checkbox']['volunteering'])){
            $profile->set_volunteering();
        }

        if(isset($_REQUEST['hobbies_checkbox']['writing'])){
            $profile->set_writing();
        }

        # Music check boxes
        if(isset($_REQUEST['music_checkbox']['alternative'])){
            $profile->set_alternative();
        }

        if(isset($_REQUEST['music_checkbox']['ambient'])){
            $profile->set_ambient();
        }

        if(isset($_REQUEST['music_checkbox']['beach'])){
            $profile->set_beach();
        }

        if(isset($_REQUEST['music_checkbox']['bluegrass'])){
            $profile->set_bluegrass();
        }

        if(isset($_REQUEST['music_checkbox']['blues'])){
            $profile->set_blues();
        }

        if(isset($_REQUEST['music_checkbox']['classical'])){
            $profile->set_classical();
        }

        if(isset($_REQUEST['music_checkbox']['classic_rock'])){
            $profile->set_classic_rock();
        }

        if(isset($_REQUEST['music_checkbox']['country'])){
            $profile->set_country();
        }

        if(isset($_REQUEST['music_checkbox']['electronic'])){
            $profile->set_electronic();
        }

        if(isset($_REQUEST['music_checkbox']['folk'])){
            $profile->set_folk();
        }

        if(isset($_REQUEST['music_checkbox']['heavy_metal'])){
            $profile->set_heavy_metal();
        }

        if(isset($_REQUEST['music_checkbox']['hip_hop'])){
            $profile->set_hip_hop();
        }

        if(isset($_REQUEST['music_checkbox']['house'])){
            $profile->set_house();
        }

        if(isset($_REQUEST['music_checkbox']['industrial'])){
            $profile->set_industrial();
        }

        if(isset($_REQUEST['music_checkbox']['jazz'])){
            $profile->set_jazz();
        }

        if(isset($_REQUEST['music_checkbox']['popular_music'])){
            $profile->set_popular_music();
        }

        if(isset($_REQUEST['music_checkbox']['progressive'])){
            $profile->set_progressive();
        }

        if(isset($_REQUEST['music_checkbox']['punk'])){
            $profile->set_punk();
        }

        if(isset($_REQUEST['music_checkbox']['r_and_b'])){
            $profile->set_r_and_b();
        }

        if(isset($_REQUEST['music_checkbox']['rap'])){
            $profile->set_rap();
        }

        if(isset($_REQUEST['music_checkbox']['reggae'])){
            $profile->set_reggae();
        }

        if(isset($_REQUEST['music_checkbox']['alternative'])){
            $profile->set_rock();
        }

        if(isset($_REQUEST['music_checkbox']['world_music'])){
            $profile->set_world_music();
        }

        # Study times
        if(isset($_REQUEST['study_times']['study_early_morning'])){
            $profile->set_study_early_morning();
        }

        if(isset($_REQUEST['study_times']['study_morning_afternoon'])){
            $profile->set_study_morning_afternoon();
        }

        if(isset($_REQUEST['study_times']['study_afternoon_evening'])){
            $profile->set_study_afternoon_evening();
        }

        if(isset($_REQUEST['study_times']['study_evening'])){
            $profile->set_study_evening();
        }

        if(isset($_REQUEST['study_times']['study_late_night'])){
            $profile->set_study_late_night();
        }

        # Drop downs
        if(isset($_REQUEST['political_views_dropbox']) && $_REQUEST['political_views_dropbox'] != 0){
            $profile->set_political_view($_REQUEST['political_views_dropbox']);
        }

        if(isset($_REQUEST['intended_major']) && $_REQUEST['intended_major'] != 0){
            $profile->set_major($_REQUEST['intended_major']);
        }

        if(isset($_REQUEST['important_experience']) && $_REQUEST['important_experience'] != 0){
            $profile->set_experience($_REQUEST['important_experience']);
        }

        if(isset($_REQUEST['sleep_time']) && $_REQUEST['sleep_time'] != 0){
            $profile->set_sleep_time($_REQUEST['sleep_time']);
        }

        if(isset($_REQUEST['wakeup_time']) && $_REQUEST['wakeup_time'] != 0){
            $profile->set_wakeup_time($_REQUEST['wakeup_time']);
        }

        if(isset($_REQUEST['overnight_guests']) && $_REQUEST['overnight_guests'] != 0){
            $profile->set_overnight_guests($_REQUEST['overnight_guests']);
        }

        if(isset($_REQUEST['loudness']) && $_REQUEST['loudness'] != 0){
            $profile->set_loudness($_REQUEST['loudness']);
        }

        if(isset($_REQUEST['cleanliness']) && $_REQUEST['cleanliness'] != 0){
            $profile->set_cleanliness($_REQUEST['cleanliness']);
        }

        if(isset($_REQUEST['free_time']) && $_REQUEST['free_time'] != 0){
            $profile->set_free_time($_REQUEST['free_time']);
        }

        $profile->save();

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your roommate profile was successfully created/updated.');
        $successCmd = CommandFactory::getCommand('ShowStudentMenu');
        $successCmd->redirect();
    }
}


?>