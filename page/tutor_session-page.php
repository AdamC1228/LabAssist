<?php

/*
 * Print the header.
 */
function printHeader()
{
	echo<<<eof
	    <!DOCTYPE html>
	    <head>
		<link rel="stylesheet" type="text/css" href="styles/primary.css" />
		<link rel="stylesheet" type="text/css" href="styles/login.css" />
		<link href="https://fonts.googleapis.com/css?family=Montserrat:400,400i,700,700i" rel="stylesheet"> 
		<link href="https://fonts.googleapis.com/css?family=Patrick+Hand+SC" rel="stylesheet"> 
		<title>Welcome to LabAssist</title>
	    </head>

eof;
}


/*
 * Print the starting body.
 */
function printStartBody()
{
	echo<<<eof
	    <body>                               
		<div id="content" style="disply: block;">

eof;
}



/*
 * Print the form.
 */
function printForm($htmlCode,$error,$buttons)
{
	echo<<<eof
	    <div class="wrapper">
		<div class="title">
		    <div class="logo"><img src="styles/img/logos/logo.png"/></div>
			<div class="form">
			    <div class="thumbnail"><img src="styles/img/icons/board.svg"/></div>
			    <div id="column-Container">
				<form  action = "tutor_session.php" method = "post" id="log_login">
				    <div class="register-fields">
					$htmlCode
				    </div>
				    <span class="error">$error</span>
				    <br>
				    <br>
					$buttons
				    <br>
				</form>
			    </div>
			</div>
		</div>
	    </div>
eof;
}

/*
 * Print the ending body.
 */
function printEndBody()
{
	echo<<<eof
		</div>
		<script type="text/javascript">
		    document.getElementById("content").style.display="block";
		</script>
	    </body>
	</html>

eof;
}


?>
