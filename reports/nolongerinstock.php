<pre style="font-family: 'IBM Courier', 'Consolas', 'Lucida', MONOSPACE">
<?php
error_reporting(E_ALL);
require_once "./env.php";
$arg_dbname = escapeshellarg($dbname);
$arg_scriptname = escapeshellarg(".read " . __FILE__.".sql");
echo "Running: ". ("/usr/bin/sqlite3 $arg_dbname $arg_scriptname") . "\n";
echo shell_exec("/usr/bin/sqlite3 $arg_dbname $arg_scriptname");
?>
</pre>
