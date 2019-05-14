<?php

require_once "logic/common/commonFunctions.php";




/*
 *
 *  Developers note: Since ID's must be unique per the HTML Standard, we must give the 
 *      expandable items a unique ID. A potential issue of the current implementaiton
 *      means that in the case of a developer, when showing both the admin and staff
 *      menus, it is possible to get duplicate ID's. For example, the Usermanagement
 *      group appears twice in the entire navigation when both menus are displayed. 
 *      due to this, It is reccomended that a random number be concatinated to the 
 *      end of any element that would appear more than once, or write the code in such
 *      a way that the ID's would be unique outright. Currently, a self incrementing
 *      number is used as the solution as it was decided that although the probability
 *      is low if we picked a random number from the entire integer value range that
 *      a duplicate value could occur, we need 100% reliability and thusly disregarded
 *      random number concatination as a potential solution for the unique ID's.
 *
 *
 */





function createNavigation()
{
	//PlaceHolder Vars
	$count=0;
	$default="";
	$sysadmin="";
	$admin="";
	$staff="";
	$tutor="";
	$student="";
	$privleged="";
	$userLevel=getUserLevelAccess($_SESSION['username']);

	//Begin Building the menu
	$html=startNavigation();

	switch($userLevel) {
	case 'sysadmin':
		$sysadmin.=startNavGroup("System Administratration","navsysadmin",false);       //Build the SysAdmin Menu
		$sysadmin.=termManagement(++$count);
		$sysadmin.=deptManagement(++$count);
		$sysadmin.=userManagement();
		$sysadmin.=endNavGroup();
		break;

	case 'developer':
		$sysadmin.=startNavGroup("System Admininistration","navsysadmin",false);        //SINCE DEV MUST SEE ALL SHOW THE SYS ADMIN MENU
		$sysadmin.=termManagement(++$count);
		$sysadmin.=deptManagement(++$count);
		$sysadmin.=userManagement();
		$sysadmin.=endNavGroup();

	case 'admin':
		$admin.=startNavGroup("Administrative","navadmin",false);                       //Build the Admin menu
		$admin.=manageSchedules();
		$admin.=classManagement(++$count);
		$admin.=sectionManagement(++$count);
		$admin.=userManagement();
		$admin.=tutoringHours();
		$admin.=reports();
		$admin.=endNavGroup();

	case 'staff':                                                                       //Build The staff menu
		$staff.=startNavGroup("Staff","navstaff",false);
		$staff.=manageSchedules();
		$staff.=sectionManagement(++$count);
		$staff.=userManagement();
		$staff.=reports();
		$staff.=endNavGroup();

	case 'tutor':                                                                       //Build the Tutor menu
		$tutor.=startNavGroup("Tutor","navtutor",false);
		$tutor.=manageTutorAvailability();
		$tutor.=endNavGroup();

	case 'student':                                                                     //Student group is the main group
		$student.=startNavGroup("Main","navmain",true);
		$student.=homePage();
		$student.=studentDefault();
		$student.=endNavGroup();
		break;

	default:
		$html.=startNavGroup("Main","navmain",true);                                    //Default case only gives the home-page.
		$html.=homePage();   
		$html.=endNavGroup();
		return $html;
	}


	//Determine which group of administrative functions to perform.
	//Admins get the admin menu
	//Staff get the staff menu
	//Developers get both menu systems and the systemadmin menu

	if($userLevel=='admin')
	{
		$privleged=$admin;
	}
	else if ($userLevel == 'developer')
	{
		$privleged=$staff.$admin.$sysadmin;
	}
	else
	{
		$privleged=$staff;
	}

	//Build the menu through creative concatination to get the order correct and then return it. 
	return ($html.=$student.$tutor.$privleged);
}








/*
 *
 *
 *
 *
 *   Navigation component functions
 *
 *
 *
 *
 */




function startNavigation()
{
	$html=<<<eof
	<script src="libraries/jquery/jquery-3.2.1.min.js"></script>
	<script src="scripts/navigation.js"></script>
eof;

	return $html;
}

function startNavGroup($name,$id,$checked)
{
	if($checked==true)
	{
		$html=<<<eof
	<div class="wrap-collapsible">
	    <input id="$id" class="toggle" type="checkbox" checked>
	    <label for="$id" class="lbl-toggle">$name</label>
	    <div class="collapsible-content">
		<div class="content-inner">
		<ul>
eof;
	}
	else
	{
		$html=<<<eof
	    <div class="wrap-collapsible">
		<input id="$id" class="toggle" type="checkbox" >
		<label for="$id" class="lbl-toggle">$name</label>
		<div class="collapsible-content">
		    <div class="content-inner">
		    <ul>
eof;
	}

	return $html;
}


