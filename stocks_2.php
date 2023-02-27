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
    <br>
    <input type="date" id="start" name="startDate"
       value="2021-11-08" min="2015-01-01" max="2023-02-01">
    <br>
    <label for="daysHeld">Days Held:</label>
    <input type="number" id="daysHeld" name="daysHeld" value="2" min="1" max="5">
    <br>
    <br>
    <span>Ult:</span>
    <input type="number" id="ult" name="ult" value="30" min="1" max="40">
    <input type="number" id="ult2" name="ult2" value="70" min="60" max="99">
    <br>
    <span>RSI:</span>
    <input type="number" id="rsi" name="rsi" value="30" min="1" max="40">
    <input type="number" id="rsi2" name="rsi2" value="70" min="60" max="99">
    <br>
    <input type="submit" name="submitButton" id="submitButton"/>
  </form>
  <form method="post" id="trade">
    <input type="text" id="thisTrade" name="thisTrade" hidden="true" value="">
    <input type="text" id="tradeInfo" name="tradeInfo" hidden="true" value="">
    <input type="text" id="heldDays" name="heldDays" hidden="true" value="">

  </form>
  <div id="message"></div>
  <div id="progress"></div>
  <div id="message2"></div>
  <div id="myPlot" style="width:100%;max-width:700px;margin:auto"></div>
  <div id="tradePlot" style="width:100%;max-width:700px;margin:auto"></div>
<div>
  <button onclick = "testFunction(document.getElementById('start').value, 0, 10000,0,0,0,0)" id="testButton" hidden="true">Show Plot</button>
</div>
<script>
  // this is for indexedDB ldb.set and ldb.get functions
    !function(){function e(t,o){return n?void(n.transaction("s").objectStore("s").get(t).onsuccess=function(e){var t=e.target.result&&e.target.result.v||null;o(t)}):void setTimeout(function(){e(t,o)},100)}var t=window.indexedDB||window.mozIndexedDB||window.webkitIndexedDB||window.msIndexedDB;if(!t)return void console.error("indexDB not supported");var n,o={k:"",v:""},r=t.open("d2",1);r.onsuccess=function(e){n=this.result},r.onerror=function(e){console.error("indexedDB request error"),console.log(e)},r.onupgradeneeded=function(e){n=null;var t=e.target.result.createObjectStore("s",{keyPath:"k"});t.transaction.oncomplete=function(e){n=e.target.db}},window.ldb={get:e,set:function(e,t){o.k=e,o.v=t,n.transaction("s","readwrite").objectStore("s").put(o)}}}();
    
  // if there is previous data, fill the input fields and allow showing the plot
    if(localStorage.getItem("startDate")){
      console.log(localStorage.getItem("startDate"));
      document.getElementById("start").value = localStorage.getItem("startDate");
      document.getElementById("rsi").value = parseInt(JSON.parse(localStorage.getItem("rsi")));
      document.getElementById("rsi2").value = parseInt(JSON.parse(localStorage.getItem("rsi2")));
      document.getElementById("ult").value = parseInt(JSON.parse(localStorage.getItem("ult")));
      document.getElementById("ult2").value = parseInt(JSON.parse(localStorage.getItem("ult2")));

      document.getElementById('testButton').hidden = false;
    }
