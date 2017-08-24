<?php

namespace Homestead;

    class RoommateProfileSearchView extends DBPager{
    /**
     * Sets up the pager object for searching questionnairs.
     *
     * @return String HTML output
     */
    public static function profile_search_pager($term)
    {
        // get the current student's gender
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), Term::getCurrentTerm());
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $student->getApplicationTerm());

        $gender = $student->getGender();

        \PHPWS_Core::initCoreClass('DBPager.php');

        $pageTags = array();

        $pageTags['USERNAME'] = _('Email');
        $pageTags['FIRST_NAME'] = _('First Name');
        $pageTags['LAST_NAME'] = _('Last Name');
        $pageTags['ACTIONS'] = _('Action');

        $pager = new DBPager('hms_student_profiles', 'RoommateProfile');

        $pager->db->addWhere('term', $term);

        // Check to see if user is assigned to an RLC
        $rlc_assignment = HMS_RLC_Assignment::checkForAssignment($student->getUsername(), $student->getApplicationTerm());
        if ($rlc_assignment != false) {
            // User is assigned to an RLC, only show results from other students in the same RLC
            $pager->db->addJoin('LEFT OUTER', 'hms_student_profiles', 'hms_learning_community_applications', 'username', 'username');
            $pager->db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'application_id', 'id');
            $pager->db->addWhere('hms_learning_community_assignment.rlc_id', $rlc_assignment['rlc_id']);
            // $pager->db->setTestMode();
        }

        // If an ASU username was entered, just use that. Otherwise, use the rest of the fields.
        if (isset($_REQUEST['asu_username']) && $_REQUEST['asu_username'] != '') {
            $pager->addWhere('hms_student_profiles.username', $_REQUEST['asu_username'], 'ILIKE');
            $_SESSION['profile_search_asu_username'] = $_REQUEST['asu_username'];
        } else {
            $m = new RoommateProfile;

            $hobbiesCount = count($m->hobbies_array);
            // Hobby check boxes
            for ($x = 0; $x < $hobbiesCount; $x++)
            {
                if (isset($_REQUEST['hobbies_checkbox'][$m->hobbies_array[$x]])){
                    $pager->addWhere('hms_student_profiles.' . $m->hobbies_array[$x], 1, '=');
                    $_SESSION['hobbies_checkbox'][$m->hobbies_array[$x]] = 1;
                }
            }

            $musicCount = count($m->music_array);
            // Music check boxes
            for ($x = 0; $x < $musicCount; $x++)
            {
                if (isset($_REQUEST['music_checkbox'][$m->music_array[$x]])){
                    $pager->addWhere('hms_student_profiles.' . $m->music_array[$x], 1, '=');
                    $_SESSION['hobbies_checkbox'][$m->music_array[$x]] = 1;
                }
            }

            $studyCount = count($m->study_array);
            // Study times
            for ($x = 0; $x < $studyCount; $x++)
            {
                if (isset($_REQUEST['study_times'][$m->study_array[$x]])){
                    $pager->addWhere('hms_student_profiles.' . $m->study_array[$x], 1, '=');
                    $_SESSION['study_times'][$m->study_array[$x]] = 1;
                }
            }

            $dropDownCount = count($m->drop_down_array);
            // Drop downs
            for ($x = 0; $x < $dropDownCount; $x++)
            {
                if(isset($_REQUEST[$m->drop_down_array[$x]]) && $_REQUEST[$m->drop_down_array[$x]] != 0){
                    $pager->addWhere('hms_student_profiles.' . $m->drop_down_array[$x], $_REQUEST[$m->drop_down_array[$x]], '=');
                    $_SESSION[$m->drop_down_array[$x]] = $_REQUEST[$m->drop_down_array[$x]];
                }

            }

            $langCount = count($m->lang_array);
            // Spoken Languages
            for ($x = 0; $x < $langCount; $x++)
            {
                if (isset($_REQUEST['language_checkbox'][$m->lang_array[$x]])){
                    $pager->addWhere('hms_student_profiles.' . $m->lang_array[$x], 1, '=');
                    $_SESSION['language_checkbox'][$m->lang_array[$x]] = 1;
                }
            }
        }

        // Join with hms_application table on username to make sure genders match.
        $pager->db->addJoin('LEFT OUTER', 'hms_student_profiles', 'hms_new_application', 'username', 'username');
        // $pager->addWhere('hms_student_profiles.user_id','hms_application.asu_username','ILIKE');
        $pager->addWhere('hms_new_application.gender', $gender, '=');

        // Don't list the current user as a match
        $pager->addWhere('hms_student_profiles.username', UserStatus::getUsername(), 'NOT LIKE');

        $pager->db->addOrder('username', 'ASC');

        $pager->setModule('hms');
        $pager->setTemplate('student/profile_search_pager.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No matches found. Try broadening your search by selecting fewer criteria.");
        $pager->addRowTags('getPagerTags');
        $pager->addPageTags($pageTags);

        return $pager->get();
    }
}
