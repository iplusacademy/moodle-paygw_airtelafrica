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
 * Contains class for Airtel Africa payment gateway.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda Limited
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_airtelafrica;

/**
 * The gateway class for Airtel Africa payment gateway.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda Limited
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_payment\gateway {

    /**
     * Currencies supported
     *
     * @return array
     */
    public static function get_supported_currencies(): array {
        return ['UGX', 'NGN', 'TZS', 'KES', 'RWF', 'XOF', 'XAF', 'CDF', 'USD', 'XAF', 'SCR', 'MGA', 'MWK', 'ZMW'];
    }

    /**
     * Countries supported
     *
     * @return array
     */
    public static function get_countries(): array {
        return ['CD', 'CG', 'GA', 'GH', 'KE', 'LR', 'MG', 'MW', 'NE', 'NG', 'RW', 'SC', 'TD', 'TZ', 'UG', 'ZA'];
    }

    /**
     * Countries supported
     *
     * @return array
     */
    private static function get_supported_countries(): array {
        $countries = self::get_countries();
        $strs = get_strings($countries, 'countries');
        $return = [];
        foreach ($countries as $country) {
            $return[$country] = $strs->$country;
        }
        return $return;
    }

    /**
     * Configuration form for the gateway instance
     *
     * Use $form->get_mform() to access the \MoodleQuickForm instance
     *
     * @param \core_payment\form\account_gateway $form
     */
    public static function add_configuration_to_gateway_form(\core_payment\form\account_gateway $form): void {
        $arr = ['brandname', 'clientid', 'clientidsb', 'secret', 'secretsb', 'live', 'sandbox', 'environment', 'country'];
        $txt = 'paygw_airtelafrica';
        $strs = get_strings($arr, $txt);
        $mform = $form->get_mform();

        $mform->addElement('text', 'brandname', $strs->brandname);
        $mform->setType('brandname', PARAM_TEXT);
        $mform->addHelpButton('brandname', 'brandname', $txt);

        $mform->addElement('text', 'clientid', $strs->clientid);
        $mform->setType('clientid', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('clientid', 'clientid', $txt);

        $mform->addElement('passwordunmask', 'secret', $strs->secret);
        $mform->setType('secret', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('secret', 'secret', $txt);

        $mform->addElement('text', 'clientidsb', $strs->clientidsb);
        $mform->setType('clientidsb', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('clientidsb', 'clientidsb', $txt);

        $mform->addElement('passwordunmask', 'secretsb', $strs->secretsb);
        $mform->setType('secretsb', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('secretsb', 'secretsb', $txt);

        $options = self::get_supported_countries();
        $mform->addElement('select', 'country', $strs->country, $options, 'UG');
        $mform->addHelpButton('country', 'country', $txt);

        $options = ['live' => $strs->live, 'sandbox' => $strs->sandbox];
        $mform->addElement('select', 'environment', $strs->environment, $options);
        $mform->addHelpButton('environment', 'environment', $txt);

        $mform->addRule('clientid', get_string('required'), 'required', null, 'client');
        $mform->addRule('secret', get_string('required'), 'required', null, 'client');
    }

    /**
     * Validates the gateway configuration form.
     *
     * @param \core_payment\form\account_gateway $form
     * @param \stdClass $data
     * @param array $files
     * @param array $errors form errors (passed by reference)
     */
    public static function validate_gateway_form(
        \core_payment\form\account_gateway $form, \stdClass $data, array $files, array &$errors): void {
        if ($data->enabled) {
            if (empty($data->clientid) || empty($data->secret) || empty($data->clientidsb) || empty($data->secretsb)) {
                $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
            }
        }
    }
}
