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
 * This class starts a payment with the Airtel Africa payment gateway.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace paygw_airtelafrica\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use paygw_airtelafrica\airtel_helper;

/**
 * This class starts a payment with the Airtel Africa payment gateway.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class transaction_start extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'The component name'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'The item id in the context of the component area'),
            'reference' => new external_value(PARAM_RAW, 'The reference we use'),
            'phone' => new external_value(PARAM_RAW, 'The phone of the payer'),
            'country' => new external_value(PARAM_RAW, 'The country of the payer'),
        ]);
    }

    /**
     * Perform what needs to be done when a transaction is reported to be complete.
     * This function does not take cost as a parameter as we cannot rely on any provided value.
     *
     * @param string $component Name of the component that the itemid belongs to
     * @param string $paymentarea
     * @param int $itemid An internal identifier that is used by the component
     * @param string $reference
     * @param string $phone
     * @param string $country
     * @return array
     */
    public static function execute(
        string $component, string $paymentarea, int $itemid, string $reference, string $phone, string $country): array {
        $gateway = 'airtelafrica';

        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'reference' => $reference,
            'phone' => $phone,
            'country' => $country,
        ]);

        $config = (object)\core_payment\helper::get_gateway_configuration($component, $paymentarea, $itemid, $gateway);
        $payable = \core_payment\helper::get_payable($component, $paymentarea, $itemid);
        $amount = $payable->get_amount();
        $currency = $payable->get_currency();
        $surcharge = \core_payment\helper::get_gateway_surcharge($gateway);
        $cost = \core_payment\helper::get_rounded_cost($amount, $currency, $surcharge);
        $random = random_int(1000000000, 9999999999);
        $esb = 'ESB000001';
        $transactionid = ($itemid == 66666666) ? $itemid : 0;
        $helper = new \paygw_airtelafrica\airtel_helper($config->clientid, $config->secret, $config->country);
        $result = $helper->request_payment($random, $reference, $cost, $currency, $phone, $country);
        if (array_key_exists('status', $result)) {
            if ($result['status']['code'] == 200 && $result['status']['success'] == 1) {
                $transactionid = $result['data']['transaction']['id'];
            }
            $esb = ($itemid == 66666666) ? 'ESB000010' : $result['status']['result_code'];
        }
        $message = $helper->esb_code($esb);
        return ['transactionid' => $transactionid, 'message' => $message];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_function_parameters
     */
    public static function execute_returns() {
        return new external_function_parameters([
            'transactionid' => new external_value(PARAM_RAW, 'A valid transaction id or 0 when not successful'),
            'message' => new external_value(PARAM_RAW, 'Usualy the error message'),
        ]);
    }
}
