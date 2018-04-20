<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";

#Global to the file
$availableReports = array(array('Lab Usage By Hour','0')array('Lab Usage By Day','1'),array('Student Usage','2'));


/*
 *
 *
 *  Report Header Functions
 *
 *
*/


function reportHeader($prevVal)
{
    $html = "<h3>Reports</h3>\n";
    $html.= "<div class='group flex' style='max-width:450px;'>\n";
    $html.= "   <div class='marginLeft10 flexRow flexAlignCenter marginTop10 marginBottom10 flex'>\n";
    $html.= "       <div class='flex flexAlignCenter paddingRight20'>\n";
    $html.=             "<span>Select a report:</span>\n";
    $html.= "       </div>\n";
    $html.= "       <div class='flex flexAlignCenter'>\n";
    $html.=             reportHeaderSelect($prevVal);
    $html.= "       </div>\n";
    $html.= "   </div> <!-- End Columns --> \n";
    $html.= "</div> <!-- End the group --> \n";
 
    return $html;
}

function reportHeaderSelect($prevVal)
{    global $availableReports;
    $html = "";

    $html.= "<form action='{$_SERVER['PHP_SELF']}' method='GET'>";
	$html.= "  <div>";
	$html.= "      <select name='selectedReport' onchange='this.form.submit()'  class='inputSelect'>";
	foreach($availableReports as $row)
	{
		if ($prevVal == $row[1])
		{
			$html.= "<option value=\"" . $row[1] . "\" selected>" . $row[0]  . "</option>";
		}
		else
		{
			$html.= "<option value=\"" . $row[1]  . "\">" . $row[0]  . "</option>";
		}
	}
	$html.= "      </select>";
	$html.= "  </div>";
	$html.= "</form>";

    return $html;
}



/*
 *
 *
 *  Report Control functions
 *
 *
*/


function printReport($reportID)
{
    global $availableReports;
    $html = "";
    
    switch ($reportID) {
        case 0:
            //LabUsageReport
            $html.= "<h1>{$availableReports[0][0]} report</h1>";
            $html.= "<h2>Hourly Data </h2>";
            $html.= labUsageReportHourly();
            break;
        case 2:
            $html.= "<h2>Daily Data </h2>";
            $html.= labUsageReportDaily();
            break;
        case 2:
            //Student Usage Report
            $html.= "<h1>{$availableReports[1][0]} report</h1>";
            $html.= studentUsageReport();
            break;
        case 3:
            $html.= "{$availableReports[2][0]}";
            break;
        case 4:
            $html.= "{$availableReports[3][0]}";
            break;
    }
    
    return $html;
}


/*
 *
 *
 *  Report Helper Functions
 *
 *
*/


function labUsageReportHourly()
{
    $html = arrayPrint(reportHourlyUsage());
    return $html;
}

function labUsageReportDaily()
{
    $html = arrayPrint(reportDailyUsage());
    return $html;
}

function studentUsageReport()
{
    $html = arrayPrint(reportStudentUsage());
    return $html;
}





/*
 *
 *
 *  Report Data Functions
 *
 *
*/



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
*/
?>
