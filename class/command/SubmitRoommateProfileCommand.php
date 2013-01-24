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

        if ($id !== FALSE){
            $profile = new RoommateProfile($id);
        } else {
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
        if (isset($_REQUEST['alternate_email']) && $_REQUEST['alternate_email'] != ''){
            $profile->set_alternate_email($_REQUEST['alternate_email']);
        } else {
            $profile->set_alternate_email('');
        }

        if (isset($_REQUEST['aim_sn']) && $_REQUEST['aim_sn'] != ''){
            $profile->set_aim_sn($_REQUEST['aim_sn']);
        } else {
            $profile->set_aim_sn('');
        }

        if (isset($_REQUEST['yahoo_sn']) && $_REQUEST['yahoo_sn'] != ''){
            $profile->set_yahoo_sn($_REQUEST['yahoo_sn']);
        } else {
            $profile->set_yahoo_sn('');
        }

        if (isset($_REQUEST['msn_sn']) && $_REQUEST['msn_sn'] != ''){
            $profile->set_msn_sn($_REQUEST['msn_sn']);
        } else {
            $profile->set_msn_sn('');
        }

        # Hobbies check boxes
        if (isset($_REQUEST['hobbies_checkbox']['arts_and_crafts'])){
            $profile->set_arts_and_crafts();
        } else {
            $profile->set_arts_and_crafts(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['books_and_reading'])){
            $profile->set_books_and_reading();
        } else {
            $profile->set_books_and_reading(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['cars'])){
            $profile->set_cars();
        } else {
            $profile->set_cars(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['church_activities'])){
            $profile->set_church_activities();
        } else {
            $profile->set_church_activities(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['collecting'])){
            $profile->set_collecting();
        } else {
            $profile->set_collecting(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['computers_and_technology'])){
            $profile->set_computers_and_technology();
        } else {
            $profile->set_computers_and_technology(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['dancing'])){
            $profile->set_dancing();
        } else {
            $profile->set_dancing(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['fashion'])){
            $profile->set_fashion();
        } else {
            $profile->set_fashion(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['fine_arts'])){
            $profile->set_fine_arts();
        } else {
            $profile->set_fine_arts(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['gardening'])){
            $profile->set_gardening();
        } else {
            $profile->set_gardening(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['games'])){
            $profile->set_games();
        } else {
            $profile->set_games(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['humor'])){
            $profile->set_humor();
        } else {
            $profile->set_humor(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['investing_personal_finance'])){
            $profile->set_investing_personal_finance();
        } else {
            $profile->set_investing_personal_finance(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['movies'])){
            $profile->set_movies();
        } else {
            $profile->set_movies(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['music'])){
            $profile->set_music();
        } else {
            $profile->set_music(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['outdoor_activities'])){
            $profile->set_outdoor_activities();
        } else {
            $profile->set_outdoor_activities(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['pets_and_animals'])){
            $profile->set_pets_and_animals();
        } else {
            $profile->set_pets_and_animals(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['photography'])){
            $profile->set_photography();
        } else {
            $profile->set_photography(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['politics'])){
            $profile->set_politics();
        } else {
            $profile->set_politics(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['sports'])){
            $profile->set_sports();
        } else {
            $profile->set_sports(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['travel'])){
            $profile->set_travel();
        } else {
            $profile->set_travel(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['tv_shows'])){
            $profile->set_tv_shows();
        } else {
            $profile->set_tv_shows(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['volunteering'])){
            $profile->set_volunteering();
        } else {
            $profile->set_volunteering(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['writing'])){
            $profile->set_writing();
        } else {
            $profile->set_writing(0);
        }

        if (isset($_REQUEST['hobbies_checkbox']['rotc'])){
            $profile->set_rotc();
        } else {
            $profile->set_rotc(0);
        }

        # Music check boxes
        if (isset($_REQUEST['music_checkbox']['alternative'])){
            $profile->set_alternative();
        } else {
            $profile->set_alternative(0);
        }

        if (isset($_REQUEST['music_checkbox']['ambient'])){
            $profile->set_ambient();
        } else {
            $profile->set_ambient(0);
        }

        if (isset($_REQUEST['music_checkbox']['beach'])){
            $profile->set_beach();
        } else {
            $profile->set_beach(0);
        }

        if (isset($_REQUEST['music_checkbox']['bluegrass'])){
            $profile->set_bluegrass();
        } else {
            $profile->set_bluegrass(0);
        }

        if (isset($_REQUEST['music_checkbox']['blues'])){
            $profile->set_blues();
        } else {
            $profile->set_blues(0);
        }

        if (isset($_REQUEST['music_checkbox']['christian'])){
            $profile->set_christian();
        } else {
            $profile->set_christian(0);
        }

        if (isset($_REQUEST['music_checkbox']['classical'])){
            $profile->set_classical();
        } else {
            $profile->set_classical(0);
        }

        if (isset($_REQUEST['music_checkbox']['classic_rock'])){
            $profile->set_classic_rock();
        } else {
            $profile->set_classic_rock(0);
        }

        if (isset($_REQUEST['music_checkbox']['country'])){
            $profile->set_country();
        } else {
            $profile->set_country(0);
        }

        if (isset($_REQUEST['music_checkbox']['electronic'])){
            $profile->set_electronic();
        } else {
            $profile->set_electronic(0);
        }

        if (isset($_REQUEST['music_checkbox']['folk'])){
            $profile->set_folk();
        } else {
            $profile->set_folk(0);
        }

        if (isset($_REQUEST['music_checkbox']['heavy_metal'])){
            $profile->set_heavy_metal();
        } else {
            $profile->set_heavy_metal(0);
        }

        if (isset($_REQUEST['music_checkbox']['hip_hop'])){
            $profile->set_hip_hop();
        } else {
            $profile->set_hip_hop(0);
        }

        if (isset($_REQUEST['music_checkbox']['house'])){
            $profile->set_house();
        } else {
            $profile->set_house(0);
        }

        if (isset($_REQUEST['music_checkbox']['industrial'])){
            $profile->set_industrial();
        } else {
            $profile->set_industrial(0);
        }

        if (isset($_REQUEST['music_checkbox']['jazz'])){
            $profile->set_jazz();
        } else {
            $profile->set_jazz(0);
        }

        if (isset($_REQUEST['music_checkbox']['popular_music'])){
            $profile->set_popular_music();
        } else {
            $profile->set_popular_music(0);
        }

        if (isset($_REQUEST['music_checkbox']['progressive'])){
            $profile->set_progressive();
        } else {
            $profile->set_progressive(0);
        }

        if (isset($_REQUEST['music_checkbox']['punk'])){
            $profile->set_punk();
        } else {
            $profile->set_punk(0);
        }

        if (isset($_REQUEST['music_checkbox']['r_and_b'])){
            $profile->set_r_and_b();
        } else {
            $profile->set_r_and_b(0);
        }

        if (isset($_REQUEST['music_checkbox']['rap'])){
            $profile->set_rap();
        } else {
            $profile->set_rap(0);
        }

        if (isset($_REQUEST['music_checkbox']['reggae'])){
            $profile->set_reggae();
        } else {
            $profile->set_reggae(0);
        }

        if (isset($_REQUEST['music_checkbox']['alternative'])){
            $profile->set_rock();
        } else {
            $profile->set_rock(0);
        }

        if (isset($_REQUEST['music_checkbox']['world_music'])){
            $profile->set_world_music();
        } else {
            $profile->set_world_music(0);
        }

        # Study times
        if (isset($_REQUEST['study_times']['study_early_morning'])){
            $profile->set_study_early_morning();
        } else {
            $profile->set_study_early_morning(0);
        }

        if (isset($_REQUEST['study_times']['study_morning_afternoon'])){
            $profile->set_study_morning_afternoon();
        } else {
            $profile->set_study_morning_afternoon(0);
        }

        if (isset($_REQUEST['study_times']['study_afternoon_evening'])){
            $profile->set_study_afternoon_evening();
        } else {
            $profile->set_study_afternoon_evening(0);
        }

        if (isset($_REQUEST['study_times']['study_evening'])){
            $profile->set_study_evening();
        } else {
            $profile->set_study_evening(0);
        }

        if (isset($_REQUEST['study_times']['study_late_night'])){
            $profile->set_study_late_night();
        } else {
            $profile->set_study_late_night(0);
        }

        # Drop downs
        if (isset($_REQUEST['political_views_dropbox']) && $_REQUEST['political_views_dropbox'] != 0){
            $profile->set_political_view($_REQUEST['political_views_dropbox']);
        } else {
            $profile->set_political_view(0);
        }

        if (isset($_REQUEST['intended_major']) && $_REQUEST['intended_major'] != 0){
            $profile->set_major($_REQUEST['intended_major']);
        } else {
            $profile->set_major(0);
        }

        if (isset($_REQUEST['important_experience']) && $_REQUEST['important_experience'] != 0){
            $profile->set_experience($_REQUEST['important_experience']);
        } else {
            $profile->set_experience(0);
        }

        if (isset($_REQUEST['sleep_time']) && $_REQUEST['sleep_time'] != 0){
            $profile->set_sleep_time($_REQUEST['sleep_time']);
        } else {
            $profile->set_sleep_time(0);
        }

        if (isset($_REQUEST['wakeup_time']) && $_REQUEST['wakeup_time'] != 0){
            $profile->set_wakeup_time($_REQUEST['wakeup_time']);
        } else {
            $profile->set_wakeup_time(0);
        }

        if (isset($_REQUEST['overnight_guests']) && $_REQUEST['overnight_guests'] != 0){
            $profile->set_overnight_guests($_REQUEST['overnight_guests']);
        } else {
            $profile->set_overnight_guests(0);
        }

        if (isset($_REQUEST['loudness']) && $_REQUEST['loudness'] != 0){
            $profile->set_loudness($_REQUEST['loudness']);
        } else {
            $profile->set_loudness(0);
        }

        if (isset($_REQUEST['cleanliness']) && $_REQUEST['cleanliness'] != 0){
            $profile->set_cleanliness($_REQUEST['cleanliness']);
        } else {
            $profile->set_cleanliness(0);
        }

        if (isset($_REQUEST['free_time']) && $_REQUEST['free_time'] != 0){
            $profile->set_free_time($_REQUEST['free_time']);
        } else {
            $profile->set_free_time(0);
        }

        # Spoken Languages
        if (isset($_REQUEST['language_checkbox']['arabic'])){
            $profile->set_arabic();
        } else {
            $profile->set_arabic(0);
        }

        if (isset($_REQUEST['language_checkbox']['bengali'])){
            $profile->set_bengali();
        } else {
            $profile->set_bengali(0);
        }

        if (isset($_REQUEST['language_checkbox']['chinese'])){
            $profile->set_chinese();
        } else {
            $profile->set_chinese(0);
        }

        if (isset($_REQUEST['language_checkbox']['english'])){
            $profile->set_english();
        } else {
            $profile->set_english(0);
        }

        if (isset($_REQUEST['language_checkbox']['french'])){
            $profile->set_french();
        } else {
            $profile->set_french(0);
        }

        if (isset($_REQUEST['language_checkbox']['german'])){
            $profile->set_german();
        } else {
            $profile->set_german(0);
        }

        if (isset($_REQUEST['language_checkbox']['hindi'])){
            $profile->set_hindi();
        } else {
            $profile->set_hindi(0);
        }

        if (isset($_REQUEST['language_checkbox']['italian'])){
            $profile->set_italian();
        } else {
            $profile->set_italian(0);
        }

        if (isset($_REQUEST['language_checkbox']['japanese'])){
            $profile->set_japanese();
        } else {
            $profile->set_japanese(0);
        }

        if (isset($_REQUEST['language_checkbox']['javanese'])){
            $profile->set_javanese();
        } else {
            $profile->set_javanese(0);
        }

        if (isset($_REQUEST['language_checkbox']['korean'])){
            $profile->set_korean();
        } else {
            $profile->set_korean(0);
        }

        if (isset($_REQUEST['language_checkbox']['malay'])){
            $profile->set_malay();
        } else {
            $profile->set_malay(0);
        }

        if (isset($_REQUEST['language_checkbox']['marathi'])){
            $profile->set_marathi();
        } else {
            $profile->set_marathi(0);
        }

        if (isset($_REQUEST['language_checkbox']['portuguese'])){
            $profile->set_portuguese();
        } else {
            $profile->set_portuguese(0);
        }

        if (isset($_REQUEST['language_checkbox']['punjabi'])){
            $profile->set_punjabi();
        } else {
            $profile->set_punjabi(0);
        }

        if (isset($_REQUEST['language_checkbox']['russian'])){
            $profile->set_russian();
        } else {
            $profile->set_russian(0);
        }

        if (isset($_REQUEST['language_checkbox']['spanish'])){
            $profile->set_spanish();
        } else {
            $profile->set_spanish(0);
        }

        if (isset($_REQUEST['language_checkbox']['tamil'])){
            $profile->set_tamil();
        } else {
            $profile->set_tamil(0);
        }

        if (isset($_REQUEST['language_checkbox']['telugu'])){
            $profile->set_telugu();
        } else {
            $profile->set_telugu(0);
        }

        if (isset($_REQUEST['language_checkbox']['vietnamese'])){
            $profile->set_vietnamese();
        } else {
            $profile->set_vietnamese(0);
        }

        $profile->save();

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your roommate profile was successfully created/updated.');
        $successCmd = CommandFactory::getCommand('ShowStudentMenu');
        $successCmd->redirect();
    }
}


?>
