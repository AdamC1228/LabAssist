<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";

function reportHourlyUsage() {
	$query = <<<'SQL'
SELECT usage.markin, usage.markout FROM usage
	JOIN sections ON usage.secid = sections.secid
	WHERE sections.term = (SELECT code FROM terms WHERE terms.activeterm = true)
SQL;

	$data = safeDBQuery($query, array());

	if($data === -1) {
		return -1;
	}

	foreach($data as $datum) {
		$formatstr = "%Y-%m-%d %T";

		$begin = strptime($datum['markin'],  $formatstr);
		$end   = strptime($datum['markout'], $formatstr);

		$wkday  = strftime("%u", strtotime($datum['markin']));

		/*
		 * NOTE:
		 *
		 * Maybe we want this report to be a count of clock-ins/hour for 
		 * each day.
		 */
		for($initVal = $begin['tm_hour']; $initVal <= $end['tm_hour']; $initVal++) {
			if(isset($retval[$wkday][$initVal])) {
				$retval[$wkday][$initVal] += 1;
			} else {
				$retval[$wkday][$initVal] = 1;
			}
		}
	}

	return $retval;
}

function reportDailyUsage() {
	$query = <<<'SQL'
SELECT usage.markin, usage.markout FROM usage
	JOIN sections ON usage.secid = sections.secid
	WHERE sections.term = (SELECT code FROM terms WHERE terms.activeterm = true)
SQL;

	$data = safeDBQuery($query, array());

	if($data === -1) {
		return -1;
	}

	$retval = array();

	foreach($data as $datum) {
		$wkday  = strftime("%u", strtotime($datum['markin']));

		if(isset($retval[$wkday])) {
			$retval[$wkday] += 1;
		} else {
			$retval[$wkday] = 1;
		}
	}

	return $retval;
}

function reportStudentUsage() {
	$query = <<<'SQL'
SELECT stu.idno, stu.realname, stu.role, stu.total_hours
	FROM student_total_usage stu
SQL;

	return safeDBQuery($query, array());
}

/*
    NOTE: A report on clockin length might be useful.
/
?>
