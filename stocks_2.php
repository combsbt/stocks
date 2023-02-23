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
  
  asort($fullList);

  echo 
  '
  <script>
  var fullList = '.json_encode($fullList).';
  var testArray = '.json_encode($testArray).';
  localStorage.setItem("fullList", JSON.stringify(fullList));
  localStorage.setItem("testArray", JSON.stringify(testArray));
  </script>
  ';

  //close the connection
  mysqli_close($dbhandle);
}

?>
<div id="myPlot" style="width:100%;max-width:700px;margin:auto"></div>
<button onclick = "testFunction(document.getElementById('start').value, 0)" >Test</button>
<script>
  let total = 10000;
  fullList = JSON.parse(localStorage.getItem("fullList"));
  testArray = JSON.parse(localStorage.getItem("testArray"));
  let dateList = [];
  let itmsByDate = {};

  Object.entries(fullList).forEach((itm, idx)=>{
    let date = itm[1]["Date"].split(' ')[0];
    if(!dateList.includes(date)){
      dateList.push(date);
      itmsByDate[date] = [];
    }
    itmsByDate[date].push(Object.entries(fullList)[idx][0])
  })
  console.log(dateList)
  localStorage.setItem("dateList", JSON.stringify(dateList));
  localStorage.setItem("itmsByDate", JSON.stringify(itmsByDate));

  function testFunction(startDate, totals){
    if (totals === 0){
      totals = {};
    }
    let dateList = JSON.parse(localStorage.getItem("dateList"));
    let itmsByDate = JSON.parse(localStorage.getItem("itmsByDate"));
    function addDays(date, days) {
      var result = new Date(date);
      result.setDate(result.getDate() + days);
      return result;
    }
    let nextDate = addDays(startDate, 2);
    let nextTrade = dateList.find(date => new Date(date) >= nextDate);
    if(itmsByDate[nextTrade]){
      itmsByDate[nextTrade].forEach(trade=>{
        let divTotal = total/itmsByDate[nextTrade].length
        total = total + divTotal*testArray[trade];
        console.log(trade)
        console.log(total)
      })
    }
    if(nextTrade){
      totals[nextTrade] = total;
      return testFunction(new Date(nextTrade), totals)
    }
    console.log(totals)
    plotTotals(totals);
  }

  function plotTotals(totals){
      var xArray = Object.keys(totals);
      var yArray = Object.values(totals);
      
      // Define Data
      var data = [{
        x:xArray,
        y:yArray,
        mode:"line"
      }];
      
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
        console.log(JSON.parse(localStorage.getItem("itmsByDate"))[pts[0]])
    });
  }
  

</script>

</body>

</html>