<?php

/*
 * Print the content.
 */
function printContent($embedHTML)
{
	echo<<<eof
	<div class="content-wrapper">
	    <div class="content">
		$embedHTML
	    </div>  <!-- Closing div for content -->
	</div>  <!-- Closing div for content-wrapper -->
eof;
}

?>

