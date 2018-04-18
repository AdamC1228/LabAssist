<?php
    
    function printContent($embedHTML)
    {
        echo<<<eof
        <script src="bower_components/chartist/dist/chartist.min.js"></script>
        <div class="content-wrapper">
            <div class="content">
                $embedHTML
            </div>  <!-- Closing div for content -->
        </div>  <!-- Closing div for content-wrapper -->
eof;
}

?>
