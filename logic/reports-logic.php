<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";

#Global to the file
$availableReports = array(array('Lab Usage By Hour','0'),array('Lab Usage By Day','1'),array('Student Usage','2'));


/*
 *
 *
 *  Report Header Functions
 *
 *
*/


function reportHeader($prevVal)
{
    $html = "<h2>Reports</h2>\n";
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
            //$html.= "<h1>{$availableReports[0][0]} report</h1>";
            $html.= "<h2 class='centerText'>Hourly Data </h2>";
            $html.= labUsageReportHourly();
            break;
        case 1:
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
    $data = reportHourlyUsage();
    
    $html = "<h2>Monday</h2>";
    $html.= "<div id='Mon'></div>";
    $html.= lineChartWithArea($data[1],"#Mon");

    $html.= "<h2>Tuesday</h2>";
    $html.= "<div id='Tue'></div>";
    $html.= lineChartWithArea($data[2],"#Tue");
    
    $html.= "<h2>Wednesday</h2>";
    $html.= "<div id='Wed'></div>";
    $html.= lineChartWithArea($data[3],"#Wed");
    
    $html.= "<h2>Thursday</h2>";
    $html.= "<div id='Thu'></div>";
    $html.= lineChartWithArea($data[4],"#Thu");
    
    $html.= "<h2>Friday</h2>";
    $html.= "<div id='Fri'></div>";
    $html.= lineChartWithArea($data[5],"#Fri");
    
    //$html.= arrayPrint(reportHourlyUsage());
    return $html;
}

function labUsageReportDaily()
{

    $data = reportDailyUsage();
    
    $html = "<h2>Daily Usage</h2>";
    $html.= "<div id='week'></div>";
    $html.= barChart($data,"#week");

    $html.= arrayPrint(reportDailyUsage());
    
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
 *  Charting Functions
 *
 *
*/


function lineChartWithArea($array,$cssElement)
{   
    $html="";
    
    $labels="";
    $data="";
    $labels.=implode(',',array_keys($array));
    $data.=implode(',',array_values($array));
    
    $html.=<<<eof
    <script>
    new Chartist.Line('$cssElement', {
            labels: [$labels],
            series: [
                [$data]
            ]
        }, {
            low: 0,
            showArea: true
        },
                axisY: {
                    onlyInteger: true
                },
                plugins: [
                    Chartist.plugins.ctAxisTitle({
                        axisX: {
                            axisTitle: 'Time (mins)',
                            axisClass: 'ct-axis-title',
                            offset: {
                                x: 0,
                                y: 50
                            },
                            textAnchor: 'middle'
                        },
                        axisY: {
                            axisTitle: 'Goals',
                            axisClass: 'ct-axis-title',
                            offset: {
                                x: 0,
                                y: -1
                            },
                            flipTitle: false
                        }
                    })
                ]
        });
    </script>
eof;
    
    return $html;
}

function barChart($array,$cssElement)
{   
//     var_dump($array);
    $html="";
    
    $labels="";
    $data="";

    $labels.=implode(',',array_keys($array));
    $data.=implode(',',array_values($array));
    
    $html.=<<<eof
    <script>
    new Chartist.Bar('$cssElement', {
            labels: [$labels],
            series: [
                [$data]
            ]
        }, {
            low: 0
        });
    </script>
eof;
    
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
WITH dept_sections AS (
	SELECT * FROM term_sections
		JOIN classes ON term_sections.cid = classes.cid
		WHERE classes.dept = ?
)
SELECT usage.markin, usage.markout FROM usage
	JOIN dept_sections ON usage.secid = dept_sections.secid
SQL;
	$dept = getUsersDepartment($_SESSION['username'])
	$data = safeDBQuery($query, array($dept));

	if($data === -1) {
		return -1;
	}

	$lims = getLimits($dept);
	if($lims === -1) {
		return -1;
	}

	$lstart = strptime($lims[0]['labstart'], "%T");
	$lend   = strptime($lims[0]['labend'], "%T");
	
	$retval = array();
	for($i = 1; $i <= 5; $i++) {
		$retval[$i] = array();

		for($j = $lstart['tm_hour']; $j <= $lend['tm_end']; $j++) {
			$retval[$i][$j] = 0;
		}
	}

	foreach($data as $datum) {
		$formatstr = "%Y-%m-%d %T";

		$begin = strptime($datum['markin'],  $formatstr);
		$end   = strptime($datum['markout'], $formatstr);

		$wkday = strftime("%u", strtotime($datum['markin']));

		/*
		 * NOTE:
		 *
		 * Maybe we want this report to be a count of clock-ins/hour for 
		 * each day.
		 */
		$minVal = min($end['tm_hour'],   $lend['tm_hour']);
		$maxVal = max($begin['tm_hour'], $lstart['tm_hour']);

		for($initVal = $maxVal; $initVal <= $minVal; $initVal++) {
			if(isset($retval[$wkday][$initVal])) {
				$retval[$wkday][$initVal] += 1;
			} else {
				$retval[$wkday][$initVal] = 1;
			}
		}
	}

	/*
	for($i = 1; $i <= 5; $i++) {
		ksort($retval[$i]);
	} 
	 */

	return $retval;
}

function reportDailyUsage() {
	$query = <<<'SQL'
SELECT usage.markin, usage.markout FROM usage
	JOIN term_sections ON usage.secid = term_sections.secid
	JOIN classes ON term_sections.cid = classes.cid
	WHERE classes.dept = ?
SQL;

	$dept = getUsersDepartment($_SESSION['username']);
	$data = safeDBQuery($query, array($dept));

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

	for($i = 1; $i <= 5; $i++) {
		ksort($retval);
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


function getLimits($dept) {
	return safeDBQuery("SELECT deptlabs.labstart, deptlabs.labend FROM deptlabs WHERE deptlabs.dept = ?", array($dept));
}

function reportSectionVisits($sect) {
	$query = <<<SQL
SELECT users.realname, COUNT(usage.markin)
	FROM usage
	JOIN term_sections ON usage.secid = term_sections.secid
	JOIN users         ON usage.student = users.idno
	WHERE usage.secid = ? AND usage.markout IS NOT NULL
	GROUP BY users.idno
SQL;

	return safeDBQuery($query, array($sect));
}

function reportSectionSummary($sect) {
	$sql = <<<SQL
SELECT COUNT(DISTINCT usage.student) as dist_visits, COUNT(usage.student) as all_visits,
	SUM(usage.markout - usage.markin) as total_hours
	FROM usage
	WHERE usage.secid = ? AND usage.markout IS NOT NULL
SQL;

	return safeDBQuery($query, array($sect));
}
/*
    NOTE: A report on clockin length might be useful.
 */

/*
 * Get the department for a user.
 */
function getUsersDepartment($user)
{
	$result = databaseQuery("SELECT deptid FROM users WHERE username=?", array($user));

	if(!empty($result) && is_array($result))
	{
		return $result[0]['deptid'];
	}
	else
	{
		return -1;
	}
}
?>
