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

    $multiplier = 1;
    $divisor    = 10;
# TID 
$tid = snmp_get($device, 'SNMPv2-MIB::sysName.0', '-Oqv');
foreach ($pre_cache['adva_fsp3kr7'] as $index => $entry) {
    if ($entry['terminationPointAlias']) {
        $alias = $entry['terminationPointAlias'];
	$t = explode('.', $index);
	$module_index = $t[0] . '.' . $t[1];
	$oid = 'ADVA-FSPR7-MIB::entityEqptType.' . $module_index . '.0.0.6';
	#$oid = '.1.3.6.1.4.1.2544.1.11.7.2.2.1.7.' . $module_index . '.0.0.6';
	$module_type = snmp_get($device, $oid, '-Oqv');
        if ($module_type == 'eqpEdfaS20' | $module_type == 'eqpAmpShgcv' | $module_type == 'eqpAmpSlgcv') {
#           echo $module_type;
            $t = explode('.',$alias);
            $farend = $t[1];
            $farend_hostname = strtolower($farend) . '.adva.sunet.se';
            $farend_device = dbFetch('SELECT * FROM `devices` WHERE `hostname` = ?', array($farend_hostname));
            $farend_device_id = dbFetchCell('SELECT `device_id` FROM `devices` WHERE `hostname` = ?', array($farend_hostname));
            if ($farend_device) {

                $t = preg_replace('/(.*)\.(.*)/', '%$2.$1 Tx%', $alias);
                $farend_oid = dbFetchCell('SELECT `sensor_oid` FROM `sensors` WHERE `device_id` = ? AND `sensor_class` = "dbm" AND `sensor_descr` LIKE ?', array($farend_device_id, $t));
                $farend_tx = snmp_get($farend_device, $farend_oid, '-Oqv');

                $t = "%" . $alias . " Rx%";
                $nearend_rx = dbFetchCell('SELECT `sensor_current`,`sensor_oid` FROM `sensors` WHERE `device_id` = ? AND `sensor_class` = "dbm" AND `sensor_descr` LIKE ?', array($device['device_id'], $t));
                $nearend_oid = dbFetchCell('SELECT `sensor_oid` FROM `sensors` WHERE `device_id` = ? AND `sensor_class` = "dbm" AND `sensor_descr` LIKE ?', array($device['device_id'], $t));

                if ($module_type == 'eqpAmpShgcv' | $module_type == 'eqpAmpSlgcv') {
                    $gain_oid = '.1.3.6.1.4.1.2544.1.11.7.3.6.1.1.26.' . $module_index . '.256.0.116';
                    $gain = snmp_get($device, $gain_oid, '-Oqv')/10;
                    $multiplier = $multiplier * $gain;
                    $divisor = $divisor * $gain;
		    $span_loss = $farend_tx - $nearend_rx + $gain;

                    $t = explode('.', $index);
                    $descr = $t[0] . '-' . $t[1] . '-N ' . $alias . " Span Loss (Raman)";
                } else {
                    $span_loss = $farend_tx - $nearend_rx;

                    $t = explode('.', $index);
                    $descr = $t[0] . '-' . $t[1] . '-N ' . $alias . " Span Loss";       
                }
                #echo $descr . "\n";
                discover_sensor($valid['sensor'], 'snr', $device, $nearend_oid, 'spanloss.'.$index, 'adva_fsp3kr7', $descr, $divisor, $multiplier, null, null, null, null, $spanloss);

           } 		
	}
    }
}
unset($entry);
//********* End of ADVA FSP3000 R7 Series
