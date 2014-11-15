<?php


PHPWS_Core::initModClass('hms', 'RlcFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

/**
 * RlcAssignmentView - View class for assigning students to LearningCommunitites.
 *
 * @author Jeremy Booker
 * @package hms
*/
class RlcAssignmentView extends hms\View{

    private $term; // The terms we're looking at applications for.
    private $rlc; // the rlc to limit this view to
    private $studentType; // The student type to limit this view to

    /**
     * Constructor
     * @param int $term
     * @param HMS_Learning_Community $rlc
     */
    public function __construct($term, HMS_Learning_Community $rlc = null, $studentType = null)
    {
        $this->term           = $term;
        $this->rlc            = $rlc;
        $this->studentType    = $studentType;
    }

    /**
     * @see View::show()
     */
    public function show()
    {
        $tags = array();
        $tags['TERM']              = Term::toString(Term::getSelectedTerm());
        $tags['SUMMARY']           = $this->display_rlc_assignment_summary(); // TODO: Deprecated, remove this
        $tags['FILTERS']           = $this->getFilters();
        $tags['ASSIGNMENTS_PAGER'] = $this->rlcApplicationPager();

        $exportForm = new PHPWS_Form('export_form');
        $exportCmd = CommandFactory::getCommand('ExportRlcApps');
        $exportCmd->initForm($exportForm);

        $exportForm->addDropBox('rlc_list', HMS_Learning_Community::getRlcList());
        $exportForm->addSubmit('submit');

        $exportForm->mergeTemplate($tags);
        $tags = $exportForm->getTemplate();

        return PHPWS_Template::process($tags, 'hms', 'admin/make_new_rlc_assignments.tpl');
    }

    /**
     * Generates a summary of the number of students assigned to each RLC.
     *
     * @deprecated
     * @return string HTML for summary table
     */
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
     * 
     * @return string HTML for community selector drop down
     */
    public function getFilters()
    {
        javascript('jquery');
        javascript('modules/hms/page_refresh');

        // Get the list of communities
        $communities = RlcFactory::getRlcList($this->term);

        $communityList = array('0'=>'All');

        foreach($communities as $key=>$val){
            $communityList[$key] = $val;
        }

        // Initialize form and submit command
        $submitCmd = CommandFactory::getCommand('ShowAssignRlcApplicants');
        $form = new PHPWS_Form('dropdown_selector');
        $submitCmd->initForm($form);
        $form->setMethod('get');
        
        
        // Community drop down
        $form->addSelect('rlc', $communityList);
        if (isset($this->rlc) && !is_null($this->rlc)) {
            $form->setMatch('rlc', $this->rlc->getId());
        }
        $form->setExtra('rlc', 'onChange="refresh_page(\'dropdown_selector\')"');

        
        // Student Type drop down
        $form->addSelect('student_type', array(0 => 'All', TYPE_CONTINUING => 'Continuing', TYPE_FRESHMEN => 'Freshmen'));
        if (isset($this->studentType)) {
            $form->setMatch('student_type', $this->studentType);
        }
        $form->setExtra('student_type', 'onChange="refresh_page(\'dropdown_selector\')"');

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/rlcApplicationListFilters.tpl');
    }

    /**
     * RLC Application pager for the RLC admin panel
     * 
     * @return string HTML for application pager
     */
    public function rlcApplicationPager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $submitCmd = CommandFactory::getCommand('AssignRlcApplicants');
        $form = new PHPWS_Form;
        $submitCmd->initForm($form);
        $form->addSubmit('Submit Changes');
        $tags = $form->getTemplate();

        $pager = new DBPager('hms_learning_community_applications','HMS_RLC_Application');
        $pager->db->addColumn('hms_learning_community_applications.*');

        $pager->db->addJoin('LEFT OUTER', 'hms_learning_community_applications', 'hms_learning_community_assignment', 'id', 'application_id');
        $pager->db->addWhere('hms_learning_community_assignment.application_id', 'NULL', '=');
        $pager->db->addWhere('term', $this->term);
        $pager->db->addWhere('denied', 0); // Only show non-denied applications in this pager


        // If community filter is set, use it
        if(isset($this->rlc)){
            $pager->db->addWhere('hms_learning_community_applications.rlc_first_choice_id', $this->rlc->getId() ,'=');
        }
        
        // If student type filter is set, use it
        if(isset($this->studentType)){
            if ($this->studentType == TYPE_FRESHMEN) {
                $pager->db->addWhere('hms_learning_community_applications.application_type', 'freshmen');
            } else if ($this->studentType == TYPE_CONTINUING) {
                // TODO fix this so 'returning' is consistent with 'continuing'.. really just use student types
                $pager->db->addWhere('hms_learning_community_applications.application_type', 'returning');
            }
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
