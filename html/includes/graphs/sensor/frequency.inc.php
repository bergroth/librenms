<?php

$scale_min = '0';

require 'includes/graphs/common.inc.php';

$rrd_options .= " COMMENT:'                                 Last   Max\\n'";

$sensor['sensor_descr_fixed'] = rrdtool_escape($sensor['sensor_descr'], 28);

$rrd_options .= " DEF:sensor=$rrd_filename:sensor:AVERAGE";
$rrd_options .= " LINE1.5:sensor#cc0000:'".$sensor['sensor_descr_fixed']."'";
$rrd_options .= ' GPRINT:sensor:LAST:%3.0lf%sHz';
$rrd_options .= ' GPRINT:sensor:MAX:%3.0lf%sHz\l';

if (is_numeric($sensor['sensor_limit'])) {
    $rrd_options .= ' HRULE:'.$sensor['sensor_limit'].'#999999::dashes';
}

if (is_numeric($sensor['sensor_limit_low'])) {
    $rrd_options .= ' HRULE:'.$sensor['sensor_limit_low'].'#999999::dashes';
}
