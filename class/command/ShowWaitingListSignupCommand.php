<?php

/**
 * Command for showing the waiting list signup interface
 * to students who re-applied already (and didn't get a room).
 * 
 * @author jbooker
 * @package Hms
 */
class ShowWaitingListSignupCommand extends Command {
    
    private $term;
    
    /**
     * Sets the term for this command.
     * @param integer $term
     */
    public function setTerm($term)
    {
        $this->term = $term;
    }
    
    /**
     * (non-PHPdoc)
     * @see Command::getRequestVars()
     */
    public function getRequestVars()
    {
        return array('action'=>'ShowWaitingListSignup', 'term'=>$this->term);
    }
    
    /**
     * (non-PHPdoc)
     * @see Command::execute()
     */
    public function execute(CommandContext $context)
    {
        $term = $context->get('term');
        if (!isset($term)) {
            throw new InvalidArgumentException('Missing term');
        }
        
        PHPWS_Core::initModClass('hms', 'WaitingListSignupView.php');
        
        $view = new WaitingListSignupView($term);
        $context->setContent($view->show());
    }
}

