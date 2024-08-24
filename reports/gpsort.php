<style>
@font-face{
font-family: "IBM Courier";
src: local('IBM Courier'), url("font-og-courier/fonts/OGCourier-Bold.otf") format("opentype");
}
@font-face{
font-family: "Cousine";
src: local('Cousine'), url("fonts/apache/cousine/Cousine-Regular.ttf") format("truetype");
}
</style>
<pre style="font-family: 'Cousine', 'IBM Courier', 'Consolas', 'Lucida', MONOSPACE; font-weight: 500; color: indigo">
<?php
error_reporting(E_ALL);
require_once "./env.php";
$arg_dbname = escapeshellarg($dbname);
$arg_scriptname = escapeshellarg(".read " . __FILE__ . ".sql");
echo "Run: " . "/usr/bin/sqlite3 $arg_dbname $arg_scriptname" . "\n";
echo shell_exec("/usr/bin/sqlite3 $arg_dbname $arg_scriptname");
?>
</pre>
