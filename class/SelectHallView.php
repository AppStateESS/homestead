<?php

namespace Homestead;

/**
 * @author jbooker
 * @package hms
 */
class SelectHallView extends View{

    private $title;
    private $term;
    private $onSelectCmd;
    private $halls;

    public function __construct(Command $onSelectCmd, $halls, $title, $term)
    {
        $this->onSelectCmd	= $onSelectCmd;
        $this->title		= $title;
        $this->term			= $term;
        $this->halls		= $halls;
    }

    public function show()
    {
        $tpl = array();

        $tpl['TITLE']   = $this->title;
        $tpl['TERM']    = Term::getPrintableSelectedTerm();

        if($this->halls == NULL){
            NQ::simple('hms', hms\NotificationView::ERROR, 'There are no halls available for the selected term.');
            $cmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
            $cmd->redirect();
        }

        PHPWS_Core::initCoreClass('Form.php');
        $form = new PHPWS_Form();
        $this->onSelectCmd->initForm($form);

        $form->setMethod('get');
        $form->addDropBox('hallId', $this->halls);
        $form->addCssClass('hallId', 'form-control');

        $form->addSubmit('submit', _('Select Hall'));

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Select Hall");

        return PHPWS_Template::process($tpl, 'hms', 'admin/select_residence_hall.tpl');
    }
}
