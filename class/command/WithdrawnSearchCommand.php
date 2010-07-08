<?php

//TODO make this better

class WithdrawnSearchCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'WithdrawnSearch');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');

        $term = 201040;

        $query = "select DISTINCT * FROM (select hms_new_application.username from hms_new_application WHERE term=$term AND withdrawn != 1 UNION select hms_assignment.asu_username from hms_assignment WHERE term=$term) as foo";
        $result = PHPWS_DB::getCol($query);

        $withdrawnCount = 0;

        foreach($result as $user){
            $student = StudentFactory::getStudentByUsername($user, $term);
            if($student->getType() == TYPE_WITHDRAWN){
                echo "Withdrawn: $user<br />\n";
                $withdrawnCount++;

                // Get the application and mark it withdrawn
                $app = HousingApplication::getApplicationByUser($user, $term);
                $app->setWithdrawn(1);
                $app->save();
            }
        }

        echo "Withdrawn count: $withdrawnCount<br />\n";

    }

}

?>