<?php

PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');

class SaveRlcCommand extends Command {

    public function getRequestVars(){
        $vars = array();

        $vars['action']         = 'ShowAddRlc';
        $vars['community_name'] = $this->community_name;
        $vars['abbreviation']   = $this->abbreviation;
        $vars['capacity']       = $this->capacity;
        $vars['hide']           = $this->hide;

        if(isset($this->id)){
            $vars['id'] = $this->id;
        }

        return $vars;
    }

    public function execute(CommandContext $context){

        if(!Current_User::allow('hms', 'learning_community_maintenance')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit learning communities.');
        }

        $community = new HMS_Learning_Community();
        $db = new PHPWS_DB('hms_learning_communities');

        try{
            $community->set_id($context->get('id'));

            $result = $db->loadObject($community);

            if(PHPWS_Error::logIfError($result)){
                $community = new HMS_Learning_Community();
            }
        } catch (Exception $ignored) {
            //pass;
        }

        $community->set_community_name($context->get('community_name'));
        $community->set_abbreviation($context->get('abbreviation'));
        $community->set_capacity($context->get('capacity'));
        $community->hide = is_null($context->get('hide')) ? 0 : $context->get('hide');

        $result = $db->saveObject($community);

        if(PHPWS_Error::logIfError($result)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not save RLC.');
        }

        $viewCommand = CommandFactory::getCommand('ShowEditRlc');
        $viewCommand->setId($community->id);

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'The RLC was saved successfully.');
        $viewCommand->redirect();
    }
}
?>
