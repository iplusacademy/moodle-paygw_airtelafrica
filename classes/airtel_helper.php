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
 * Contains helper class to work with Airtel Africa REST API.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_airtelafrica;

use curl;
use stdClass;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');


/**
 * Contains helper class to work with Airtel Africa REST API.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class airtel_helper {

    /**
     * @var string The base API URL
     */
    public $airtelurl;

    /**
     * @var string Client ID
     */
    private $clientid;

    /**
     * @var string Airtel Africa App secret
     */
    private $secret;

    /**
     * @var string The country where Airtel Africa client is located
     */
    private $country;

    /**
     * @var string The oath bearer token
     */
    public $token;

    /**
     * @var boolean testing
     */
    public $testing;

    /**
     * helper constructor.
     *
     * @param string $clientid The client id.
     * @param string $secret Airtel Africa secret.
     * @param string $country Airtel Africa location.
     * @param string $sandbox Whether we are working with the sandbox environment or not.
     */
    public function __construct(
        string $clientid, string $secret, string $country = 'UG', string $sandbox = 'sandbox') {
        $this->clientid = $clientid;
        $this->secret = $secret;
        $this->airtelurl = self::get_baseurl($sandbox);
        $this->country = $country;
        $this->testing = ((defined('PHPUNIT_TEST') && PHPUNIT_TEST) || defined('BEHAT_SITE_RUNNING'));
    }

    /**
     * Which url should be used.
     *
     * @param string $sandbox
     * @return string
     */
    private static function get_baseurl(string $sandbox): string {
        return $sandbox == 'sandbox' ? 'https://openapiuat.airtel.africa/' : 'https://openapi.airtel.africa/';
    }

    /**
     * Are we testing?
     *
     * @param string $id
     * @return bool
     */
    private function is_testing(string $id): bool {
        return $this->testing && $id === '66666666';
    }

    /**
     * Collection API: Payments - USSD Push.
     *
     * @param int $transactionid
     * @param string $reference
     * @param float $amount
     * @param string $currency
     * @param string $userphone
     * @param string $usercountry
     * @return array Formatted API response.
     */
    public function request_payment(
        int $transactionid, string $reference, float $amount, string $currency, string $userphone, string $usercountry): array {
        if ($this->is_testing($userphone)) {
            $result = [
                'data' => [
                    'transaction' => ['id' => '8334msn88', 'status' => 'SUCCESS']],
                    'status' => [
                        'code' => '200',
                        'message' => 'SUCCESS',
                        'result_code' => 'ESB000010',
                        'response_code' => 'DP00800001006',
                        'success' => true]];
        }

        $location = 'merchant/v1/payments/';
        $headers = ['X-Country' => $this->country, 'X-Currency' => $currency];
        $data = [
            'reference' => $reference,
            'subscriber' => [
                'country' => strtoupper($usercountry),
                'currency' => $currency,
                'msisdn' => $userphone],
            'transaction' => [
                'amount' => $amount,
                'country' => $this->country,
                'currency' => $currency,
                'id' => $transactionid]];
        return $this->is_testing($userphone) ? $result : $this->request_post($location, $data, $headers);
    }

    /**
     * Collection API: Refund.
     *
     * @param string $airtelmoneyid
     * @param string $currency
     * @return array Formatted API response.
     */
    public function make_refund(string $airtelmoneyid, string $currency): array {
        if ($this->is_testing($airtelmoneyid)) {
            $result = [
                'data' => [
                    'transaction' => [
                        'airtel_money_id' => 'CI210104.1549.C00029',
                        'status' => 'SUCCESS']],
                'status' => [
                    'code' => '200',
                    'message' => 'SUCCESS',
                    'result_code' => 'ESB000010',
                    'success' => true]];
        }
        $headers = ['X-Country' => $this->country, 'X-Currency' => $currency];
        $data = ['transaction' => ['airtel_money_id' => $airtelmoneyid]];
        return $this->is_testing($airtelmoneyid) ? $result : $this->request_post('standard/v1/payments/refund', $data, $headers);
    }

    /**
     * Collection API: transaction enquiry
     *
     * @param string $transid
     * @param string $currency
     * @return array Formatted API response.
     */
    public function transaction_enquiry(string $transid, string $currency): array {
        if ($this->is_testing($transid)) {
            $result = [
                'data' => [
                    'transaction' => [
                           'airtel_money_id' => 'C3648.00993.538XX.XX67',
                           'id' => '8334msn88',
                           'message' => 'success',
                           'status' => 'TS']],
                'status' => [
                    'code' => 200,
                    'message' => 'SUCCESS',
                    'result_code' => 'ESB000010',
                    'response_code' => 'DP00800001006',
                    'success' => true]];
        }
        $headers = ['Accept' => '*/*', 'X-Country' => $this->country, 'X-Currency' => $currency];
        return $this->is_testing($transid) ? $result : $this->request_post("standard/v1/payments/$transid", [], $headers, 'GET');
    }

    /**
     * Captures an authorized payment, by ID.
     *
     * @param string $location
     * @param array $data
     * @param array $headers
     * @param string $verb
     * @return array Decoded API response.
     */
    private function request_post(
        string $location, array $data, array $headers = [], string $verb = 'POST'): array {
        $decoded = $result = '';
        $client = new \GuzzleHttp\Client();
        if ($this->token == '') {
            $authdata = ['client_id' => $this->clientid, 'client_secret' => $this->secret, 'grant_type' => 'client_credentials'];
            try {
                $response = $client->request(
                    'POST',
                    $this->airtelurl . 'auth/oauth2/token',
                    ['headers' => ['Content-Type' => 'application/json'], 'json' => $authdata]);
                $result = json_decode($response->getBody()->getContents(), true);
                $this->token = array_key_exists('access_token', $result) ? $result['access_token'] : '';
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $this->token = '';
                return [];
            }
        }
        $headers = array_merge($headers, ['Content-Type' => 'application/json', 'Authorization' => 'Bearer   ' . $this->token]);
        try {
            $response = $client->request($verb, $this->airtelurl . $location, ['headers' => $headers, 'json' => $data]);
            $result = $response->getBody()->getContents();
        } catch (\Exception $e) {
            // TODO: Bad gateway.
            $result = $e->getMessage();
        } finally {
            $decoded = json_decode($result, true);
            // Trigger an event.
            $eventargs = ['context' => \context_system::instance(),
                'other' => ['verb' => $verb, 'location' => $location, 'token' => $this->token, 'result' => $decoded]];
            $event = \paygw_airtelafrica\event\request_log::create($eventargs);
            $event->trigger();
        }
        return $decoded;
    }

    /**
     * Transaction code
     * @param string $code
     * @return string
     */
    public static function ta_code(string $code): string {
        $returns = [
            'TF' => 'Transaction Failed',
            'TS' => 'Transaction Success',
            'TA' => 'Transaction Ambiguous',
            'TIP' => 'Transaction in Progress'];
        return array_key_exists($code, $returns) ? $returns[$code] : '';
    }

    /**
     * Esb code
     * @param string $code
     * @return string
     */
    public static function esb_code(string $code): string {
        $returns = [
            'ESB000001' => 'Something went wrong.',
            'ESB000004' => 'An error occurred while initiating the payment.',
            'ESB000008' => 'Field validation.',
            'ESB000011' => 'Transaction failed',
            'ESB000010' => 'Your transaction has been successfully processed.',
            'ESB000014' => 'An error occurred while fetching the transaction status.',
            'ESB000033' => 'Invalid MSISDN Length. MSISDN Length should be ',
            'ESB000034' => 'Invalid Country Name ',
            'ESB000035' => 'Invalid Currency Code ',
            'ESB000036' => 'Invalid MSISDN Length. MSISDN Length should be ? and should start with 0. ',
            'ESB000039' => 'Vendor is not configured to do transaction in the country. ',
            'ESB000041' => 'External Transaction ID already exists.',
            'ESB000045' => 'No Transaction Found With Provided Transaction Id.'];
        return array_key_exists($code, $returns) ? $returns[$code] : '';
    }

    /**
     * Return code
     * @param string $code
     * @return string
     */
    public static function dp_code(string $code): string {
        $returns = [
            'DP00800001001' => 'Valid pin',
            'DP00800001002' => 'Invalid pin',
            'DP00800001003' => 'Exceeds balance',
            'DP00800001004' => 'Invalid Amount',
            'DP00800001005' => 'User did not enter pin',
            'DP00800001006' => 'In process',
            'DP00800001007' => 'Not enough balance',
            'DP00800001008' => 'Refused',
            'DP00800001009' => 'Do not honor',
            'DP00800001010' => 'Transaction not permitted',
            'DP00800001024' => 'Transaction timed out'];
        return array_key_exists($code, $returns) ? $returns[$code] : '';
    }

    /**
     * Router code
     * @param string $code
     * @return string
     */
    public static function rr_code(string $code): string {
        $returns = [
            'ROUTER001' => 'The wallet is not configured.',
            'ROUTER003' => 'Mandatory parameters are missing either in the header or body.',
            'ROUTER005' => 'Country route is not configured.',
            'ROUTER006' => 'Invalid country code provided.',
            'ROUTER007' => 'Not authorized to perform any operations in the provided country.',
            'ROUTER112' => 'Invalid currency provided.',
            'ROUTER114' => 'An error occurred while validating the pin.',
            'ROUTER115' => 'Pin you have entered is incorrect.',
            'ROUTER116' => 'The encrypted value of the pin is incorrect. Kindly re-check the encryption mechanism.',
            'ROUTER117' => 'An error occurred while generating the response.'];
        return array_key_exists($code, $returns) ? $returns[$code] : '';
    }
}
