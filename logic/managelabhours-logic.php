<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";

/*
 * Two dropdown boxes
 * - Select a start/end time for a section.
 * 	- Start: onchange=submit
 * 	- End: Should start after start.
 * 	- Go from 8:00 AM to 8:00 PM
 */

 
function genLabTimeForm()
{
    $startTime=-1;
    $endTime=-1;
    if(isset($_POST['startTime']) && !empty($_POST['startTime']))
    {
        $startTime=$_POST['startTime'];
    }
    
    if(isset($_POST['startTime']) && !empty($_POST['startTime']))
    {
        $endTime=$_POST['startTime'];
    } 
 
    $startBox=getStartBox($startTime);
    $endBox = getEndBox($startTime,$endTime);

    $html=<<<eof
    <div class="flex flexGrow group">
        <form class="flex flexColumn flexGrow">
            <div class="flex flexRow alignCenterFlex flexGrow">
                <div class="flex"
                    Yolo
                </div>
                <div class="flex"
                    {$startBox}
                </div>
                <div class="flex"
                    Swag
                </div>
                <div class="flex"
                    {$endBox}
                </div>
            </div> <!-- formEntryBlock-->
            <div class="flex flexRow alignCenterFlex centerFlex flexGrow">
                <input type=submit value="Cancel" name="submit" class="btn">
                <input type=submit value="Submit" name="submit" class="btn">
            </div> <!-- buttonBlock-->
        </form> <!-- formBlock -->
    </div> <!-- Container End -->
    
eof;
    return $html;
}
 
 
 
/*
 * Create a start time selecting box.
 *
 * Will go from the minimum starting time to 30 mins before the max ending time.
 */
function getStartBox($currTime) {
	/* 
	 * Earliest starting time, 8:00 in the morning.
	 */
	$initTime = strptime("08:00:00", "%T");

	/*
	 * Set default value if not specified.
	 */
	if($currTime === -1) $currTime = $initTime;

	/*
	 * Latest starting time, 7:30 at night.
	 *
	 * Half an hour before the latest closing time, so that we can guarantee 
	 * the lab is open some time.
	 */
	$latestTime = strptime("19:30:00", "%T");

	$html = "";

	$html .= "<select onchange=submit name='startTime'>\n";

	$shouldContinue = true;

	$val = $initTime;

	while($shouldContinue) {
		$oval = $val;

		$val = advanceHalfHour($val);

		/*
		 * When our advance has hit the next value, don't continue.
		 */
		if($val === $latestTime) {
			$shouldContinue = false;
		}
		
		$valStr = "{$oval['tm_hour']}:{$oval['tm_min']}";

		if($oval === $currTime) {
			$html .= "\t<option value='{$valStr}' selected>{$valStr}</option>\n";
		} else {
			$html .= "\t<option value='{$valStr}'>{$valStr}</option>\n";
		}
	}

	$html .= "</select>";

	return $html;
}

/*
 * Get an ending time select box.
 *
 * Will go from 30 mins after startTime to the max ending time.
 */
function getEndBox($startTime, $currTime) {
	$initTime = advanceHalfHour($startTime);

	/*
	 * Set default value if not specified.
	 */
	if($currTime === -1) $currTime = $initTime;

	/*
	 * Latest ending time, 7:30 at night.
	 */
	$latestTime = strptime("19:30:00", "%T");

	$html = "";

	$html .= "<select onchange=submit name='endTime'>\n";

	$shouldContinue = true;

	$val = $initTime;

	while($shouldContinue) {
		$oval = $val;

		$val = advanceHalfHour($val);

		/*
		 * When our advance has hit the next value, don't continue.
		 */
		if($val === $latestTime) {
			$shouldContinue = false;
		}
		
		$valStr = "{$oval['tm_hour']}:{$oval['tm_min']}";

		if($oval === $currTime) {
			$html .= "\t<option value='{$valStr}' selected>{$valStr}</option>\n";
		} else {
			$html .= "\t<option value='{$valStr}'>{$valStr}</option>\n";
		}
	}

	$html .= "</select>";

	return $html;
}

/*
 * Set the timing limits for a department.
 */
function setTimes($dept, $start, $end) {
	$query = <<<SQL
INSERT INTO deptlabs(dept, labstart, labend) VALUES (?, ?, ?)
	ON CONFLICT(dept) DO UPDATE SET
		(labstart, labend) = (EXCLUDED.labstart, EXCLUDED.labend)
SQL;

	return databaseQuery($query, array($dept, $start, $end));
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

?>