function startSubGroup($name,$unique,$checked)
{
	if($checked==true)
	{
		$html=<<<eof
	<div class="wrap-collapsible">
	    <input id="$unique" class="toggle" type="checkbox" checked>
	    <label for="$unique" class="lblSub-toggle">$name</label>
	    <div class="collapsible-content">
		<div class="content-inner">
		<ul>
eof;
	}
	else
	{
		$html=<<<eof
	    <div class="wrap-collapsible">
		<input id="$unique" class="toggle" type="checkbox" >
		<label for="$unique" class="lblSub-toggle">$name</label>
		<div class="collapsible-content">
		    <div class="content-inner">
		    <ul>
eof;
	}

	return $html;
}

function endNavGroup()
{
	$html=<<<eof
	</ul>
		</div>
	    </div>
	</div>
eof;
	return $html;
}



function homePage()
{
	$html=<<<eof
	<li class="">
	    <a href="portal.php" class="nav-menu-item">Home</a>
	</li>
eof;

	return $html;
}

function studentDefault()
{
	$html= <<<eof
	<li class="">
	    <a href="showschedule.php" class="nav-menu-item">Tutor Schedule</a>
	</li>
	<li class="">
	    <a href="forum.php" class="nav-menu-item">Q/A Forum</a>
	</li>
	<!-- <li class="">
	    <a href="updateprofile.php" class="nav-menu-item">Account Settings</a>
	</li> -->
eof;

	return $html;
}


function sectionManagement($uID)
{
	$html=startSubGroup("Section Management","sectMgt".$uID,false);
	$html.=<<<eof
	    <li>
		<a href="managesections.php" class="nav-menu-item-sub">Manage Sections</a>
	    </li>
	    <li>
		<a href="addsection.php" class="nav-menu-item-sub">Add Section</a>
	    </li>
eof;
	$html.=endNavGroup();

	return $html;
}

function termManagement($uID)
{
	$html=startSubGroup("Term Management","termMgt".$uID,false);
	$html.=<<<eof
	    <li>
		<a href="manageterms.php" class="nav-menu-item-sub">Manage Terms</a>
	    </li>
	    <li>
		<a href="addterm.php" class="nav-menu-item-sub">Add Terms</a>
	    </li>
eof;
	$html.=endNavGroup();

	return $html;
}

function deptManagement($uID)
{
	$html=startSubGroup("Department Management","deptMgt".$uID,false);
	$html.=<<<eof
	    <li>
		<a href="managedepartments.php" class="nav-menu-item-sub">Manage Departments</a>
	    </li>
	    <li>
		<a href="adddepartment.php" class="nav-menu-item-sub">Add Department</a>
	    </li>
eof;
	$html.=endNavGroup();

	return $html;
}

function classManagement($uID)
{
	$html=startSubGroup("Class Management","clasMgt".$uID,false);
	$html.=<<<eof
	<li>
	    <a href="manageclasses.php" class="nav-menu-item-sub">Manage Classes</a>
	</li>
	<li>
	    <a href="addclass.php" class="nav-menu-item-sub">Add Class</a>
	</li>
eof;
	$html.=endNavGroup();

	return $html;
}

function userManagement()
{
	$html=<<<eof
	<li class="">
	    <a href="manageusers.php" class="nav-menu-item">User Management</a>
	</li>
eof;
	return $html;
}

function tutoringHours()
{
	$html=<<<eof
	<li class="">
	    <a href="managelabhours.php" class="nav-menu-item">Lab Hours</a>
	</li>
eof;

	return $html;
}

function reports()
{
	$html=<<<eof
	<li class="">
	    <a href="reports.php" class="nav-menu-item">Reports</a>
	</li>
eof;

	return $html;
}

function manageSchedules()
{
	$html= <<<eof
	<li class="">
	    <a href="manageschedules.php" class="nav-menu-item">Assign Tutor Schedule</a>
	</li>
eof;
	return $html;
}

function manageTutorAvailability() {
	$html = <<<eof
<li class="">
    <a href="managetutoravailability.php" class="nav-menu-item">Update Tutor Availability</a>
</li>
eof;

	return $html;
}
?>
