<?php
    
    function printContent($embedHTML)
    {
        echo<<<eof
        <link rel="stylesheet" type="text/css" href="styles/tables.css" />
        <link rel="stylesheet" type="text/css" href="styles/normalize.css" />
        <div class="content-wrapper">
            <div class="content">
                $embedHTML
            </div>  <!-- Closing div for content -->
        </div>  <!-- Closing div for content-wrapper -->
eof;
}

?>
