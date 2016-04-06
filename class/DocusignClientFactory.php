<?php

/**
 * Factory class for creating Docusign Clients.
 *
 * @author Chris Detsch
 * @package hms
 */
class DocusignClientFactory {

    /**
     * Returns a Client object for making API calls to Docusign
     *
     * @throws DatabaseException
     * @return Array Associative array of damage types
     */
    public static function getClient()
    {
        $docusignUsername = PHPWS_Settings::get('hms', 'docusign_username');
        if ($docusignUsername === null || $docusignUsername == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign username.');
        }

        $docusignPassword = PHPWS_Settings::get('hms', 'docusign_password');
        if ($docusignPassword === null || $docusignPassword == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign password.');
        }

        $docusignKey = PHPWS_Settings::get('hms', 'docusign_key');
        if ($docusignKey === null || $docusignKey == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign key.');
        }

        $docusignEnv = PHPWS_Settings::get('hms', 'docusign_env');
        if ($docusignEnv === null || $docusignEnv == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign key.');
        }

        return new Docusign\Client($docusignKey, $docusignUsername, $docusignPassword, $docusignEnv);
    }
}
