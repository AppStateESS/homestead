<?php

PHPWS_Core::initCoreClass('DBPager.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');


/**
 * Provides the dbpager view for showing the roster of students invited/accepted/confirmed to a given Learning Community
 *
 * @author jbooker
 * @package HMS
 *
 */
class RlcRosterPager extends DBPager {

    private $rlc;

    public function __construct(HMS_Learning_Community $rlc)
    {
        parent::__construct('hms_learning_community_applications', 'HMS_RLC_Application');
        $this->rlc = $rlc;

        $tags = array();
        $tags['TITLE'] = $this->rlc->get_community_name() . ' Assignments ' . Term::toString(Term::getSelectedTerm());

        $backCmd = CommandFactory::getCommand('ShowSearchByRlc');
        $tags['BACK_LINK'] = $backCmd->getURI();

        $adminAddCmd = CommandFactory::getCommand('ShowAdminAddRlcMember');
        $adminAddCmd->setCommunity($this->rlc);
        $tags['ADD_URI'] = $adminAddCmd->getURI();

        $this->db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'application_id', 'id');
        $this->db->addJoin('LEFT OUTER', 'hms_learning_community_applications', 'hms_new_application', 'username', 'username AND hms_new_application.term=hms_learning_community_applications.term');
        $this->db->addJoin('LEFT OUTER', 'hms_learning_community_applications', 'hms_roommate',
                            'username', 'requestor OR hms_learning_community_applications.username = hms_roommate.requestee AND hms_roommate.confirmed = \'1\'');
        $this->db->addWhere('hms_learning_community_applications.term', Term::getSelectedTerm());
        $this->db->addWhere('hms_learning_community_assignment.rlc_id', $this->rlc->get_id());
        $this->db->addColumn('hms_learning_community_applications.*');
        $this->db->addColumn('hms_learning_community_assignment.*');
        $this->db->addColumn('hms_roommate.*');
        // $this->db->setTestMode();
        // $this->db->select();


        $this->joinResult('username', 'hms_assignment', 'asu_username', 'bed_id', 'bed_assignment');
        $this->joinResult('username', 'hms_new_application', 'username AND hms_new_application.term=hms_learning_community_applications.term', 'student_type', 's_type');
        $this->joinResult('username', 'hms_roommate', 'requestee OR hms_learning_community_applications.username=hms_roommate.requestor', 'confirmed', 'conf');

        $this->setModule('hms');
        $this->setTemplate('admin/view_by_rlc_pager.tpl');
        $this->setLink('index.php?module=hms&action=ViewByRlc&rlc=' . $this->rlc->get_id());
        $this->setEmptyMessage('There are no students assigned to this learning community.');
        $this->addPageTags($tags);
        $this->addRowTags('viewByRLCPagerTags');
        $this->setReportRow('viewByRLCExportFields');
        $this->get();
        //var_dump($this->getFinalTemplate());
        //exit();
    }
}
