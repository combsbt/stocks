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
					$tickers = mysqli_query($dbhandle, "SELECT DISTINCT table_name FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME IN ('Date') AND TABLE_SCHEMA='Stocks'" );

					$startDate = $_POST['startDate'];
					
					function getTrade($tradeDate, $dbhandle, $tickers){
						$fullList = [];
						while ($row = mysqli_fetch_array($tickers)) {
							// get rid of tickers with bad names for now
							if( $row[0] != "all" && $row[0] != "brk-b" && $row[0] != "key" && $row[0] != "keys"){
								// select rows where all 3 indicator conditions are met for selling
								$newRes = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$tradeDate' AND ($row[0].Close > $row[0].bb_up AND $row[0].rsi > 70 AND $row[0].ult > 70) limit 1");
								while($test = mysqli_fetch_array($newRes)){
									//echo $test[0]." ".$row[0]."<br>";
									$fullList[$row[0]]=$test[0];
								}
							}
						}
						asort($fullList);
						//gonna need empty array check
						$newList = [];
						forEach($fullList as $key => $value){
							$sameAs = array_values($fullList)[0] == $value; 
							if($sameAs){
								$newList[$key] = $value;
							}
							
						}
						$randomPick = array_rand($newList, 1);
						echo $randomPick."<br>".$newList[$randomPick];
					}	

					getTrade($startDate, $dbhandle, $tickers);


					//close the connection
					mysqli_close($dbhandle);


        }
    ?>

</body>
 
</html>

