<?php

namespace Homestead;
PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

class RlcAssignmentsView extends View {

    public function show(){
        PHPWS_Core::initCoreClass('DBPager.php');

        $tags = array();

        $tags['TITLE'] = "View Final RLC Assignments " . Term::toString(Term::getSelectedTerm());

        $pager = new DBPager('hms_learning_community_assignment','HMS_RLC_Assignment');

        //$pager->db->addWhere('hms_learning_community_applications.hms_assignment_id','hms_learning_community_assignment.id','=');
        $pager->db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'application_id', 'id');
        $pager->db->addWhere('hms_learning_community_applications.term', Term::getSelectedTerm());
        $pager->db->addWhere('hms_learning_community_assignment.state', 'confirmed');

        $pager->joinResult('application_id','hms_learning_community_applications','id','username', 'username');
        $pager->joinResult('application_id','hms_learning_community_applications','id','term', 'term');
        $pager->setModule('hms');
        $pager->setTemplate('admin/display_final_rlc_assignments.tpl');
        $pager->setLink('index.php?module=hms&type=rlc&op=assign_applicants_to_rlcs');
        $pager->setEmptyMessage('No RLC assignments have been made.');
        $pager->addPageTags($tags);
        $pager->addRowTags('getAdminPagerTags');
        $pager->setReportRow('getAdminCsvRow');

        Layout::addPageTitle("RLC Assignments");

        return $pager->get();
    }
}