</script>
<br/>
<div id="buttons">
</div>
<script>
  // function that calculates all trades and sets 
  function testFunction(startDate, totals, total, fullList, testArray, fullSpy, dateList){
    if (totals === 0){
      totals = {};
      ldb.get('fullList', function (value) {
        console.log('fullList length ', Object.entries(JSON.parse(value)).length);
        fullList = JSON.parse(value);
        //fullList = JSON.parse(localStorage.getItem("fullList"));
        ldb.get('testArray', function (value) {
          console.log('testArray length ', Object.entries(JSON.parse(value)).length);
          testArray = JSON.parse(value);
          ldb.get('fullSpy', function (value) {
            fullSpy = JSON.parse(value);
            ldb.get('dateList', function (value) {
              console.log('dateList length ', Object.entries(JSON.parse(value)).length);
              dateList = JSON.parse(value);
              let daysHeld = JSON.parse(localStorage.getItem("daysHeld"));
              function addDays(date, days) {
                var result = new Date(date);
                result.setDate(result.getDate() + days);
                return result;
              }
              let nextDate = addDays(startDate, parseInt(daysHeld));
              let nextTrade = dateList.find(date => new Date(date) >= nextDate);
              ldb.get('itmsByDate', function (value) {
                  console.log('itmsByDate length ', Object.entries(JSON.parse(value)).length);
                  itmsByDate = JSON.parse(value);
                  if(itmsByDate[nextTrade]){
                    itmsByDate[nextTrade].forEach(trade=>{
                      let divTotal = total/itmsByDate[nextTrade].length
                      total = total + divTotal*testArray[trade];
                    })
                  }
                  if(nextTrade){
                    totals[nextTrade] = total;
                    return testFunction(new Date(nextTrade), totals, total, fullList, testArray, fullSpy,dateList)
                  }

                  ldb.set("totals", JSON.stringify(totals));
                  //localStorage.setItem("totals", JSON.stringify(totals));
                  plotTotals(totals, fullList, testArray, fullSpy);
                  document.getElementById("testButton").hidden = true;
              });
            });      
          });  
        });
      });
    }
    else{
      let daysHeld = JSON.parse(localStorage.getItem("daysHeld"));
      function addDays(date, days) {
        var result = new Date(date);
        result.setDate(result.getDate() + days);
        return result;
      }
      let nextDate = addDays(startDate, parseInt(daysHeld));
      let nextTrade = dateList.find(date => new Date(date) >= nextDate);
      ldb.get('itmsByDate', function (value) {
          itmsByDate = JSON.parse(value);
          if(itmsByDate[nextTrade]){
            itmsByDate[nextTrade].forEach(trade=>{
              let divTotal = total/itmsByDate[nextTrade].length
              total = total + divTotal*testArray[trade];
              console.log('count');
            })
          }
          if(nextTrade){
            totals[nextTrade] = total;
            return testFunction(new Date(nextTrade), totals, total, fullList, testArray, fullSpy,dateList)
          }

          ldb.set("totals", JSON.stringify(totals));
          //localStorage.setItem("totals", JSON.stringify(totals));
          plotTotals(totals, fullList, testArray, fullSpy);
          document.getElementById("testButton").hidden = true;
      });  
    }
  }

  function plotTotals(totals, fullList, testArray, fullSpy){
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

    if(localStorage.getItem("tradePts")){
      let tradePts = JSON.parse(localStorage.getItem("tradePts"));
      data = [{
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
      },
      {
        x:[tradePts[0]],
        y:[yArray[xArray.indexOf(tradePts[0])]],
        mode:"markers",
        name:""
      }
      ]   
    }
    
    // Define Layout
    var layout = { 
      title: "Date vs. Total"
    };

    
    // Display using Plotly
    Plotly.newPlot("myPlot", data, layout, totals);
    myPlot.on("plotly_click", function(data){
      var pts = "";
      for(var i=0; i < data.points.length; i++){
          pts = [data.points[i].x, data.points[i].y.toFixed(2)];
      }
      localStorage.setItem("tradePts", JSON.stringify(pts));
      const myNode = document.getElementById("buttons");
      while (myNode.firstChild) {
        myNode.removeChild(myNode.lastChild);
      }
      ldb.get('itmsByDate', function (value) {
        itmsByDate = JSON.parse(value);
        ldb.get('testArray', function (value) {
          testArray = JSON.parse(value);
          tradeList = itmsByDate[pts[0]];
          //let tradeList = JSON.parse(localStorage.getItem("itmsByDate"))[pts[0]];
          
          let info = document.createElement('h2');
          info.innerHTML = "Trades from " + pts[0];
          document.getElementById("buttons").appendChild(info);

          tradeList.forEach(itm=>{
            console.log(itm);
            let btn = document.createElement('Button');
            btn.id = itm;
            btn.innerHTML = itm;
            let div = document.createElement('div');
            let percent = testArray[itm]*100
            div.style.color = percent>0?"green":"red";
            div.innerHTML = percent.toFixed(4) + " %";
            document.getElementById("buttons").appendChild(btn);
            document.getElementById(itm).setAttribute("onclick", "plotTrade("+JSON.stringify(itm)+")");
            document.getElementById("buttons").appendChild(div);
            let br = document.createElement('br');
            document.getElementById("buttons").appendChild(br);

          })
          plotTotals(totals, fullList, testArray, fullSpy)
        });
      });
    }); 
  }
  function plotTrade(itm){
    console.log("plotTrade")
    localStorage.setItem("thisTrade", itm);
    ldb.get('fullList', function (value) {
        console.log('fullList length ', Object.entries(JSON.parse(value)).length);
        fullList = JSON.parse(value);
        if(fullList){
          var tradeDate = itm.split(" ")[0];
          var tradeInfo = fullList[itm]['rsi']>50?"SELL":"BUY"
          document.getElementById("thisTrade").value = itm;
          document.getElementById("tradeInfo").value = tradeInfo;
          document.getElementById("heldDays").value = JSON.parse(localStorage.getItem("daysHeld"));
          document.getElementById("trade").submit();
        }
      });
  }
  

