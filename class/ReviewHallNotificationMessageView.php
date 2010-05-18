<?php
/**
 * ReviewHallNotificationMessageView
 *
 *  Creates the view for reviewing hall notifications.
 *
 * @author Daniel West <lw77517 at appstate dot edu>
 * @package mod
 * @subpackage hms
 */
PHPWS_Core::initModClass('hms', 'View.php');

class ReviewHallNotificationMessageView extends View {
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
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        $tpl = array();
        if(is_array($this->halls)){
            foreach($this->halls as $hall){
                $_hall = new HMS_Residence_Hall($hall);
                $tpl['halls'][] = array('HALL'=>$_hall->hall_name);
            }
        } else {
            $hall = new HMS_Residence_Hall($this->halls);
            $tpl['halls'][] = array('HALL'=>$hall->hall_name);
        }
        
        $tpl['FROM']    = ($this->anonymous && Current_User::allow('hms', 'anonymous_notification')) ? FROM_ADDRESS : Current_User::getEmail();
        $tpl['SUBJECT'] = $this->subject;
        $tpl['BODY']    = preg_replace('/\n/', '<br />', $this->body);

        $form = new PHPWS_Form('edit_email');

        $editCmd = CommandFactory::getCommand('ShowHallNotificationEdit');
        $editCmd->initForm($form);

        $form->addHidden('anonymous',   isset($this->anonymous) ? $this->anonymous : '');
        $form->addHidden('subject',     $this->subject);
        $form->addHidden('body',        $this->body);
        $form->addHidden('hall',        $this->halls);
        $form->addSubmit('back',        'Edit Message');
        $tpl['BACK'] = implode('', $form->getTemplate());

        $form2 = new PHPWS_Form('review_email');

        $sendCmd = CommandFactory::getCommand('SendNotificationEmails');
        $sendCmd->initForm($form2);

        $form2->addHidden('anonymous',  isset($this->anonymous) ? $this->anonymous : '');
        $form2->addHidden('subject',    $this->subject);
        $form2->addHidden('body',       $this->body);
        $form2->addHidden('hall',       $this->halls);
        $form2->addSubmit('Send Emails');
        $tpl['SUBMIT'] = implode('', $form2->getTemplate());

        Layout::addPageTitle("Review Hall Email");

        return PHPWS_Template::process($tpl, 'hms', 'admin/review_hall_email.tpl');
    }
}
?>
