<?php

namespace Homestead;

class SummerContextApplicationFactory extends ContextApplicationFactory {

    public function populateApplicationSpecificFields()
    {
        $this->app->setApplicationType('summer');
        $this->app->setRoomType($this->context->get('room_type'));
        $this->app->setSmokingPreference($this->context->get('smoking_preference'));
    }
}
