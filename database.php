
<?php

$username = "root";
$password = "";
$hostname = "localhost"; 
$database="Stocks";

//connection to the mysql database,
$dbhandle = mysqli_connect($hostname, $username, $password,$database )
or die("Unable to connect to MySQL");
echo "Connected to MySQL<br>";


//execute the SQL Statement
$result = mysqli_query($dbhandle, "SELECT DISTINCT table_name FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME IN ('Date') AND TABLE_SCHEMA='Stocks'" );

$startDate = '2020-01-01';
//fetch tha data from the database 
$testArray = array();
while ($row = mysqli_fetch_array($result)) {
	// get rid of tickers with bad names for now
	if( $row[0] != "all" && $row[0] != "brk-b" && $row[0] != "key" && $row[0] != "keys"){
	// select rows where all 3 indicator conditions are met for selling
	$newRes = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$startDate' AND ($row[0].Close > $row[0].bb_up AND $row[0].rsi > 70 AND $row[0].ult > 70) ");
	while($test = mysqli_fetch_array($newRes)){
		echo explode(" ", $test[0])[0]." SELL ".$row[0]." at ".$test["Close"].
		" check ".date('Y-m-d', strtotime($test[0]. ' + 2 days'))."<br>";
		$testDate = date('Y-m-d', strtotime($test[0]. ' + 2 days'));
		// test 2 days later to see if sell was profitable
		$newRes2 = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$testDate' limit 1");
		while($test2 = mysqli_fetch_array($newRes2)){
			echo $test2[0]." ".$test2["Close"]." diff% ".($test2["Close"]-$test["Close"])/$test["Close"]."<br>";
			$testArray[explode(" ", $test[0])[0].$row[0]] = ((-1)*($test2["Close"]-$test["Close"]))/$test["Close"];
		}

	}
	$newRes2 = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$startDate' AND ($row[0].ult < 30 AND $row[0].Close < $row[0].bb_low AND $row[0].rsi < 30) ");
	while($test = mysqli_fetch_array($newRes2)){
		echo explode(" ", $test[0])[0]." BUY ".$row[0]." at ".$test["Close"].
		" check ".date('Y-m-d', strtotime($test[0]. ' + 2 days'))."<br>";
		$testDate = date('Y-m-d', strtotime($test[0]. ' + 2 days'));
		// test 2 days later to see if sell was profitable
		$newRes2 = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$testDate' limit 1");
		while($test2 = mysqli_fetch_array($newRes2)){
			echo $test2[0]." ".$test2["Close"]." diff% ".($test2["Close"]-$test["Close"])/$test["Close"]."<br>";
			$testArray[explode(" ", $test[0])[0].$row[0]] = (($test2["Close"]-$test["Close"]))/$test["Close"];
		}
	}	
}
}
$init = 10000;
$sum = $init;
foreach ($testArray as $key => $value) {
	$sum = $sum + $sum * ($value);
	echo $sum."<br>";
}
echo "START ".$init." TOTAL ".$sum;
// foreach ($testArray as $key => $value) {
//     $newRes = mysqli_query($dbhandle, "SELECT * FROM $key WHERE $key.Date >= '$value'");
// 		while($test = mysqli_fetch_array($newRes)){
// 			echo $test["Close"]."<br>";
// 		}
// }

//		$checkDate = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date === date('Y-m-d', strtotime($test[0]. ' + 2 days')));


// while ($row = mysqli_fetch_array($result)) {
// echo $row[0]."<br>";
// if( $row[0] != "all" && $row[0] != "brk-b" && $row[0] != "key" && $row[0] != "keys"){
// 	$newRes = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '2023-02-01' AND (($row[0].Close > $row[0].bb_up AND $row[0].rsi > 70 AND $row[0].ult > 70) OR ($row[0].ult < 30 AND $row[0].Close < $row[0].bb_low AND $row[0].rsi < 30)) ");
// 	while($test = mysqli_fetch_array($newRes)){
// 		echo $test[0]." rsi: ".$test['rsi']." ult: ".$test['ult']." Close: ".$test['Close']." bb_up: ".$test['bb_up']." bb_low: ".$test['bb_low']."<br>";
// 	}	
// }
// }

// while ($row = mysqli_fetch_array($result)) {
// echo $row[0]."<br>";
// if( $row[0] != "all" && $row[0] != "brk-b" && $row[0] != "key" && $row[0] != "keys"){
// 	$newRes = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '2023-02-01' AND ($row[0].Close > $row[0].bb_up OR $row[0].rsi > 70 OR $row[0].ult > 70 OR $row[0].ult < 30 or $row[0].Close < $row[0].bb_low or $row[0].rsi < 30) ");
// 	while($test = mysqli_fetch_array($newRes)){
// 		echo $test[0]." rsi: ".$test['rsi']." ult: ".$test['ult']." Close: ".$test['Close']." bb_up: ".$test['bb_up']." bb_low: ".$test['bb_low']."<br>";
// 	}	
// }
// }


//close the connection
mysqli_close($dbhandle);

?>