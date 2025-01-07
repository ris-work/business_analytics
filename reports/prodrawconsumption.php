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
  	min-width: 98vw;
  	min-height: 98vh;
overflow-x: hidden;
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
@media print {
body{
}
pre{
text-align: left;
display: none;
}
thead > * {
position: relative;
height: 1em;
top: 0;
}
thead > th {
color: #000 !important;
background: #fff;
print-color-adjust: exact;
-webkit-print-color-adjust: exact;
}
th {
color: #000 !important;
background: #fff !important;
print-color-adjust: exact;
-webkit-print-color-adjust: exact;
}
.as-is table td{
width: max-content !important;
min-width: 10rem !important;
}
.as-is table td:nth-child(2){
width: max-content !important;
background: #ccc !important;
min-width: 4rem !important;
}
.as-is table th{
width: max-content !important;
min-width: 6rem !important;
}
th:nth-child(2), td:nth-child(2){
/*background: repeating-linear-gradient(45deg, #000 0rem 0.1rem, white 0.1rem 0.6rem) !important;*/
/*background: repeating-linear-gradient(45deg, #000 0px 1px, white 1px 7px) !important;*/
background: #bbb;
print-color-adjust: exact;
-webkit-print-color-adjust: exact;
}
th:nth-child(5), td:nth-child(5){
/*background: repeating-linear-gradient(30deg, #000 0.5px 1px, white 1px 11px, #000 11.5px 12px) !important;*/
background: #bbb;
print-color-adjust: exact;
-webkit-print-color-adjust: exact;
}
th:nth-child(9), td:nth-child(9){
/*background: repeating-linear-gradient(-30deg, #000 0px 0.1px, white 0.1px 0.6px) !important;*/
background: #bbb;
print-color-adjust: exact;
-webkit-print-color-adjust: exact;
}
th:nth-child(1), td:nth-child(1){
text-align: right;
max-width: 2.25rem;
}
th:nth-child(2), td:nth-child(2){
text-align: right;
max-width: 2rem;
}
/*th:nth-child(3), td:nth-child(3){
display: none;
}*/
th:nth-child(3), td:nth-child(3){
display: none;
max-width: 1.25rem;
}
th:nth-child(5), td:nth-child(5){
/*display: none;*/
max-width: 1.5rem;
}
th:nth-child(6), td:nth-child(6){
display: none;
}
th:nth-child(7), td:nth-child(7){
max-width: 5rem;
}
th:nth-child(8), td:nth-child(8){
display: none;
}
th:nth-child(9), td:nth-child(9){
max-width: 2rem;
}
tbody > tr:nth-child(odd) > td{
background: rgba(0,0,0,1) !important;
color: #fff;
print-color-adjust: exact;
-webkit-print-color-adjust: exact;
}
table > tr:nth-child(even) > td{
background: #fff !important;
color: #000;
print-color-adjust: exact;
-webkit-print-color-adjust: exact;
}
.table-container {
text-align: left;
}
table>tbody>tr>td{
padding-bottom: 0 !important;
padding-top: 0 !important;
overflow-x: clip;
overflow-y: clip;
margin-left: 0.15rem;
margin-right: 0.15rem;
}
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
tr:nth-child(5n+1){
border-bottom: 2px solid rebeccapurple;
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
font-size: 2.5ex;
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
$arg_scriptname = escapeshellarg(".read " . __FILE__ . ".sql");
putenv("TMPDIR=/www/");
echo "Run: " . "/usr/bin/sqlite3 $arg_dbname $arg_scriptname" . "\n";
echo shell_exec("/usr/bin/sqlite3 $arg_dbname $arg_scriptname");
?>
</pre>
