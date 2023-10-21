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
        $this->resetAfterTest(true);
    }

    /**
     * Test callback.
     * @coversNothing
     */
    public function test_callback(): void {
        $client = new \GuzzleHttp\Client();
        $authdata = ['client_id' => 'fakeclientid', 'client_secret' => 'fakesecret', 'grant_type' => 'client_credentials'];
        $headers = ['Content-Type' => 'application/json'];
        $url = 'https://test.medical-access.org/payment/gateway/airtelafrica/callback.php';
        $response = $client->request('POST', $url, ['headers' => $headers, 'json' => $authdata]);
        $result = json_decode($response->getBody()->getContents(), true);
        $this->assertEmpty($result);
    }
}
