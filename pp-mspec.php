#!/usr/bin/php
<?php
$sourcedir="$argv[1]";
$sourcefile="$argv[2]";
echo "	Re-processing " . $sourcedir . $sourcefile . "\n";
$contents=file($sourcedir . $sourcefile);
$webdir="/var/www/html/mspec/";
$stypes=file(dirname($_SERVER['PHP_SELF']) . "/servertypes.list");
unlink($sourcedir . $sourcefile);

echo "	Exporting results to " . $webdir . preg_replace('/\\.[^.\\s]{3,4}$/', '', $sourcefile) . '.html' . "\n";

fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($webdir . preg_replace('/\\.[^.\\s]{3,4}$/', '', $sourcefile) . '.html', 'wb');
$STDERR = fopen($webdir . 'error.log', 'wb');

$servdetails=array();

//var_dump($contents);

echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">';

foreach($stypes as $plan) {
	$linetoarr=explode("\t", $plan);
	$splans[$linetoarr[0]]=array($linetoarr[1],$linetoarr[2],$linetoarr[3],$linetoarr[4]); 
}

foreach($contents as $line) {
	$linetoarr=explode(",",$line);
	$servdetails[$linetoarr[0]]=array('ip'=>$linetoarr[1],'cpu'=>$linetoarr[2],'ram'=>$linetoarr[3],'drives'=>explode(" ",$linetoarr[4]));
}

echo "<div class='container'>";
echo "<div class='panel panel-primary' style='margin-top: 15px;'><div class='panel-heading'><h2 class='panel-title'>" . preg_replace('/\\.[^.\\s]{3,4}$/', '', $sourcefile) . "</h2></div>";
//echo "<div class='panel-body'></div>
echo "<table id='' class='table'>";
echo "<thead style='font-weight: bold'><tr><td>Server</td><td>IP</td><td>CPU</td><td>RAM</td><td>Drives</td><td>Plan</td></tr></thead><tbody>";
while($server = current($servdetails)) {
	$plan="";
	$driveset=array('virt'=>0,'128gb'=>0,'512gb'=>0,'1tb'=>0,'2tb'=>0,'3tb'=>0,'4tb'=>0,'raid'=>0);
	foreach($server['drives'] as $drive) {
		print_r(strpos($drive,'1tb'));
		if(strpos($drive,'1000')!==FALSE) {
			$driveset['1tb']++;
		}
		if(floatval($drive) >= 6000) {
			$driveset['raid']++;
		}
		if(empty($drive)) {
			$driveset['virt']++;
		} 
	}
	$drivelist="";
	foreach($driveset as $drivetype=>$count) {
		if($count >= 1) {
			$drivelist=" " . $count . "x" . strtoupper($drivetype);
		}
	}
	foreach($splans as $name=>$values) {
		/*echo "CPU: " . strtoupper(preg_replace('/[^\p{L}\p{N}\s]/u', '', $server['cpu']));
		echo " - Comparing to: " . strtoupper(preg_replace('/[^\p{L}\p{N}\s]/u', '', $values[0])) . strcasecmp (preg_replace('/[^\p{L}\p{N}\s]/u', '', $server['cpu']), preg_replace('/[^\p{L}\p{N}\s]/u', '', $values[0])) . "<br>";
		echo "RAM: " . $server['ram'];
		echo " - Comparing to: " . $values[1] . strcasecmp ($server['ram'],$values[1]) . "<br>";
		echo "Drives: " . $drivelist;
		echo " - Comparing to: " . $values[2] . strpos($drivelist, $values[2]) . "<br>";*/
		
		if ( strcasecmp (preg_replace('/[^\p{L}\p{N}\s]/u', '', $server['cpu']), preg_replace('/[^\p{L}\p{N}\s]/u', '', $values[0])) === 0 && strcasecmp ($server['ram'],$values[1]) === 0/*strpos($drivelist, $values[2]) !== FALSE */) { 
			$plan="<span class='label' style='line-height: 18px; background-color:" . $values[3] . "'>" . $name . "</span>";			
		} else {
		}
	}
		
	echo "<tr>";
	echo "<td>" . preg_replace("/^[^\.]*/i","<strong>$0</strong>",key($servdetails)) . "</td><td>" . $server['ip'] . "</td><td>" . $server['cpu'] . "</td><td>" . $server['ram'] . "</td><td>" . $drivelist . "</td><td>" . $plan . "</td>";
	echo "<tr>";
	next($servdetails);
}
echo "</tbody>";
echo "</table>";
//div panel
echo "</div>";
//div container
echo "</div>";
?>
