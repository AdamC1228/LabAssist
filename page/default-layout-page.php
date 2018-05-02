<?php

require_once "logic/common/version.php";

/*
 * Print the default layout for content.
 */
function printContent($embedHTML)
{
    $version=getCurrentVersion();
    
	echo<<<eof
	<link rel="stylesheet" type="text/css" href="styles/tables.css" />
	<link rel="stylesheet" type="text/css" href="styles/normalize.css" />
	<div class="content-wrapper">
	    <div class="content">
		$embedHTML
		
        <div class='footerCust'>
            <b><em>$version</em></b>
        </div>
        
	    </div>  <!-- Closing div for content -->
	</div>  <!-- Closing div for content-wrapper -->
eof;
}

?>