</script>

<?php
  if(array_key_exists('submitButton', $_POST)) {
  echo '<script>
  document.getElementById("testButton").hidden = true;
  document.getElementById("submitButton").hidden = true;
  localStorage.setItem("tradePts", false);
  </script>';
  $username = "root";
  $password = "";
  $hostname = "localhost"; 
  $database="Stocks";

  //connection to the mysql database,
  $dbhandle = mysqli_connect($hostname, $username, $password, $database)
  or die("Unable to connect to MySQL");
  echo "<script>document.getElementById('message').innerHTML = 'Connected to MySQL'</script>";

  //execute the SQL Statement
  $result = mysqli_query($dbhandle, "SELECT DISTINCT table_name FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME IN ('Date') AND TABLE_SCHEMA='Stocks'" );

  $startDate = $_POST['startDate'];
  echo "<script>
  let thisDate = JSON.stringify(".json_encode($startDate).")
  document.getElementById('message').innerHTML = 'Getting data from '+ thisDate
  </script>";
  //fetch tha data from the database 
  $testArray = array();
  $fullList = array();
  $fullSpy = array();
  $count = 0;
  $rsi = $_POST['rsi'];
  $rsi2 = $_POST['rsi2'];
  $ult = $_POST['ult'];
  $ult2 = $_POST['ult2'];
  while ($row = mysqli_fetch_array($result)) {
    // get rid of tickers with bad names for now
    if( $row[0] != "all" && $row[0] != "brk-b" && $row[0] != "key" && $row[0] != "keys"){
    if(true){
      // select rows where all 3 indicator conditions are met for selling
      $newRes = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$startDate' AND ($row[0].Close > $row[0].bb_up AND $row[0].rsi > $rsi2 AND $row[0].ult > $ult2) ");
      while($test = mysqli_fetch_array($newRes)){
        $count = $count + 1;
        echo "<script>document.getElementById('progress').innerHTML = ".json_encode($count)."</script>";
        $fullList[explode(" ", $test[0])[0]." ".$row[0]] = $test;
        // echo explode(" ", $test[0])[0]." SELL ".$row[0]." at ".$test["Close"].
        // " check ".date('Y-m-d', strtotime($test[0]. ' + 2 days'))."<br>";
        $testDate = date('Y-m-d', strtotime($test[0]. ' + '.$_POST["daysHeld"].' days'));
        // test 2 days later to see if sell was profitable
        $newRes2 = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$testDate' limit 1");
        while($test2 = mysqli_fetch_array($newRes2)){
          // echo $test2[0]." ".$test2["Close"]." diff% ".($test2["Close"]-$test["Close"])/$test["Close"]."<br>";
          $testArray[explode(" ", $test[0])[0]." ".$row[0]] = ((-1)*($test2["Close"]-$test["Close"]))/$test["Close"];
        }

      }  
    }
    if(true){
      // select rows where all 3 indicator conditions are met for buying
      $newRes3 = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$startDate' AND ($row[0].ult < $ult AND $row[0].Close < $row[0].bb_low AND $row[0].rsi < $rsi) ");
      while($test = mysqli_fetch_array($newRes3)){
        $count = $count + 1;
        echo "<script>document.getElementById('progress').innerHTML = ".json_encode($count)."</script>";
        $fullList[explode(" ", $test[0])[0]." ".$row[0]] = $test;
        // echo explode(" ", $test[0])[0]." BUY ".$row[0]." at ".$test["Close"].
        // " check ".date('Y-m-d', strtotime($test[0]. ' + 2 days'))."<br>";
        $testDate = date('Y-m-d', strtotime($test[0]. ' + '.$_POST["daysHeld"].' days'));
        // test 2 days later to see if buy was profitable
        $newRes4 = mysqli_query($dbhandle, "SELECT * FROM $row[0] WHERE $row[0].Date >= '$testDate' limit 1");
        while($test2 = mysqli_fetch_array($newRes4)){
          // echo $test2[0]." ".$test2["Close"]." diff% ".($test2["Close"]-$test["Close"])/$test["Close"]."<br>";
          $testArray[explode(" ", $test[0])[0]." ".$row[0]] = (($test2["Close"]-$test["Close"]))/$test["Close"];
        }
      }
    }
    $testSpy = "spy";
    $newRes5 = mysqli_query($dbhandle, "SELECT * FROM $testSpy WHERE $testSpy.Date >= '$startDate' ");
    while($test = mysqli_fetch_array($newRes5)){
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
  var daysHeld = '.json_encode($_POST["daysHeld"]).';
  var rsi = '.json_encode($_POST["rsi"]).';
  var rsi2 = '.json_encode($_POST["rsi2"]).';
  var ult = '.json_encode($_POST["ult"]).';
  var ult2 = '.json_encode($_POST["ult2"]).';
  document.getElementById("progress").innerHTML = "Total trades analyzed: '.json_encode($count).'"
  try{
    ldb.set("fullList", JSON.stringify(fullList), function(){
      console.log("SETTING FULLLIST")
      });
    //localStorage.setItem("fullList", JSON.stringify(fullList));  
  } catch (error) {
    console.log(error)
    document.getElementById("message").innerHTML = "TOO MUCH BIGGIDNETSESSS"
    document.getElementById("message2").innerHTML = "NOT SO MANNY PLZ"
    document.getElementById("testButton").hidden = true;
  }
  ldb.set("testArray", JSON.stringify(testArray));
  //localStorage.setItem("testArray", JSON.stringify(testArray));
  ldb.set("fullSpy", JSON.stringify(fullSpy));
  //localStorage.setItem("fullSpy", JSON.stringify(fullSpy));
  localStorage.setItem("daysHeld", JSON.stringify(daysHeld));
  localStorage.setItem("rsi", JSON.stringify(rsi));
  localStorage.setItem("rsi2", JSON.stringify(rsi2));
  localStorage.setItem("ult", JSON.stringify(ult));
  localStorage.setItem("ult2", JSON.stringify(ult2));
  localStorage.setItem("startDate", '.json_encode($startDate).');
  document.getElementById("start").value = '.json_encode($startDate).';
  document.getElementById("rsi").value = '.json_encode($rsi).';
  document.getElementById("rsi2").value = '.json_encode($rsi2).';
  document.getElementById("ult").value = '.json_encode($ult).';
  document.getElementById("ult2").value = '.json_encode($ult2).';
  document.getElementById("daysHeld").value = '.json_encode($_POST["daysHeld"]).';
  </script>
  ';

  //close the connection
  mysqli_close($dbhandle);
  echo '<script>
  document.getElementById("testButton").hidden = false;
  document.getElementById("submitButton").hidden = false;
  </script>';
  echo '
  <script>
  ldb.get("fullList", function (value) {
        fullList = JSON.parse(value);
        ldb.get("testArray", function (value) {
          testArray = JSON.parse(value);
           ldb.get("fullSpy", function (value) {
            fullSpy = JSON.parse(value);
            let total = 10000;
            let dateList = [];
            let itmsByDate = {};

            if(fullList){
              Object.entries(fullList).forEach((itm, idx)=>{
                let date = itm[1]["Date"].split(" ")[0];
                if(!dateList.includes(date)){
                  dateList.push(date);
                  itmsByDate[date] = [];
                }
                itmsByDate[date].push(Object.entries(fullList)[idx][0])
              })
              ldb.set("dateList", JSON.stringify(dateList));
              //localStorage.setItem("dateList", JSON.stringify(dateList));
              ldb.set("itmsByDate", JSON.stringify(itmsByDate));
              //localStorage.setItem("itmsByDate", JSON.stringify(itmsByDate));
            }; 
          });
        });      
      });
  </script>
  ';
}

?>
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
    $newRes6 = mysqli_query($dbhandle, "SELECT * FROM $ticker WHERE $ticker.Date >= '$startDate' AND $ticker.Date <= '$endDate' ");
    while($test2 = mysqli_fetch_array($newRes6)){
      // echo $test2[0]." ".$test2["Close"]." diff% ".($test2["Close"]-$test["Close"])/$test["Close"]."<br>";
      $plotArray[$test2['Date']] = $test2["Close"];
    }

    $testDate = date('Y-m-d', strtotime($thisDate. ' + '.$_POST["heldDays"].' days'));
    // test 2 days later to see if sell was profitable
    $newRes7 = mysqli_query($dbhandle, "SELECT * FROM $ticker WHERE $ticker.Date >= '$testDate' limit 1");
    $endTrade = mysqli_fetch_array($newRes7);

    echo 
    '
    <script>
      ldb.get("testArray", function (value) {
        testArray = JSON.parse(value);
        var percent = testArray['.json_encode($_POST["thisTrade"]).']*100;
        var gainOf = percent > 0?" gain of ":" loss of ";
        var percentBlurb = gainOf + percent.toFixed(4) + "%";
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
          title: '.json_encode($_POST['thisTrade']).'+" "+'.json_encode($_POST['tradeInfo']).'+percentBlurb
        };
        
        // Display using Plotly
        Plotly.newPlot("tradePlot", data, layout);
      });
    </script>
    ';

    //close the connection
    mysqli_close($dbhandle);
    echo '
    <script>
      ldb.get("totals", function (value) {
        totals = JSON.parse(value);
        ldb.get("itmsByDate", function (value) {
          itmsByDate = JSON.parse(value);
          ldb.get("testArray", function (value) {
            testArray = JSON.parse(value);
            ldb.get("fullList", function (value) {
              fullList = JSON.parse(value);
              ldb.get("fullSpy", function (value) {
                fullSpy = JSON.parse(value);
                if(totals){
                  plotTotals(totals, fullList, testArray, fullSpy);
                  document.getElementById("testButton").hidden = true;
                  let tradeList = itmsByDate["'.explode(" ", $_POST["thisTrade"])[0].'"] ;
                  console.log(tradeList)
                  let info = document.createElement("h2");
                  info.innerHTML = "Trades from " + "'.explode(" ", $_POST["thisTrade"])[0].'";
                  document.getElementById("buttons").appendChild(info);
                  tradeList.forEach(itm=>{
                    console.log(itm);
                    let btn = document.createElement("Button");
                    btn.id = itm;
                    btn.innerHTML = itm;
                    let div = document.createElement("div");
                    let percent = testArray[itm]*100
                    div.style.color = percent>0?"green":"red";
                    div.innerHTML = percent.toFixed(4) + " %";
                    document.getElementById("buttons").appendChild(btn);
                    document.getElementById(itm).setAttribute("onclick", "plotTrade("+JSON.stringify(itm)+")");
                    document.getElementById("buttons").appendChild(div);
                    let br = document.createElement("br");
                    document.getElementById("buttons").appendChild(br);

                  })
                }
              });
            });
          });  
        });
      });
    </script
    ';
  }
   
?>
</body>

</html>