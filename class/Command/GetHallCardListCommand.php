<?php

namespace Homestead\Command;

use Homestead\Term;
use Homestead\ResidenceHallFactory;

class GetHallCardListCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'GetHallCardList');
    }

    public function execute(CommandContext $context)
    {
        $halls = ResidenceHallFactory::getHallsForTerm(Term::getSelectedTerm());
        //get number of residence, number beds, to get number free beds
        foreach($halls as $hall) {
            $hall->numBeds = $hall->get_number_of_beds();
            $hall->numAssignees = $hall->get_number_of_assignees();
            $hall->numFree = $hall->numBeds - $hall->numAssignees;
            \PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
            if($hall->exterior_image_id != 0){
                $manager = \Cabinet::fileManager('exterior_image_id', $hall->exterior_image_id);
                $hall->imageLink = $manager->file_assoc->_file_path;

            }else{
                $hall->imageLink = 'mod/hms/img/newland.jpg';
            }
        }
        //var_dump($manager->file_assoc->_file_path, $halls);exit;
        echo json_encode($halls);
        exit;
    }
}
