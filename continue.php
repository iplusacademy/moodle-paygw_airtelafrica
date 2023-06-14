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
require_once(__DIR__ . '/../../../config.php');  // phpcs:ignore
global $CFG, $DB, $USER;
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->libdir . '/filelib.php');

// Keep out casual intruders.
if (empty($_POST) || !empty($_GET)) {
    http_response_code(400);
    throw new moodle_exception('invalidrequest', 'core_error');
}

$gateway = 'airtelafrica';
$table = 'paygw_airtelafrica';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // This is a local check if everything went well.
    require_login();
    if (!confirm_sesskey()) {
        redirect($CFG->wwwroot);
    }

    $data = new stdClass();
    foreach ($_POST as $key => $value) {
        if ($key !== clean_param($key, PARAM_ALPHANUMEXT)) {
            throw new moodle_exception('invalidrequest', 'core_error', '', null, $key);
        }
        if (is_array($value)) {
            throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Unexpected array param: '.$key);
        }
        $data->$key = fix_utf8($value);
    }

    $courseid = $DB->get_field('enrol', 'courseid', ['enrol' => 'fee', 'id' => $data->itemid]);
    $url = new \moodle_url('/course/view.php', ['id' => $courseid]);
    $course = $DB->get_record("course", ['id' => $courseid], '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);
    $PAGE->set_context($context);
    $eventargs = [
        'context' => $context,
        'userid' => $USER->id,
        'other' => ['itemid' => $data->itemid, 'reference' => $data->reference]];
    \paygw_airtelafrica\event\request_log::create($eventargs)->trigger();

    $cond = ['transactionid' => $data->transactionid, 'paymentid' => $data->itemid];
    if ($DB->record_exists($table, $cond)) {
        if ($DB->get_field($table, 'timecompleted', $cond) == 0) {
            redirect($url);
        }
        $payrec = $DB->get_record($table, $cond);
        $config = \core_payment\helper::get_gateway_configuration(
            $data->component, $data->paymentarea, $data->itemid, $gateway);
        $helper = new \paygw_airtelafrica\airtel_helper($config);
        $payable = \core_payment\helper::get_payable($data->component, $data->paymentarea, $data->itemid);
        $currency = $payable->get_currency();
        $surcharge = \core_payment\helper::get_gateway_surcharge($gateway);
        $amount = (int)\core_payment\helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);
        $result = $helper->transaction_enquiry($data->transactionid, $currency);
        $status = \paygw_airtelafrica\airtel_helper::array_helper('status', $result);
        $data = \paygw_airtelafrica\airtel_helper::array_helper('data', $result);
            $trans = 'TIP';
        if ($status && $data && $status['code'] == 200 && $status['success']) {
            $transaction = \paygw_airtelafrica\airtel_helper::array_helper('transaction', $data);
            if ($transaction) {
                $suc = true;
                $trans = $transaction['status'];
                if ($transaction['status'] == 'TS') {
                    $paymentid = \core_payment\helper::save_payment(
                        $payable->get_account_id(),
                        $data->component,
                        $data->paymentarea,
                        $data->transactionid,
                        $payrec->userid,
                        $amount,
                        $currency,
                        $gateway);
                    $record = new \stdClass();
                    $record->paymentid = $paymentid;
                    $record->pp_orderid = $transaction['airtel_money_id'];
                    $suc = $DB->insert_record($table, $record);
                    $suc = $suc && \core_payment\helper::deliver_order(
                        $data->component,
                        $data->paymentarea,
                        $data->transactionid,
                        $paymentid,
                        $payrec->userid);
                }
            }
        }
    }
    redirect($url);
} else {
    die('Nothing to do');
}
