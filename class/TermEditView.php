<?php

namespace Homestead;

/**
 * TermEditView - View class for editing terms
 *
 * @package Homestead
 * @author jbooker
 */
class TermEditView extends View {

    private $term;

    public function __construct($term) {
        $this->term = $term;
    }

    public function show()
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'edit_terms')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit terms.');
        }

        $printable = Term::getPrintableSelectedTerm();

        $tpl = array();
        $tpl['TITLE'] = dgettext('hms', 'Term settings for ') . $printable;

        $newTermCmd = CommandFactory::getCommand('ShowCreateTerm');
        $tpl['NEW_TERM_URI'] = $newTermCmd->getURI();

        // Is this the Current Term?
        if(Term::isCurrentTermSelected()) {
            $tpl['CURRENT_TERM_TEXT'] = dgettext('hms',
                    'This term is the <strong>active term</strong>.  To make another term active, please select it from the list at the top-left.');
        } else {
            $tpl['CURRENT_TERM_TEXT'] = dgettext('hms',
                    'This term is <strong>not</strong> the active term.');

            if(Current_User::allow('hms', 'activate_term')) {
                $cmd = CommandFactory::getCommand('SetCurrentTerm');
                $cmd->setTerm(Term::getSelectedTerm());
                $tpl['SET_TERM_URI'] = $cmd->getURI();
                $tpl['SET_TERM_TEXT'] = "Make <strong>$printable</strong> the Current Term";
            }
        }

        // What's with the Banner Queue?
        $term = new Term(Term::getSelectedTerm());
        if($term->getBannerQueue()) {
            $tpl['QUEUE_ENABLED'] = '';
            $count = $term->getQueueCount();
            $tpl['BANNER_QUEUE_COUNT'] = $count;
            if($count > 0) {
                $cmd = CommandFactory::getCommand('ProcessBannerQueue');
                $cmd->setTerm(Term::getSelectedTerm());
                $tpl['BANNER_QUEUE_PROCESS_URI'] = $cmd->getURI();
            } else {
                $cmd = CommandFactory::getCommand('DisableBannerQueue');
                $cmd->setTerm(Term::getSelectedTerm());
                $tpl['BANNER_QUEUE_LINK'] = $cmd->getLink('Disable');
            }
        } else {
            $tpl['QUEUE_DISABLED'] = '';
            $cmd = CommandFactory::getCommand('EnableBannerQueue');
            $cmd->setTerm(Term::getSelectedTerm());
            $tpl['BANNER_QUEUE_LINK'] = $cmd->getLink('Enable');
        }

        if($term->getMealPlanQueue()){
            $tpl['MEAL_PLAN_QUEUE_ENABLED'] = '';

            $count = MealPlanFactory::getQueueSize($term->getTerm());

            $tpl['MEAL_PLAN_QUEUE_SIZE'] = $count;

            // Show process & disable button
            $processMealCmd = CommandFactory::getCommand('ProcessMealPlanQueue');
            $tpl['PROCESS_MEAL_URI'] = $processMealCmd->getUri();
        } else {
            // Queue is disabled
            $tpl['MEAL_PLAN_QUEUE_DISABLED'] = '';

            // Show enable button
            $enableCmd = CommandFactory::getCommand('EnableMealPlanQueue');
            $tpl['MEAL_PLAN_ENABLE_URI'] = $enableCmd->getUri();
        }

        // Terms and Conditions
        PHPWS_Core::initModClass('hms', 'TermsConditionsAdminView.php');
        $tcav = new TermsConditionsAdminView($this->term);
        $tpl['TERMS_CONDITIONS_CONTENT'] = $tcav->show();

        // Features and Deadlines
        PHPWS_Core::initModClass('hms', 'ApplicationFeatureListView.php');
        $aflv = new ApplicationFeatureListView(Term::getSelectedTerm());
        $tpl['FEATURES_DEADLINES_CONTENT'] = $aflv->show();

        Layout::addPageTitle("Term Settings");

        return \PHPWS_Template::process($tpl, 'hms', 'admin/TermEditView.tpl');
    }
}
