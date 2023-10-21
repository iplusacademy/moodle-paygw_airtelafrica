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
 * Privacy provider tests.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_airtelafrica\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use paygw_airtelafrica\privacy\provider;
use stdClass;

/**
 * Privacy provider test for payment gateway airtelafrica.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider_test extends provider_testcase {

    /** @var stdClass A student. */
    protected $user;

    /** @var stdClass A payment record. */
    protected $payrec;

    /**
     * Basic setup for these tests.
     * @covers \paygw_airtelafrica\privacy\provider
     */
    public function setUp(): void {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $account = $generator->get_plugin_generator('core_payment')->create_payment_account(['gateways' => 'airtelafrica']);
        $this->user = $generator->create_user();
        $id = $generator->get_plugin_generator('core_payment')->create_payment(
            ['accountid' => $account->get('id'), 'amount' => 1, 'gateway' => 'airtelafrica', 'userid' => $this->user->id]);
        $data = new stdClass();
        $data->paymentid = $id;
        $data->userid = $this->user->id;
        $data->transactionid = '666666666';
        $data->moneyid = 'badmoneyid';
        $data->timecreated = time();
        $pid = $DB->insert_record('paygw_airtelafrica', $data);
        $data->id = $pid;
        $this->payrec = $data;
    }

    /**
     * Test returning metadata.
     * @covers \paygw_airtelafrica\privacy\provider
     */
    public function test_get_metadata(): void {
        $collection = new collection('paygw_airtelafrica');
        $this->assertNotEmpty(provider::get_metadata($collection));
    }

    /**
     * Test for provider.
     * @covers \paygw_airtelafrica\privacy\provider
     */
    public function test_provider(): void {
        global $DB;
        $this->assertEquals(1, $DB->count_records('paygw_airtelafrica', []));
        $context = \context_user::instance($this->user->id);
        $contextlist = provider::get_contexts_for_userid($this->user->id);
        $this->assertCount(1, $contextlist);
        $list = new approved_contextlist($this->user, 'paygw_airtelafrica', [$context->instanceid]);
        $this->assertNotEmpty($list);
        provider::delete_data_for_user($list);
        provider::delete_data_for_all_users_in_context($context);
        $user = self::getDataGenerator()->create_user();
        $context = \context_user::instance($user->id);
        $list = new approved_contextlist($user, 'paygw_airtelafrica', [$context->instanceid]);
        $this->assertNotEmpty($list);
        provider::export_payment_data(\context_system::instance(), ['course'], $this->payrec);
        $this->assertEmpty(provider::delete_data_for_payment_sql($this->payrec->paymentid, []));
        $this->assertEquals(0, $DB->count_records('paygw_airtelafrica', []));
    }

    /**
     * Test for remove.
     * @covers \paygw_airtelafrica\privacy\provider
     */
    public function test_remove(): void {
        global $DB;
        provider::export_payment_data(\context_system::instance(), ['course'], $this->payrec);
        $this->assertEmpty(provider::delete_data_for_payment_sql($this->payrec->paymentid, []));
        $this->assertEquals(0, $DB->count_records('paygw_airtelafrica', []));
    }


    /**
     * Check the exporting of payments for a user.
     * @covers \paygw_airtelafrica\privacy\provider
     */
    public function test_export(): void {
        $context = \context_user::instance($this->user->id);
        $this->export_context_data_for_user($this->user->id, $context, 'paygw_airtelafrica');
        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());
        $this->export_all_data_for_user($this->user->id, 'paygw_airtelafrica');
    }

    /**
     * Tests new functions.
     * @covers \paygw_airtelafrica\privacy\provider
     */
    public function test_new_functions(): void {
        $context = \context_user::instance($this->user->id);
        $userlist = new userlist($context, 'paygw_airtelafrica');
        provider::get_users_in_context($userlist);
        $this->assertCount(1, $userlist);

        $scontext = \context_system::instance();
        $userlist = new userlist($scontext, 'paygw_airtelafrica');
        provider::get_users_in_context($userlist);
        $this->assertCount(0, $userlist);

        $approved = new approved_userlist($context, 'paygw_airtelafrica', [$this->user->id]);
        provider::delete_data_for_users($approved);
        $userlist = new userlist($context, 'paygw_airtelafrica');
        provider::get_users_in_context($userlist);
        $this->assertCount(1, $userlist);
    }
}
