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
 * @copyright  2023 Medical Access Uganda Limited
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_airtelafrica\event;

/**
 * Testing event logs
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda Limited
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class request_log_test extends \advanced_testcase {

    /**
     * Test request_log.
     * @covers \paygw_airtelafrica\event\request_log
     */
    public function test_gateway(): void {
        global $DB;
        $this->resetAfterTest();
        $eventarray = [
            'context' => \context_system::instance(),
            'relateduserid' => 2,
            'other' => [
                'token' => 'faketoken',
                'transaction' => ['id' => 'fakeid'],
            ],
        ];
        $sink = $this->redirectEvents();
        $event = request_log::create($eventarray);
        $event->trigger();
        $this->assertEquals('Gateway log', $event->get_name());
        $this->assertEquals('token  : faketoken <br />transaction  : {"id":"fakeid"} <br />', $event->get_description());

        $this->assertCount(0, $DB->get_records('logstore_standard_log'));
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\paygw_airtelafrica\event\request_log', $event);
        $this->assertEquals(\context_system::instance(), $event->get_context());
        $this->assertEquals(2, $event->relateduserid);
        $sink->close();
    }
}
