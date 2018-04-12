<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";

function generateScheduleTable($dept) {
	$TIME_SLICE = 30;

	$scheds = retrieveSchedules($dept);
	$lims   = getLimits($dept)[0];

	$scheds = mapRecords($scheds);

	$ret=<<<'HTML'
<div>
	<h3>Tutor Schedule:</h3>
</div>
<div class="tableStyleA dropShadow center" id="table">
	<table>
		<tr>
			<th class='timeColumn'>Time Period</th>
			<th>Monday</th>
			<th>Tuesday</th>
			<th>Wednesday</th>
			<th>Thursday</th>
			<th>Friday</th>
		</tr>
HTML;

	if(empty($lims) || !is_array($lims)) {
		$ret.= "</table></div><br><br>Unexpected error getting lab times.";

		return $ret;
	}

	$formatstr = "%T";

	$tme = strptime($lims['labstart'], $formatstr);
	$end = strptime($lims['labend'],   $formatstr);

	while($tme != $end) {
		$otime = $tme;

		if($TIME_SLICE === 30) {
			$tme = advanceHalfHour($tme);
		} else {
			$tme = advanceHour($tme); 
		}

		$nm  = $otime['tm_min'];
		$onm = $tme['tm_min'];

		if($nm  === 29) $nm  = 30;
		if($onm === 29) $onm = 30;

		$twelve  = strftime("%l:%M %p", strtotime("{$otime['tm_hour']}:{$nm}"));
		$otwelve = strftime("%l:%M %p", strtotime("{$tme['tm_hour']}:{$onm}"));

		$ret .= <<<HTML
<tr>
	<td> {$twelve} - {$otwelve} </td>
HTML;

		$clearPrev = false;

		for($i = 1; $i <= 5; $i++) {
			if(isset($scheds[$i][$otime['tm_hour']][$otime['tm_min']])) {
				$sched = $scheds[$i][$otime['tm_hour']][$otime['tm_min']][0];

				/*
				 * @NOTE
				 *
				 * Consider colorizing these table entries.
				 *
				 * Steps for that:
				 * 	* Create a table of colors in the 
				 * 	database, containing color name, hex 
				 * 	value, and whether or not the color is 
				 * 	used for a tutor.
				 * 	* When someone is promoted to tutor, 
				 * 	either randomly assign them a color or 
				 * 	pick one from the unused color list.
				 * 	* When someone is demoted, free the 
				 * 	color back into the list.
				 */
				$ret .= "<td>{$sched['name']}</td>";
			} else {
				/* $ret .= "<td>No tutors available</td>"; */
				$ret .= "<td></td>";
			}
		}

		$ret .= "</tr>";
	}

	$ret .= "</table></div>";

	return $ret;
}

function getLimits($dept) {
	return databaseQuery("SELECT deptlabs.labstart, deptlabs.labend FROM deptlabs WHERE deptlabs.dept = ?", array($dept));
}

function mapRecords($data) {
	$ret = array();

	foreach($data as $row) {
		$wkday  = strftime("%u", strtotime($row['strdate']));
		$wkname = strftime("%A", strtotime($row['strdate']));

		$cnt = 0;

		for($tme = $row['starttime']; $tme !== $row['endtime']; $tme = advanceHalfHour($tme)) {
			$idx = 0;

			if($tme['tm_min'] === 29) {
				$idx += 1;
			}

			$ret[$wkday][$tme['tm_hour']][$tme['tm_min']][] = array(
				'name'       => $row['rname'],
				'idno'       => $row['idno'],
				'mangled-id' => "{$row['idno']} {$tme['tm_hour']}:{$tme['tm_min']} {$wkname}"
			);

			$cnt += 1;
		}
	}

	return $ret;
}

function advanceHalfHour($tme) {
	$ret = $tme;

	if($tme['tm_min'] === 29) {
		$ret['tm_hour'] += 1;
		$ret['tm_min']   = 0;
	} else {
		$ret['tm_min'] = 29;
	}

	return $ret;
}

function advanceHour($tme) {
	$ret = $tme;

	if($tme['tm_min'] === 0) {
		$ret['tm_hour'] += 1;
		$ret['tm_min']   = 0;
	} else {
		$ret['tm_min'] = 29;
	}

	return $ret;
}

