<?php

class LotteryAdminCreateAppCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'LotteryAdminCreateApp');
    }

    public function execute(CommandContext $context)
    {

        if(!Current_User::allow('hms', 'lottery_admin')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to administer re-application features.');
        }

        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'MealPlan.php');

        $viewCmd = CommandFactory::getCommand('ShowLotteryAdminEntry');

        //TODO: use the lottery term setting here instead?
        $term = Term::getSelectedTerm();

        $username = $context->get('asu_username');
        if(!isset($username) || empty($username)){
            NQ::simple('hms', hms\NotificationView::ERROR, 'You must enter a valid user name.');
            $viewCmd->redirect();
        }

        try{
            $student = StudentFactory::getStudentByUsername($context->get('asu_username'), $term);
        }catch(StudentNotFoundException $e){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Inavlid user name. No student with that user name could be found.');
            $viewCmd->redirect();
        }

        $application = new LotteryApplication(0, $term, $student->getBannerId(), $student->getUsername(), $student->getGender(), $student->getType(), $student->getApplicationTerm(), null, MealPlan::BANNER_MEAL_STD, $physicalDisability, $psychDisability, $genderNeed, $medicalNeed, 0, NULL, 0, NULL, 0, 0, 0, 0);

        try{
            $application->save();
        }catch(Exception $e){
            NQ::simple('hms', hms\NotificationView::ERROR,'There was a problem saving the application.');
            $viewCmd->redirect();
        }

        NQ::simple('hms', hms\NotificationView::SUCCESS, 'The lottery application was created successfully.');
        $viewCmd->redirect();
    }
}
