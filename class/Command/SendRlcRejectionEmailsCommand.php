<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\HMS_RLC_Application;
use \Homestead\HMS_Email;
use \Homestead\UserStatus;
use \Homestead\NotificationView;
use \Homestead\StudentFactory;
use \Homestead\Exception\PermissionException;

  /**
   * This command will notify all rejected students that their
   * RLC Application has been denied.
   *
   * @author Robert Bost <bostrt at tux dot appstate dot edu>
   */

class SendRlcRejectionEmailsCommand extends Command
{

    private $application;

    public function getRequestVars()
    {
        return array('action' => 'SendRlcRejectionEmails');
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'email_rlc_rejections')){
            throw new PermissionException('You do not have permission to send RLC rejections.');
        }

        $term = Term::getSelectedTerm();
        $deniedApps = HMS_RLC_Application::getNonNotifiedDeniedApplicantsByTerm($term);

        $email = new HMS_Email();

        foreach($deniedApps as $app)
        {
            $student = StudentFactory::getStudentByUsername($app['username'], $term);
            $email->sendRlcApplicationRejected($student, $term);
            $application = HMS_RLC_Application::getApplicationById($app['id']);
            $application->setDeniedEmailSent(1);
            $application->save();
        }

        \NQ::Simple('hms', NotificationView::SUCCESS, 'RLC rejection emails sent.');
        $context->goBack();
    }

    public function setApplication($app)
    {
        $this->application = $app;
    }

}
