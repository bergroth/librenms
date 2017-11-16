<?php
/**
 * LibreNMS - ADVA device support - Pre-Cache for Sensors
 *
 * @category   Network_Monitoring
 * @package    LibreNMS
 * @subpackage ADVA device support
 * @author     Christoph Zilian <czilian@hotmail.com>
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 * @link       https://github.com/librenms/librenms/

 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

//********* ADVA FSP3000 R7 Series

# TID
print " rx:" . $sensor_value; 
$farend_hostname = strtolower(preg_replace('/.* (\S*)\.(\S*) .*/', '$2.adva.sunet.se', $sensor['sensor_descr']));
$farend_descr = preg_replace('/.* (\S*)\.(\S*) .*/', '%$2.$1 Tx%', $sensor['sensor_descr']);
$farend_device = dbFetch('SELECT * FROM `devices` WHERE `hostname` = ?', array($farend_hostname));
$farend_device = $farend_device[0];
if ($farend_device) {
    $farend_oid = dbFetchCell('SELECT `sensor_oid` FROM `sensors` WHERE `device_id` = ? AND `sensor_class` = "dbm" AND `sensor_descr` LIKE ?', array($farend_device['device_id'], $farend_descr));
    $farend_tx = snmp_get($farend_device, $farend_oid, '-Oqv');

    if ($sensor['sensor_multiplier'] == 1) {
        $sensor_value = $farend_tx - $sensor_value;
    } else {
        $sensor_value = $farend_tx - $sensor_value + $sensor['sensor_multiplier']*10;
    }
}
print " g:" . $sensor['sensor_multiplier'];
print " tx:" . $farend_tx;
print " l:" . $sensor_value . "\n";
unset($entry);

//********* End of ADVA FSP3000 R7 Series
