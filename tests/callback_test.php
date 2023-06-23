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
 * Testing callback in Airtel Africa payments API
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_airtelafrica;

/**
 * Testing callback in Airtel Africa payments API
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class callback_test extends \advanced_testcase {

    /**
     * Setup function.
     */
    protected function setUp(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');
        $this->resetAfterTest(true);
    }

    /**
     * Test callback.
     * @coversNothing
     */
    public function test_callback() {
        $client = new \GuzzleHttp\Client();
        $authdata = ['client_id' => 'fakeclientid', 'client_secret' => 'fakesecret', 'grant_type' => 'client_credentials'];
        $headers = ['Content-Type' => 'application/json'];
        $url = $this->get_local_url('callback');
        $response = $client->request('POST', $url, ['headers' => $headers, 'json' => $authdata]);
        $result = json_decode($response->getBody()->getContents(), true);
        $this->assertEmpty($result);
    }

    /**
     * Test continue.
     * @coversNothing
     */
    public function test_continue() {
        $client = new \GuzzleHttp\Client();
        $data = ['sesskey' => sesskey(),
            'component' => 'enrol_fee',
            'paymentarea' => 'fee',
            'itemid' => 82,
            'transactionid' => '4871171159',
            'reference' => 'course33333'];
        $url = $this->get_local_url('continue');
        $response = $client->request('POST', $url, ['form_params' => $data]);
        $result = json_decode($response->getBody()->getContents(), true);
        $this->assertEmpty($result);
    }

    /**
     * Get local url.
     * @param string $phpfile
     * @return string
     */
    private function get_local_url(string $phpfile) {
        global $CFG;
        $url = new \moodle_url("/payment/gateway/airtelafrica/$phpfile.php");
        $url = $url->raw_out();
        $url = str_ireplace(['https://', 'http://'], '', $url);
        $dom = 'test.ewallah.net';
        $lines = file($CFG->dirroot . '/config.php');
        foreach ($lines as $line) {
            if (strpos($line, '$CFG->wwwroot') !== false) {
                $dom = str_ireplace('$CFG->wwwroot', '', $line);
                $dom = str_ireplace(["'", '"', "=", ";", " "], '', $dom);
                $dom = str_ireplace(["\r\n", "\r", "\n", "\\r", "\\n", "\\r\\n"], '', $dom);
                break;
            }
        }
        return str_ireplace('www.example.com/moodle', $dom, $url);
    }
}


