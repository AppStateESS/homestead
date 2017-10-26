<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\CommandFactory;
use \Homestead\RlcAssignmentFactory;
use \Homestead\RlcAssignmentInvitedState;
use \Homestead\NotificationView;

/**
 * SendRlcInvitesComands
 *
 * Transitions all new rlc assignments to the 'Invited' state.
 *
 * @author jbooker
 * @package HMS
 */
class SendRlcInvitesCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'SendRlcInvites');
    }

    public function execute(CommandContext $context)
    {
        $resultCmd = CommandFactory::getCommand('ShowSendRlcInvites');

        $respondByDate = $context->get('respond_by_date');
        $respondByTime = $context->get('time');

        if(!isset($respondByDate) || $respondByDate == ''){
            \NQ::simple('hms', NotificationView::ERROR, 'Please choose a \'respond by\' date.');
            $resultCmd->redirect();
        }

        $dateParts = explode('/', $respondByDate);
        $respondByTimestamp = mktime($respondByTime, null, null, $dateParts[0], $dateParts[1], $dateParts[2]);

        $term = Term::getSelectedTerm();

        $studentType = $context->get('type');

        if(!isset($studentType)){
            \NQ::simple('hms', NotificationView::ERROR, 'Please choose a student type.');
            $resultCmd->redirect();
        }

        $assignments = RlcAssignmentFactory::getAssignmentsByTermStateType($term, 'new', $studentType);

        if(sizeof($assignments) == 0){
            \NQ::simple('hms', NotificationView::WARNING, 'No invites needed to be sent.');
            $resultCmd->redirect();
        }

        foreach($assignments as $assign){
            $assign->changeState(new RlcAssignmentInvitedState($assign, $respondByTimestamp));
        }

        \NQ::simple('hms', NotificationView::SUCCESS, 'Learning community invites sent.');
        $resultCmd->redirect();
    }
}
