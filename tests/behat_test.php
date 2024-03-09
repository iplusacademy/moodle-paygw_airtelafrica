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
final class behat_test extends \advanced_testcase {

    /**
     * Setup function.
     */
    protected function setUp(): void {
        global $CFG;
        require_once($CFG->dirroot . '/payment/gateway/airtelafrica/tests/behat/behat_paygw_airtelafrica.php');
        $this->resetAfterTest(true);
    }

    /**
     * Test callback.
     * @covers \behat_paygw_airtelafrica
     */
    public function test_behat(): void {
        $behat = new \behat_paygw_airtelafrica();
        $behat->i_configure_airtel();
    }
}
