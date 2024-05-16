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
 * @copyright  Medical Access Uganda Limited (e-learning.medical-access.org)
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_DEBUG_DISPLAY', true);

// @codingStandardsIgnoreLine
require_once(__DIR__ . '/../../../config.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
} else {
    // Handles callbacks done by Airtel Africa.
    if ($response = json_decode(file_get_contents('php://input'), true)) {
        // Sample data>
        $gateway = 'airtelafrica';
        $table = 'paygw_airtelafrica';
        $transaction = \paygw_airtelafrica\airtel_helper::array_helper('transaction', $response);
        if ($transaction) {
            $transactionid = \paygw_airtelafrica\airtel_helper::array_helper('id', $transaction) ?? '';
            $succes = \paygw_airtelafrica\airtel_helper::array_helper('status_code', $transaction) ?? 'TF';
            $cond = ['transactionid' => $transactionid];
            if ($succes == 'TS' && $transactionid != '' && $DB->record_exists($table, $cond)) {
                $payrec = $DB->get_record($table, $cond);
                $msg = \paygw_airtelafrica\airtel_helper::array_helper('message', $transaction) ?? 'Unknown';
                $mid = \paygw_airtelafrica\airtel_helper::array_helper('airtel_money_id', $transaction) ?? '';
                $courseid = $DB->get_field('enrol', 'courseid', ['enrol' => $payrec->paymentarea, 'id' => $payrec->paymentid]);
                $eventargs = [
                    'context' => \context_course::instance($courseid),
                    'userid' => $payrec->userid,
                    'other' => [
                        'message' => $msg,
                        'paymentid' => $payrec->paymentid,
                        'id' => $transactionid,
                        'airtel_money_id' => $mid,
                    ],
                ];
                $conf = \core_payment\helper::get_gateway_configuration(
                    $payrec->component,
                    $payrec->paymentarea,
                    $payrec->paymentid,
                    $gateway
                );
                $helper = new \paygw_airtelafrica\airtel_helper($conf);
                $helper->enrol_user($transactionid, $payrec->paymentid, $payrec->component, $payrec->paymentarea);
            } else {
                $eventargs = [
                    'context' => \context_system::instance(),
                    'userid' => 2,
                    'other' => $transaction,
                ];
            }
            \paygw_airtelafrica\event\request_log::create($eventargs)->trigger();
        }
    }
}
die();
