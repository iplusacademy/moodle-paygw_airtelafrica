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
 * Testing callback in Airtel Africa payments API
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_airtelafrica;

/**
 * Testing callback in Airtel Africa payments API
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class callback_test extends \advanced_testcase {

    /** @var \core_payment\account account */
    private $account;

    /**
     * Setup function.
     */
    protected function setUp(): void {
        $this->resetAfterTest(true);
        set_config('country', 'UG');
        $generator = $this->getDataGenerator()->get_plugin_generator('core_payment');
        $this->account = $generator->create_payment_account(['gateways' => 'airtelafrica']);
    }

    /**
     * Test callback.
     * @coversNothing
     */
    public function test_callback() {
        global $CFG;
        require_once($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');
        $client = new \GuzzleHttp\Client();
        $authdata = ['client_id' => 'fakeclientid', 'client_secret' => 'fakesecret', 'grant_type' => 'client_credentials'];
        $headers = ['Content-Type' => 'application/json'];
        $url = 'https://test.ewallah.net/payment/gateway/airtelafrica/callback.php';
        $response = $client->request('POST', $url, ['headers' => $headers, 'json' => $authdata]);
        $result = json_decode($response->getBody()->getContents(), true);
        $this->assertEmpty($result);
    }

    /**
     * Test continue.
     * @coversNothing
     */
    public function test_continue() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');
        $generator = $this->getDataGenerator();
        $account = $generator->get_plugin_generator('core_payment')->create_payment_account(['gateways' => 'airtelafrica']);
        $course = $generator->create_course();
        $user = $generator->create_user(['country' => 'UG', 'phone2' => '666666666']);
        $accountid = $account->get('id');
        $data = ['courseid' => $course->id, 'customint1' => $accountid, 'cost' => 6666, 'currency' => 'UGX', 'roleid' => 5];
        $feeplugin = enrol_get_plugin('fee');
        $feeid = $feeplugin->add_instance($course, $data);
        $config = new \stdClass();
        $config->clientid = getenv('login') ? getenv('login') : 'fakelogin';
        $config->clientidsb = getenv('login') ? getenv('login') : 'fakelogin';
        $config->brandname = 'maul';
        $config->environment = 'sandbox';
        $config->secret = getenv('secret') ? getenv('secret') : 'fakesecret';
        $config->secretsb = getenv('secret') ? getenv('secret') : 'fakesecret';
        $config->country = 'UG';
        $DB->set_field('payment_gateways', 'config', json_encode($config), []);
        $this->setUser($user);

        $client = new \GuzzleHttp\Client();
        $data = ['itemid' => $feeid, 'reference' => 'course33333'];
        $url = 'https://test.ewallah.net/payment/gateway/airtelafrica/continue.php';
        $response = $client->request('POST', $url, ['form_params' => $data]);
        $result = json_decode($response->getBody()->getContents(), true);
        $this->assertEmpty($result);
    }
}


