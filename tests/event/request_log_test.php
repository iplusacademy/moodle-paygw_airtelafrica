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
 * Testing event logs
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_airtelafrica\event;

/**
 * Testing event logs
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class request_log_test extends \advanced_testcase {

    /**
     * Test request_log.
     * @covers \paygw_airtelafrica\event\request_log
     */
    public function test_gateway() {
        $this->resetAfterTest();
        $eventarray = [
            'context' => \context_system::instance(),
            'relateduserid' => 2,
            'other' => [
                'token' => 'faketoken',
                'transaction' => ['id' => 'fakeid'],
            ],
        ];
        $event = request_log::create($eventarray);
        $event->trigger();
        $event->get_name();
        $event->get_description();
    }
}
