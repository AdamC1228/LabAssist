 <?php
header("Content-type: text/csv");
header("Content-disposition: attachment; filename=report.csv");
readfile($_GET['file']);
?>
