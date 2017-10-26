<?php

namespace Homestead;

class LotteryContextApplicationFactory extends ContextApplicationFactory {

    public function populateApplicationSpecificFields()
    {
        //TODO lottery stuff here
        $this->app->setApplicationType('lottery');
    }
}
