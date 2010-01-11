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

PHPWS_Core::initModClass('hms', 'View.php');

class ShowHallNotificationEditView extends View {
    private $subject;
    private $body;
    private $anonymous;
    private $halls;

    public function __construct($subject=null, $body=null, $anonymous=false, $halls=array()){
        $this->subject   = $subject;
        $this->body      = $body;
        $this->anonymous = $anonymous;
        $this->halls     = $halls;
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

        $form->addSubmit('Submit');

        //After you ask "wtf?", check the third parameter on preg_replace (only removes the first two occurances)
        $tpl['EMAIL'] = preg_replace('/<br \/>/', '', implode('<br />', $form->getTemplate()), 2);

        return PHPWS_Template::process($tpl, 'hms', 'admin/hall_notification_email_page.tpl');
    }
}
?>
