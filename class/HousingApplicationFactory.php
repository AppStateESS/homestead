<?php

PHPWS_Core::initModClass('hms', 'HousingApplication.php');
PHPWS_Core::initModClass('hms', 'FallApplication.php');
PHPWS_Core::initModClass('hms', 'SpringApplication.php');
PHPWS_Core::initModClass('hms', 'SummerApplication.php');
PHPWS_Core::initModClass('hms', 'LotteryApplication.php');

PHPWS_Core::initModClass('hms', 'ContextApplicationFactory.php');

class HousingApplicationFactory {

    public static function getApplicationFromContext(CommandContext $context, $term, Student $student, $applicationType)
    {
        return ContextApplicationFactory::getApplicationFromContext($context, $term, $student, $applicationType);
    }

    public static function getApplicationFromSession(Array $sessionData, $term, Student $student, $applicationType)
    {
        $context = new CommandContext();
        $context->clearParams();
        $context->setParams($sessionData);
    	return ContextApplicationFactory::getApplicationFromContext($context, $term, $student, $applicationType);
    }

    public static function getApplicationById($id)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'SpringApplication.php');
        PHPWS_Core::initModClass('hms', 'SummerApplication.php');
        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');
        PHPWS_Core::initModClass('hms', 'WaitingListApplication.php');

        $application = new HousingApplication();
        $db = new PHPWS_DB('hms_new_application');
        $db->addWhere('id', $id);
        $result = $db->loadObject($application);

        if(PHPWS_Error::logIfError($result)){
            throw new Exception("There was an retreiving the HousingApplication object from the database.");
        }

        if(is_null($application)){
            return null;
        }

        switch($application->application_type){
            case 'fall':
                $app = new FallApplication($application->id);
                break;
            case 'spring':
                $app = new SpringApplication($application->id);
                break;
            case 'summer':
                $app = new SummerApplication($application->id);
                break;
            case 'lottery':
                $app = new LotteryApplication($application->id);
                break;
            case 'offcampus_waiting_list':
                $app = new WaitingListApplication($application->id);
                break;
            default:
                //throw new InvalidArgumentException('Unknown application type: ' . $application->application_type);
                $app = new FallApplication($application->id);
        }

        return $app;
    }

    /**
     * Returns the HousingApplication object (of a specific child type) for the given term,
     * or null if no Housing Application exists.
     *
     * Uses BannerIDs to lookup students.
     * Replaces HousingApplication::getApplicationByUser()
     *
     * @param Student $student
     * @param string $term
     * @param string $applicationType
     * @throws DatabaseException
     * @throws InvalidArgumentException
     * @return Mixed<HousingApplication|null> A HousingApplication subclass object, or null of no applicaiton exists
     */
    public static function getAppByStudent(Student $student, $term, $applicationType = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'SpringApplication.php');
        PHPWS_Core::initModClass('hms', 'SummerApplication.php');
        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');
        PHPWS_Core::initModClass('hms', 'WaitingListApplication.php');

        $db = new PHPWS_DB('hms_new_application');
        $db->addWhere('banner_id', $student->getBannerId());
        $db->addWhere('term', $term);

        if(!is_null($applicationType)){
            $db->addWhere('application_type', $applicationType);
        }

        $result = $db->select('row');

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if ($result === false || $result === null) {
            return null;
        }

        switch($result['application_type']){
            case 'fall':
                $app = new FallApplication($result['id']);
                break;
            case 'spring':
                $app = new SpringApplication($result['id']);
                break;
            case 'summer':
                $app = new SummerApplication($result['id']);
                break;
            case 'lottery':
                $app = new LotteryApplication($result['id']);
                break;
            case 'offcampus_waiting_list':
                $app = new WaitingListApplication($result['id']);
                break;
            default:
                throw new InvalidArgumentException('Unknown application type: ' . $result['application_type']);
        }

        return $app;
    }
}
