<?php
namespace Homestead\Docusign;

require_once PHPWS_SOURCE_DIR . 'mod/hms/vendor/autoload.php';

/*
 * Copyright 2013 DocuSign Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

if (! function_exists('json_decode')) {
  throw new \Exception('DocuSign PHP API Client requires the JSON PHP extension');
}

if (! function_exists('curl_version')) {
  throw new \Exception('DocuSign PHP API Client requires the PHP Client URL Library');
}

if (! function_exists('http_build_query')) {
  throw new \Exception('DocuSign PHP API Client requires http_build_query()');
}

if (! ini_get('date.timezone') && function_exists('date_default_timezone_set')) {
  date_default_timezone_set('UTC');
}

class Client {

    // The DocuSign Credentials
    private $creds;

    // The version of DocuSign API
    private $version;

    // The DocuSign Environment
    private $environment;

    // The DocuSign Account Id
    private $accountID;

    // The base url of the DocuSign Account
    private $baseURL;

    // The flag indicating if it has multiple DocuSign accounts
    private $hasMultipleAccounts = false;

    public function __construct($integratorKey, $email, $password, $environment, $version = 'v2', $accountId = '') {

        // Create Credentials object
        $this->creds = new Creds($integratorKey, $email, $password);

        // Initialize local variables
        $this->version = $version;
        $this->environment = $environment;
        $this->accountID = $accountId;

        //$this->curl = new CurlIO();

        // Authenticate
        $this->authenticate();
    }

    public function authenticate() {
        $url = $this->getAPIUrl() . '/login_information';

        $http = new \GuzzleHttp\Client();
        try {
            $request = new \GuzzleHttp\Psr7\Request('GET', $url);
            $response = $http->send($request, ['headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'X-DocuSign-Authentication' => $this->getAuthHeader()]]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw $e;
        }
        $json = json_decode($response->getBody(), true);

        $this->baseURL = $json['loginAccounts'][0]['baseUrl'];
        $this->accountID = $json['loginAccounts'][0]['accountId'];

        /*
        if( count($response->loginAccounts) > 1 ) $this->hasMultipleAccounts = true;
        $defaultBaseURL = '';
        $defaultAccountID = '';
        foreach($response->loginAccounts as $account) {
            if( !empty($this->accountID) ) {
                if( $this->accountID == $account->accountId ) {
                    $this->baseURL = $account->baseUrl;
                    break;
                }
            }
            if( $account->isDefault == 'true' ) {
                $defaultBaseURL = $account->baseUrl;
                $defaultAccountID = $account->accountId;
            }
        }
        if(empty($this->baseURL)) {
            $this->baseURL = $defaultBaseURL;
            $this->accountID = $defaultAccountID;
        }
        */
    }

    public function getAPIUrl() {
    	return ('https://' . $this->environment . '.docusign.net/restapi/' . $this->version);
    }

    public function getCreds() { return $this->creds; }
    public function getVersion() { return $this->version; }
    public function getEnvironment() { return $this->environment; }
    public function getBaseURL() { return $this->baseURL; }
    public function getAccountID() { return $this->accountID; }
    public function hasMultipleAccounts() { return $this->hasMultipleAccounts; }
    public function getHeaders($accept = 'Accept: application/json', $contentType = 'Content-Type: application/json') {
        //TODO Fix this?
        return array(
            $this->getAuthHeader(),
            $contentType,
            $accept
        );
    }

    public function getAuthHeader()
    {
    	$authArray = array('Username' => $this->creds->getEmail(),
                           'Password' => $this->creds->getPassword(),
                           'IntegratorKey' => $this->creds->getIntegratorKey());
        $authJson = json_encode($authArray);
        return $authJson;
    }
}
