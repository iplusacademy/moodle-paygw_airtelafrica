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
 * Step definitions related to  Airtel Africa payment callback.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.
// For that reason, we can't even rely on $CFG->admin being available here.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;
use core_payment\helper;

/**
 * Step definitions related to Airtel Africa payment callback.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_paygw_airtelafrica extends behat_base {

    /**
     * Create a fake local callback function because the behat environment is not publicly accessible.
     * @Then I call airtel callback
     */
    public function i_call_airtel_callback() {
        global $DB;

        $uid = $this->get_session_user()->id;
        $acc = $DB->get_record('payment_accounts', []);
        $enr = $DB->get_record('enrol', ['enrol' => 'fee']);
        if ($pay = $DB->get_record('paygw_airtelafrica', [])) {
            $DB->set_field('paygw_airtelafrica', 'timecompleted', time());
            $DB->set_field('paygw_airtelafrica', 'transactionid', '666666666');
            if ($pay->moneyid != '666666666') {
                $DB->set_field('paygw_airtelafrica', 'moneyid', '666666666');
                $payid = helper::save_payment($acc->id, 'enrol_fee', 'fee', $enr->id, $uid, 5000, 'UGX', 'paygw_airtelafrica');
                helper::deliver_order('enrol_fee', 'fee', $enr->id, $payid, $uid);
                $this->getSession()->wait(1000);
            } else {
                $this->getSession()->wait(1000);
                $this->getSession()->getDriver()->reload();
            }
        } else {
            $data = new \stdClass;
            $data->paymentid = $enr->id;
            $data->userid = $uid;
            $data->transactionid = '666666666';
            $data->timecreated = time() - 1;
            $DB->insert_record('paygw_airtelafrica', $data);
        }
    }
}
