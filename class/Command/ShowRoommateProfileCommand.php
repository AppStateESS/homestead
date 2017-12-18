<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\RoommateProfileFactory;
use \Homestead\RoommateProfileView;
use \Homestead\HMS_Roommate;
use \Homestead\UserStatus;

class ShowRoommateProfileCommand extends Command {

    //private $username;
    private $term;
    private $banner_id;

    /*public function setUsername($user){
        $this->username = $user;
    }*/

    public function setTerm($term){
        $this->term = $term;
    }

    public function setBannerID($bannerId){
        $this->banner_id = $bannerId;
    }

    public function getRequestVars(){
        return array('action'=>'ShowRoommateProfile', 'term'=>$this->term, 'banner_id'=>$this->banner_id);
    }

    public function execute(CommandContext $context)
    {
        $student = StudentFactory::getStudentByBannerID($context->get('banner_id'), $context->get('term'));
        $profile = RoommateProfileFactory::getProfile($context->get('banner_id'), $context->get('term'));

        // Check for a roommate request (made by this student, or someone else requesting this student)
        $roommate = HMS_Roommate::get_confirmed_roommate(UserStatus::getUsername(), $this->term);
        $hasPendingRequest = HMS_Roommate::has_roommate_request(UserStatus::getUsername(),$this->term);

        if(UserStatus::isAdmin()){
            // If user is an admin, making requests doesn't apply to them.. they're just viewing the profile
            $canMakeNewRequest = false;
        } else {
            if(!is_null($roommate) || $hasPendingRequest){
                // Student has a confirmed roommate, or has made a request already.. cannot make another request
                $canMakeNewRequest = false;
            }else{
                // Student has no confirmed roommate, and does not have a pending request.. can make a new requeste
                $canMakeNewRequest = true;
            }
        }

        $view = new RoommateProfileView($student, $profile, $canMakeNewRequest);
        $context->setContent($view->show());
    }
}
