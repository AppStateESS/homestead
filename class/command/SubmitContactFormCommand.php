<?php

PHPWS_Core::initModClass('hms', 'HMS_Email.php');
PHPWS_Core::initModClass('hms', 'ContactFormThankYouView.php');

class SubmitContactFormCommand extends Command {
    private $asu_username;
    private $application_term;
    private $student_type;

    public function setUsername($username){
        $this->asu_username = $username;
    }

    public function setApplicationTerm($appTerm){
        $this->application_term = $appTerm;
    }

    public function setStudentType($type){
        $this->student_type = $type;
    }

    public function getRequestVars()
    {
        return array('action'           => 'SubmitContactForm',
                     'asu_username'     => $this->asu_username,
                     'application_term' => $this->application_term,
                     'student_type'     => $this->student_type);
    }

    public function execute(CommandContext $context)
    {
        $send_to = array();
        $send_to[] = 'brownbw@appstate.edu';
        $send_to[] = 'ticklejw@appstate.edu';
        $send_to[] = 'jb67803@appstate.edu';
        $send_to[] = 'braswelldl@appstate.edu';
        $send_to[] = 'burlesonst@appstate.edu';

        $from    = 'uha@appstate.edu';
        $subject = 'HMS Contact Form';

        $body  = "Username: ".$context->get('asu_username')."\n";
        $body .= "Application date: ".$context->get('application_term')."\n";
        $body .= "Student Type: ".$context->get('student_type')."\n";

        $body .= "\n\nInput from student:\n\n";
        $body .= "Name: ".$context->get('name')."\n";
        $body .= "Email: ".$context->get('email')."\n";
        $body .= "Phone #: ".$context->get('phone')."\n";
        $body .= "Type: ".$context->get('stype')."\n";
        $body .= "Text field:\n";
        $body .= "".$context->get('comments')."\n\n";

        if( !HMS_Email::send_email($send_to, $from, $subject, $body) ){
            //Maybe we shouldn't say anything...
            //NQ::simple('hms', hms\NotificationView::ERROR, 'Error sending email!');
        }

        $view = new ContactFormThankYouView();

        $context->setContent($view->show());
    }
}
?>
