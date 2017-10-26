<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\PackageDeskFactory;
use \Homestead\PackageDeskView;
use \Homestead\Exception\PermissionException;

/**
 * Command/controller for showing the PackageDesk interface
 * @author jbooker
 *
 */
class ShowPackageDeskMenuCommand extends Command {

    /**
     * (non-PHPdoc)
     * @see Command::getRequestVars()
     */
    public function getRequestVars()
    {
        return array('action'=>'ShowPackageDeskMenu');
    }

    /**
     * (non-PHPdoc)
     * @see Command::execute()
     */
    public function execute(CommandContext $context)
    {
        // Check permissions
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'package_desk')){
            throw new PermissionException('You do not have permission to access the Package Desk.');
        }

        $desks = PackageDeskFactory::getPackageDesksAssoc();

        $view = new PackageDeskView($desks);

        $context->setContent($view->show());
    }

}
