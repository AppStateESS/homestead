<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class UpdateEmergencyContactRegistration extends ApplicationFeatureRegistration {
    function __construct()
    {
        $this->name = 'UpdateEmergencyContact';
        $this->description = 'Update Emergency & Missing Persons Contact';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 8;
    }

    public function showForStudent(Student $student, $term)
    {
        return true;
    }
}

class UpdateEmergencyContact extends ApplicationFeature {
    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
        PHPWS_Core::initModClass('hms', 'UpdateEmergencyContactMenuBlockView.php');

        $application = HousingApplicationFactory::getAppByStudent($student, $this->term);

        return new UpdateEmergencyContactMenuBlockView($student, $this->getStartDate(), $this->getEndDate(), $application);
    }
}
