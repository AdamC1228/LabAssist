<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";

#Global to the file
$availableReports = array(array('Lab Usage By Hour','0'),array('Lab Usage By Day','1'),array('Usage by Student','2'),array('Section Usage','3'));

$sectionReports = array(array('Section Usage','3'));



/*
 *
 *
 *  Report Header Functions
 *
 *
 */

/*
 * Print report header.
 */
function printReportHeader()
{

	$reportSelected='0';
	$term='';
	$reportSection='';

	#Print the header where the user can select the appropriate report
	if(isSet($_GET['selectedReport']) && !empty($_GET['selectedReport']))
	{
		$reportSelected=$_GET['selectedReport'];
	}

	if(isSet($_GET['selectedTerm']) && !empty($_GET['selectedTerm']))
	{
		$term=$_GET['selectedTerm'];
	}
	else
	{
		$term=databaseExecute("select code from terms where activeterm=true;")[0]['code'];
	}

	if(isset($_GET['selectedSection']) && !empty($_GET['selectedSection'])) {
		$reportSection=$_GET['selectedSection'];
	} else {
		$reportSection = -1;
	}

	$html = "";
	$html.= "<h2>Reports</h2>\n";
	$html.= "<form action='{$_SERVER['PHP_SELF']}' method='GET'>";
	$html.= "   <div class='flex group alignCenterFlex marginBottom80'>";
	$html.=         reportHeader($reportSelected,$term);
	$html.=         reportSections($reportSelected,$reportSection,$term);
	$html.= "       <div class='flex flexAlignCenter marginBottom10 marginLeft40'>";
	$html.= "           <input name='showReport' style='height:40px; margin-top:10px;' class='btn' type='submit' value='View Report'>";
	$html.= "       </div> <!-- btn Group -->";
	$html.= "   </div> <!-- Header Group -->";
	$html.= "</form> <!-- Header Form -->";

	return $html;
}


/*
 * Create the report header.
 */
function reportHeader($prevRep,$prevTerm)
{
	$html = "<div class='flexColumn flex marginRight20 paddingRight10' style='max-width:450px;'>\n";
	$html.= "   <div class='marginLeft10 flexRow  rightAlignFlex flexAlignCenter marginTop10 marginBottom10 flex'>\n";
	$html.= "       <div class='flex flexAlignCenter paddingRight20'>\n";
	$html.=             "<span>Select a report:</span>\n";
	$html.= "       </div>\n";
	$html.= "       <div class='flex flexAlignCenter'>\n";
	$html.=             reportHeaderReportSelect($prevRep);
	$html.= "       </div>\n";
	$html.= "   </div> <!-- End Columns --> \n";
	$html.= "   <div class='marginLeft10 flexRow rightAlignFlex flexAlignCenter marginTop10 marginBottom10 flex'>\n";
	$html.= "       <div class='flex flexAlignCenter paddingRight20'>\n";
	$html.=             "<span>Select a term:</span>\n";
	$html.= "       </div>\n";
	$html.= "       <div class='flex flexAlignCenter'>\n";
	$html.=             reportHeaderTermSelect($prevTerm);
	$html.= "       </div>\n";
	$html.= "   </div> <!-- End Columns --> \n";
	$html.= "</div> <!-- End the group --> \n";

	return $html;
}

/*
 * Create the report selector.
 */
function reportHeaderReportSelect($prevVal)
{    
	global $availableReports;
	$html = "";


	$html.= "  <div>";
	$html.= "      <select name='selectedReport' onchange='this.form.submit()' class='inputSelect'>";
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


	return $html;
}

/*
 * Create the sections report.
 */
function reportSections($report,$prevVal,$term)
{
	global $sectionReports;

	if(in_array($report,array_column($sectionReports,'1')))
	{
		$html = "<div class='flex marginLeft10 paddingRight20' style='max-width:450px; max-height:60px;'>\n";
		$html.= "   <div class='marginLeft10 flexRow flexAlignCenter paddingRight20 marginTop10 marginBottom10 flex'>\n";
		$html.= "       <div class='flex flexAlignCenter paddingRight20'>\n";
		$html.=             "<span>Section:</span>\n";
		$html.= "       </div>\n";
		$html.= "       <div class='flex flexAlignCenter '>\n";
		$html.=             reportHeaderSectionSelect($prevVal,$term);
		$html.= "       </div>\n";
		$html.= "   </div> <!-- End Columns --> \n";
		$html.= "</div> <!-- End the group --> \n";
		return $html;
	}
}

/*
 * Create the header for the sections report.
 */
