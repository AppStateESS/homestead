<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'RoomChangeView.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');

class StudentRoomChangeCommand extends Command {

    protected $requestId = NULL;

    public function getRequestVars(){
        $vars = array('action'=>'StudentRoomChange');
        if(isset($this->requestId))
            $vars['request_id'] = $this->requestId;

        return $vars;
    }

    public function execute(CommandContext $context){
        //If the student has a pending request load it from the db
        $rc = new RoomChangeRequest;
        $rc = $rc->search(UserStatus::getUsername());

        if(!is_null($rc) && $rc->state instanceof CompletedChangeRequest){
            //NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your previous room change has completed, fill out this form to begin another.');
            $rc = NULL;
        } elseif(!is_null($rc) && $rc->state instanceof DeniedChangeRequest){
            //NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your previous room change was denied, fill out this form to begin another.');
            $rc = NULL;
        }

        $view = new RoomChangeView($this, $rc);

        $context->setContent($view->show());
    }

    public function getId(){
        return $this->requestId;
    }
}
?>