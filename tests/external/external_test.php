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
 * Testing externals in payments API
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda Limited
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_airtelafrica\external;

use core_external;

/**
 * Testing externals in payments API
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda Limited
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
final class external_test extends \advanced_testcase {

    /** @var config configuration */
    private $config;

    /** @var string phone */
    private $phone;

    /** @var int feeid. */
    private $feeid;

    /**
     * Tests initial setup.
     *
     */
    protected function setUp(): void {
        global $DB;
        $this->resetAfterTest(true);
        $this->phone = getenv('phone') ? getenv('phone') : '666666666';
        $generator = $this->getDataGenerator();
        $account = $generator->get_plugin_generator('core_payment')->create_payment_account(['gateways' => 'airtelafrica']);
        $accountid = $account->get('id');
        $course = $generator->create_course();
        $user = $generator->create_user(['country' => 'UG', 'phone2' => $this->phone]);
        $data = ['courseid' => $course->id, 'customint1' => $accountid, 'cost' => 66666, 'currency' => 'UGX', 'roleid' => 5];
        $feeplugin = enrol_get_plugin('fee');
        $this->feeid = $feeplugin->add_instance($course, $data);
        $config = new \stdClass();
        $config->clientid = getenv('login') ? getenv('login') : 'fakelogin';
        $config->clientidsb = getenv('login') ? getenv('login') : 'fakelogin';
        $config->brandname = 'maul';
        $config->environment = 'sandbox';
        $config->secret = getenv('secret') ? getenv('secret') : 'fakesecret';
        $config->secretsb = getenv('secret') ? getenv('secret') : 'fakesecret';
        $config->country = 'UG';
        $DB->set_field('payment_gateways', 'config', json_encode($config), []);
        $this->config = (array)$config;
        $this->setUser($user);
    }

    /**
     * Test external config for js.
     * @covers \paygw_airtelafrica\external\get_config_for_js
     */
    public function test_config_for_js(): void {
        $result = get_config_for_js::execute('enrol_fee', 'fee', $this->feeid);
        $this->assertEquals('UG', $result['country']);
    }

    /**
     * Test external transaction_start.
     * @covers \paygw_airtelafrica\external\transaction_start
     */
    public function test_transaction_start(): void {
        $result = transaction_start::execute('enrol_fee', 'fee', $this->feeid);
        $this->assertArrayHasKey('message', $result);
        $result = transaction_start::execute('enrol_fee', 'fee', $this->feeid);
        $this->assertArrayHasKey('message', $result);
    }

    /**
     * Test external transaction complete.
     * @covers \paygw_airtelafrica\airtel_helper
     * @covers \paygw_airtelafrica\external\transaction_start
     * @covers \paygw_airtelafrica\external\transaction_complete
     */
    public function test_transaction_complete(): void {
        $result = transaction_start::execute('enrol_fee', 'fee', $this->feeid);
        $result = transaction_complete::execute('enrol_fee', 'fee', $this->feeid, '666666666');
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
    }

    /**
     * Test complete cycle.
     * @covers \paygw_airtelafrica\airtel_helper
     * @covers \paygw_airtelafrica\external\get_config_for_js
     * @covers \paygw_airtelafrica\external\transaction_start
     * @covers \paygw_airtelafrica\external\transaction_complete
     */
    public function test_complete_cycle(): void {
        if ($this->config['clientidsb'] == 'fakelogin') {
            $this->markTestSkipped('No login credentials');
        }
        $result = get_config_for_js::execute('enrol_fee', 'fee', $this->feeid);
        $clientid = getenv('login') ? getenv('login') : 'fakelogin';
        $currency = $result['currency'];
        $this->assertEquals($clientid, $result['clientid']);
        $this->assertEquals('maul', $result['brandname']);
        $this->assertEquals('UG', $result['country']);
        $this->assertEquals(66666, $result['cost']);
        $this->assertEquals('UGX', $currency);
        $this->assertEquals($this->phone, $result['phone']);
        $this->assertEquals('UG', $result['usercountry']);
        $this->assertNotEmpty($result['reference']);

        $result = transaction_start::execute('enrol_fee', 'fee', $this->feeid);
        if (count($result) > 0) {
            $this->assertNotEmpty($result['transactionid']);
            $this->assertEquals('Your transaction has been successfully processed.', $result['message']);

            $transactionid = $result['transactionid'];
            $result = transaction_complete::execute('enrol_fee', 'fee', $this->feeid, $transactionid, $currency);
            if (count($result) > 0) {
                $this->assertArrayHasKey('success', $result);
                $result = transaction_complete::execute('enrol_fee', 'fee', $this->feeid, $transactionid, $currency);
                if (count($result) > 0) {
                    $this->assertArrayHasKey('success', $result);
                }
            }
        }
    }

    /**
     * Test request log.
     * @covers \paygw_airtelafrica\event\request_log
     * @covers \paygw_airtelafrica\airtel_helper
     */
    public function test_request_log(): void {
        global $DB;
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $this->setUser($user);
        $configs = $DB->get_records('payment_gateways');
        $config = reset($configs);
        $config = json_decode($config->config);
        \paygw_airtelafrica\event\request_log::get_name();
        $arr = [
            'context' => \context_system::instance(),
            'relateduserid' => $user->id,
            'other' => [
                'currentcy' => 'USD',
                'amount' => 66666,
                'orderId' => 20,
                'paymentId' => 333,
            ],
        ];
        $event = \paygw_airtelafrica\event\request_log::create($arr);
        $event->trigger();
        $event->get_description();
    }

    /**
     * Test payable.
     * @covers \paygw_airtelafrica\external\get_config_for_js
     */
    public function test_payable(): void {
        global $CFG;
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $course = $generator->create_course();
        $feeplugin = enrol_get_plugin('fee');
        $this->setUser($user);
        $paygen = $generator->get_plugin_generator('core_payment');
        $account = $paygen->create_payment_account(['gateways' => 'airtelafrica']);
        $accountid = $account->get('id');
        $data = ['courseid' => $course->id, 'customint1' => $accountid, 'cost' => 66666, 'currency' => 'EUR', 'roleid' => 5];
        $this->feeid = $feeplugin->add_instance($course, $data);

        $paymentid = $paygen->create_payment(['accountid' => $accountid, 'amount' => 10, 'userid' => $user->id]);
        $payable = \enrol_fee\payment\service_provider::get_payable('fee', $this->feeid);
        $this->assertEquals($accountid, $payable->get_account_id());
        $this->assertEquals(66666, $payable->get_amount());
        $this->assertEquals('EUR', $payable->get_currency());
        $successurl = \enrol_fee\payment\service_provider::get_success_url('fee', $this->feeid);
        $this->assertEquals($CFG->wwwroot . '/course/view.php?id=' . $course->id, $successurl->out(false));
        $account = new \core_payment\account($payable->get_account_id());

        \enrol_fee\payment\service_provider::deliver_order('fee',  $this->feeid, $paymentid, $user->id);
        $context = \context_course::instance($course->id);
        $this->assertTrue(is_enrolled($context, $user));
        $this->assertTrue(user_has_role_assignment($user->id, 5, $context->id));
    }
}
