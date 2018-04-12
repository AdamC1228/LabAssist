<?php

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

    
    function printStartBody()
    {
        echo<<<eof
            <body>                               
                <div id="content">

eof;
    }

    
    
    function printForm($htmlCode,$error)
    {
        echo<<<eof
            <div class="wrapper">
                <div class="title">
                    <div class="logo"><img src="styles/img/logos/logo.png"/></div>
                        <div class="form">
                            <div class="thumbnail"><img src="styles/img/icons/register.svg"/></div>
                            <h1>Registration</h1>
                            <div id="column-Container">
                                <form  action = "user_registration.php" method = "post" id="reg_login">
                                    <div class="register-fields">
                                        <br>
                                        $htmlCode
                                    </div>
                                    <span class="error">$error</span>
                                    <br>
                                    <div class="floatCenter">
                                        <input type="submit" class="btn marginright10" id="reg-cancel" name="reg-cancel" value="Cancel"/> 
                                        <input type="submit" class="btn marginleft10" id="reg-submit" name="reg-submit" value="Submit"/>
                                    </div>
                                    <br>
                                </form>
                            </div>
                        </div>
                </div>
            </div>
eof;
    }

    function printEndBody()
    {
        echo<<<eof
                </div>
            </body>
        </html>
        
eof;
    }


?>
