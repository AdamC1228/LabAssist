<?php
    
    function printHeader()
    {
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
        header("Pragma: no-cache"); // HTTP 1.0.
        header("Expires: 0"); // Proxies.  
        echo<<<eof
            <!DOCTYPE html>
            <head>
                <link rel="stylesheet" type="text/css" href="styles/portal.css" />
                <link rel="stylesheet" type="text/css" href="styles/primary.css" />
                <link href="https://fonts.googleapis.com/css?family=Montserrat:400,400i,700,700i" rel="stylesheet"/> 
                <link href="https://fonts.googleapis.com/css?family=Patrick+Hand+SC" rel="stylesheet"/> 
                <title>Welcome to LabAssist</title>
            </head>
eof;

         
    }

function printStartBody()
{
    echo<<<eof
                <body>
                <div class="layout">
eof;
     
}
    
function printPortalHead()
{
    echo<<<eof
                <div class="portalhead">
                    <div class="headername"><b>LabAssist</b></div>
                    <div class="headermenu">
                        <a href="logout.php"> <img src="/styles/img/icons/logout.svg" title="Logout" alt="Logout"/></a>
                    </div>
                </div>  <!-- Closing div for portalhead -->
eof;
}

function printNavBar($userInfo,$navigationHTML)
{
    echo<<<eof

                <div class="sidebar">
                    <div class="userInfo">
                        $userInfo
                    </div>
                    <div class="navigation">
                        <ul>
                            $navigationHTML
                        </ul>
                    </div>
                </div>  <!-- Closing div for sidebar -->
eof;
         
}

function printEndBody()
{
    echo<<<eof
            </div> <!-- Closing div for layout -->
            </body> 
    </html>
    
eof;

         
}

?>
