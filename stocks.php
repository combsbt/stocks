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
  var allTrades = '.json_encode($allTrades).';
  localStorage.setItem("allTrades", JSON.stringify(allTrades));
  </script>
  ';

  // echo var_dump($allTrades);
  // echo var_dump($fullList);

  //close the connection
  mysqli_close($dbhandle);
}
  //echo var_dump($_POST);

?>
<div id="myPlot" style="width:100%;max-width:700px;margin:auto"></div>
<div style="margin:auto">
  <p id="thisTrade"></p>
</div>
<div id="tradePlot" style="width:100%;max-width:700px;margin:auto"></div>
<script>
  if(localStorage.getItem("allTrades")){
    if(localStorage.getItem("startDate")){
      console.log(localStorage.getItem("startDate"));
      document.getElementById("start").value = localStorage.getItem("startDate");
    }
    var allTrades = JSON.parse(localStorage.getItem("allTrades"));
    var xArray = Object.keys(allTrades);
    var yArray = [];
    Object.keys(allTrades).forEach(date => {
      yArray.push(allTrades[date][0]);
    })
    
    // Define Data
    var data = [{
        x:xArray,
        y:yArray,
        mode:"line"
      }];

    if(localStorage.getItem("tradeDate")){
      let tradeIndex = xArray.indexOf(localStorage.getItem("tradeDate").split(" ")[0])
      data = [{
        x:xArray,
        y:yArray,
        mode:"line"
      },
      {
        x:[xArray[tradeIndex]],
        y:[yArray[tradeIndex]],
        mode:"markers"
      }]     
    }
    
    
    // Define Layout
    var layout = { 
      title: "Date vs. Total"
    };
    
    // Display using Plotly
    Plotly.newPlot("myPlot", data, layout);

    myPlot.on("plotly_click", function(data){
        var pts = "";
        for(var i=0; i < data.points.length; i++){
            pts = [data.points[i].x, data.points[i].y.toFixed(2)];
        }
        //alert("Closest point clicked:\n\n"+pts);
        localStorage.setItem("tradeDate", allTrades[pts[0]][1]);

        //undefined if last date
        var nextDate = Object.keys(allTrades)[Object.keys(allTrades).indexOf(pts[0])+1];
        localStorage.setItem("nextDate", nextDate);
        console.log(nextDate);
        var fullList = JSON.parse(localStorage.getItem("fullList"));
        console.log(fullList[allTrades[pts[0]][1]]);
        var tradeInfo = fullList[allTrades[pts[0]][1]];
        localStorage.setItem("tradeInfo", tradeInfo['rsi']>60?"SELL":"BUY");

        if(localStorage.getItem("tradeDate")){
          document.getElementById("thisTrade").innerHTML = localStorage.getItem("tradeDate");
          document.getElementById("tradeInfo").innerHTML = localStorage.getItem("tradeInfo");
          plotTrade();

        
        }
    });

    function plotTrade(){
      console.log("plotTrade")
      if(localStorage.getItem("tradeDate") && localStorage.getItem("fullList")){
        var tradeDate = localStorage.getItem("tradeDate");
        var tradeInfo = localStorage.getItem("tradeInfo");
        document.getElementById("thisTrade").value = tradeDate;
        document.getElementById("tradeInfo").value = tradeInfo;
        document.getElementById("trade").submit();
      }
    }
  }
</script>
<?php
  //plot the trade with 28 days leading up to it
  if(array_key_exists('thisTrade', $_POST)){
    $username = "root";
    $password = "";
    $hostname = "localhost"; 
    $database="Stocks";

    //connection to the mysql database,
    $dbhandle = mysqli_connect($hostname, $username, $password,$database )
    or die("Unable to connect to MySQL");
    // echo "Connected to MySQL<br>";
    
    // echo $_POST['thisTrade'];
    $thisDate = explode(' ',$_POST['thisTrade'])[0];
    $ticker =   explode(' ',$_POST['thisTrade'])[1];
    $startDate = date('Y-m-d', strtotime($thisDate. ' - 28 days'));
    $endDate = date('Y-m-d', strtotime($thisDate. ' + 20 days'));
    // test 2 days later to see if sell was profitable
    $plotArray = [];
    $newRes2 = mysqli_query($dbhandle, "SELECT * FROM $ticker WHERE $ticker.Date >= '$startDate' AND $ticker.Date <= '$endDate' ");
    while($test2 = mysqli_fetch_array($newRes2)){
      // echo $test2[0]." ".$test2["Close"]." diff% ".($test2["Close"]-$test["Close"])/$test["Close"]."<br>";
      $plotArray[$test2['Date']] = $test2["Close"];
    }

    $testDate = date('Y-m-d', strtotime($thisDate. ' + 2 days'));
    // test 2 days later to see if sell was profitable
    $newRes3 = mysqli_query($dbhandle, "SELECT * FROM $ticker WHERE $ticker.Date >= '$testDate' limit 1");
    $endTrade = mysqli_fetch_array($newRes3);

    echo 
    '
    <script>
      var xArray = '.json_encode(array_keys($plotArray)).';
      var yArray = '.json_encode(array_values($plotArray)).';
      
      // Define Data
      var data = [{
        x:xArray,
        y:yArray,
        mode:"line"
      },
      {
        x:['.json_encode($thisDate." 00:00:00").','.json_encode($endTrade[0]).'],
        y:['.json_encode($plotArray[$thisDate." 00:00:00"]).','.json_encode($endTrade['Close']).'],
        mode:"markers"
        }];
      
      // Define Layout
      var layout = { 
        title: '.json_encode($_POST['thisTrade']).'+" "+'.json_encode($_POST['tradeInfo']).'
      };
      
      // Display using Plotly
      Plotly.newPlot("tradePlot", data, layout);
    </script>
    ';

    //close the connection
    mysqli_close($dbhandle);
  }
   
?>
</body>

</html>
