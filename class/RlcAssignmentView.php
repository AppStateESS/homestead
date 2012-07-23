<?php

PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

class RlcAssignmentView extends View {

    private $rlcId; // the rlc ID to limit this view to

    public function __construct($rlcId = NULL){
        $this->rlcId = $rlcId;
    }

    public function show(){

        $tags = array();
        $tags['TITLE']             = 'RLC Assignments - ' . Term::toString(Term::getSelectedTerm());
        $tags['SUMMARY']           = self::display_rlc_assignment_summary();
        $tags['DROPDOWN']          = PHPWS_Template::process(self::getDropDown(), 'hms', 'admin/dropdown_template.tpl');
        $tags['ASSIGNMENTS_PAGER'] = self::rlc_application_admin_pager();

        $export_form = new PHPWS_Form('export_form');
        $exportCmd = CommandFactory::getCommand('ExportRlcApps');
        $exportCmd->initForm($export_form);

        $export_form->addDropBox('rlc_list', HMS_Learning_Community::getRlcList());
        $export_form->addSubmit('submit');

        $export_form->mergeTemplate($tags);
        $tags = $export_form->getTemplate();

        return PHPWS_Template::process($tags, 'hms', 'admin/make_new_rlc_assignments.tpl');
    }

    public function display_rlc_assignment_summary()
    {
        $template = array();

        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addColumn('capacity');
        $db->addColumn('id');
        $communities = $db->select();

        if(!$communities) {
            $template['no_communities'] = _('No communities have been enterred.');
            return PHPWS_Template::process($template, 'hms',
                    'admin/make_new_rlc_assignments_summary.tpl');
        }

        $count = 0;
        $total_assignments = 0;
        $total_available = 0;

        foreach($communities as $community) {
            $db = new PHPWS_DB('hms_learning_community_assignment');
            $db->addJoin('LEFT OUTER', 'hms_learning_community_applications', 'hms_learning_community_assignment', 'id', 'application_id');
            $db->addWhere('rlc_id', $community['id']);
            $db->addWhere('gender', MALE);
            $db->addWhere('hms_learning_community_applications.term', Term::getSelectedTerm());
            $db->addWhere('hms_learning_community_assignment.application_id', 'NULL', '!=');

            $male = $db->select('count');

            $db->resetWhere();
            $db->addWhere('rlc_id', $community['id']);
            $db->addWhere('gender', FEMALE);
            $db->addWhere('hms_learning_community_applications.term', Term::getSelectedTerm());
            $db->addWhere('hms_learning_community_assignment.application_id', 'NULL', '!=');
            $female = $db->select('count');

            if($male   == NULL) $male   = 0;
            if($female == NULL) $female = 0;
            $assigned = $male + $female;

            $template['headings'][$count]['HEADING']       = $community['community_name'];

            $template['assignments'][$count]['ASSIGNMENT'] = "$assigned ($male/$female)";
            $total_assignments += $assigned;

            $template['available'][$count]['AVAILABLE']    = $community['capacity'];
            $total_available += $community['capacity'];

            $template['remaining'][$count]['REMAINING']    = $community['capacity'] - $assigned;
            $count++;
        }

        $template['TOTAL_ASSIGNMENTS'] = $total_assignments;
        $template['TOTAL_AVAILABLE'] = $total_available;
        $template['TOTAL_REMAINING'] = $total_available - $total_assignments;

        return PHPWS_Template::process($template, 'hms',
                'admin/make_new_rlc_assignments_summary.tpl');
    }

    /**
     * Generates a template for the rlc sort dropdown box
     */
    public function getDropDown()
    {
        javascript('jquery');
        javascript('modules/hms/page_refresh');

        $communities = HMS_Learning_Community::getRlcList();

        $dropList = array('0'=>'All');

        foreach($communities as $key=>$val){
            $dropList[$key] = $val;
        }

        $submitCmd = CommandFactory::getCommand('ShowAssignRlcApplicants');

        $form = new PHPWS_Form('dropdown_selector');
        $submitCmd->initForm($form);

        $form->setMethod('get');
        $form->addSelect('rlc', $dropList);

        if( isset($this->rlcId) && !is_null($this->rlcId)) {
            $form->setMatch('rlc', $this->rlcId);
        }
        $form->setExtra('rlc', 'onChange="refresh_page(\'dropdown_selector\')"');

        return $form->getTemplate();
    }

    /**
     * RLC Application pager for the RLC admin panel
     */
    public function rlc_application_admin_pager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $submitCmd = CommandFactory::getCommand('AssignRlcApplicants');
        $form = new PHPWS_Form;
        $submitCmd->initForm($form);
        $form->addSubmit('Submit Changes');
        $tags = $form->getTemplate();

        $pager = new DBPager('hms_learning_community_applications','HMS_RLC_Application');
        $pager->db->addColumn('hms_learning_community_applications.*');
        // The 'addOrder' calls must not be used in order for the sort order buttons on the pager to work
        #$pager->db->addOrder('hms_learning_communities.abbreviation','ASC');
        #$pager->db->addOrder('hms_learning_community_applications.date_submitted', 'ASC');
        //$pager->db->addOrder('user_id','ASC');

        $pager->db->addJoin('LEFT OUTER', 'hms_learning_community_applications', 'hms_learning_community_assignment', 'id', 'application_id');
        $pager->db->addWhere('hms_learning_community_assignment.application_id', 'NULL', '=');
        $pager->db->addWhere('term', Term::getSelectedTerm());
        $pager->db->addWhere('denied', 0); // Only show non-denied applications in this pager


        if(isset($this->rlcId) && !is_null($this->rlcId) && $this->rlcId > 0){
            $pager->db->addWhere('hms_learning_community_applications.rlc_first_choice_id', $this->rlcId ,'=');
        }

        $pager->setModule('hms');
        $pager->setLink('index.php?module=hms&action=SubmitRlcAssignments');
        $pager->setTemplate('admin/rlc_assignments_pager.tpl');
        $pager->setEmptyMessage("No pending RLC applications.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle1"');
        $pager->addPageTags($tags);
        $pager->addRowTags('getAdminPagerTags');
        $pager->setReportRow('applicantsReport');

        Layout::addPageTitle("RLC Assignments");

        return $pager->get();
    }
}
?>
