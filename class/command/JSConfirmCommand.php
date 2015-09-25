<?php

class JSConfirmCommand extends Command {

    private $onConfirmCommand; // the command to execute if the user confirms

    private $link;
    private $title;
    private $question;

    public function setOnConfirmCommand(Command $cmd){
        $this->onConfirmCommand = $cmd;
    }

    public function setQuestion($text){
        $this->question = $text;
    }

    public function setLink($link){
        $this->link = $link;
    }

    public function setTitle($title){
        $this->title = $title;
    }

    public function getRequestVars()
    {
        return $this->onConfirmCommand->getRequestVars();
    }

    public function getLink($text = null, $target = null, $cssClass = null, $title = null)
    {

        $js = array();
        $js['QUESTION']     = $this->question;
        $js['LINK']         = $this->link;
        $js['TITLE']        = $this->title;

        $address = 'index.php?module=hms';
        foreach($this->onConfirmCommand->getRequestVars() as $key=>$var){
            $address .= "&$key=$var";
        }

        $js['ADDRESS']     = $address;

        return javascript('confirm', $js);
    }

    public function execute(CommandContext $context)
    {

    }
}
