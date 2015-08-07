<?php
/**
 * ShowHallNotificationSelectView
 *
 *  Creates the interface for showing hall selection for notification.
 *
 * @author Daniel West <lw77517 at appstate dot edu>
 * @package mod
 * @subpackage hms
 */

class ShowHallNotificationSelectView extends hms\View{

    public function show(){
        /*
        if(!Current_User::allow('hms', 'email_hall')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to message halls.');
        }
        */
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $submitCmd = CommandFactory::getCommand('ShowHallNotificationEdit');
        $form = new PHPWS_Form('select_halls_to_email');
        $submitCmd->initForm($form);

        javascript('jquery_ui');

        $cmd = CommandFactory::getCommand('ShowHallNotificationEdit');
        $form = new PHPWS_Form("select_halls");
        $cmd->initForm($form);
        $form->addSubmit('submit', 'Submit');
        $form->setExtra('submit', 'onclick="submitHallList();"');
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Hall Notification Select");

        return PHPWS_Template::process($tpl, 'hms', 'admin/messages.tpl').Layout::getJavascript("modules/hms/hall_expander", array("DIV"=>"hall_list", "FORM"=>"select_halls"));
    }
}
