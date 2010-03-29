<?php

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
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must provide a year.');
            $viewCmd->redirect();
        }

        if(!isset($sem) || is_null($sem) || empty($sem)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must provide a semester.');
            $viewCmd->redirect();
        }

        // Check to see if the specified term already exists
        if(Term::isValidTerm($year . $sem)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error: That term already exists.');
            $errorCmd->redirect();
        }

        $term = new Term(NULL);
        $term->setTerm($year . $sem);
        $term->setBannerQueue(1);

        try{
            $term->save();
        }catch(DatabaseException $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was an error saving the term. Please try again or contact ESS.');
            $viewCmd->redirect();
        }

        $text = Term::toString($term->getTerm());

        $copy = $context->get('copy_drop');

        if('hallStructure'){
            $copyAssignments = false;
        }else if('assignments'){
            $copyAssignments = true;
        }else{
            // either $copy == 'nothing', or the view didn't specify... either way, we're done
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, "$text term created successfully.");
            $successCmd->redirect();
        }

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $db = new PHPWS_DB();

        try{
            $db->query('BEGIN');

            # Get the halls from the current term
            $halls = HMS_Residence_Hall::get_halls(Term::getCurrentTerm());

            foreach ($halls as $hall){
                $hall->copy($term->getTerm(), $copyAssignments);
            }

            $db->query('COMMIT');

        }catch(Exception $e){
            $db->query('ROLLBACK');
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was an error copying the hall structure and/or assignments. The term was created, but nothing was copied.');
            $errorCmd->redirect();
        }

        if($copyAssignments){
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, "$text term created successfully. The hall structure and assignments were copied successfully.");
        }else{
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, "$text term created successfully and hall structure copied successfully.");
        }
        Term::setSelectedTerm($term->getTerm());
        $successCmd->redirect();
    }
}

?>