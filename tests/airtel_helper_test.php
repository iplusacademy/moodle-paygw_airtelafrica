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
 * Testing generator in payments API
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_airtelafrica;

/**
 * Testing generator in payments API
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class airtel_helper_test extends \advanced_testcase {


    /** @var string phone */
    private $phone;

    /** @var string login */
    private $login;

    /** @var string secret */
    private $secret;

    /** @var string base */
    protected $base = 'https://openapiuat.airtel.africa/';

    /**
     * Setup function- we will create a course and add an assign instance to it.
     */
    protected function setUp(): void {
        $this->resetAfterTest(true);
        set_config('country', 'UG');
        $this->phone = getenv('phone');
        $this->login = getenv('login');
        $this->secret = getenv('secret');
    }

    /**
     * Test Airtel Africa helper
     * @covers \paygw_airtelafrica\airtel_helper
     * @covers \paygw_airtelafrica\event\request_log
     */
    public function test_empty_helper() {
        $helper = new \paygw_airtelafrica\airtel_helper('fake', 'user');
        $this->assertEquals(get_class($helper), 'paygw_airtelafrica\airtel_helper');
        $this->assertEquals('Transaction Success', \paygw_airtelafrica\airtel_helper::ta_code('TS'));
        $this->assertEquals('In process', \paygw_airtelafrica\airtel_helper::dp_code('DP00800001006'));
        $this->assertEquals('Something went wrong.', \paygw_airtelafrica\airtel_helper::esb_code('ESB000001'));
        $this->assertEquals('Invalid currency provided.', \paygw_airtelafrica\airtel_helper::rr_code('ROUTER112'));
        $random = random_int(1000000000, 9999999999);
        try {
            $helper->request_payment($random, "course$random", 1000, 'UGX', '1234567', 'BE');
        } catch (\moodle_exception $e) {
            $this->assertEquals('Exception - Invalid country code provided.', $e->getmessage());
        }
    }

    /**
     * Test manual callback Airtel Africa payment
     * @covers \paygw_airtelafrica\airtel_helper
     * @covers \paygw_airtelafrica\event\request_log
     */
    public function test_callback_manualy() {
        $user = $this->getDataGenerator()->create_user(['country' => 'UG', 'phone2' => '66666666']);
        $this->setUser($user);
        $random = random_int(1000000000, 9999999999);
        $helper = new \paygw_airtelafrica\airtel_helper($this->login, $this->secret);

        $result = $helper->request_payment($random, "course$random", 1000, 'UGX', '66666666', 'UG');
        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals(true, $result['status']['success']);

        $result = $helper->transaction_enquiry('66666666', 'UGX');
        $this->assertEquals('TS', $result['data']['transaction']['status']);
        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals(true, $result['status']['success']);

        $result = $helper->make_refund('66666666', 'UGX');
        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals(true, $result['status']['success']);
    }


    /**
     * Test manual Airtel Africa payment
     * @covers \paygw_airtelafrica\airtel_helper
     * @covers \paygw_airtelafrica\event\request_log
     */
    public function test_airtel_manualy() {
        if ($this->login == '') {
            $this->markTestSkipped('No login credentials');
        }
        $user = $this->getDataGenerator()->create_user(['country' => 'UG', 'phone1' => $this->phone]);
        $this->setUser($user);
        $random = random_int(1000000000, 9999999999);
        $helper = new \paygw_airtelafrica\airtel_helper($this->login, $this->secret, 'UG');

        // Correct pin.
        $result = $helper->request_payment($random, "course$random", 100, 'UGX', $this->phone, 'UG');
        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals(1, $result['status']['success']);
        $transactionid = $result['data']['transaction']['id'];

        $this->ping_payment((int)$transactionid);

        // Incorrect pin.
        $random = random_int(1000000000, 9999999999);
        $result = $helper->request_payment($random, "course$random", 100, 'UGX', $this->phone, 'UG');
        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals(1, $result['status']['success']);
        $transactionid = $result['data']['transaction']['id'];
        $this->ping_payment((int)$transactionid);
    }

    /**
     * Test Airtel Africa payment
     * @covers \paygw_airtelafrica\airtel_helper
     * @covers \paygw_airtelafrica\event\request_log
     */
    public function test_airtel_payment() {
        if ($this->login == '') {
            $this->markTestSkipped('No login credentials');
        }
        $user = $this->getDataGenerator()->create_user(['country' => 'UG', 'phone2' => $this->phone]);
        $this->setUser($user);
        $random = random_int(1000000000, 9999999999);
        $helper = new \paygw_airtelafrica\airtel_helper($this->login, $this->secret, 'UG');

        // Make payment.
        $result = $helper->request_payment($random, "course$random", 66, 'UGX', $this->phone, 'UG');
        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals(1, $result['status']['success']);

        // Get transaction.
        $transactionid = $result['data']['transaction']['id'];
        $result = $helper->transaction_enquiry($transactionid, 'UGX');
        $this->assertEquals('TIP', $result['data']['transaction']['status']);
        $this->assertEquals('DP00800001006', $result['status']['response_code']);
        $this->assertEquals(200, $result['status']['code']);
        $this->assertTrue($result['status']['success']);
        $this->assertEquals('ESB000010', $result['status']['result_code']);

        // Cancel payment.
        $helper = new \paygw_airtelafrica\airtel_helper($this->login, $this->secret, 'UG');
        $result = $helper->make_refund('66666666', 'UGX');
        $this->assertEquals(200, $result['status']['code']);

        $user = $this->getDataGenerator()->create_user(['country' => 'UG']);
        $this->setUser($user);
        try {
            $helper->request_payment($random, "course$random", 1000, 'UGX', '1234567', 'BE');
        } catch (\moodle_exception $e) {
            $this->assertEquals('Exception - Invalid country code provided.', $e->getmessage());
        }
    }

    /**
     * Test callback
     * @covers \paygw_airtelafrica\airtel_helper
     */
    public function test_callback() {
        // TODO: we should use an external server to test out the callback.
        $location = 'https://test.ewallah.net/payment/gateway/airtelafrica/callback.php';
        $data = ['transaction' => [
           'id' => 'BBZMiscxy',
           'message' => 'Paid UGX 5,000 to MAUL, Charge UGX 140, Trans ID MP210603.1234.L06941.',
           'status_code' => 'TS',
           'airtel_money_id' => 'MP210603.1234.L06941']];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_PROXY, $location);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_VERBOSE, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_URL, $location);
        $result = curl_exec($curl);
        $this->assertStringNotContainsString('MAUL', $result);
        @curl_close($curl);
    }

    /**
     * Ping payment
     * @param string $transactionid
     */
    private function ping_payment(string $transactionid) {
        if ($transactionid == '0') {
            throw new \moodle_exception('Invalid transaction id.');
        }
        $helper = new \paygw_airtelafrica\airtel_helper($this->login, $this->secret, 'UG');
        for ($i = 1; $i < 11; $i++) {
            $result = $helper->transaction_enquiry($transactionid, 'UGX');
            if ($transactionid > 0 && array_key_exists('status', $result) && array_key_exists('data', $result)) {
                $response = $result['status']['response_code'];
                if ($response == 'DP00800001001') {
                    $cancelid = $result['data']['transaction']['airtel_money_id'];
                    // Cancel payment.
                    $cancelresult = $helper->make_refund((int)$cancelid, 'UGX');
                    $this->assertNotEquals(500, $cancelresult['status']['code']);
                    break;
                }
                $response = $result['data']['transaction']['status'];
                if ($response == 'TF' || $response == 'TS') {
                    break;
                }
                sleep(15);
            }
        }
    }
}
