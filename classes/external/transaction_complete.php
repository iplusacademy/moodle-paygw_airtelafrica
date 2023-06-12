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
 * This class completes a payment with the Airtel Africa payment gateway.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace paygw_airtelafrica\external;

use core_payment\helper;
use paygw_airtelafrica\airtel_helper;
use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;

/**
 * This class completes a payment with the Airtel Africa payment gateway.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class transaction_complete extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'The component name'),
            'area' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'The item id in the context of the component area'),
            'transactionid' => new external_value(PARAM_TEXT, 'The transaction id coming back from Airtel Africa'),
            'currency' => new external_value(PARAM_TEXT, 'The currency used')
        ]);
    }

    /**
     * Perform what needs to be done when a transaction is reported to be complete.
     * This function does not take cost as a parameter as we cannot rely on any provided value.
     *
     * @param string $component Name of the component that the itemid belongs to
     * @param string $area The payment area
     * @param int $itemid An internal identifier that is used by the component
     * @param string $transactionid Airtel Africa order ID
     * @param string $currency Airtel Africa order ID
     * @return array
     */
    public static function execute(string $component, string $area, int $itemid, string $transactionid, string $currency): array {
        global $DB, $USER;
        $gateway = 'airtelafrica';
        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'area' => $area,
            'itemid' => $itemid,
            'transactionid' => $transactionid,
            'currency' => $currency,
        ]);
        // Handle payments that are already handled.
        if ($DB->get_field('paygw_airtelafrica', 'timecompleted', ['transactionid' => $transactionid]) > 0) {
            return ['success' => true, 'message' => airtel_helper::ta_code('TS')];
        }
        $config = helper::get_gateway_configuration($component, $area, $itemid, $gateway);
        $helper = new airtel_helper($config);
        $helper->token = $DB->get_field('paygw_airtelafrica', 'moneyid', ['transactionid' => $transactionid]);
        $suc = false;
        $trans = 'TF';
        $result = $helper->transaction_enquiry($transactionid, $currency);
        $status = airtel_helper::array_helper('status', $result);
        $data = airtel_helper::array_helper('data', $result);
        if ($status && $data && $status['code'] == '200') {
            $transaction = airtel_helper::array_helper('transaction', $data);
            if ($transaction) {
                $userid = (int)$USER->id;
                $trans = $transaction['status'];
                $suc = (bool)$status['success'];
                $cond = ['transactionid' => $transactionid, 'userid' => $userid];
                if ($DB->record_exists('paygw_airtelafrica', $cond)) {
                    $payable = helper::get_payable($component, $area, $itemid);
                    $surcharge = helper::get_gateway_surcharge($gateway);
                    $amount = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);
                    $payid = $payable->get_account_id();
                    $payment = helper::save_payment($payid, $component, $area, $itemid, $userid, $amount, $currency, $gateway);
                    $suc = helper::deliver_order($component, $area, $itemid, $payment, $userid);
                    $DB->set_field('paygw_airtelafrica', 'timecompleted', time(), $cond);
                    if ($moneyid = airtel_helper::array_helper('airtel_money_id', $transaction)) {
                        $DB->set_field('paygw_airtelafrica', 'moneyid', $moneyid, $cond);
                    }
                }
            }
        }
        return ['success' => $suc, 'message' => airtel_helper::ta_code($trans)];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_function_parameters
     */
    public static function execute_returns() {
        return new external_function_parameters([
            'success' => new external_value(PARAM_BOOL, 'Whether everything was successful or not.'),
            'message' => new external_value(PARAM_RAW, 'Message (usually the error message).'),
        ]);
    }
}
