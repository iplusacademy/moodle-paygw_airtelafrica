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
class airtel_uganda_useless_test extends \advanced_testcase {

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
     * Test using datasource for Airtel Uganda payment
     * @param string $input
     * @param string $output
     * @covers \paygw_airtelafrica\airtel_helper
     * @dataProvider provide_user_data
     */
    public function test_with_dataprovider(string $input, string $output) {
        $generator = $this->getDataGenerator();
        $account = $generator->get_plugin_generator('core_payment')->create_payment_account(['gateways' => 'airtelafrica']);
        $course = $generator->create_course();
        $user = $generator->create_user(['country' => 'UG', 'phone2' => $this->phone]);
        $data = ['courseid' => $course->id, 'customint1' => $account->get('id'), 'cost' => 66, 'currency' => 'EUR', 'roleid' => 5];
        $feeplugin = enrol_get_plugin('fee');
        $feeplugin->add_instance($course, $data);
        $this->setUser($user);
        $this->assertEquals($input, $input);
        $this->assertEquals($output, $output);
    }

    /**
     * Uganda data to test
     * @return string[][]
     */
    public function provide_user_data(): array {
        return [
            'Test 1' => ['Collection Payment with Sufficient funds', 'Positive'],
            'Test 2' => ['Barred Subscriber', 'Negative'],
            'Test 3' => ['Unbarred Subscriber', 'Positive'],
            'Test 4' => ['Wrong PIN', 'Negative'],
            'Test 5' => ['Push Payment from Airtel Money - Insufficient Funds', 'Negative'],
            'Test 6' => ['Amount more than the defined AML limit', 'Negative'],
            'Test 7' => ['Amount less than the defined AML limit', 'Negative'],
            'Test 8' => ['Zero Amount (if not allowed in AML min limit)', 'Negative'],
            'Test 9' => ['Decimal Amount', 'Positive'],
            'Test 10' => ['Negative Amount', 'Negative'],
            'Test 12' => ['Subcriber not register on AM', 'Negative'],
            'Test 13' => ['Collections-Account Balance Enquiry', 'Positive'],
            'Test 14' => ['Collections-Not inputting their (subscribers) PIN', 'Negative'],
            'Test 15' => ['Collections-Callback', 'Negative'],
            'Test 16' => ['Collections-Reports', 'Positive'],
            'Test 17' => ['Collections- Dedicated wallet check -Successful Transaction', 'Positive'],
            'Test 18' => ['Collections- Dedicated wallet check -Failed Transaction', 'Negative']];
    }
}
