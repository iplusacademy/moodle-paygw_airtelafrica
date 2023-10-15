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

    /** @var config configuration */
    private $config;

    /**
     * Setup function.
     */
    protected function setUp(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');
        $this->config = ['environment' => 'sandbox', 'clientidsb' => getenv('login'), 'secretsb' => getenv('secret')];
        $this->resetAfterTest(true);
    }

    /**
     * Test callback.
     * @coversNothing
     */
    public function test_callback() {
        if ($this->config['clientidsb'] == '') {
            $this->markTestSkipped('No login credentials');
        }
        $client = new \GuzzleHttp\Client();
        $authdata = ['client_id' => 'fakeclientid', 'client_secret' => 'fakesecret', 'grant_type' => 'client_credentials'];
        $headers = ['Content-Type' => 'application/json'];
        $url = $this->get_callback_host() . '/payment/gateway/airtelafrica/callback.php';
        $response = $client->request('POST', $url, ['headers' => $headers, 'json' => $authdata]);
        $result = json_decode($response->getBody()->getContents(), true);
        $this->assertEmpty($result);
    }

    /**
     * Which host should be used for callback.
     *
     * @return string
     */
    private function get_callback_host(): string {
        global $CFG;
        $dom = str_ireplace('http://', '', $CFG->wwwroot);
        $dom = str_ireplace('https://', '', $dom);
        if (stripos($dom, 'example.com') !== false) {
            // Local domain is example domain while testing, so we have to get the info from config.
            $dom = str_ireplace('www.example.com', self::get_hostname(), $dom);
            $dom = str_ireplace('/moodle', '', $dom);
            $http = 'https://';
        }
        return $http . $dom;
    }

    /**
     * Which hostname are we running, get the info from config file.
     *
     * @return string
     */
    private static function get_hostname(): string {
        $lines = file('config.php');
        $needle = '$CFG->wwwroot';
        $result = '127.0.0.1';
        foreach ($lines as $line) {
            if (stripos($line, $needle) !== false) {
                $line = str_ireplace($needle, '', $line);
                $line = str_ireplace(' ', '', $line);
                $line = str_ireplace(PHP_EOL, '', $line);
                $line = str_ireplace(';', '', $line);
                $line = str_ireplace('=', '', $line);
                $line = str_ireplace('"', '', $line);
                $line = str_ireplace("'", '', $line);
                $line = str_ireplace('http://', '', $line);
                $line = str_ireplace('https://', '', $line);
                $result = strip_tags($line);
                break;
            }
        }
        // Localhost callbacks are redirected.
        $result = str_ireplace('localhost', 'test.medical-access.org', $result);
        $result = str_ireplace('127.0.0.1', 'test.medical-access.org', $result);
        $result = str_ireplace('/moodle', '', $result);
        return $result;
    }

}
