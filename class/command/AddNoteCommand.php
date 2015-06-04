<?php

class AddNoteCommand extends Command {

    private $username;
    private $note;

    public function setUsername($username){
        $this->username = $username;
    }

    public function setNote($note){
        $this->note = $note;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'AddNote');

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        if(isset($this->note)){
            $vars['note'] = $this->note;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $username = $context->get('username');
        $note = $context->get('note');

        if(!isset($username) || empty($username)){
            throw new InvalidArgumentException('Missing username');
        }

        if(!isset($note) || empty($note)){
            throw new InvalidArgumentException('No text was provided for the note.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($username, ACTIVITY_ADD_NOTE, UserStatus::getUsername(), $note);

        # Redirect back to whereever the user came from
        $context->goBack();
    }
}
