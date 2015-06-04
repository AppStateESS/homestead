
<?php

class GetAssignmentTypeDropboxCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $options = array(
                ASSIGN_ADMIN             => 'Administrative',
                ASSIGN_LOTTERY	         => 'Lottery',
                ASSIGN_FR   	         => 'Freshmen',
                ASSIGN_TRANSFER          => 'Transfer',
                ASSIGN_APH               => 'APH',
                ASSIGN_RLC_FRESHMEN      => 'RLC Freshmen',
                ASSIGN_RLC_TRANSFER      => 'RLC Transfer',
                ASSIGN_RLC_CONTINUING    => 'RLC Continuing',
                ASSIGN_HONORS_FRESHMEN   => 'Honors Freshmen',
                ASSIGN_HONORS_CONTINUING => 'Honors Continuing',
                ASSIGN_LLC_FRESHMEN      => 'LLC Freshmen',
                ASSIGN_LLC_CONTINUING    => 'LLC Continuing',
                ASSIGN_INTL              => 'International',
                ASSIGN_RA                => 'RA',
                ASSIGN_RA_ROOMMATE       => 'RA Roommate',
                ASSIGN_MEDICAL           => 'Medical',
                ASSIGN_SPECIAL           => 'Special Needs',
                ASSIGN_RHA               => 'RHA/NRHH',
                ASSIGN_SCHOLARS          => 'Diversity &amp; Plemmons Scholars');
        $form = new PHPWS_Form('select_assignment');
        $form->addSelect('type', $options);
        $form->setMatch('type', ASSIGN_ADMIN);
        $form->setClass('type', 'form-control');
        $template = $form->getTemplate();
        echo \PHPWS_Template::process($template, 'hms', 'admin/assignment_type_dropbox.tpl');
        exit();
    }
}

