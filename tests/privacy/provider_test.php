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
 * @copyright  2022 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_airtelafrica\privacy;

use core_privacy\local\metadata\collection;
use paygw_airtelafrica\privacy\provider;
use core_privacy\local\request\writer;

/**
 * Privacy provider test for enrol_paypal.
 *
 * @package    paygw_airtelafrica
 * @copyright  2022 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider_test extends \core_privacy\tests\provider_testcase {

    /**
     * Test for provider::get_metadata().
     * @covers \paygw_airtelafrica\privacy\provider
     */
    public function test_provider() {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $account = $generator->get_plugin_generator('core_payment')->create_payment_account(['gateways' => 'airtelafrica']);
        $user = $generator->create_user();
        $id = $generator->get_plugin_generator('core_payment')->create_payment(
            ['accountid' => $account->get('id'), 'amount' => 1, 'gateway' => 'airtelafrica', 'userid' => $user->id]);
        $this->assertEquals('privacy:metadata', provider::get_reason());
        // TODO: add a payment.
        $newrec = new \stdClass();
        $newrec->paymentid = $id;
        $newrec->pp_orderid = 'fake order';
        $DB->insert_record('paygw_airtelafrica', $newrec);
        $newrec = new \stdClass();
        $newrec->id = $id;
        $this->assertEquals(1, $DB->count_records('paygw_airtelafrica', []));
        provider::export_payment_data(\context_system::instance(), ['course'], $newrec);
        $this->assertEmpty(provider::delete_data_for_payment_sql("$id", []));
        $this->assertEquals(0, $DB->count_records('paygw_airtelafrica', []));
    }
}
