<?php

    function printHeader()
    {
        echo<<<eof
            <!DOCTYPE html >
            <head>
                <link rel="stylesheet" type="text/css" href="styles/primary.css" />
                <link rel="stylesheet" type="text/css" href="styles/error.css" />
                <title>Welcome to LabAssist</title>
            </head>

        
eof;
    }

    
    function printStartBody()
    {
        echo<<<eof
            <body class="construction">                               
                <div id="content">


eof;
    }

    
    
    function printForm($html)
    {
        echo<<<eof
                    <div class="form">
                    <div class="errSVG"><img src="styles/img/icons/construction.svg"/></div>
                    <h1> Under Construction</h1>
                    $html
                    </div>  
            
eof;
    }

    function printEndBody()
    {
        echo<<<eof
                </div>
            <script src="libraries/jquery/jquery-3.2.1.min.js"></script>
            </body>
        </html>
        
eof;
    }


?>
