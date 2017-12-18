<?php

namespace Homestead;

class RoommateProfileComparison {

    public static function compare(RoommateProfile $profile1, RoommateProfile $profile2)
    {
        //var_dump($profile1);
        //var_dump($profile2);

        // Get a list of each profile variables
        $p1Vars = get_object_vars($profile1);
        $p2Vars = get_object_vars($profile2);

        // Remove some fields we don't want to compare on
        unset($p1Vars['id']);
        unset($p1Vars['username']);
        unset($p1Vars['banner_id']);
        unset($p1Vars['date_submitted']);
        unset($p1Vars['term']);
        unset($p1Vars['alternate_email']);
        unset($p1Vars['fb_link']);
        unset($p1Vars['instagram_sn']);
        unset($p1Vars['twitter_sn']);
        unset($p1Vars['tumblr_sn']);
        unset($p1Vars['kik_sn']);
        unset($p1Vars['about_me']);
        unset($p1Vars['hobbies_array']);
        unset($p1Vars['music_array']);
        unset($p1Vars['study_array']);
        unset($p1Vars['drop_down_array']);
        unset($p1Vars['lang_array']);

        unset($p2Vars['id']);
        unset($p2Vars['username']);
        unset($p2Vars['banner_id']);
        unset($p2Vars['date_submitted']);
        unset($p2Vars['term']);
        unset($p2Vars['alternate_email']);
        unset($p2Vars['fb_link']);
        unset($p2Vars['instagram_sn']);
        unset($p2Vars['twitter_sn']);
        unset($p2Vars['tumblr_sn']);
        unset($p2Vars['kik_sn']);
        unset($p2Vars['about_me']);
        unset($p2Vars['hobbies_array']);
        unset($p2Vars['music_array']);
        unset($p2Vars['study_array']);
        unset($p2Vars['drop_down_array']);
        unset($p2Vars['lang_array']);


        $itemsMatched = 0;

        // Foreach variable, see if these two matches
        foreach(array_keys($p1Vars) as $keyName){
            if($p1Vars[$keyName] == $p2Vars[$keyName]){
                $itemsMatched++;
            }
        }

        $percent = round(($itemsMatched / sizeof($p1Vars)) * 100);

        return $percent;
    }
}
