<?php

namespace Homestead\command;

use \Homestead\Command;

/**
 * @author jbooker
 * @package hms
 */

class HallOverviewCommand extends Command {

    private $hallId;

    public function setHallId($id){
        $this->hallId = $id;
    }

    public function getRequestVars(){
        $vars = array('action'=>'HallOverview');

        if(isset($this->hallId)){
            $vars['hallId'] = $this->hallId;
        }

        return $vars;
    }

    public function getSubLink($text, $parentVars)
    {
        $regularLink = PHPWS_Text::moduleLink(dgettext('hms', $text), 'hms', $parentVars);

        $nakedDisplayCmd = CommandFactory::getCommand('SelectResidenceHall');
        $nakedDisplayCmd->setTitle('Hall Overview');
        $nakedDisplayCmd->setOnSelectCmd(CommandFactory::getCommand('HallOverviewNakedDisplay'));

        return $regularLink . ' [' . $nakedDisplayCmd->getLink('Printable') . ']';
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms','run_hall_overview')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to see the Hall Overview.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HallOverview.php');

        $hallId = $context->get('hallId');

        if(!isset($hallId)){
            throw new InvalidArgumentException('Missing hall ID.');
        }

        $hall = new HMS_Residence_Hall($hallId);

        // Check for a hall/term mismatch, since halls are indexed by ID and not by name & term
        if($hall->term != Term::getSelectedTerm()){
            $hallOverviewCmd = CommandFactory::getCommand('SelectResidenceHall');
            $hallOverviewCmd->setTitle('Edit a Residence Hall');
            $hallOverviewCmd->setOnSelectCmd(CommandFactory::getCommand('HallOverview'));
            $hallOverviewCmd->redirect();
        }

        $hallOverview = new HallOverview($hall);
        $context->setContent($hallOverview->show());
    }
}