function retrieveAvailability($dept) {
	$sql=<<<'SQL'
SELECT  users.realname as rname, users.idno, availability.starttime as starttime, availability.endtime as endtime
	FROM users 
	JOIN availability ON availability.student = users.idno
	JOIN deptlabs     ON availability.dept    = deptlabs.dept
	WHERE availability.dept = ?
	AND availability.starttime::time >= deptlabs.labstart
	AND availability.endtime::time   <= deptlabs.labend
SQL;

	$result = databaseQuery($sql, array($dept));

	if(empty($result) || !is_array($result)) {
		$result = array();
	}

	$arcount = count($result);

	for($i=0; $i < $arcount; $i++) {
		$formatstr = "%Y-%m-%d %T";

		$result[$i]['strdate'] = $result[$i]['starttime'];

		$result[$i]['starttime'] = strptime($result[$i]['starttime'], $formatstr);
		$result[$i]['endtime']   = strptime($result[$i]['endtime'],   $formatstr);
	}

	return $result;
}

function retrieveSchedules($dept) {
	$sql = <<<'SQL'
SELECT  users.realname as rname, users.idno, schedules.starttime as starttime, schedules.endtime as endtime
	FROM users 
	JOIN schedules ON schedules.student = users.idno
	JOIN deptlabs  ON schedules.dept    = deptlabs.dept
	WHERE schedules.dept = ?
	AND schedules.starttime::time >= deptlabs.labstart
	AND schedules.endtime::time   <= deptlabs.labend
SQL;

	$result = databaseQuery($sql, array($dept));

	if(empty($result) || !is_array($result)) {
		$result = array();
	}

	$arcount = count($result);

	for($i=0; $i < $arcount; $i++) {
		$formatstr = "%Y-%m-%d %T";

		$result[$i]['strdate'] = $result[$i]['starttime'];

		$result[$i]['starttime'] = strptime($result[$i]['starttime'], $formatstr);
		$result[$i]['endtime']   = strptime($result[$i]['endtime'],   $formatstr);
	}

	return $result;
}

function registerSchedule($val, $dept) {
	list($idno, $hour, $min, $day) = sscanf($val, "%s %d:%d %s");

	$sql = <<<'SQL'
INSERT INTO schedules(student, dept, starttime, endtime) values (?, ?, ?, ?)
SQL;

	$nhour = $hour;
	$nmin  = $min;

	if($min === 29) {
		$nhour += 1;
		$nmin   = 0;
	} else {
		$nmin = 29;
	}

	$startstr = "";
	$endstr   = "";

	if($min === 0) {
		$startstr = "January 1996 {$day} {$hour}:00";
	} else {
		$startstr = "January 1996 {$day} {$hour}:{$min}";
	}

	if($nmin === 0) {
		$endstr   = "January 1996 {$day} {$nhour}:00";
	} else {
		$endstr   = "January 1996 {$day} {$nhour}:{$nmin}";
	}

	$start = strftime("%F %T", strtotime($startstr));
	$end   = strftime("%F %T", strtotime($endstr));

	$res = databaseQuery($sql, array($idno, $dept, $start, $end));

	/* 	$res = false; */

	if(!is_array($res)) {
		return false;
	}

	return true;
}

function unregisterSchedule($val, $dept) {
	$idno = "";
	$hour = 0;
	$min  = 0;
	$day  = 0;

	if($val[0] === 'N') {
		list($idno, $hour, $min, $day) = sscanf($val, "NONE %s %d:%d %s");
	} else {
		list($idno, $hour, $min, $day) = sscanf($val, "%s %d:%d %s");
	}

	$sql = <<<'SQL'
DELETE FROM schedules where student = ? AND dept = ? AND starttime = ? AND endtime = ?
SQL;

	$nhour = $hour;
	$nmin  = $min;

	if($min === 29) {
		$nhour += 1;
		$nmin   = 0;
	} else {
		$nmin = 29;
	}

	$startstr = "";
	$endstr   = "";

	if($min === 0) {
		$startstr = "January 1996 {$day} {$hour}:00";
	} else {
		$startstr = "January 1996 {$day} {$hour}:{$min}";
	}

	if($nmin === 0) {
		$endstr   = "January 1996 {$day} {$nhour}:00";
	} else {
		$endstr   = "January 1996 {$day} {$nhour}:{$nmin}";
	}

	$start = strftime("%F %T", strtotime($startstr));
	$end   = strftime("%F %T", strtotime($endstr));

	$res = databaseQuery($sql, array($idno, $dept, $start, $end));

	/* 	$res = false; */

	if(!is_array($res)) {
		return false;
	}

	return true;
}
?>
