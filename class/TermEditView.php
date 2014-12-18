<?php

PHPWS_Core::initModClass('hms', 'View.php');

class TermEditView extends homestead\View {
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

        $term = Term::getSelectedTerm();
        $printable = Term::getPrintableSelectedTerm();

        $tpl = array();
        $tpl['TITLE'] = dgettext('hms', 'Term settings for ') . $printable;

        // Is this the Current Term?
        $tpl['CURRENT_TERM_LEGEND'] = dgettext('hms', 'Current Term');
        if(Term::isCurrentTermSelected()) {
            $tpl['CURRENT_TERM_TEXT'] = dgettext('hms',
                    'This term is the <strong>active term</strong>.  To make another term active, please select it from the list at the top-left.');
        } else {
            $tpl['CURRENT_TERM_TEXT'] = dgettext('hms',
                    'This term is <strong>not</strong> the active term.');
             
            if(Current_User::allow('hms', 'activate_term')) {
                $cmd = CommandFactory::getCommand('SetCurrentTerm');
                $cmd->setTerm(Term::getSelectedTerm());
                $tpl['CURRENT_TERM_LINK'] = sprintf(
                        $cmd->getLink('Make <strong>%s</strong> the Current Term.'), $printable);
            }
        }

        // What's with the Banner Queue?
        $tpl['BANNER_QUEUE_LEGEND'] = dgettext('hms', 'Banner Queue');
        $qtext = dgettext('hms', 'The Banner Queue for this term is <strong>%s</strong>.');
        $qcount = dgettext('hms', 'There are %d items currently queued for reporting to Banner.');
        $term = new Term(Term::getSelectedTerm());
        if($term->getBannerQueue()) {
            $tpl['BANNER_QUEUE_TEXT'] = sprintf($qtext, dgettext('hms', 'enabled'));
            $count = $term->getQueueCount();
            $tpl['BANNER_QUEUE_COUNT'] = sprintf($qcount, $count);
            if($count > 0) {
                $cmd = CommandFactory::getCommand('ProcessBannerQueue');
                $cmd->setTerm(Term::getSelectedTerm());
                $tpl['BANNER_QUEUE_PROCESS'] = $cmd->getLink('Process and Disable');
            } else {
                $cmd = CommandFactory::getCommand('DisableBannerQueue');
                $cmd->setTerm(Term::getSelectedTerm());
                $tpl['BANNER_QUEUE_LINK'] = $cmd->getLink('Disable');
            }
        } else {
            $tpl['BANNER_QUEUE_TEXT'] = sprintf($qtext, dgettext('hms', 'disabled'));
            $cmd = CommandFactory::getCommand('EnableBannerQueue');
            $cmd->setTerm(Term::getSelectedTerm());
            $tpl['BANNER_QUEUE_LINK'] = $cmd->getLink('Enable');
        }

        // Terms and Conditions
        $tpl['TERMS_CONDITIONS_LEGEND'] = dgettext('hms', 'Terms and Conditions');
        PHPWS_Core::initModClass('hms', 'TermsConditionsAdminView.php');
        $tcav = new TermsConditionsAdminView($this->term);
        $tpl['TERMS_CONDITIONS_CONTENT'] = $tcav->show();
        
        // Docusign
        $docusignForm = new PHPWS_Form('docusign');
        $docusignForm->addText('docusign_template_id');
        $docusignForm->setLabel('docusign_template_id', 'Template ID');
        
        // Features and Deadlines
        $tpl['FEATURES_DEADLINES_LEGEND'] = dgettext('hms', 'Important Dates and Deadlines');
        PHPWS_Core::initModClass('hms', 'ApplicationFeatureListView.php');
        $aflv = new ApplicationFeatureListView(Term::getSelectedTerm());
        $tpl['FEATURES_DEADLINES_CONTENT'] = $aflv->show();

        Layout::addPageTitle("Term Settings");

        return PHPWS_Template::process($tpl, 'hms', 'admin/TermEditView.tpl');
    }
}
?>