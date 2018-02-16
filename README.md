# Homestead
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/AppStateESS/homestead/badges/quality-score.png?s=d4e5a31be92390a264c73c4282dd8cfb9c36400b)](https://scrutinizer-ci.com/g/AppStateESS/homestead/)
[![Code Climate](https://codeclimate.com/github/AppStateESS/homestead/badges/gpa.svg)](https://codeclimate.com/github/AppStateESS/homestead)
[![Build Status](https://api.travis-ci.org/AppStateESS/homestead.svg?branch=master)](https://travis-ci.org/AppStateESS/homestead)

## On-campus Housing Management
Homestead is a web-application for managing on-campus student housing.

## Development Setup
* Install [phpwebsite](https://github.com/AppStateESS/phpwebsite/) using postgresql
* Clone this repo into the `phpwebsite/mod` into an `hms` directory: `git clone git@github.com:AppStateESS/homestead.git hms`
* Install the HMS module from the phpwebsite control panel
* Copy `mod/hms/inc/hms_defines.php` and `mod/hms/inc/SOAPDataOverride.php` to `phpwebsite/inc/`
* In the `phpwebsite/inc/hms_defines.php` file you just copied, change the following values to `true`:
  * `HMS_DEBUG` (causes uncaught exceptions to be echoed to the browser, instead of caught, logged, and emailed)
  * `SOAP_INFO_TEST_FLAG` (forces use of hard-coded student info in `TestSOAP.php`)
  * `EMAIL_TEST_FLAG` (causes emails to be logged to `phpwebsite/log/email.log`)
* Install [Composer](https://getcomposer.org/doc/00-intro.md)
* Install dependencies with Composer -- from inside the `hms` directory: `./composer.phar install`
* Install [Node.js](https://nodejs.org/download/) (includes npm) (Something like `sudo yum install npm` should work)
  * ~~Bower~~
  * ~~Use npm to install [Bower](http://bower.io): `sudo npm install -g bower`~~
  * ~~Use Bower to install dependencies -- from inside the `hms` directory: `bower install`~~
  * Bower is deprecated. Use Yarn instead.
* Setup the Postgresql [Fuzzy String Matching Extensions](http://www.postgresql.org/docs/9.1/static/fuzzystrmatch.html):
  * `sudo yum install postgresql-contrib`
  * From the psql command line on the Homestead database: `create extension FUZZYSTRMATCH;`
