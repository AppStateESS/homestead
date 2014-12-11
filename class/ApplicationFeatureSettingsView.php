<?php

PHPWS_Core::initModClass('hms', 'View.php');

class ApplicationFeatureSettingsView extends homestead\View
{
    private $feature;

    public function __construct(ApplicationFeature $feature)
    {
        $this->feature = $feature;
    }

    public function show()
    {
        $f = $this->feature;
        $reg = $f->getRegistration();

        PHPWS_Core::initCoreClass('Form.php');
        $form = new PHPWS_Form($reg->getName());

        $cmd = CommandFactory::getCommand('SaveApplicationFeature');
        if($f->getId() < 1) {
            $cmd->setName($reg->getName());
            $cmd->setTerm($f->getTerm());
        } else {
            $cmd->setFeatureId($f->getId());
        }
        $cmd->initForm($form);

        // TODO: Command Business
        $form->addCheck('enabled');
        if($f->isEnabled())
            $form->setMatch('enabled', true);
        $form->setLabel('enabled', $reg->getDescription());

        if($reg->requiresStartDate()) {
            $form->addText('start_date');
            $form->setExtra('start_date', 'class="datepicker"');
            if(!is_null($f->getStartDate())) {
                $form->setValue('start_date', strftime('%m/%d/%Y', $f->getStartDate()));
            }
            $form->setLabel('start_date', dgettext('hms', 'Start Date:'));
        }

        if($reg->requiresEditDate()) {
            $form->addText('edit_date');
            $form->setExtra('edit_date', 'class="datepicker"');
            if(!is_null($f->getEditDate())) {
                $form->setValue('edit_date', strftime('%m/%d/%Y', $f->getEditDate()));
            }
            $form->setLabel('edit_date', dgettext('hms', 'Edit Date:'));
        }

        if($reg->requiresEndDate()) {
            $form->addText('end_date');
            $form->setExtra('end_date', 'class="datepicker"');
            if(!is_null($f->getEndDate())) {
                $form->setValue('end_date', strftime('%m/%d/%Y', $f->getEndDate()));
            }
            $form->setLabel('end_date', dgettext('hms', 'End Date:'));
        }

        $form->addSubmit('Save');
        $form->addReset('Undo');

        javascript('datepicker');

        $vars = array('FORM_SELECT'   => '.app-feature-setting form',
                'ENABLE_SELECT' => 'input[name="enabled"]',
                'HIDDEN_SELECT' => '.app-feature-setting-hidable',
                'SUBMIT_SELECT' => '.app-feature-setting-submit');
        javascript('modules/hms/ajaxForm', $vars);

        $tpl = $form->getTemplate();
        return PHPWS_Template::process($tpl, 'hms', 'admin/ApplicationFeatureSettingsView.tpl');
    }
}
