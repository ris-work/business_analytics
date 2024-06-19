<link rel="stylesheet" type="text/css" href="node_modules/98.css/style.css" />
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
  	min-width: 99vw;
  	min-height: 99vh;
width: fit-content;
height: fit-content;
padding:0;
margin:auto;
background: var(--surface);
}

pre {
margin:0;
padding:0;
  	background: repeating-linear-gradient(to bottom, #ffd0d0ff 0em, #ffd0d0ff 1.1em, #8888ffff 1.1em, #8888ffff 1.2em, #a0eea0ff 1.2em, #a0eea0ff 2.3em, #ff8888ff 2.3em,  #ff8888ff 2.4em, #c0c0ffff 2.4em, #c0c0ffff 3.5em, #0000ff30 3.5em, #88aa88ff 3.5em, #88aa88ff 3.6em);
    line-height: 1.2em;
    filter: hue-rotate(270deg) grayscale(15%);
}

td{
max-width: 15em;
word-break: break-word;
white-space: pre;
overflow-x: clip;
overflow-x: scroll;
border: 1px dashed black;
vertical-align: baseline;
padding: 0.5em !important;
}
td:nth-child(odd){
background: #cff;
}
tr:nth-child(odd){
background: #fec;
}
tr:nth-child(even){
background: #fff;
}
th{
position: sticky;
top: 0;
background: #666;
color: white;
font-size: 1.2em;
text-align: center;
border-left: 1px dashed #fff;
border-right: 1px dashed #fff;
}
table{
border-collapse: collapse;
margin: auto;
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
