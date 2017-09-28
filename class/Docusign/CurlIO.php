<?php
namespace Homestead\Docusign;

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

class CurlIO extends IO {

    public function makeRequest($url, $method = 'GET', $headers = array(), $params = array(), $data = NULL) {
        $response;
        $curl = curl_init($url . '?' . http_build_query($params, NULL, '&'));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //return the transfer as a string
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        switch (strtoupper($method)) {

            case 'POST':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;

            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;

            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;

            default:
                break;
        }

        try {
            $result = curl_exec($curl);
            if( curl_error($curl) != '' ) {
                throw new IOException(curl_error($curl));
            }
            $jsonResult = json_decode($result);
            $response = (!is_null($jsonResult)) ? $jsonResult : $result;
        } catch(\Exception $e) {
            throw new IOException($e);
        }

        curl_close($curl);

        if (is_array($response) && array_key_exists('errorCode', $response)) {
            throw new IOException($response->errorCode . ': ' . $response->message);
        }

        return $response;
    }

}
