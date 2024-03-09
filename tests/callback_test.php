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
 * @copyright  2023 Medical Access Uganda Limited
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_airtelafrica;

/**
 * Testing callback in Airtel Africa payments API
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda Limited
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class callback_test extends \advanced_testcase {

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
        global $DB;
        $data = new \stdClass;
        $data->paymentid = 140;
        $data->userid = 13;
        $data->transactionid = '4206315384';
        $data->moneyid = null;
        $data->timecreated = time();
        $data->component = 'enrol_fee';
        $data->paymentarea = 'fee';
        $DB->insert_record('paygw_airtelafrica', $data);
        $client = new \GuzzleHttp\Client();
        $data = [
            'transaction' => [
                'id' => '4206315384',
                'message' => 'Pseudo paid UGX 5,000 to MAUL',
                'status_code' => 'TS',
                'airtel_money_id' => 'MP210603.1234.L06941',
            ],
        ];
        $headers = ['Content-Type' => 'application/json'];
        $url = 'https://test.ewallah.net/payment/gateway/airtelafrica/callback.php';
        $response = $client->request('POST', $url, ['headers' => $headers, 'json' => $data]);
        $result = json_decode($response->getBody()->getContents(), true);
        $this->assertEmpty($result);
    }
}
