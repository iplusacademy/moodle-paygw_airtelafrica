<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'paygw_airtelafrica', language 'en'
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda Limited
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['airtelstart'] = 'We sent you a request for payment.</br>
Please complete the payment using your cell phone.</br>
You have 3 minutes to complete this transaction.</br>
The moment we receive a confirmation by Airtel Africa, you will be able to access the course.';
$string['amountmismatch'] = 'The amount you attempted to pay does not match the required fee. Your account has not been debited.';
$string['authorising'] = 'Authorising the payment. Please wait...';
$string['brandname'] = 'Brand name';
$string['brandname_help'] = 'An optional label that overrides the business name for the your account on the Airtel Africa site.';
$string['cannotfetchorderdatails'] = 'Could not fetch payment details from Airtel Africa. Your account has not been debited.';
$string['checkairtelafrica'] = 'Airtel Africa';
$string['check_warning'] = 'HTTP Port should be open for sandbox testing';
$string['check_details'] = 'Airtel Africa needs port 80 for the sandbox';
$string['cleanuptask'] = 'Clean up unfinished Airtel payments task';
$string['clientid'] = 'Client ID';
$string['clientid_help'] = 'The client ID that Airtel Africa generated for your application.';
$string['clientidsb'] = 'Sandbox Client ID';
$string['clientidsb_help'] = 'The sandbox client ID that Airtel Africa generated for your application.';
$string['country'] = 'Country';
$string['country_help'] = 'In which country is this client located';
$string['environment'] = 'Environment';
$string['environment_help'] = 'You can set this to Sandbox if you are using sandbox accounts (for testing purpose only).';
$string['gatewaydescription'] = 'Airtel Africa is an authorised payment gateway provider for processing mobile money.';
$string['gatewayname'] = 'Airtel Africa';
$string['internalerror'] = 'An internal error has occurred. Please contact us.';
$string['live'] = 'Live';
$string['paymentnotcleared'] = 'payment not cleared by Airtel Africa.';
$string['pluginname'] = 'Airtel Africa';
$string['pluginname_desc'] = 'The Airtel Africa plugin allows you to receive payments via Airtel Africa.';
$string['privacy:metadata:paygw_airtelafrica:userid'] = 'The userid of the user.';
$string['privacy:metadata:paygw_airtelafrica:transactionid'] = 'The transactionid of the payment.';
$string['privacy:metadata:paygw_airtelafrica:paymentid'] = 'The payment id of the payment.';
$string['privacy:metadata:paygw_airtelafrica:moneyid'] = 'The Airtel Monay id of the payment.';
$string['privacy:metadata:paygw_airtelafrica:timecreated'] = 'The time the payment was created.';
$string['privacy:metadata:paygw_airtelafrica:timecompleted'] = 'The time the payment was completed.';
$string['privacy:metadata:paygw_airtelafrica'] = 'The Airtel Africa payment gateway stores payment information.';
$string['request_log'] = 'Gateway log';
$string['repeatedorder'] = 'This order has already been processed earlier.';
$string['sandbox'] = 'Sandbox';
$string['secret'] = 'Secret';
$string['secret_help'] = 'The secret that Airtel Africa generated for your application.';
$string['secretsb'] = 'Sandbox secret';
$string['secretsb_help'] = 'The Sandbox secret that Airtel Africa generated for your application.';
$string['unable'] = 'Unable to communicate with Airtel Africa';
$string['validcontinue'] = 'Please wait until we receive confirmation by Aitel, +-30 seconds before you continue.';
$string['validtransaction'] = 'We got a valid transactionid: {$a}';
$string['warning_phone'] = 'Please be sure that this is <strong>your</strong> Mobile phone number and country. You can change the number and country on your <a href="/user/edit.php" title="profile">profile page</a>.</br>
Airtel Africa needs a number <b>without</b> the country code.';
