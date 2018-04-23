<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";

function generateScheduleTable($dept) {
	$TIME_SLICE = 30;
	
	$data = retrieveSchedules($dept);
	//$lims = getLimits($dept)[0];
	$lims = getLimits($dept);


	//Edit
	if(is_array($lims) && !empty($lims))
	{
		$lims= $lims[0];
	}
	else
	{
		return "Unexpected error getting lab times.";
	}
	//End Edit

	$data = mapSchedules($data);

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

	//Needs removing
	if(empty($lims) || !is_array($lims)) {
		$ret.= <<<HTML
	</table>
</div>
<br/><br/>
Unexpected error getting lab times.
HTML;
		//Needs removing

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

		for($i = 1; $i <= 5; $i++) {
			$wkname = wknumtoname($i);

			$timeString = "{$otime['tm_hour']}:{$otime['tm_min']} {$wkname}";

			/* $timeVal    = timeArrToStamp($otime); */
			/* $timeString = strftime("%H:%M %A"); */

			if(isset($data[$i][$otime['tm_hour']][$otime['tm_min']])) {
				$strang = genUnclaimButton($timeString);

				$ret .= "<td>$strang</td>";
			} else {

				$strang = genClaimButton($timeString);

				$ret .= "<td>$strang</td>";
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

function mapSchedules($data) {
	$ret = array();

	foreach($data as $row) {
		$wkday  = strftime("%u", strtotime($row['strdate']));
		$wkname = strftime("%A", strtotime($row['strdate']));

		$cnt = 0;

		for($tme = $row['starttime']; $tme !== $row['endtime']; $tme = advanceHalfHour($tme)) {
			$idx = 0;

			if($tme['tm_min'] === 29)
				$idx += 1;

			$ret[$wkday][$tme['tm_hour']][$tme['tm_min']] = array(
				'name' => $row['rname']
			);

			$cnt += 1;
		}
	}

	return $ret;
}

function genClaimButton($tme) {
	$val = "{$_SESSION['useridno']} {$tme}";

	$html=<<<HTML
<form action='{$_SERVER['PHP_SELF']}' method='post'>
	<button class='btnSmall' type='submit' name='claimSlot' value='{$tme}'>
		<b>+</b>
	</button>
</form>
HTML;

	return $html;
}

function genUnclaimButton($tme) {   
	$html=<<<HTML
<form action='{$_SERVER['PHP_SELF']}' method='post'>
	<button class='btnSmallGold' type='submit' name='unclaimSlot' value='{$tme}'>
		<b>Unclaim</b>
	</button>
</form>
HTML;

	return $html;
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

function retrieveSchedules($dept) {
	$sql=<<<'SQL'
SELECT  filt_users.realname as rname, availability.starttime as starttime, availability.endtime as endtime
	FROM (SELECT * FROM users WHERE users.idno = ?) as filt_users
	JOIN availability ON availability.student = filt_users.idno
	JOIN deptlabs     ON availability.dept    = deptlabs.dept
	WHERE availability.dept = ?
	AND availability.starttime::time >= deptlabs.labstart
	AND availability.endtime::time   <= deptlabs.labend
SQL;

	$result = databaseQuery($sql, array($_SESSION['useridno'], $dept));

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

function registerClaim($val, $depart) {
	list($hour, $min, $day) = sscanf($val, "%d:%d %s");

	$sql = <<<'SQL'
INSERT INTO availability(student, dept, starttime, endtime) values (?, ?, ?, ?)
SQL;

	$idno = $_SESSION['useridno'];

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

	$res = databaseQuery($sql, array($idno, $depart, $start, $end));

	if(!is_array($res)) {
		return false;
	}

	return true;
}

function registerUnclaim($val, $depart) {
	list($hour, $min, $day) = sscanf($val, "%d:%d %s");

	$sql = <<<'SQL'
DELETE FROM availability where student = ? AND dept = ? AND starttime = ? AND endtime = ?
SQL;

	$idno = $_SESSION['useridno'];

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

	$res = databaseQuery($sql, array($idno, $depart, $start, $end));

	if(!is_array($res)) {
		return false;
	}

	return true;
}
?>
