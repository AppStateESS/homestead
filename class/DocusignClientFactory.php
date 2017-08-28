<?php

namespace Homestead;

use \Homestead\exception\InvalidConfigurationException;
use \Homestead\Docusign\Client;

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
     * @return \Docusign\Client object
     */
    public static function getClient()
    {
        $docusignUsername = \PHPWS_Settings::get('hms', 'docusign_username');
        if ($docusignUsername === null || $docusignUsername == '') {
            throw new InvalidConfigurationException('Missing docusign username.');
        }

        $docusignPassword = \PHPWS_Settings::get('hms', 'docusign_password');
        if ($docusignPassword === null || $docusignPassword == '') {
            throw new InvalidConfigurationException('Missing docusign password.');
        }

        $docusignKey = \PHPWS_Settings::get('hms', 'docusign_key');
        if ($docusignKey === null || $docusignKey == '') {
            throw new InvalidConfigurationException('Missing docusign key.');
        }

        $docusignEnv = \PHPWS_Settings::get('hms', 'docusign_env');
        if ($docusignEnv === null || $docusignEnv == '') {
            throw new InvalidConfigurationException('Missing docusign key.');
        }

        return new Client($docusignKey, $docusignUsername, $docusignPassword, $docusignEnv);
    }
}
