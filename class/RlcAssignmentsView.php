<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

class RlcAssignmentsView extends View {

    public function show(){
        PHPWS_Core::initCoreClass('DBPager.php');

        $tags = array();

        $tags['TITLE'] = "View Final RLC Assignments " . Term::toString(Term::getSelectedTerm());

/*        $tags['PRINT_RECORDS'] = "// TODO: Print Records";
        $tags['EXPORT'] = "// TODO: Export Records";*/

        $pager = &new DBPager('hms_learning_community_assignment','HMS_RLC_Assignment');
      
        //$pager->db->addWhere('hms_learning_community_applications.hms_assignment_id','hms_learning_community_assignment.id','=');
        $pager->db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'id', 'hms_assignment_id');
        $pager->db->addWhere('hms_learning_community_applications.term', Term::getSelectedTerm()); 

        $pager->joinResult('id','hms_learning_community_applications','hms_assignment_id','user_id', 'user_id');
        $pager->setModule('hms');
        $pager->setTemplate('admin/display_final_rlc_assignments.tpl');
        $pager->setLink('index.php?module=hms&type=rlc&op=assign_applicants_to_rlcs');
        $pager->setEmptyMessage('No RLC assignments have been made.');
        $pager->addPageTags($tags);
        $pager->addRowTags('getAdminPagerTags');

        return $pager->get();
    }
}
?>
