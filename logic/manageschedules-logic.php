<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";

/*
 * Generate the scheduling table.
 */
function generateScheduleTable($dept) {
	$TIME_SLICE = 30;

	$prevValue = "";

	if(isset($_POST['tutorAvailability']) && !empty($_POST['tutorAvailability'])) {
		$prevValue = $_POST['tutorAvailability'];
	}

	$data   = retrieveAvailability($dept);
	$scheds = retrieveSchedules($dept);
	$lims   = getLimits($dept)[0];

	$data   = mapRecords($data);
	$scheds = mapRecords($scheds);

	$ret=<<<'HTML'
<div>
	<h3>Edit Tutor Schedule:</h3>
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
			if(isset($data[$i][$otime['tm_hour']][$otime['tm_min']])) {
				$tutors = $data[$i][$otime['tm_hour']][$otime['tm_min']];

				if(isset($scheds[$i][$otime['tm_hour']][$otime['tm_min']])) {
					$sched = $scheds[$i][$otime['tm_hour']][$otime['tm_min']][0];

					if(isset($sched['mangled-id'])) {
						$prevValue = $sched['mangled-id'];
					}
				}

				$ret .= <<<HTML
<td>
	<form action='{$_SERVER['PHP_SELF']}' method='post'>
HTML;

				$nullVal = substr($tutors[0]['mangled-id'], 10);

				$ret .= generateSelectBox("tutorAvailability", $tutors,
					$prevValue, 'mangled-id', 'name',
					"NONE {$nullVal}");

				$ret .= <<<HTML
	</form>
</td>
HTML;

				if($clearPrev) {
					$clearPrev = false;

					$prevValue = "";
				}
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

/*
 * Get the limits for a departments labs.
 */
function getLimits($dept) {
	return databaseQuery("SELECT deptlabs.labstart, deptlabs.labend FROM deptlabs WHERE deptlabs.dept = ?", array($dept));
}

/*
 * Convert time-date records into a more sensible form.
 */
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

/*
 * Advance a record a half-hour.
 */
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

/*
 * Advance a record an hour.
 */
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

/*
 * Retrive availability records
 */
function retrieveAvailability($dept) {
	$sql=<<<'SQL'
WITH term_availability AS (
	SELECT * FROM availability WHERE availability.term = (SELECT code FROM terms WHERE terms.activeterm)
)
SELECT  users.realname as rname, users.idno, term_availability.starttime as starttime, term_availability.endtime as endtime
	FROM users 
	JOIN term_availability ON term_availability.student = users.idno
	JOIN deptlabs     ON term_availability.dept    = deptlabs.dept
	WHERE term_availability.dept = ?
	AND term_availability.starttime::time >= deptlabs.labstart
	AND term_availability.endtime::time   <= deptlabs.labend
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

/*
 * Retrieve scheduling data.
 */
function retrieveSchedules($dept) {
	$sql = <<<'SQL'
WITH term_schedules AS (
	SELECT * FROM schedules WHERE schedules.term = (SELECT code FROM terms WHERE terms.activeterm)
)
SELECT  users.realname as rname, users.idno, term_schedules.starttime as starttime, term_schedules.endtime as endtime
	FROM users 
	JOIN term_schedules ON term_schedules.student = users.idno
	JOIN deptlabs  ON term_schedules.dept    = deptlabs.dept
	WHERE term_schedules.dept = ?
	AND term_schedules.starttime::time >= deptlabs.labstart
	AND term_schedules.endtime::time   <= deptlabs.labend
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

/*
 * Register a schedule.
 */
function registerSchedule($val, $dept) {
	list($idno, $hour, $min, $day) = sscanf($val, "%s %d:%d %s");

	$sql = <<<'SQL'
INSERT INTO schedules(student, dept, starttime, endtime, term) values (?, ?, ?, ?, (SELECT code FROM terms WHERE terms.activeterm))
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

	if(!is_array($res)) {
		return false;
	}

	return true;
}

/*
 * Unregister a schedule.
 */
function unregisterSchedule($val, $dept) {
	$idno = "";
	$hour = 0;
	$min  = 0;
	$day  = 0;

	if($val[0] === 'N') {
		list($hour, $min, $day) = sscanf($val, "NONE %d:%d %s");
	} else {
		list($idno, $hour, $min, $day) = sscanf($val, "%s %d:%d %s");
	}

	$sql = <<<'SQL'
DELETE FROM schedules WHERE dept = ? AND starttime = ? AND endtime = ?
	AND term = (SELECT code FROM terms WHERE terms.activeterm)
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

	$params = array($dept, $start, $end);

	$res = databaseQuery($sql, $params);

	/* 	$res = false; */

	if(!is_array($res)) {
		return false;
	}

	return true;
}
?>
