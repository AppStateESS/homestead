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
        $tags['BACK_LINK'] = $backCmd->getLink('<i class="fa fa-arrow-left"></i> RLC List', null, 'btn btn-info');

        $adminAddCmd = CommandFactory::getCommand('ShowAdminAddRlcMember');
        $adminAddCmd->setCommunity($this->rlc);
        $tags['ADD_URI'] = $adminAddCmd->getURI();

        $this->db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'application_id', 'id');
        $this->db->addWhere('hms_learning_community_applications.term', Term::getSelectedTerm());
        $this->db->addWhere('hms_learning_community_assignment.rlc_id', $this->rlc->get_id());

        //$this->joinResult('id','hms_learning_community_applications','hms_assignment_id','user_id', 'user_id');
        $this->setModule('hms');
        $this->setTemplate('admin/view_by_rlc_pager.tpl');
        $this->setLink('index.php?module=hms&action=ViewByRlc&rlc=' . $this->rlc->get_id());
        $this->setEmptyMessage('There are no students assigned to this learning community.');
        $this->addPageTags($tags);
        $this->addRowTags('viewByRLCPagerTags');
        $this->setReportRow('viewByRLCExportFields');
    }
}

?>