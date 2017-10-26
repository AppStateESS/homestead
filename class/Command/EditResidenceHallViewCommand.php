<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\HMS_Residence_Hall;
use \Homestead\ResidenceHallView;
use \Homestead\Term;
use \Homestead\CommandFactory;
use \Homestead\Exception\PermissionException;

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class EditResidenceHallViewCommand extends Command {

    private $hallId;

    public function setHallId($id){
        $this->hallId = $id;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'EditResidenceHallView');

        if(isset($this->hallId)){
            $vars['hallId'] = $this->hallId;
        }

        return $vars;
    }

    public function getSubLink($text, $parentVars){
        return \PHPWS_Text::moduleLink(dgettext('hms', $text), 'hms', $parentVars);
    }

    public function execute(CommandContext $context)
    {

        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'hall_view') ){
            throw new PermissionException('You do not have permission to edit halls.');
        }

        // Check for a  hall ID
        $hallId = $context->get('hallId');
        if(!isset($hallId)){
            throw new \InvalidArgumentException('Missing hall ID.');
        }

        $hall = new HMS_Residence_Hall($hallId);

        // Check for a hall/term mismatch, since halls are indexed by ID and not by name & term
        if($hall->term != Term::getSelectedTerm()){
            $residenceHallCmd = CommandFactory::getCommand('SelectResidenceHall');
            $residenceHallCmd->setTitle('Edit a Residence Hall');
            $residenceHallCmd->setOnSelectCmd(CommandFactory::getCommand('EditResidenceHallView'));
            $residenceHallCmd->redirect();
        }

        $hallView = new ResidenceHallView($hall);

        $context->setContent($hallView->show());
    }
}
