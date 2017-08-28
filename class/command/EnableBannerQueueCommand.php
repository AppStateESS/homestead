<?php

namespace Homestead\command;

use \Homestead\Command;

class EnableBannerQueueCommand extends Command {
    private $term;

    public function setTerm($term) {
        $this->term = $term;
    }

    public function getRequestVars() {
        $vars = array('action' => 'EnableBannerQueue');

        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }

        return $vars;
    }

    public function execute(CommandContext $context) {

        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'banner_queue')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to enable/disable the Banner queue.');
        }


        if(is_null($this->term)) {
            $this->term = $context->get('term');
        }

        $term = $this->term;

        if(is_null($term)) {
            throw new \InvalidArgumentException('No term was specified to DisableBannerQueue');
        }

        $term = new Term($term);

        $term->setBannerQueue(TRUE);
        $term->save();
        \NQ::Simple('hms', NotificationView::SUCCESS, 'Banner Queue has been enabled for ' . Term::toString($term->term) . '.');

        CommandContext::goBack();
    }
}
