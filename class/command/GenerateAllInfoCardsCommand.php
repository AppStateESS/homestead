<?php

/**
 * Controller/command for generating the entire set of RIC forms for a semester.
 *
 * @author jbooker
 * @package hms
 * @see GenerateInfoCardCommand
 */
class GenerateAllInfoCardsCommand extends Command {

    public function getRequestVars()
    {
        return array(
                'action' => 'GenerateAllInfoCards'
        );
    }

    public function execute(CommandContext $context)
    {
        $term = Term::getSelectedTerm();

        $checkins = CheckinFactory::getCheckinsOrderedByRoom($term);

        if (!isset($checkins) || count($checkins) <= 0) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'No check-ins were found for the selected term.');
            $cmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
            $cmd->redirect();
        }

        $view = new InfoCardPdfView();

        foreach ($checkins as $checkin) {
            $infoCard = new InfoCard($checkin);

            $view->addInfoCard($infoCard);
        }

        $view->getPdf()->output();
        exit();
    }
}
