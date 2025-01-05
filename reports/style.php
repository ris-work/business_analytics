<?php
$stylesheet = "node_modules/98.css/style.css";
if(array_key_exists('style', $_COOKIE)){
		if($_COOKIE["style"] == "7"){
				$stylesheet="node_modules/7.css/dist/7.css";
				//echo "Style: 7\n";
		}
		elseif($_COOKIE["style"] == "xp"){
				$stylesheet="node_modules/xp.css/dist/XP.css";
				//echo "Style: xp\n";
		}
		else{
				$style = $_COOKIE["style"];
				//echo "Unrecognized style: $style\n";
		}
}
else {
		//echo "No style was set\n";
		//var_dump($_COOKIE["style"]);
}
?>
