<?php

    function printHeader()
    {
        echo<<<eof
            <!DOCTYPE html>
            <head>
                <link rel="stylesheet" type="text/css" href="styles/primary.css" />
                <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet"> 
                <link rel="stylesheet" type="text/css" href="styles/login.css" />
                <title>Welcome to LabAssist</title>
            </head>

        
eof;
    }

    
    function printStartBody()
    {
        echo<<<eof
            <body>                               
                <div id="content" style='display:none;'>


eof;
    }

    
    
    function printForm($error)
    {
        echo<<<eof
            <div class="wrapper">
                <div class="title">
                    <div class="logo"><img src="styles/img/logos/logo.png"/></div>
                    <div class="form">
                        <form action = "login.php" method = "post" class="login-form" id="pass_login">
                        <div class="thumbnail"><img src="styles/img/icons/user.svg"/></div>
                            <h1> User Login</h1>
                            <br>
                                <div class="control-group">
                                    <input maxlength="30" name="username" type="text" class=" inputprimary" value="" placeholder="Username" id="login-name">
                                </div>
                                <div class="control-group">
                                    <input maxlength="60" name="password" type="password" class=" inputprimary" value="" autocomplete="off" placeholder="Password" id="login-password">
                                </div>
                            <span class="error" id="error1">$error</span>
                            <br>
                            <input type="submit" class="btn" id="user-submit" name="pass-submit" value="Login"/>
                            <br>
                            <br>
                            <p class="message">Looking for kiosk login? <a href="#">Kiosk Login</a></p>
                            <p class="note">Found a bug? Report it <a href="https://github.com/AdamC1228/LabAssist/issues">here!</a></p>
                        </form>

                        <form action = "login.php" method = "post"  class="kiosk-form" id="form_login">
                        <div class="thumbnail"><img src="styles/img/icons/kiosk.svg"/></div>
                            <h2> Kiosk</h2>
                            <br>
                                <div class="control-group">
                                    <input maxlength="30" name="sidno" type="text" class=" inputprimary" value="" autocomplete="off" placeholder="Student/Employee ID #" autofocus id="sidno">
                                </div>
                            <span class="error" id="error2">$error</span>
                            <br>
                            <input type="submit" class="btn" id="kiosk-submit" name="kiosk-submit" value="Login"/>
                            <br>
                            <br>
                            <p class="message">Looking for user login? <a href="#">User Login</a></p>
                            <p class="note">Found a bug? Report it <a href="https://github.com/AdamC1228/LabAssist/issues">here!</a></p>
                        </form>
                    </div>  
                </div>
            </div>
            
eof;
    }

    function printEndBody()
    {
        echo<<<eof
                </div>
            <script src="libraries/jquery/jquery-3.2.1.min.js"></script>
            <script src="scripts/login.js"></script>
                <script type="text/javascript">
                    document.getElementById("content").style.display="block";
                </script>
            </body>
        </html>
        
eof;
    }


?>
