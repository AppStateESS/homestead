<?php

namespace Homestead\command;

use \Homestead\Command;

PHPWS_Core::initModClass('hms', 'ContractFactory.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'Docusign/Client.php');
PHPWS_Core::initModClass('hms', 'Docusign/EnvelopeFactory.php');
PHPWS_Core::initModClass('hms', 'Docusign/RecipientView.php');

class BeginDocusignCommand extends Command
{
    private $term;
    private $agreedCommand;
    private $parentName;
    private $parentEmail;

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function setReturnCmd(Command $cmd)
    {
        $this->agreedCommand = $cmd;
    }

    public function setParentName($name)
    {
        $this->parentName = $name;
    }

    public function setParentEmail($email)
    {
        $this->parentEmail = $email;
    }

    public function getRequestVars()
    {
        $vars = array('action' => 'BeginDocusign', 'term' => $this->term);

        // In case we need parent signatures, these should be set. If they're not set,
        // we'll get null values and know how to handle those in execute()
        $vars['parentName'] = $this->parentName;
        $vars['parentEmail'] = $this->parentEmail;

        if (!isset($this->agreedCommand)) {
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
        if ($docusignUsername === null || $docusignUsername == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign username.');
        }

        $docusignPassword = PHPWS_Settings::get('hms', 'docusign_password');
        if ($docusignPassword === null || $docusignPassword == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign password.');
        }

        $docusignKey = PHPWS_Settings::get('hms', 'docusign_key');
        if ($docusignKey === null || $docusignKey == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign key.');
        }

        $docusignEnv = PHPWS_Settings::get('hms', 'docusign_env');
        if ($docusignEnv === null || $docusignEnv == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign key.');
        }

        $term = $context->get('term');
        $termObj = new Term($term);

        // Get Docusign Template IDs for this term
        $templateId = $termObj->getDocusignTemplate();
        $under18TemplateId = $termObj->getDocusignUnder18Template();

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $under18 = $student->isUnder18();

        $docusignClient = new Docusign\Client($docusignKey, $docusignUsername, $docusignPassword, $docusignEnv);

        $templateRoles = array(
            array(
                "roleName" => 'Student',
                "email" => $student->getEmailAddress(),
                "name" => $student->getLegalName(),
                "clientUserId" => $student->getBannerId()
            )
        );

        // If student is under 18, then add parent role to list of signers
        if ($under18) {
            $parentName = $context->get('parentName');
            $parentEmail = $context->get('parentEmail');

            $templateRoles[] = array(
                "roleName" => 'Parent',
                "email" => $parentEmail,
                "name" => $parentName
                    //"clientUserId" => $student->getBannerId()
            );
        }

        // Check for an existing contract
        $contract = ContractFactory::getContractByStudentTerm($student, $term);

        if ($contract === false) {
            // Create a new envelope and save it
            if ($under18) {
                // If student is under 18, use the template with parent signatures
                $envelope = Docusign\EnvelopeFactory::createEnvelopeFromTemplate($docusignClient, $under18TemplateId, 'University Housing Contract', $templateRoles, 'sent', $student->getBannerId());
            } else {
                // Student is over 18, so use the 1-signature template (without a parent signature)
                $envelope = Docusign\EnvelopeFactory::createEnvelopeFromTemplate($docusignClient, $templateId, 'University Housing Contract', $templateRoles, 'sent', $student->getBannerId());
            }

            // Create a new contract to save the envelope ID
            $contract = new Contract($student, $term, $envelope->getEnvelopeId(), $envelope->getStatus(), strtotime($envelope->getStatusDateTime()));
            ContractFactory::save($contract);
        } else {
            // Use the existing envelope id
            $envelope = Docusign\EnvelopeFactory::getEnvelopeById($docusignClient, $contract->getEnvelopeId());
        }


        $recipientView = new Docusign\RecipientView($docusignClient, $envelope, $student->getBannerId(), $student->getLegalName(), $student->getEmailAddress());

        $returnCmd = CommandFactory::getCommand($context->get('onAgreeAction'));
        $returnCmd->setTerm($term);

        $roommateRequestId = $context->get('roommateRequestId');
        if (isset($roommateRequestId) && $roommateRequestId != null) {
            $returnCmd->setRoommateRequestId($roommateRequestId);
        }

        $mealPlan = $context->get('meal_plan');
        if(isset($mealPlan) && $mealPlan !== null){
            $returnCmd->setMealPlan($mealPlan);
        }

        $returnUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . $returnCmd->getURI();

        $url = $recipientView->getRecipientViewUrl($returnUrl);

        PHPWS_Core::reroute($url);
        //$context->setContent('beginning signing process');
    }

}
