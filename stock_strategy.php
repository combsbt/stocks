<!DOCTYPE html>
<html>
     
<head>
    <title>
    	STONKS
    </title>
</head>
 
<body style="text-align:center;">
     
    <h1 style="color:green;">
        STONKS
    </h1>
 
    <form method="post">
      <label for="start">Start date:</label>
			<input type="date" id="start" name="startDate"
	       value="<?php echo $_POST?$_POST['startDate']:"2015-01-01";?>" min="2015-01-01" max="2023-02-01">
	    <br>
	    <input type="submit" name="submitButton"/>
    </form>


    <?php
      if(array_key_exists('submitButton', $_POST)) {
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

					$startDate = $_POST['startDate'];
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
					//close the connection
					mysqli_close($dbhandle);


        }
    ?>

</body>
 
</html>