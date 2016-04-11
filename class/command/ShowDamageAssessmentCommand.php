<?php

class ShowDamageAssessmentCommand extends Command {


    public function getRequestVars()
    {
        return array('action' => 'ShowDamageAssessment');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'damage_assessment')){
            throw new PermissionException('You do not have permission to perform room damage assessment.');
        }

        PHPWS_Core::initModClass('hms', 'RoomDamageAssessmentView.php');

        $view = new RoomDamageAssessmentView();

        $context->setContent($view->show());
    }
}
