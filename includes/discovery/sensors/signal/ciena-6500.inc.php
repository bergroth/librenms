<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Neil Lathwood <neil@lathwood.co.uk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'Ciena-6500';

$multiplier = 1;
$divisor    = 1;
$oids = array();
$oids_tmp = snmpwalk_cache_multi_oid($device, 'nnOpticalPmRecentIfIndex', array(), 'NORTEL-OPTICAL-PM-MIB');
foreach ($oids_tmp as $index => $entry)
{
	foreach ($entry as $index2 => $entry2)
	{
		array_push($oids, $entry2);
	}
}
$oids = array_unique($oids);

foreach ($oids as $index_tmp => $index)
{
	# SIGNAL DGD
        $multiplier = 1;
        $divisor    = 1;
        if (is_numeric($index) )
        {
                $oid = '.1.3.6.1.4.1.562.68.10.1.1.1.1.1.6.'.$index.'.131'; #DGDMAX-OCH NEND RCV"
                #$descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', array($index, $device['device_id'])) . ' Rx Power';
                $descr = snmp_get($device, "nnOpticalPmRecentMonTypeDescr.$index.131", '-Oqv', 'NORTEL-OPTICAL-PM-MIB');
                #$limit_low = $entry['jnxDomCurrentRxLaserPowerLowAlarmThreshold']/$divisor;
                #$warn_limit_low = $entry['jnxDomCurrentRxLaserPowerLowWarningThreshold']/$divisor;
                #$limit = $entry['jnxDomCurrentRxLaserPowerHighAlarmThreshold']/$divisor;
                #$warn_limit = $entry['jnxDomCurrentRxLaserPowerHighWarningThreshold']/$divisor;
                $current = snmp_get($device, "nnOpticalPmCurr15MinValue.$index.131", '-Oqv', 'NORTEL-OPTICAL-PM-MIB');
                if (is_numeric($current)) {
                        d_echo("Current value" . $current);
                        echo ("This is the current value:");
			echo $current;
                        echo (' ');
                        echo $index;
                        echo (' ');
			echo ("This is the current oid:");
			echo $oid;
			echo (' ');
			$entPhysicalIndex = $index;
                        $entPhysicalIndex_measured = 'ports';
                        discover_sensor($valid['sensor'], 'signal', $device, $oid, 'rx-'.$index, 'ciena-6500', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
                }
        }
}

