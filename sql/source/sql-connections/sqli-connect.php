<?php

//including the Mysql connect parameters.
include("../sql-connections/db-creds.inc");
include "../../../config/config.inc.php";
error_reporting(0);

//mysql connections for stacked query examples.
$con1 = mysqli_connect($_DVWA['db_server'],$_DVWA['db_user'],$_DVWA['db_password']);

// Check connection
if (mysqli_connect_errno($con1))
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
else
{
    @mysqli_select_db($con1, $dbname) or die ( "Unable to connect to the database: $dbname");
}


?>




 
