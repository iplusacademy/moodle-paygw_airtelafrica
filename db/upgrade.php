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
 * paygw_airtelafrica upgrade script.
 *
 * @package    paygw_airtelafrica
 * @copyright  2023 Medical Access Uganda Limited
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade script.
 *
 * @param int $oldversion
 * @return bool always true
 */
function xmldb_paygw_airtelafrica_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2023062800) {
        $table = new xmldb_table('paygw_airtelafrica');
        $field = new xmldb_field('component', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'enrol_fee', 'moneyid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('paymentarea', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'fee', 'component');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Plugin savepoint reached.
        upgrade_plugin_savepoint(true, 2023062800, 'paygw', 'airtelafrica');
    }
    return true;
}
