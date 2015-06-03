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

class ShowHallNotificationEditView extends hms\View{

    private $subject;
    private $body;
    private $anonymous;
    private $halls;
    private $floors;

    public function __construct($subject=null, $body=null, $anonymous=false, $halls=array(), $floors=array()){
        $this->subject   = $subject;
        $this->body      = $body;
        $this->anonymous = $anonymous;
        $this->halls     = $halls;
        $this->floors    = $floors;
    }

    public function show(){
        $tpl = array();

        $tpl['HEADER'] = 'Email';

        $submitCmd = CommandFactory::getCommand('ReviewHallNotificationMessage');
        $form = new PHPWS_Form('email_content');
        $submitCmd->initForm($form);

        if(Current_User::allow('hms', 'anonymous_notifications')){
            $form->addCheck('anonymous');
            $form->setMatch('anonymous', $this->anonymous);
            $form->setLabel('anonymous', 'Send Anonymously: ');
        }

        $form->addText('subject', (!is_null($this->subject) ? $this->subject : ''));
        $form->setLabel('subject', 'Subject:');
        $form->setSize('subject', 35);

        $form->addTextarea('body', (!is_null($this->body) ? $this->body : ''));
        $form->setLabel('body', 'Message:');

        if(!empty($this->halls)){
            $form->addHidden('hall', $this->halls);
        }

        if(!empty($this->floors)){
            $form->addHidden('floor', $this->floors);
        }

        //javascript('modules/hms/autoFocus', array('ELEMENT'=>$form->getId('subject')));
        $form->setExtra('subject', 'autoFocus');
        $form->addSubmit('Submit');

        //After you ask "wtf?", check the last parameter on preg_replace (only removes the first two occurances)
        $tpl['EMAIL'] = preg_replace('/<br \/>/', '', implode('<br />', $form->getTemplate()), 2);

        Layout::addPageTitle("Hall Notification Edit");

        return PHPWS_Template::process($tpl, 'hms', 'admin/hall_notification_email_page.tpl');
    }
}
?>
