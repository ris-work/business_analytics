<style>
@font-face{
font-family: "IBM Courier";
src: local('IBM Courier'), url("font-og-courier/fonts/OGCourier-Bold.otf") format("opentype");
}
@font-face{
font-family: "Cousine";
src: local('Cousine'), url("fonts/apache/cousine/Cousine-Regular.ttf") format("truetype");
}
body {
  	width: 100vw;
  	height: 100vh;
  	min-width: 100vw;
  	min-height: 100vh;
width: fit-content;
height: fit-content;
padding:0;
margin:0;
}

pre {
margin:0;
padding:0;
  	background: repeating-linear-gradient(to bottom, #ffd0d0ff 0em, #ffd0d0ff 1.1em, #8888ffff 1.1em, #8888ffff 1.2em, #a0eea0ff 1.2em, #a0eea0ff 2.3em, #ff8888ff 2.3em,  #ff8888ff 2.4em, #c0c0ffff 2.4em, #c0c0ffff 3.5em, #0000ff30 3.5em, #88aa88ff 3.5em, #88aa88ff 3.6em);
    line-height: 1.2em;
    filter: hue-rotate(270deg) grayscale(15%);
}
</style>
<pre style="font-family: 'Cousine', 'IBM Courier', 'Consolas', 'Lucida', MONOSPACE; font-weight: 500; color: indigo">
<?php
error_reporting(E_ALL);
require_once "./env.php";
$arg_dbname = escapeshellarg($dbname);
$arg_scriptname = escapeshellarg(".read " . __FILE__.".sql");
echo "Run: ". ("/usr/bin/sqlite3 $arg_dbname $arg_scriptname") . "\n";
echo shell_exec("/usr/bin/sqlite3 $arg_dbname $arg_scriptname");
?>
</pre>
