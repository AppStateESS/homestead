<?php

namespace Homestead\command;

use \Homestead\Command;

class ShowRlcReapplicationPageTwoCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars()
    {
        $reqVars = array();

        $reqVars['action'] = 'ShowRlcReapplicationPageTwo';
        $reqVars['term'] = $this->term;

        return $reqVars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms', 'RlcReapplicationPageTwoView.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

        session_write_close();
        session_start();

        if(!isset($_SESSION['RLC_REAPP'])){
            $errorCmd = CommandFactory::getCommand('ShowStudentMenu');
            $errorCmd->redirect();
        }

        $reApp = $_SESSION['RLC_REAPP'];

        $rlcs = array(new HMS_Learning_Community($reApp->rlc_first_choice_id));

        if(isset($reApp->rlc_second_choice_id) && !is_null($reApp->rlc_second_choice_id)){
            $rlcs[] = new HMS_Learning_Community($reApp->rlc_second_choice_id);
        }

        if(isset($reApp->rlc_third_choice_id) && !is_null($reApp->rlc_third_choice_id)){
            $rlcs[] = new HMS_Learning_Community($reApp->rlc_third_choice_id);
        }

        $view = new RlcReapplicationPageTwoView($rlcs, $context->get('term'), $reApp);

        $context->setContent($view->show());
    }
}
