<?php

namespace Homestead\command;

use \Homestead\Command;
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
PHPWS_Core::initModClass('hms', 'HousingApplication.php');

class ShowStudentMenuCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowStudentMenu');
    }

    public function execute(CommandContext $context){
        $currentTerm = Term::getCurrentTerm();
        $username = UserStatus::getUsername();

        # Create a contact form command, redirect to it in case of error.
        $contactCmd = CommandFactory::getCommand('ShowContactForm');

        //TODO add try catch blocks here for StudentNotFound exception
        $student = StudentFactory::getStudentByUsername($username, $currentTerm);

        $applicationTerm = $student->getApplicationTerm();

        // In case this is a new freshmen, they'll likely have no student type in the "current" term.
        // So, instead, we need to lookup the student in their application term.
        if($applicationTerm > $currentTerm){
            $student = StudentFactory::getStudentByUsername($username, $applicationTerm);
        }

        $studentType 	= $student->getType();
        $studentClass	= $student->getClass();
        $dob 			= $student->getDob();
        $gender         = $student->getGender();

        # Check for banner errors in any of these calls
        if(empty($applicationTerm) || empty($studentType) ||
        empty($studentClass) ||
        empty($dob) ||
        $gender === '' || $gender === 'N' || $gender === null ||
        is_null($dob))
        {
            # TODO: HMS_Mail here
            \PHPWS_Error::log('Initial banner lookup failed', 'hms', 'show_welcome_screen', "username: " . UserStatus::getUsername());
            $badDataCmd = CommandFactory::getCommand('ShowBadBannerData');
            $badDataCmd->redirect();
        }

        # Recreate the student object using the student's application term
        $student = StudentFactory::getStudentByUsername($username, $applicationTerm);

        # Check for an assignment in the current term. So far, this only matters for type 'Z' (readmit) students
        $assignment = HMS_Assignment::checkForAssignment($username, $currentTerm);

        /******************************************
         * Sort returning students (lottery) from *
         * freshmen (first-time application)      *
         ******************************************/
        # Check application term for past or future
        if($applicationTerm <= $currentTerm || ($studentType == TYPE_READMIT && $assignment === TRUE)){
            /**************
             * Continuing *
             **************/
            # Application term is in the past

            /*
             * There's an exception above for type 'Z' (readmit) students.
             * Their application terms will be in the past. They're considered continuing if they're
             * already assigned. Otherwise, (not assigned) they're considered freshmen
             */

            # Redirect to the returning student menu
            $cmd = CommandFactory::getCommand('ShowReturningStudentMenu');
            $cmd->redirect();
        }else if($applicationTerm > $currentTerm || $studentType == TYPE_READMIT){
            /**
             * Exception for type 'Z' (readmit) students.
             * Their application term is in the past, but they should be treated as freshmen/transfer
             */

            /**
             * This is somehwat of a hack for type 'Z' (readmit) students.
             * This code sets the student's application term to the term after the current term, since type Z students.
             * This makes *everything* else work right.
             */
            if($studentType == TYPE_READMIT){
                $applicationTerm = Term::getNextTerm(Term::getCurrentTerm());
                //TODO find a way around this, because this doesn't work
                $_SESSION['application_term'] = $application_term;
            }

            /*********************
             * Incoming Freshmen *
             *********************/
            # Application term is in the future

            # Check the student type, must be freshmen, transfer, readmit, or non-degree
            /** Commenting this out since we have freshmen students with future application terms with student types of 'C'
            if(!$student->isInternational() && $studentType != TYPE_FRESHMEN && $studentType != TYPE_TRANSFER && $studentType != TYPE_RETURNING && $studentType != TYPE_READMIT && $studentType != TYPE_NONDEGREE){
                # No idea what's going on here, send to a contact page
                $contactCmd->redirect();
            }
            */

            # Make sure the user's application term exists in hms_term,
            # otherwise give a "too early" message
            if(!Term::isValidTerm($applicationTerm)){
                PHPWS_Core::initModClass('hms', 'WelcomeScreenViewInvalidTerm.php');
                $view = new WelcomeScreenViewInvalidTerm($applicationTerm, $contactCmd);
                $context->setContent($view->show());
                return;
            }

            # Make sure the student doesn't already have an assignment on file for the current term
            if(HMS_Assignment::checkForAssignment($username, $currentTerm)){
                # No idea what's going on here, send to a contact page
                $contactCmd->redirect();
            }

            # Check to see if the user has an application on file already for every required term
            # If so, forward to main menu
            $requiredTerms = HousingApplication::checkAppliedForAllRequiredTerms($student);

            if(count($requiredTerms) > 0){
                # Student is missing a required application, so redirect to the application form for that term
                $appCmd = CommandFactory::getCommand('ShowHousingApplicationWelcome');
                $appCmd->setTerm($requiredTerms[0]);
                $appCmd->redirect();
            }else{
                $menuCmd = CommandFactory::getCommand('ShowFreshmenMainMenu');
                $menuCmd->redirect();
            }
        }
    }
}
