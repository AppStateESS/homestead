<?php

namespace Homestead\Command;

use Homestead\UserStatus;
use Homestead\StudentFactory;
use Homestead\RoommateProfileFactory;

class GetSuggestedRoommatesCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'GetSuggestedRoommates');
    }

    public function execute(CommandContext $context)
    {
        $term = $context->get('term');

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $currentProfile = RoommateProfileFactory::getProfile($student->getBannerId(), $term);

        $profiles = RoommateProfileFactory::getPotentialProfiles($student, $term);

        var_dump($profiles);exit;
    }
}
