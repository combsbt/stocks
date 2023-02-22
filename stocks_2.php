<!DOCTYPE html>
<html>
<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>

<head>
  <title>
    STONKS
  </title>
</head>
 
<body style="text-align:center;">
     
  <h1 style="color:green;">
      STONK$
  </h1>

  <form method="post">
    <label for="start">Start date:</label>
    <input type="date" id="start" name="startDate"
       value="2018-01-01" min="2018-01-01" max="2023-02-01">
    <br>
    <input type="submit" name="submitButton"/>
  </form>
  <script>
    if(localStorage.getItem("startDate")){
      console.log(localStorage.getItem("startDate"));
      document.getElementById("start").value = localStorage.getItem("startDate");
    }
  </script>
  <form method="post" id="trade">
    <input type="text" id="thisTrade" name="thisTrade" hidden="true" value="">
    <input type="text" id="tradeInfo" name="tradeInfo" hidden="true" value="">
  </form>

<?php
  if(array_key_exists('submitButton', $_POST)) {
  echo '<script>localStorage.setItem("tradeDate", null);</script>';
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
  echo
  '
  <script>
    localStorage.setItem("startDate", '.json_encode($startDate).');
  </script>
  ';
  //fetch tha data from the database 
  $testArray = array();
  $fullList = array();
  while ($row = mysqli_fetch_array($result)) {
    // get rid of tickers with bad names for now
    if( $row[0] != "all" && $row[0] != "brk-b" && $row[0] != "key" && $row[0] != "keys"){
    // select rows where all 3 indicator conditions are met for selling
    $newRes = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$startDate' AND ($row[0].Close > $row[0].bb_up AND $row[0].rsi > 70 AND $row[0].ult > 70) ");
    while($test = mysqli_fetch_array($newRes)){
      $fullList[explode(" ", $test[0])[0]." ".$row[0]] = $test;
      // echo explode(" ", $test[0])[0]." SELL ".$row[0]." at ".$test["Close"].
      // " check ".date('Y-m-d', strtotime($test[0]. ' + 2 days'))."<br>";
      $testDate = date('Y-m-d', strtotime($test[0]. ' + 2 days'));
      // test 2 days later to see if sell was profitable
      $newRes2 = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$testDate' limit 1");
      while($test2 = mysqli_fetch_array($newRes2)){
        // echo $test2[0]." ".$test2["Close"]." diff% ".($test2["Close"]-$test["Close"])/$test["Close"]."<br>";
        $testArray[explode(" ", $test[0])[0]." ".$row[0]] = ((-1)*($test2["Close"]-$test["Close"]))/$test["Close"];
      }

    }
    $newRes2 = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$startDate' AND ($row[0].ult < 30 AND $row[0].Close < $row[0].bb_low AND $row[0].rsi < 30) ");
    while($test = mysqli_fetch_array($newRes2)){
      $fullList[explode(" ", $test[0])[0]." ".$row[0]] = $test;
      // echo explode(" ", $test[0])[0]." BUY ".$row[0]." at ".$test["Close"].
      // " check ".date('Y-m-d', strtotime($test[0]. ' + 2 days'))."<br>";
      $testDate = date('Y-m-d', strtotime($test[0]. ' + 2 days'));
      // test 2 days later to see if sell was profitable
      $newRes2 = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$testDate' limit 1");
      while($test2 = mysqli_fetch_array($newRes2)){
        // echo $test2[0]." ".$test2["Close"]." diff% ".($test2["Close"]-$test["Close"])/$test["Close"]."<br>";
        $testArray[explode(" ", $test[0])[0]." ".$row[0]] = (($test2["Close"]-$test["Close"]))/$test["Close"];
      }
    } 
  }
  }
  $init = 10000;
  $sum = $init;
  foreach ($testArray as $key => $value) {
    $sum = $sum + $sum * ($value);
    // echo $sum."<br>";
  }
  //echo "START ".$init." TOTAL ".$sum."<br>";
  asort($fullList);

  echo 
  '
  <script>
  var fullList = '.json_encode($fullList).';
  console.log("list");
  console.log(JSON.stringify(fullList).length);
  localStorage.setItem("fullList", JSON.stringify(fullList));
  </script>
  ';

  $allTrades = [];

  function getTrades($fullList, $startDate, $testArray, $total, $allTrades){
    $newList = [];
    forEach($fullList as $key => $value){
      if(explode(' ',$key)[0] >= $startDate){
        $newList[$key]=$value;
      }   
    }
    asort($newList);
    $newerList = [];
    //echo var_dump($newList);
    forEach($newList as $key => $value){
      $sameAs = explode(' ', array_keys($newList)[0])[0] == explode(' ', $key)[0]; 
      if($sameAs){
        $newerList[$key] = $value;
      }
      
    }

    if(count($newerList) > 1){
      $randomPick = array_rand($newerList, 1);  
    }
    else if(count($newerList) == 1){
      $randomPick = array_keys($newerList)[0];
    }
    else{
      //echo var_dump($newerList);
      return($allTrades);
    }

    //echo $randomPick."<br>";
    
    if(array_key_exists($randomPick, $testArray)){
      //echo $testArray[$randomPick]."<br>";  
      $total = $total + $total * $testArray[$randomPick];
      //echo $total."<br>";
      $allTrades[explode(" ", $randomPick)[0]] = [$total,$randomPick];
      
    }
    else{
      return $allTrades;
    }

    $newStart = explode(' ',$randomPick)[0];
    $testDate = date('Y-m-d', strtotime($newStart. ' + 2 days'));
    return getTrades($fullList, $testDate, $testArray, $total, $allTrades);


  }

  $start = hrtime(true); //set timer

  $allTrades = getTrades($fullList, $startDate, $testArray, 10000, $allTrades);

  $end = hrtime(true);            
  echo (($end - $start) / 1000000000)." seconds<br>";

  
  // echo var_dump($allTrades)."<br>"; 
  $data = json_encode($allTrades);


  //echo $data;

  echo strval(count($allTrades))." trades<br>";

  echo 
  '
  <script>
  var fullList = '.json_encode($fullList).';
  var testArray = '.json_encode($testArray).';
  localStorage.setItem("fullList", JSON.stringify(fullList));
  localStorage.setItem("testArray", JSON.stringify(testArray));
  </script>
  ';

  // echo var_dump($allTrades);
  // echo var_dump($fullList);

  //close the connection
  mysqli_close($dbhandle);
}
  //echo var_dump($_POST);

?>

<button onclick = "testFunction()" >Test</button>
<script>
  function testFunction(){
    let total = 10000;
    let fullList = JSON.parse(localStorage.getItem("fullList"));
    console.log(fullList);
    
    let dateList = [];
    let itmsByDate = {};
    Object.entries(fullList).forEach((itm, idx)=>{
      //console.log(itm);
      let date = itm[1]["Date"].split(' ')[0];
      console.log(date);
      if(!dateList.includes(date)){
        dateList.push(date);
        itmsByDate[date] = [];
      }
      itmsByDate[date].push(Object.entries(fullList)[idx][0])
    })
    console.log(dateList)
    console.log(itmsByDate)
    Object.entries(itmsByDate).forEach(itm=>{
      console.log(itm[1].length)
    })
  }
  

</script>

</body>

</html>