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
 * Handles callback received from Airtel Africa
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_DEBUG_DISPLAY', true);

// @codingStandardsIgnoreLine
require_once(__DIR__ . '/../../../config.php');

global $CFG, $DB;
require_once($CFG->dirroot . '/course/lib.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handles callbacks done by Airtel Africa.
    if ($response = json_decode(file_get_contents('php://input'), true)) {
        $gateway = 'airtelafrica';
        $table = 'paygw_airtelafrica';
        $transaction = \paygw_airtelafrica\airtel_helper::array_helper('transaction', $response);
        if ($transaction) {
            $transactionid = \paygw_airtelafrica\airtel_helper::array_helper('id', $transaction) ?? '';
            $cond = ['transactionid' => $transactionid];
            if ($transactionid != '' && $DB->record_exists($table, $cond)) {
                $payrec = $DB->get_record($table, $cond);
                $msg = \paygw_airtelafrica\airtel_helper::array_helper('message', $transaction) ?? 'Unknown';
                $mid = \paygw_airtelafrica\airtel_helper::array_helper('airtel_money_id', $transaction) ?? '';
                $courseid = $DB->get_field('enrol', 'courseid', ['enrol' => 'fee', 'id' => $payrec->paymentid]);
                $eventargs = [
                    'context' => \context_course::instance($courseid),
                    'userid' => $payrec->userid,
                    'other' => ['message' => $msg, 'id' => $tid, 'airtel_money_id' => $mid]];
                \paygw_airtelafrica\event\request_log::create($eventargs)->trigger();
                $conf = \core_payment\helper::get_gateway_configuration('enrol_fee', 'fee', $payrec->paymentid, $gateway);
                $helper = new \paygw_airtelafrica\airtel_helper($conf);
                $payable = \core_payment\helper::get_payable('enrol_fee', 'fee', $payrec->paymentid);
                $currency = $payable->get_currency();
                $result = $helper->transaction_enquiry($transactionid, $currency);
                $status = \paygw_airtelafrica\airtel_helper::array_helper('status', $result);
                $data = \paygw_airtelafrica\airtel_helper::array_helper('data', $result);
                if ($status && $data && $status['code'] == '200' && $status['success']) {
                    $transaction = \paygw_airtelafrica\airtel_helper::array_helper('transaction', $data);
                    if ($transaction && $transaction['status'] == 'TS') {
                        $surcharge = \core_payment\helper::get_gateway_surcharge($gateway);
                        $amount = (int)\core_payment\helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);
                        $DB->set_field('paygw_airtelafrica', 'timecompleted', time(), $cond);
                        $DB->set_field('paygw_airtelafrica', 'moneyid', $mid, $cond);
                        try {
                            $paymentid = \core_payment\helper::save_payment(
                                $payable->get_account_id(),
                                'enrol_fee',
                                'fee',
                                $transactionid,
                                $payrec->userid,
                                $amount,
                                $currency,
                                $gateway);
                            \core_payment\helper::deliver_order('enrol_fee', 'fee', $transactionid, $paymentid, $payrec->userid);
                        } catch (Exception $e) {
                            die($e->getMessage());
                        }
                    }
                }
            }
        }
    }
}
die();
