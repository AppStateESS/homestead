<?php

class SelectFloorView extends hms\View
{
    private $title;
    private $term;
    private $onSelectCmd;
    private $halls;

    public function __construct(Command $onSelectCmd, $halls, $title, $term)
    {
        $this->onSelectCmd = $onSelectCmd;
        $this->title = $title;
        $this->term = $term;
        $this->halls = $halls;
    }

    public function show()
    {
        $tpl = array();

        if ($this->halls == NULL) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'There are no halls available for the selected term.');
            $cmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
            $cmd->redirect();
        }

        $tpl['TITLE']   = $this->title;
        $tpl['TERM']    = Term::getPrintableSelectedTerm();

        javascript('jquery');
        javascript('modules/hms/select_floor');

        # Setup the form
        $form = new PHPWS_Form();
        $this->onSelectCmd->initForm($form);

        $form->setMethod('get');
        $form->addDropBox('residence_hall', $this->halls);
        $form->setLabel('residence_hall', 'Residence hall');
        $form->setMatch('residence_hall', 0);
        $form->setClass('residence_hall', 'form-control');

        $form->addDropBox('floor', array(0 => ''));
        $form->setLabel('floor', 'Floor');
        $form->setClass('floor', 'form-control');

        $form->addSubmit('submit_button', 'Select');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Select Floor");

        return PHPWS_Template::process($tpl, 'hms', 'admin/select_floor.tpl');
    }

}