function reportHeaderSectionSelect($prevVal,$term)
{    
	$html = "";

	if(isUserRoleGreaterThanOrEqualTo($_SESSION['useridno'], 'admin') === 1) {
		$result=safeDBQuery('select sections.secid,sections.code,classes.name from sections,classes where sections.cid=classes.cid and dept=? and term=?',array(getUsersDepartment($_SESSION['username']),$term));
	} else {
		$result=safeDBQuery('select sections.secid,sections.code,classes.name from sections,classes where sections.cid=classes.cid and teacher=?',array($_SESSION['username']));
	}

	if($result==-1)
		return "No sections taught for selected term";

	$html.= "  <div>";
	$html.= "      <select name='selectedSection'   class='inputSelect'>";
	foreach($result as $row)
	{
		if ($prevVal == $row['secid'])
		{
			$html.= "<option value=\"" . $row['secid'] . "\" selected>[{$row['code']}] - {$row['name']}</option>";
		}
		else
		{
			$html.= "<option value=\"" . $row['secid']  . "\">[{$row['code']}] - {$row['name']}</option>";
		}
	}
	$html.= "      </select>";
	$html.= "  </div>";


	return $html;
}

/*
 * Get the list of terms.
 */
function reportHeaderTermSelect($current)
{
	$sql="SELECT code,activeterm FROM terms";
	$result = databaseExecute($sql);

	if(empty($result) || !is_array($result))
	{
		$html = "Unable to fetch terms. Contact system administrator.";
	}
	else
	{            
		/*
		 * Create select box.
		 */
		$html="<div><select name='selectedTerm' onchange='this.form.submit()' class='inputSelect '>";

		foreach($result as $row)
		{
			if ($current == $row["code"])
			{
				if($row['activeterm']==true)
				{
					$html.= "<option value='{$row['code']}' selected>{$row['code']} (Active Term)</option>";
				}
				else
				{
					$html.= "<option value='{$row['code']}' selected>{$row['code']}</option>";
				}
			}
			else
			{
				if($row['activeterm']==true)
				{
					$html.= "<option value='{$row['code']}' >{$row['code']} (Active Term)</option>";
				}
				else
				{
					$html.= "<option value='{$row['code']}' >{$row['code']}</option>";
				}
			}
		}

		$html .= "</select></div>";
	}

	return $html;
}

/*
 *
 *
 *  Report Control functions
 *
 *
 */


/*
 * Print a report.
 */
