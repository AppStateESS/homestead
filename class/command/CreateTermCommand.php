<?php

namespace Homestead\command;

use \Homestead\Command;

class CreateTermCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'CreateTerm');
    }

    public function execute(CommandContext $context)
    {

        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'edit_terms')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit terms.');
        }

        $successCmd = CommandFactory::getCommand('ShowEditTerm');
        $errorCmd = CommandFactory::getCommand('ShowCreateTerm');

        $year = $context->get('year_drop');
        $sem = $context->get('term_drop');

        if(!isset($year) || is_null($year) || empty($year)){
            NQ::simple('hms', hms\NotificationView::ERROR, 'You must provide a year.');
            $errorCmd->redirect();
        }

        if(!isset($sem) || is_null($sem) || empty($sem)){
            NQ::simple('hms', hms\NotificationView::ERROR, 'You must provide a semester.');
            $errorCmd->redirect();
        }

        // Check to see if the specified term already exists
        if(!Term::isValidTerm($year . $sem)){
            $term = new Term(NULL);
            $term->setTerm($year . $sem);
            $term->setBannerQueue(1);
            $term->setMealPlanQueue(1);

            try{
                $term->save();
            }catch(DatabaseException $e){
                NQ::simple('hms', hms\NotificationView::ERROR, 'There was an error saving the term. Please try again or contact ESS.');
                $errorCmd->redirect();
            }
        }else{

            $term = new Term($year . $sem);

            // The term already exists, make sure there are no halls for this term
            $db = new \PHPWS_DB('hms_residence_hall');
            $db->addWhere('term', $term->getTerm());
            $num = $db->count();

            if(!is_null($num) && $num > 0){
                NQ::simple('hms', hms\NotificationView::ERROR, 'One or more halls already exist for this term, so nothing can be copied.');
                $errorCmd->redirect();
            }
        }

        $text = Term::toString($term->getTerm());

        $copy            = $context->get('copy_pick');
        $copyAssignments = false;
        $copyRoles       = false;

        // If you want to copy roles and/or assignments
        // you must also copy the hall structure.
        if(isset($copy['struct'])){
            // Copy hall structure
            if(isset($copy['assign'])){
                // Copy assignments.
                $copyAssignments = true;
            }
            if(isset($copy['role'])){
                // Copy roles.
                $copyRoles = true;
            }
        }else{
            // either $copy == 'nothing', or the view didn't specify... either way, we're done
            NQ::simple('hms', hms\NotificationView::SUCCESS, "$text term created successfully.");
            $successCmd->redirect();
        }

        # Figure out which term we're copying from, if there isn't one then use the "current" term.
        $fromTerm = $context->get('from_term');
        if(is_null($fromTerm)){
            $fromTerm = Term::getCurrentTerm();
        }

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');

        $db = new \PHPWS_DB();

        try{
            $db->query('BEGIN');
            # Get the halls from the current term
            $halls = HMS_Residence_Hall::get_halls($fromTerm);
            set_time_limit(36000);

            foreach ($halls as $hall){
                $hall->copy($term->getTerm(), $copyAssignments, $copyRoles);
            }

            $db->query('COMMIT');

        }catch(Exception $e){

            $db->query('ROLLBACK');
            \PHPWS_Error::log(print_r($e, true), 'hms');
            NQ::simple('hms', hms\NotificationView::ERROR, 'There was an error copying the hall structure and/or assignments. The term was created, but nothing was copied.');
            $errorCmd->redirect();
        }

        if($copyAssignments){
            NQ::simple('hms', hms\NotificationView::SUCCESS, "$text term created successfully. The hall structure and assignments were copied successfully.");
        }else{
            NQ::simple('hms', hms\NotificationView::SUCCESS, "$text term created successfully and hall structure copied successfully.");
        }
        Term::setSelectedTerm($term->getTerm());
        $successCmd->redirect();
    }
}
