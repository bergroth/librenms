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

foreach ($pre_cache['adva_fsp3kr7'] as $index => $entry) {
    if ($entry['entityFacilityAidString'] and $entry['pmSnapshotCurrentInputPower']) {
        $oidRX        = '.1.3.6.1.4.1.2544.1.11.7.7.2.3.1.2.' . $index;
        $currentRX    = $entry['pmSnapshotCurrentInputPower']/$divisor;
        $descr        = $entry['entityFacilityAidString'].' RX';

        discover_sensor(
            $valid['sensor'],
            'dbm',
            $device,
            $oidRX,
            'pmSnapshotCurrentInputPower'.$index,
            'adva_fsp3kr7',
            $descr,
            $divisor,
            $multiplier,
            null,
            null,
            null,
            null,
            $currentRX
        );
    }//End if Input Power

    if ($entry['entityFacilityAidString'] and $entry['pmSnapshotCurrentOutputPower']) {
        $oidTX     = '.1.3.6.1.4.1.2544.1.11.7.7.2.3.1.1.' . $index;
        $descr     = $entry['entityFacilityAidString'].' TX';
        $currentTX = $entry['pmSnapshotCurrentOutputPower']/$divisor;

        discover_sensor(
            $valid['sensor'],
            'dbm',
            $device,
            $oidTX,
            'pmSnapshotCurrentOutputPower'.$index,
            'adva_fsp3kr7',
            $descr,
            $divisor,
            $multiplier,
            null,
            null,
            null,
            null,
            $currentTX
        );
    }//End if Output Power

   # Amp ports with ifalias
   if ($entry['terminationPointAlias']) {
        $alias = $entry['terminationPointAlias'];
        $t = explode('.', $index);
        $module_index = $t[0] . '.' . $t[1];
        $oid = 'ADVA-FSPR7-MIB::entityEqptType.' . $module_index . '.0.0.6';
        #$oid = '.1.3.6.1.4.1.2544.1.11.7.2.2.1.7.' . $module_index . '.0.0.6';
        $module_type = snmp_get($device, $oid, '-Oqv');
        if ($module_type == 'eqpEdfaS20' | $module_type == 'eqpAmpShgcv' | $module_type == 'eqpAmpSlgcv') {
            $t = explode('.',$index);
            $tx_pwr_index = $t[0] . '.' . $t[1] . '.' . $t[2] . '.' . $t[3] . '.15';
            #$tx_pwr_oid = 'ADVA-FSPR7-MIB::pmSnapshotCurrentOutputPower.' . $tx_pwr_index;
            $tx_pwr_oid = '.1.3.6.1.4.1.2544.1.11.7.7.2.3.1.1.' . $tx_pwr_index;
            $current_tx = snmp_get($device, $tx_pwr_oid, '-Oqv -t20')/$divisor;

            $descr = $t[0] . '-' . $t[1] . '-N ' . $alias;
            $limit_low=null;
            $warn_limit_low=null;
            $warn_limit=null;
            $limit=null;
            $entPhysicalIndex=null;
            $entPhysicalIndex_measured=null;

            discover_sensor($valid['sensor'], 'dbm', $device, $tx_pwr_oid, 'tx-'.$index, 'adva_fsp3kr7',
               $descr.' Tx', $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit,
               $current_tx, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);

#            echo "Module " . $module_type . "\n";
#            var_dump($entry['terminationPointAlias']);
            #Find Roadm N port
            #16640 = Network port
            #53504 = Upgrade port
            #12800 = OSC port
            $entityConnectionToId_oid='ADVA-FSPR7-MIB::entityConnectionToId.' . $module_index . '.53504';
            $upstream_port = snmp_getnext($device, $entityConnectionToId_oid, '-Oqvn');
#            var_dump($upstream_port);
            $t = explode('.', $upstream_port);
            #15 = om
            $upstream_index = $t[15] . '.' . $t[16] . '.' . $t[17] . '.' . $t[18] . '.15';
            #$rx_pwr_oid = 'ADVA-FSPR7-MIB::pmSnapshotCurrentInputPower.' . $upstream_index;
            $rx_pwr_oid = '.1.3.6.1.4.1.2544.1.11.7.7.2.3.1.2.' . $upstream_index;
            $current_rx = snmp_get($device, $rx_pwr_oid, '-Oqv -t20')/$divisor;
#            var_dump($current_rx);
            
            $limit_low=null;
            $warn_limit_low=null;
            $warn_limit=null;
            $limit=null;
            $entPhysicalIndex=null;
            $entPhysicalIndex_measured=null;

            discover_sensor($valid['sensor'], 'dbm', $device, $rx_pwr_oid, 'rx-'.$index, 'adva_fsp3kr7',
               $descr.' Rx', $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit,
               $current_rx, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
   } 
}//End foreach entry
unset($entry);
//********* End of ADVA FSP3000 R7 Series
