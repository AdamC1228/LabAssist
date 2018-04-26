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
    $startTime=strptime("08:00:00", "%T");
    $endTime=strptime("16:00:00", "%T");
    if(isset($_POST['startTime']) && !empty($_POST['startTime']))
    {
        $startTime=strptime($_POST['startTime'], "%R");
    }
    
    if(isset($_POST['endTime']) && !empty($_POST['endTime']))
    {
        $endTime=strptime($_POST['endTime'], "%R");
    } 
 
 
    $startBox=getStartBox($startTime);
    $endBox = getEndBox($startTime,$endTime);

//     $post = arrayPrint($_POST);
//     $post .= "<br>" . arrayPrint($startTime);
//     $post .= "<br>" . arrayPrint($endTime);
    
    $html=<<<eof
    <h2> Set Lab Times</h2>
    <div class="flex flexGrow group">
        <form class="flex flexColumn flexGrow" action="managelabhours.php" method="post">
            <div class="flex flexColumn alignCenterFlex flexGrow">
                <div class=' flex flexRow marginTop10 marginBottom20'>
                    <div class="flex flexAlignCenter marginRight20" style="width:100px;">
                        Start Time
                    </div>
                    <div class="flex flexAlignCenter">
                        {$startBox}
                    </div>
                </div>
                <div class='flex flexRow marginBottom20'>
                    <div class="flex flexAlignCenter marginRight20" style= "width:100px;">
                        End Time
                    </div>
                    <div class="flex flexAlignCenter">
                        {$endBox}
                    </div>
                </div>
            </div> <!-- formEntryBlock-->
            <div class="flex flexRow marginBottom10 alignCenterFlex centerFlex flexGrow">
                <input type=submit value="Cancel" name="formCancel" class="btn marginRight10">
                <input type=submit value="Submit" name="formSubmit" class="btn marginLeft10">
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
	$latestTime = strptime("20:00:00", "%T");

	$html = "";

	$html .= "<select onchange=\"this.form.submit()\" name='startTime' class='inputSelectSmall'>\n";

	$shouldContinue = true;

	$val = $initTime;

	while($shouldContinue) {
		$oval = $val;

		$val = advanceHalfHour($val);

		/*
		 * When our advance has hit the next value, don't continue.
		 */
		if($val == $latestTime) {
			$shouldContinue = false;
		}
		
		$valStr = sprintf("%d:%'02d", $oval['tm_hour'], $oval['tm_min']);
		
		if($oval == $currTime) {
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
	
	$nTime = $currTime;

	/*
	 * Set default value if not specified.
	 */
	if($currTime === -1) $nTime = $initTime;

	/*
	 * Latest ending time, 7:30 at night.
	 */
	$latestTime = strptime("20:30:00", "%T");

	$html = "";

	$html .= "<select name='endTime' class='inputSelectSmall'>\n";

	$shouldContinue = true;

	$val = $initTime;

	while($shouldContinue) {
		$oval = $val;

		$val = advanceHalfHour($val);

		/*
		 * When our advance has hit the next value, don't continue.
		 */
		if($val == $latestTime) {
			$shouldContinue = false;
		}
		
		$valStr = sprintf("%d:%'02d", $oval['tm_hour'], $oval['tm_min']);

		
		if($valStr == $nTime) {
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


	
	if($tme['tm_min'] == 30) {
		$ret['tm_hour'] += 1;
		$ret['tm_min']   = 0;
	} else {
		$ret['tm_min'] = 30;
	}

	return $ret;
}

?>
