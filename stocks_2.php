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
  <div id="progress">
  </div>

<?php
  if(array_key_exists('submitButton', $_POST)) {
  $username = "root";
  $password = "";
  $hostname = "localhost"; 
  $database="Stocks";

  //connection to the mysql database,
  $dbhandle = mysqli_connect($hostname, $username, $password, $database)
  or die("Unable to connect to MySQL");
  echo "Connected to MySQL<br>";


  //execute the SQL Statement
  $result = mysqli_query($dbhandle, "SELECT DISTINCT table_name FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME IN ('Date') AND TABLE_SCHEMA='Stocks'" );

  $startDate = $_POST['startDate'];
  echo "Getting data from ".$startDate;
  //fetch tha data from the database 
  $testArray = array();
  $fullList = array();
  $fullSpy = array();
  $count = 0;
  while ($row = mysqli_fetch_array($result)) {
    // get rid of tickers with bad names for now
    if( $row[0] != "all" && $row[0] != "brk-b" && $row[0] != "key" && $row[0] != "keys"){
    // select rows where all 3 indicator conditions are met for selling
    $newRes = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$startDate' AND ($row[0].Close > $row[0].bb_up AND $row[0].rsi > 70 AND $row[0].ult > 70) ");
    while($test = mysqli_fetch_array($newRes)){
      $count = $count + 1;
      echo "<script>document.getElementById('progress').innerHTML = ".json_encode($count)."</script>";
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
      $count = $count + 1;
      echo "<script>document.getElementById('progress').innerHTML = ".json_encode($count)."</script>";
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
    $testSpy = "spy";
    $newRes3 = mysqli_query($dbhandle, "SELECT * FROM $testSpy WHERE $testSpy.Date >= '$startDate' ");
    while($test = mysqli_fetch_array($newRes3)){
      $fullSpy[explode(" ", $test[0])[0]] = $test["Close"];
    }
  }
  }
  
  asort($fullList);
  asort($fullSpy);

  echo 
  '
  <script>
  var fullList = '.json_encode($fullList).';
  var testArray = '.json_encode($testArray).';
  var fullSpy = '.json_encode($fullSpy).';
  localStorage.setItem("fullList", JSON.stringify(fullList));
  localStorage.setItem("testArray", JSON.stringify(testArray));
  localStorage.setItem("fullSpy", JSON.stringify(fullSpy));
  localStorage.setItem("startDate", '.json_encode($startDate).');
  document.getElementById("start").value = '.json_encode($startDate).';
  </script>
  ';

  //close the connection
  mysqli_close($dbhandle);
  echo '<script>document.getElementById("testButton").hidden = false</script>';
}

?>
<div id="myPlot" style="width:100%;max-width:700px;margin:auto"></div>
<div>
  <button onclick = "testFunction(document.getElementById('start').value, 0)" id="testButton">Test</button>
</div>
<br/>
<div id="buttons">
</div>
<script>
  let total = 10000;
  fullList = JSON.parse(localStorage.getItem("fullList"));
  testArray = JSON.parse(localStorage.getItem("testArray"));
  fullSpy = JSON.parse(localStorage.getItem("fullSpy"));
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
      })
    }
    if(nextTrade){
      totals[nextTrade] = total;
      return testFunction(new Date(nextTrade), totals)
    }
    plotTotals(totals);
    document.getElementById("testButton").hidden = true;
  }

  function plotTotals(totals){
      var xArray = Object.keys(totals);
      var yArray = Object.values(totals);

      var spyValues = []; 
      Object.keys(totals).forEach(itm=>{
        spyValues.push((fullSpy[itm] * 10000)/Object.entries(fullSpy).sort()[0][1])
      })

      
      // Define Data
      var data = [{
        x:xArray,
        y:yArray,
        mode:"line",
        name:"strategy"
      },
      {

        x:Object.keys(totals),
        y:spyValues,
        mode:"line",
        name:"s&p500"
      }
      ];
      
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
        const myNode = document.getElementById("buttons");
        while (myNode.firstChild) {
          myNode.removeChild(myNode.lastChild);
        }
        let tradeList = JSON.parse(localStorage.getItem("itmsByDate"))[pts[0]];
        tradeList.forEach(itm=>{
          console.log(itm);
          let btn = document.createElement('Button');
          btn.id = itm;
          btn.innerHTML = itm;
          let div = document.createElement('div');
          let percent = testArray[itm]*100
          div.innerHTML = percent.toFixed(4) + " %";
          document.getElementById("buttons").appendChild(btn);
          document.getElementById("buttons").appendChild(div);
          let br = document.createElement('br');
          document.getElementById("buttons").appendChild(br);

        })

    });
  }
  

</script>

</body>

</html>