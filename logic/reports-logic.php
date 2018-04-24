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
    
    if(isSet($_GET['reportSection']) && !empty($_GET['reportSection']))
    {
        $reportSection=$_GET['reportSection'];
    }
    else
    {
        $reportSection=-1;
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

function reportHeaderSectionSelect($prevVal,$term)
{    
    $html = "";

//     $result=safeDBQuery('select sections.secid,sections.code,classes.name from sections,classes where sections.cid=classes.cid and teacher=?',array($_SESSION['username']));

    $result=safeDBQuery('select sections.secid,sections.code,classes.name from sections,classes where sections.cid=classes.cid and teacher=? and term=?',array('800241353',$term));

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
        }
        else
        {
            $html.= "No data available";
        }
    }
    
    //$html.= arrayPrint(reportHourlyUsage());
    return $html;
}

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
        }
        else
        {
            $html.= "No data available";
        }  
    }
    return $html;
}

function studentUsageReport()
{

    $html = "<link rel='stylesheet' type='text/css' href='styles/tables.css'>";
    $html.= studentUsageReportView(reportStudentUsage());

    return $html;
}

function sectionUsageReport()
{
    $html = "";
    $html.= "<link rel='stylesheet' type='text/css' href='styles/tables.css'>";
    
    if(isset($_GET['selectedSection']) && !empty($_GET['selectedSection']))
    {
        $html.= sectionUsageReportView(reportSectionVisits($_GET['selectedSection']));
    }
    
//     $html.= arrayPrint(reportSectionVisits(2));

    return $html;
}

function sectionOverviewReport()
{
    $html = '';
    $data = '';
    if(isset($_GET['selectedSection']) && !empty($_GET['selectedSection']))
    {
        $data = reportSectionSummary($_GET['selectedSection'])[0];
        
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


function studentUsageReportView($dataset)
{

    if(empty($dataset))
	{
		return "<p>Database error<p>";
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
		$retval[wknumtoname($i)] = array();
	}

	
	if($data === -1) {
		return -1;
	}

	foreach($data as $datum) {
		$wkday  = wknumtoname(strftime("%u", strtotime($datum['markin'])));

		if(isset($retval[$wkday])) {
			$retval[$wkday] += 1;
		} else {
			$retval[$wkday] = 1;
		}
	}
	
	return $retval;
}


function reportStudentUsage() {
	$sql = <<<'SQL'
SELECT stu.idno, stu.realname, stu.role, stu.total_hours
	FROM student_total_usage stu
SQL;

	return safeDBQuery($sql, array());
}


function getLimits($dept) {
	$query = <<<SQL
SELECT deptlabs.labstart, deptlabs.labend FROM deptlabs WHERE deptlabs.dept = ?
SQL;
	return safeDBQuery($query, array($dept));
}

function reportSectionVisits($sect) {
	$sql = <<<SQL
SELECT users.realname, users.email, COUNT(usage.markin)
	FROM usage
	JOIN term_sections ON usage.secid = term_sections.secid
	JOIN users         ON usage.student = users.idno
	WHERE usage.secid = ? AND usage.markout IS NOT NULL
	GROUP BY users.idno
SQL;

	return safeDBQuery($sql, array($sect));
}

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

	$dat['total_hours'] = DateInterval::createFromDateString($dat['total_hours']).format("%d days, %h hours and %i minutes");

	return $dat;
}
/*
    NOTE: A report on clockin length might be useful.
 */
?>
