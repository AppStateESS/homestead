<?php

class BeginDocusignCommand extends Command {
	
    public function setTerm($term){
        $this->term = $term;
    }

    public function setReturnCmd(Command $cmd){
        $this->agreedCommand = $cmd;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'BeginDocusign', 'term'=>$this->term);

        if(!isset($this->agreedCommand)){
            return $vars;
        }

        // Get the action to do when someone agrees to the terms
        $onAgreeVars = $this->agreedCommand->getRequestVars();
        $onAgreeAction = $onAgreeVars['action'];

        // Unset it so it doesn't conlict
        unset($onAgreeVars['action']);

        // Reset it under a different name
        $onAgreeVars['onAgreeAction'] = $onAgreeAction;

        return array_merge($vars, $onAgreeVars);
    }
    
    public function execute(CommandContext $context)
    {
        $docusignUsername = PHPWS_Settings::get('hms', 'docusign_username');
        if($docusignUsername === null || $docusignUsername == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
        	throw new InvalidConfigurationException('Missing docusign username.');
        }
        
        $docusignPassword = PHPWS_Settings::get('hms', 'docusign_password');
        if($docusignPassword === null || $docusignPassword == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign password.');
        }
        
        $docusignKey = PHPWS_Settings::get('hms', 'docusign_key');
        if($docusignKey === null || $docusignKey == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign key.');
        }
        
        $docusignEnv = PHPWS_Settings::get('hms', 'docusign_env');
        if($docusignEnv === null || $docusignEnv == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign key.');
        }
        
        $term = $context->get('term');
        $termObj = new Term($term);
        $templateId = $termObj->getDocusignTemplate();
        
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        
        $docusignClient = new Docusign\Client($docusignKey, $docusignUsername, $docusignPassword, $docusignEnv);
        
        $templateRoles = array(
                            array(
                                "roleName" => 'Student',
                                "email" => $student->getEmailAddress(),
                                "name" => $student->getLegalName(),
                                "clientUserId" => $student->getBannerId()
                            )
                         );
                         
        $envelope = Docusign\EnvelopeFactory::createEnvelopeFromTemplate($docusignClient, $templateId, 'University Housing Contract', $templateRoles, 'sent');
        var_dump($envelope);
        exit;
        
        // TODO Save the envelope ID
        
        // TODO, if there's already an envelope ID and it isn't signed, then use it instead
        
        $recipientView = new Docusign\RecipientView($docusignClient, $envelope, $student->getBannerId(), $student->getLegalName());
        
        $returnCmd = CommandFactory::getCommand($context->get('onAgreeAction'));
        $returnCmd->setTerm($term);
        $returnUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . $returnCmd->getURI();
        //var_dump($returnUrl);
        $url = $recipientView->getRecipientViewUrl($returnUrl);
        //var_dump($url);exit;
        PHPWS_Core::reroute($url);
        //$context->setContent('beginning signing process');
    }
}