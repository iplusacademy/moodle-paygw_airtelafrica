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

use core_payment\helper;
use core_text;
use curl;

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
    private $airtelurl;

    /**
     * @var string Client ID
     */
    public $clientid;

    /**
     * @var string Airtel Africa App secret
     */
    private $secret;

    /**
     * @var string The country where Airtel Africa client is located
     */
    private $country;

    /**
     * @var bool Sandbox
     */
    private $sandbox;

    /**
     * @var string The oath bearer token
     */
    public $token;

    /**
     * @var boolean testing
     */
    public $testing;

    /**
     * Helper constructor.
     *
     * @param array $config The gateway configuration.
     * @param string $country Airtel Africa location.
     */
    public function __construct(array $config, string $country = 'UG') {
        $this->sandbox = (bool)strtolower($config['environment']) == 'sandbox';
        $this->clientid = $config[$this->sandbox ? 'clientidsb' : 'clientid'];
        $this->secret = $config[$this->sandbox ? 'secretsb' : 'secret'];
        $this->airtelurl = $this::get_baseurl();
        $this->country = array_key_exists('country', $config) ? $config['country'] : $country;
        $this->testing = (defined('BEHAT_SITE_RUNNING') || (defined('PHPUNIT_TEST') && PHPUNIT_TEST));
        if ($this->testing) {
            $this->sandbox = true;
        }
    }

    /**
     * Which url should be used.
     *
     * @return string
     */
    private function get_baseurl(): string {
        return $this->sandbox ? 'https://openapiuat.airtel.africa/' : 'https://openapi.airtel.africa/';
    }

    /**
     * Production or sandbox.
     *
     * @return string
     */
    private function get_base(): string {
        return $this->sandbox ? 'sandbox' : 'production';
    }

    /**
     * Are we testing?
     *
     * We assume there is no user with telephone number 666666666
     *
     * @param string $id
     * @return bool
     */
    private function is_testing(string $id): bool {
        return defined('BEHAT_SITE_RUNNING') ? $id == '666666666' : $this->testing && $id == '666666666';
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
                    'transaction' => [
                        'id' => '666666666',
                        'status' => 'SUCCESS',
                    ],
                ],
                'status' => [
                    'code' => '200',
                    'message' => 'SUCCESS',
                    'result_code' => 'ESB000010',
                    'response_code' => 'DP00800001006',
                    'success' => true,
                ],
            ];
        }

        $location = 'merchant/v1/payments/';
        $headers = ['X-Country' => $this->country, 'X-Currency' => $currency];
        $data = [
            'reference' => substr($reference, 0, 64),
            'subscriber' => [
                'country' => strtoupper($usercountry),
                'currency' => $currency,
                'msisdn' => $userphone,
            ],
            'transaction' => [
                'amount' => $amount,
                'country' => $this->country,
                'currency' => $currency,
                'id' => $transactionid,
            ],
        ];
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
                        'status' => 'SUCCESS',
                    ],
                ],
                'status' => [
                    'code' => '200',
                    'message' => 'SUCCESS',
                    'result_code' => 'ESB000010',
                    'success' => true,
                ],
            ];
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
                       'id' => '666666666',
                       'message' => 'success',
                       'status' => 'TS',
                    ],
                ],
                'status' => [
                    'code' => '200',
                    'message' => 'SUCCESS',
                    'result_code' => 'ESB000010',
                    'response_code' => 'DP00800001006',
                    'success' => true,
                ],
            ];
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
    private function request_post(string $location, array $data, array $headers = [], string $verb = 'POST'): array {
        $decoded = [];
        $result = '';
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
            } catch (\Exception $e) {
                mtrace_exception($e);
                return [];
            }
        }
        $headers = array_merge($headers, ['Content-Type' => 'application/json', 'Authorization' => 'Bearer   ' . $this->token]);
        try {
            $response = $client->request($verb, $this->airtelurl . $location, ['headers' => $headers, 'json' => $data]);
            $result = $response->getBody()->getContents();
        } catch (\Exception $e) {
            mtrace_exception($e);
            $result = $e->getMessage();
        } finally {
            $decoded = json_decode($result, true);
            // Trigger an event.
            $eventargs = [
                'context' => \context_system::instance(),
                'other' => [
                    'verb' => $verb,
                    'location' => $this->get_base() . ':' . $location,
                    'token' => $this->token,
                    'result' => $decoded,
                ],
            ];
            $event = \paygw_airtelafrica\event\request_log::create($eventargs);
            $event->trigger();
            // Uncomment folowing line to have the data returned by Airtel.
            // mtrace($result);.
        }

        return $decoded ?? [];
    }

    /**
     * Enrol the user
     *
     * @param string $transactionid
     * @param int $itemid
     * @param string $component Name of the component that the itemid belongs to
     * @param string $area The payment area
     * @return string
     */
    public function enrol_user(string $transactionid, int $itemid, string $component, string $area): string {
        global $DB;
        // We assume the transaction failed.
        $trans = 'TF';
        $cond = ['transactionid' => $transactionid, 'paymentid' => $itemid];
        if ($rec = $DB->get_record('paygw_airtelafrica', $cond)) {
            if ($rec->timecompleted == 0) {
                $this->token = $rec->moneyid;
                $payable = helper::get_payable($component, $area, $itemid);
                $currency = $payable->get_currency();
                $result = $this->transaction_enquiry($transactionid, $currency);
                $status = self::array_helper('status', $result);
                $data = self::array_helper('data', $result);
                if ($status && $data && $status['code'] == '200') {
                    $transaction = self::array_helper('transaction', $data);
                    if ($transaction) {
                        $trans = $transaction['status'];
                        // If the payment was successul.
                        if ($trans == 'TS') {
                            // We have a succesfull transaction.
                            $moneyid = self::array_helper('airtel_money_id', $transaction);
                            $surcharge = helper::get_gateway_surcharge('airtelafrica');
                            $amount = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);
                            $payid = $payable->get_account_id();
                            $saved = helper::save_payment(
                                $payid,
                                $component,
                                $area,
                                $itemid,
                                $rec->userid,
                                $amount,
                                $currency,
                                'airtelafrica');
                            helper::deliver_order($component, $area, $itemid, $saved, $rec->userid);
                            $DB->set_field('paygw_airtelafrica', 'timecompleted', time(), $cond);
                            $DB->set_field('paygw_airtelafrica', 'moneyid', $moneyid, $cond);
                        }
                    }
                }
            }
        }
        return $trans;
    }

    /**
     * Transaction code
     * @param string $code
     * @return string
     */
    public static function esb_code(string $code): string {
        $returns = [
            'ESB000001' => 'Something went wrong.',
            'ESB000004' => 'An error occurred while initiating the payment.',
            'ESB000008' => 'Field validation.',
            'ESB000011' => 'Transaction failed.',
            'ESB000010' => 'Your transaction has been successfully processed.',
            'ESB000014' => 'An error occurred while fetching the transaction status.',
            'ESB000033' => 'Invalid MSISDN Length. MSISDN Length should be ',
            'ESB000034' => 'Invalid Country Name.',
            'ESB000035' => 'Invalid Currency Code.',
            'ESB000036' => 'Invalid MSISDN Length. MSISDN Length should be ? and should start with 0.',
            'ESB000039' => 'Vendor is not configured to do transaction in the country.',
            'ESB000041' => 'External transaction ID already exists.',
            'ESB000045' => 'No transaction found with provided transaction Id.',
        ];
        return array_key_exists($code, $returns) ? $returns[$code] : '';
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
            'TIP' => 'Transaction in Progress',
        ];
        return array_key_exists($code, $returns) ? $returns[$code] : '';
    }

    /**
     * Return code
     *
     * Collection api DP008 specific codes.
     * @param string $code
     * @return string
     */
    public static function dp_code(string $code): string {
        $returns = [
            'DP00800001000' => 'Transaction ambigous',
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
            'DP00800001024' => 'Transaction timed out',
            'DP00800001025' => 'Transaction not found',
            'DP00800001029' => 'Transaction expired',
        ];
        return array_key_exists($code, $returns) ? $returns[$code] : '';
    }

    /**
     * Array helper.
     *
     * @param string $key
     * @param array $arr
     * @return array||bool
     */
    public static function array_helper(string $key, array $arr) {
        return (array_key_exists($key, $arr)) ? $arr[$key] : false;
    }

    /**
     * User data helper.
     *
     * @return array
     */
    public function current_user_data() {
        global $USER;
        $arr = [];
        $user = \core_user::get_user($USER->id, 'id, phone1, phone2, country');
        if ($user) {
            $phone = $user->phone2 == '' ? $user->phone1 : $user->phone2;
            $phone = preg_replace("/[^0-9]/", '', $phone);
            if (strlen($phone) > 5) {
                $arr = ['id' => $user->id, 'country' => strtoupper($user->country), 'phone' => $phone];
            }
        }
        return $arr;
    }
}
