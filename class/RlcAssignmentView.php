<?php

namespace Homestead;

/**
 * RlcAssignmentView - View class for assigning students to LearningCommunitites.
 *
 * @author Jeremy Booker
 * @package hms
 */
class RlcAssignmentView extends View
{
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
        $this->term = $term;
        $this->rlc = $rlc;
        $this->studentType = $studentType;
    }

    /**
     * @see View::show()
     */
    public function show()
    {
        $tags = array();
        $tags['TERM'] = Term::toString(Term::getSelectedTerm());
        $tags['FILTERS'] = $this->getFilters();
        $tags['ASSIGNMENTS_PAGER'] = $this->rlcApplicationPager();

        $exportForm = new \PHPWS_Form('export_form');
        $exportCmd = CommandFactory::getCommand('ExportRlcApps');
        $exportCmd->initForm($exportForm);

        $exportForm->addDropBox('rlc_list', HMS_Learning_Community::getRlcList());
        $exportForm->setClass('rlc_list', 'form-control');
        $exportForm->addSubmit('submit', 'Export');
        $exportForm->setClass('submit', 'btn btn-primary');

        $exportForm->mergeTemplate($tags);
        $tags = $exportForm->getTemplate();

        return \PHPWS_Template::process($tags, 'hms', 'admin/make_new_rlc_assignments.tpl');
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

        $communityList = array('0' => 'All');

        foreach ($communities as $key => $val) {
            $communityList[$key] = $val;
        }

        // Initialize form and submit command
        $submitCmd = CommandFactory::getCommand('ShowAssignRlcApplicants');
        $form = new \PHPWS_Form('dropdown_selector');
        $submitCmd->initForm($form);
        $form->setMethod('get');


        // Community drop down
        $form->addSelect('rlc', $communityList);
        if (isset($this->rlc) && !is_null($this->rlc)) {
            $form->setMatch('rlc', $this->rlc->getId());
        }
        $form->setClass('rlc', 'form-control');
        $form->setExtra('rlc', 'onChange="refresh_page(\'dropdown_selector\')"');


        // Student Type drop down
        $form->addSelect('student_type', array(0 => 'All', TYPE_CONTINUING => 'Continuing', TYPE_FRESHMEN => 'Freshmen'));
        if (isset($this->studentType)) {
            $form->setMatch('student_type', $this->studentType);
        }
        $form->setClass('student_type', 'form-control');
        $form->setExtra('student_type', 'onChange="refresh_page(\'dropdown_selector\')"');

        return \PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/rlcApplicationListFilters.tpl');
    }

    /**
     * RLC Application pager for the RLC admin panel
     *
     * @return string HTML for application pager
     */
    public function rlcApplicationPager()
    {
        \PHPWS_Core::initCoreClass('DBPager.php');

        $submitCmd = CommandFactory::getCommand('AssignRlcApplicants');
        $form = new \PHPWS_Form;
        $submitCmd->initForm($form);
        $form->addSubmit('submit', 'Submit Changes');
        $form->setClass('submit', 'btn btn-primary');
        $tags = $form->getTemplate();

        $pager = new DBPager('hms_learning_community_applications', 'HMS_RLC_Application');
        $pager->db->addColumn('hms_learning_community_applications.*');

        $pager->db->addJoin('LEFT OUTER', 'hms_learning_community_applications', 'hms_learning_community_assignment', 'id', 'application_id');
        $pager->db->addWhere('hms_learning_community_assignment.application_id', 'NULL', '=');
        $pager->db->addWhere('term', $this->term);
        $pager->db->addWhere('denied', 0); // Only show non-denied applications in this pager
        // If community filter is set, use it
        if (isset($this->rlc)) {
            $pager->db->addWhere('hms_learning_community_applications.rlc_first_choice_id', $this->rlc->getId(), '=');
        }

        // If student type filter is set, use it
        if (isset($this->studentType)) {
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
        $pager->addPageTags($tags);
        $pager->addRowTags('getAdminPagerTags');
        $pager->setReportRow('applicantsReport');

        \Layout::addPageTitle("RLC Assignments");

        return $pager->get();
    }

}
