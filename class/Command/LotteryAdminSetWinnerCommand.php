<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\StudentFactory;
use \Homestead\CommandFactory;
use \Homestead\HousingApplicationFactory;
use \Homestead\Exception\PermissionException;
use \Homestead\NotificationView;
use \Homestead\Exception\StudentNotFoundException;

class LotteryAdminSetWinnerCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'LotteryAdminSetWinner');
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'lottery_admin')){
            throw new PermissionException('You do not have permission to administer re-application features.');
        }

        $bannerIds = $context->get('banner_ids');
        $term = Term::getSelectedTerm();

        $bannerIds = explode("\n", $bannerIds);

        foreach($bannerIds as $bannerId) {

            // Trim any excess whitespace
            $bannerId = trim($bannerId);

            // Skip blank lines
            if($bannerId == '') {
            	continue;
            }

        	$student = StudentFactory::getStudentByBannerId($bannerId, $term);

            try{
                $application = HousingApplicationFactory::getAppByStudent($student, $term);
            }catch(StudentNotFoundException $e){
                \NQ::simple('hms', NotificationView::ERROR, "No matching student was found for: {$bannerId}");
                continue;
            }

            if(is_null($application)){
                \NQ::simple('hms', NotificationView::ERROR, "No housing application for: {$bannerId}");
                continue;
            }

            $application->magic_winner = 1;

            try{
                $application->save();
            }catch(\Exception $e){
                \NQ::simple('hms', NotificationView::ERROR, "Error setting flag for: {$bannerId}");
                continue;
            }

            \NQ::simple('hms', NotificationView::SUCCESS, "Magic flag set for: {$bannerId}");
        }

        $viewCmd = CommandFactory::getCommand('ShowLotteryAutoWinners');
        $viewCmd->redirect();
    }
}