function printReport($reportID)
{
	global $availableReports;
	$html = "";

	switch ($reportID) {
	case 0:
		//LabUsageReport
		//$html.= "<h1>{$availableReports[0][0]} report</h1>";
		$html.= "<h2 class='centerText'>Hourly Usage</h2>";
		$html.= labUsageReportHourly();
		break;
	case 1:
		$html.= "<h2 class='centerText'>Daily Usage</h2>";
		$html.= labUsageReportDaily();
		break;
	case 2:
		//Student Usage Report
		$html.= "<h2 class='centerText'>Student Usage</h2>";
		$html.= studentUsageReport();
		break;
	case 3:
		$html.= "<h2 class='centerText marginTop30'>Section Usage Overview</h2>";
		$html.= sectionOverviewReport();
		$html.= sectionUsageReport();
		break;
	case 4:
		$html.= "<h2 class='centerText'>EasterEgg</h2>";
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

function generateDownloadLink($header, $data , $pos)
{

    $filename=createCSV($header,$data ,$pos);

    $html=<<<eof
    
    <div class='flex flexGrow flexAlignCenter center Flex marginTop30 marginBottom80 centerAlignFlex'>
        <form action='reportdownload.php' action='GET' class='flex centerFlex flexGrow flexAlignCenter centerAlignFlex'>

                <input type='hidden' name='file' value='{$filename}'>
                <input type='submit' class='btn' name='formSubmit' value='Download raw data'>

                
        </form>
    </div>
    
eof;


    return $html;
}
 
 
 
 
 

/*
 * Create the hourly lab usage report.
 */
function labUsageReportHourly()
{
	$html = "";
	if(isSet($_GET['selectedTerm']) && !empty($_GET['selectedTerm']))
	{
		$term=$_GET['selectedTerm'];
		$data = reportHourlyUsage($term);

		if($data !== -1)
		{
			$html.= "<script src='bower_components/chartist-plugin-axistitle/dist/chartist-plugin-axistitle.js'></script>";

			$html.= "<h2>Monday</h2>";
			$html.= "<div class='custLine' id='Mon'></div>";
			$html.= lineChartWithArea($data[1],"#Mon");

			$html.= "<h2>Tuesday</h2>";
			$html.= "<div class='custLine' id='Tue'></div>";
			$html.= lineChartWithArea($data[2],"#Tue");

			$html.= "<h2>Wednesday</h2>";
			$html.= "<div class='custLine' id='Wed'></div>";
			$html.= lineChartWithArea($data[3],"#Wed");

			$html.= "<h2>Thursday</h2>";
			$html.= "<div class='custLine' id='Thu'></div>";
			$html.= lineChartWithArea($data[4],"#Thu");

			$html.= "<h2>Friday</h2>";
			$html.= "<div class='custLine' id='Fri'></div>";
			$html.= lineChartWithArea($data[5],"#Fri");
			
            $html.= generateDownloadLink(array_keys($data[1]),array_values($data),1);
		}
		else
		{
			$html.= "No data available";
		}
	}

	//$html.= arrayPrint(reportHourlyUsage());
	return $html;
}

/*
 * Create the daily lab usage report.
 */
function labUsageReportDaily()
{
	$html = "";
	if(isSet($_GET['selectedTerm']) && !empty($_GET['selectedTerm']))
	{
		$term =$_GET['selectedTerm'];
		$data = reportDailyUsage($term);

		if($data !== -1)
		{
			$html.= "<script src='bower_components/chartist-plugin-axistitle/dist/chartist-plugin-axistitle.js'></script>";
			$html.= "<div class='custBar' id='week'></div>";
			$html.= barChart($data,"#week");
			$html.= generateDownloadLink(array_keys($data[1]),array_values($data),1);
		}
		else
		{
			$html.= "No data available";
		}  
	}
	return $html;
}

/*
 * Crete the student usage report.
 */
function studentUsageReport()
{
    $html = "";
    if(isSet($_GET['selectedTerm']) && !empty($_GET['selectedTerm']))
    {
            $term = $_GET['selectedTerm'];
            $data = reportStudentUsage($term);
            $html.= "<link rel='stylesheet' type='text/css' href='styles/tables.css'>";
            $html.= studentUsageReportView($data);
            $html.= generateDownloadLink(array_keys($data[0]),array_values($data),1);
    }
    return $html;

}

/*
 * Create the section usage report.
 */
function sectionUsageReport()
{
	$html = "";
	$html.= "<link rel='stylesheet' type='text/css' href='styles/tables.css'>";

	if(isset($_GET['selectedSection']) && !empty($_GET['selectedSection']))
	{
		if(isset($_GET['selectedTerm']) && !empty($_GET['selectedTerm']))
		{
            $data = reportSectionVisits($_GET['selectedSection'],$_GET['selectedTerm']);
            if($data !== -1 )
            {
                $html.= sectionUsageReportView($data);
                $html.= generateDownloadLink(array_keys($data[0]),array_values($data),1);
            }
		}
	}

	return $html;
}

/*
 * Create the section overview report.
 */
function sectionOverviewReport()
{
	$html = '';
	$data = '';
	if(isset($_GET['selectedSection']) && !empty($_GET['selectedSection']))
	{
		$data = reportSectionSummary($_GET['selectedSection']);

		if($data['total_hours']===null)
		{
			$data['total_hours']=0;
		}


		$html.=<<<eof
	<div class='group flex flexAlignCenter marginBottom40 reportOverviewWrapper'>
	    <div class='flex reportOverview'>
		<div class= 'flex flexGrow flexColumn'>
		    <div class='flex flexRow rightAlignFlex'>
			<div class='flex marginLeft10 rightAlignFlex marginRight20'>
			    <b>Distinct Visits:</b>
			</div>
			<div class='flex' style='min-width:40px;'>
			    {$data['dist_visits']}
			</div>
		    </div> <!-- end row -->
		    <div class='flex flexGrow rightAlignFlex flexRow'>
			<div class='flex marginLeft10 rightAlignFlex marginRight20'>
			    <b>Total Visits:</b>
			</div>
			<div class='flex' style='min-width:40px;'>
			    {$data['all_visits']}
			</div>
		    </div> <!-- end row -->
		</div><!-- group 1 -->
		<div class= 'flex flexGrow flexColumn'>
		    <div class='flex flexGrow rightAlignFlex flexRow'>
			<div class='flex marginLeft10 marginRight20'>
			    <b>Cumulative Tutoring Hours:</b>
			</div>
			<div class='flex' style='min-width:40px;'>
			    {$data['total_hours']}
			</div>
		    </div> <!-- end row -->
		</div> <!-- end group2 -->
	    </div> <!-- end overview -->
	</div> <!-- end overviewWrapper -->
eof;

	}
	//     $html.= arrayPrint(reportSectionSummary(2));

	return $html;
}

/*
 *
 *
 *  Charting Functions
 *
 *
 */


/*
 * Create a line chart.
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
	    showArea: true,
	    height: 250,
	    axisY: {
		onlyInteger: true,
		offset:50
	    },
	    axisX: {
		onlyInteger: true,
		offset:50
	    },
	    plugins: [
		Chartist.plugins.ctAxisTitle({
		    axisX: {
			axisTitle: 'Hour',
			axisClass: 'ct-axis-title',
			offset: {
			    x: 0,
			    y: 50
			},
			textAnchor: 'middle'
		    },
		    axisY: {
			axisTitle: 'Logins',
			axisClass: 'ct-axis-title',
			offset: {
			    x: 0,
			    y: -15
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

/*
 * Create a bar chart.
 */
function barChart($array,$cssElement)
{   
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
	    low: 0,
	    height: 250,
	    axisY: {
		onlyInteger: true,
		offset:50
	    },
	    axisX: {
		onlyInteger: true,
		offset:50
	    },
	    plugins: [
		Chartist.plugins.ctAxisTitle({
		    axisX: {
			axisTitle: 'Day',
			axisClass: 'ct-axis-title',
			offset: {
			    x: 0,
			    y: 50
			},
			textAnchor: 'middle'
		    },
		    axisY: {
			axisTitle: 'Logins',
			axisClass: 'ct-axis-title',
			offset: {
			    x: 0,
			    y: -15
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



/*
 *
 *
 *  Table Functions
 *
 *
 */


/*
 * Create a student usage table.
 */
function studentUsageReportView($dataset)
{

	if(empty($dataset) || ($dataset === -1))
	{
		return "<p>No data available<p>";
	}

	$html=<<<eof
	<div class="tableStyleA dropShadow center" id="table">
	<form class="" action="manageusers.php" method="post">
	<table>
	    <thead>
		<tr>
		    <th>ID#</th>
		    <th>Full Name </th>
		    <th>Role</th>
		    <th>Lab Usage (Hrs)</th>
		</tr>
	    </thead>
eof;
	foreach ($dataset as $row)
	{
		$html.="<tr>\n";
		$html.="    <td>". $row['idno'] ."</td>\n";
		$html.="    <td>". $row['realname'] ."</td>\n";
		$html.="    <td>". $row['role'] ."</td>\n";
		$html.="    <td>". $row['total_hours'] ."</td>\n";
		$html.="</tr>\n";
	}

	$html.="</table>\n</form>\n</div>\n";

	return $html;
}

/*
 * Create a section usage report.
 */
function sectionUsageReportView($dataset)
{

	$html=<<<eof
	<div class="tableStyleA dropShadow center marginTop30" id="table">
	<form class="" action="manageusers.php" method="post">
	<table>
	    <thead>
		<tr>
		    <th>Full Name </th>
		    <th>E-mail</th>
		    <th>Lab Visits</th>
		</tr>
	    </thead>
eof;

	if(empty($dataset) || !is_array($dataset))
	{
		$html.="</table>\n</form>\n</div>\n";
	}
	else
	{
		foreach ($dataset as $row)
		{
			$html.="<tr>\n";
			$html.="    <td>". $row['realname'] ."</td>\n";
			$html.="    <td>". $row['email'] ."</td>\n";
			$html.="    <td>". $row['count'] ."</td>\n";
			$html.="</tr>\n";
		}

		$html.="</table>\n</form>\n</div>\n";
	}

	return $html;
}

/*
 *
 *
 *  Report Data Functions
 *
 *
 */

/*
 * Get the hourly usage report.
 */
function reportHourlyUsage($term) {

	$query = <<<'SQL'
WITH dept_sections AS (
	SELECT * FROM (SELECT * FROM sections WHERE sections.term = ?) AS ts
		JOIN classes ON ts.cid = classes.cid
		WHERE classes.dept = ? AND ts.code <> 'TUT' -- Filter out tutoring sections
)
SELECT usage.markin, usage.markout FROM usage
	JOIN dept_sections ON usage.secid = dept_sections.secid
SQL;
	$dept = getUsersDepartment($_SESSION['username']);
	$data = safeDBQuery($query, array($term, $dept));

	if($data === -1) {
		return -1;
	}

	$lims = getLimits($dept);
	if($lims === -1) {
		return -1;
	}

	$lstart = strptime($lims[0]['labstart'], "%T");
	$lend   = strptime($lims[0]['labend'],   "%T");

	$retval = array();
	for($i = 1; $i <= 5; $i++) {
		$retval[$i] = array();

		for($j = $lstart['tm_hour']; $j <= $lend['tm_hour']; $j++) {
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

	return $retval;
}

/*
 * Create the daily usage report.
 */
function reportDailyUsage($term) {
	$query = <<<'SQL'
WITH dept_sections AS (
	SELECT * FROM (SELECT * FROM sections WHERE sections.term = ?) as ts
		JOIN classes ON ts.cid = classes.cid
		WHERE classes.dept = ? AND ts.code <> 'TUT' -- Filter out tutoring sections
)
SELECT usage.markin, usage.markout FROM usage
	JOIN dept_sections ON usage.secid = dept_sections.secid
SQL;

	$dept = getUsersDepartment($_SESSION['username']);
	$data = safeDBQuery($query, array($term, $dept));

	$retval = array();
	for($i = 1; $i <= 5; $i++) {
		$retval["'" . wknumtoname($i) . "'"] = 0;
	}

	if($data === -1) {
		return -1;
	}

	foreach($data as $datum) {
		$wkday  = "'" . wknumtoname(strftime("%u", strtotime($datum['markin']))) . "'";

		if(isset($retval[$wkday])) {
			$retval[$wkday] += 1;
		} else {
			$retval[$wkday] = 1;
		}
	}

	return $retval;
}


/*
 * Get the student usage data.
 */
function reportStudentUsage($term) {
	$sql = <<<'SQL'
WITH filt_sections AS (
	SELECT * FROM sections WHERE sections.term = ?
)
SELECT users.idno,
	users.realname,
	users.role,
	sum(usage.markout - usage.markin) AS total_hours
	FROM usage
	JOIN filt_sections ON usage.secid = filt_sections.secid
	JOIN users         ON usage.student = users.idno
	WHERE usage.markout IS NOT NULL
	GROUP BY usage.student, users.realname, users.role, users.idno
	ORDER BY users.role, users.realname
SQL;

	$res = safeDBQuery($sql, array($term));
	if($res === -1) return -1;

	$ret = array();

	foreach($res as $dat) {
		$dat['total_hours'] = explode('.', $dat['total_hours'])[0];

		array_push($ret, $dat);
	}

	return $ret;
}


/*
 * Get the limit data.
 */
function getLimits($dept) {
	$query = <<<SQL
SELECT deptlabs.labstart, deptlabs.labend FROM deptlabs WHERE deptlabs.dept = ?
SQL;
	return safeDBQuery($query, array($dept));
}

/*
 * Get the section visit report.
 */
function reportSectionVisits($sect, $term) {
	$sql = <<<SQL
WITH filt_sections AS (
	SELECT * FROM sections WHERE sections.term = ?
)
SELECT users.realname, users.email, COUNT(usage.markin)
	FROM usage
	JOIN filt_sections ON usage.secid = filt_sections.secid
	JOIN users         ON usage.student = users.idno
	WHERE usage.secid = ? AND usage.markout IS NOT NULL
	GROUP BY users.idno
SQL;

	return safeDBQuery($sql, array($term, $sect));
}

/*
 * Get the section summary report.
 */
function reportSectionSummary($sect) {
	$sql = <<<SQL
SELECT COUNT(DISTINCT usage.student) as dist_visits, COUNT(usage.student) as all_visits,
	SUM(usage.markout - usage.markin) as total_hours
	FROM usage
	WHERE usage.secid = ? AND usage.markout IS NOT NULL
SQL;

	$dat = safeDBQuery($sql, array($sect));
	if($dat === -1) {
		return -1;
	}

	$dat = $dat[0];

	$tstamp = explode(".", $dat['total_hours'])[0];

	$ar = array();

	/*
	 * Fix issue with '9 days ...'
	 */
	if(stripos($tstamp, "days") !== FALSE) {
		list($dys, $hrs, $mins, $secs) = sscanf($tstamp, "%d days %d:%d:%d");

		while($hrs >= 24) {
			$dys += 1;
			$hrs -= 24;
		}

		$strang = sprintf("%'02d:%'02d:%'02d", $hrs, $mins, $secs);

		$ar = strptime($strang, "%T");

		$ar['tm_hour'] += (24 * $dys);
	} else {
		$ar = strptime($tstamp, "%T");
	}

	$days = 0;
	while($ar['tm_hour'] >= 24) {
		$days += 1;

		$ar['tm_hour'] -= 24;
	}

	if($days > 0) {
		$dat['total_hours'] = sprintf("%d days, %d:%'02d", $days, $ar['tm_hour'], $ar['tm_min']);
	} else {
		$dat['total_hours'] = sprintf("%d:%'02d", $ar['tm_hour'], $ar['tm_min']);
	}

	return $dat;
}
/*
    NOTE: A report on clockin length might be useful.
 */
?>
