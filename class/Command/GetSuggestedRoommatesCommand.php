<?php

namespace Homestead\Command;

use Homestead\UserStatus;
use Homestead\StudentFactory;
use Homestead\RoommateProfileFactory;
use Homestead\RoommateProfileComparison;

class GetSuggestedRoommatesCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'GetSuggestedRoommates');
    }

    public function execute(CommandContext $context)
    {
        $term = $context->get('term');

        if(UserStatus::isAdmin()){
            // If user is admin, they can search for any user's matches
            $student = StudentFactory::getStudentByBannerId($context->get('bannerId'), $term);
        } else {
            // If user is student, they can only search for their own matches
            $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        }

        $currentProfile = RoommateProfileFactory::getProfile($student->getBannerId(), $term);

        // Get the list of other unassigned freshmen of the same gender
        $profiles = RoommateProfileFactory::getPotentialProfiles($student, $term);

        // Score each profile against this one
        $scoreList = array();
        foreach($profiles as $profile){
            $score = (int) RoommateProfileComparison::compare($currentProfile, $profile);
            $scoreList[$profile->getBannerId()] = $score;
        }

        // Sort the scores, results are in order of least to greatest
        asort($scoreList);

        // If we have more than 25 results, take to top 25 best matches
        if(sizeof($scoreList) > 25){
            $scoreList = array_slice($scoreList, -25);
        }

        // Reverse the list to get best matches first
        $scoreList = array_reverse($scoreList, true);

        // For each result, generate an array with the students name, username, and match percent
        $matchList = array();

        foreach($scoreList as $bannerId => $matchPercent){
            $matchStudent = StudentFactory::getStudentByBannerId($bannerId, $term);

            // If preferred name is set, use it as first name, otherwise just use first name
            $prefName = $matchStudent->getPreferredName();
            if(!isset($prefName) || $prefName === ''){
                $firstName = $matchStudent->getFirstName();
            } else {
                $firstName = $prefName;
            }

            $lastName = substr($matchStudent->getLastName(), 0, 1);

            $name = $firstName . ' ' . $lastName . '.';

            $matchList[] = array(
                            'name' => $name,
                            'bannerId' => $matchStudent->getBannerId(),
                            'matchPercent' => $matchPercent);
        }

        echo json_encode($matchList);exit;
    }
}
