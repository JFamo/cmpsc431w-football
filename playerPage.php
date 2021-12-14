<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conf = include('config.php');

$username = $conf['username'];
$password = $conf['password'];
$host = $conf['host'];
$dbname = $conf['dbname'];
$port = $conf['port'];

if(array_key_exists("playerid", $_POST)){
    $thisplayer = $_POST["playerid"];
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
    $playerBioQuery = 'SELECT P.fname, P.mname, P.lname, P.age, PO.abbr, PO.name AS posn, T.city, T.name AS team FROM players P INNER JOIN playsposition PP ON PP.playerid=P.playerid INNER JOIN positions PO ON PP.posid=PO.posid LEFT JOIN activeroster A ON A.playerid=P.playerid LEFT JOIN teams T ON T.teamid=A.teamid WHERE P.playerid=?';
    $playerBioResults = $conn->prepare($playerBioQuery);
    $playerBioResults->execute(array($thisplayer));
    $playerBio = $playerBioResults->fetch();
    $statsQuery = 'SELECT G.gameid, G.kickoff, PL.playerid, PL.fname, PL.lname, Pass.yards AS pyards, Pass.attempts, Pass.completions, Pass.touchdowns AS ptouchdowns, Pass.interceptions AS pinterceptions, Rush.yards AS ryards, Rush.carries, Rush.touchdowns AS rtouchdowns, Rush.fumbles, Receive.yards AS cyards, Receive.receptions, Receive.touchdowns AS ctouchdowns, Receive.targets, Kick.makes, Kick.tries, Kick.longest, Kick.xpa, Kick.xpm, Def.tackles, Def.tacklesforloss, Def.sacks, Def.forcedfumbles, Def.interceptions
    FROM players PL, gameroster GR, games G, passing Pass, rushing Rush, receiving Receive, kicking Kick, defense Def WHERE  G.gameID = GR.gameID
    AND PL.playerID = GR.playerID
    AND GR.performanceID = Def.performanceID
    AND GR.performanceID = Kick.performanceID
    AND GR.performanceID = Receive.performanceID
    AND GR.performanceID = Rush.performanceID
    AND GR.performanceID = Pass.performanceID
    AND PL.playerID = ?';
    $statsResults = $conn->prepare($statsQuery);
    $statsResults->execute(array($thisplayer));
    $careerQuery = 'SELECT PL.playerid, SUM(Pass.yards) AS pyards, SUM(Pass.attempts) AS attempts, SUM(Pass.completions) AS completions, SUM(Pass.touchdowns) AS ptouchdowns, 
    SUM(Pass.interceptions) AS pinterceptions, SUM(Rush.yards) AS ryards, SUM(Rush.carries) AS carries, SUM(Rush.touchdowns) AS rtouchdowns, SUM(Rush.fumbles) AS fumbles,
    SUM(Receive.yards) AS cyards, SUM(Receive.receptions) AS receptions, SUM(Receive.touchdowns) AS ctouchdowns, SUM(Receive.targets) AS targets, SUM(Kick.makes) AS makes,
    SUM(Kick.tries) AS tries, MAX(Kick.longest) AS longest, SUM(Kick.xpa) AS xpa, SUM(Kick.xpm) AS xpm, SUM(Def.tackles) AS tackles, SUM(Def.tacklesforloss) AS tacklesforloss,
    SUM(Def.sacks) AS sacks, SUM(Def.forcedfumbles) AS forcedfumbles, SUM(Def.interceptions) AS interceptions
    FROM players PL, gameroster GR, games G, passing Pass, rushing Rush, receiving Receive, kicking Kick, defense Def WHERE  G.gameID = GR.gameID
    AND PL.playerID = GR.playerID
    AND GR.performanceID = Def.performanceID
    AND GR.performanceID = Kick.performanceID
    AND GR.performanceID = Receive.performanceID
    AND GR.performanceID = Rush.performanceID
    AND GR.performanceID = Pass.performanceID
    AND PL.playerID = ?';
    $careerRes = $conn->prepare($careerQuery);
    $careerRes->execute(array($thisplayer));

} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}
?>

<style>
<?php include './styles.css'; ?>
</style>

<!DOCTYPE html>
<html style="font-family:">
    <head>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
        <title>FootballSimXYZ</title>
    </head>
    <body style=""> 
    <div style="text-align:center; margin: 1rem 1rem 1rem 1rem;">
        <a id="backButton" href="./home.php">Home</a>
        <div style="text-align:center">
            <h2><?php echo $playerBio['fname'] . ' ' . $playerBio['mname'] . ' ' . $playerBio['lname'];?></h2>
            <h4><?php echo $playerBio['abbr'] . ' - ' . $playerBio['posn'];?></h4>
            <h4><?php echo $playerBio['age'] . ' y/o ';?></h4>
            <p>Player for the:</p>
            <?php
                if(is_null($playerBio['team'])){
                    echo "<h3>Free Agent</h3>";
                }
                else{
                    echo "<h3>" . $playerBio['city'] . " " . $playerBio['team'] . "</h3>";
                }
            ?>
        </div>
        <h4 class="subTitle">Games</h4>
        <div class="dataTable">
            <div class="headerRow">
                <span class="dataItem" style="flex:8%;">Date</span>
                <span class="dataItem" style="flex:4%;">PASS</span>
                <span class="dataItem" style="flex:4%;">ATT</span>
                <span class="dataItem" style="flex:4%;">CMP</span>
                <span class="dataItem" style="flex:4%;">TDS</span>
                <span class="dataItem" style="flex:4%;">INT</span>
                <span class="dataItem" style="flex:4%;">RUSH</span>
                <span class="dataItem" style="flex:4%;">CAR</span>
                <span class="dataItem" style="flex:4%;">TDS</span>
                <span class="dataItem" style="flex:4%;">FMB</span>
                <span class="dataItem" style="flex:4%;">REC</span>
                <span class="dataItem" style="flex:4%;">TAR</span>
                <span class="dataItem" style="flex:4%;">CAT</span>
                <span class="dataItem" style="flex:4%;">TDS</span>
                <span class="dataItem" style="flex:4%;">MAKE</span>
                <span class="dataItem" style="flex:4%;">TRY</span>
                <span class="dataItem" style="flex:4%;">LONG</span>
                <span class="dataItem" style="flex:4%;">XPA</span>
                <span class="dataItem" style="flex:4%;">XPM</span>
                <span class="dataItem" style="flex:4%;">TKL</span>
                <span class="dataItem" style="flex:4%;">TFL</span>
                <span class="dataItem" style="flex:4%;">SACK</span>
                <span class="dataItem" style="flex:4%;">FF</span>
                <span class="dataItem" style="flex:4%;">INT</span>
            </div>
            <?php while ($game = $statsResults->fetch()): ?>
            <div class="dataRow">
                <span class="dataItem" style="flex:8%;"><?php echo $game['kickoff'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['pyards'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['attempts'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['completions'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['ptouchdowns'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['pinterceptions'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['ryards'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['carries'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['rtouchdowns'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['fumbles'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['cyards'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['targets'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['receptions'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['ctouchdowns'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['makes'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['tries'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['longest'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['xpa'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['xpm'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['tackles'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['tacklesforloss'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['sacks'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['forcedfumbles'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['interceptions'];?></span>
            </div>
            <?php endwhile; ?>
        </div>
        <h4 class="subTitle">Career</h4>
        <div class="dataTable">
            <div class="headerRow">
                <span class="dataItem" style="flex:4%;">PASS</span>
                <span class="dataItem" style="flex:4%;">ATT</span>
                <span class="dataItem" style="flex:4%;">CMP</span>
                <span class="dataItem" style="flex:4%;">TDS</span>
                <span class="dataItem" style="flex:4%;">INT</span>
                <span class="dataItem" style="flex:4%;">RUSH</span>
                <span class="dataItem" style="flex:4%;">CAR</span>
                <span class="dataItem" style="flex:4%;">TDS</span>
                <span class="dataItem" style="flex:4%;">FMB</span>
                <span class="dataItem" style="flex:4%;">REC</span>
                <span class="dataItem" style="flex:4%;">TAR</span>
                <span class="dataItem" style="flex:4%;">CAT</span>
                <span class="dataItem" style="flex:4%;">TDS</span>
                <span class="dataItem" style="flex:4%;">MAKE</span>
                <span class="dataItem" style="flex:4%;">TRY</span>
                <span class="dataItem" style="flex:4%;">LONG</span>
                <span class="dataItem" style="flex:4%;">XPA</span>
                <span class="dataItem" style="flex:4%;">XPM</span>
                <span class="dataItem" style="flex:4%;">TKL</span>
                <span class="dataItem" style="flex:4%;">TFL</span>
                <span class="dataItem" style="flex:4%;">SACK</span>
                <span class="dataItem" style="flex:4%;">FF</span>
                <span class="dataItem" style="flex:4%;">INT</span>
            </div>
            <?php while ($game = $careerRes->fetch()): ?>
            <div class="dataRow">
                <span class="dataItem" style="flex:4%;"><?php echo $game['pyards'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['attempts'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['completions'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['ptouchdowns'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['pinterceptions'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['ryards'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['carries'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['rtouchdowns'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['fumbles'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['cyards'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['targets'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['receptions'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['ctouchdowns'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['makes'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['tries'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['longest'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['xpa'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['xpm'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['tackles'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['tacklesforloss'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['sacks'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['forcedfumbles'];?></span>
                <span class="dataItem" style="flex:4%;"><?php echo $game['interceptions'];?></span>
            </div>
            <?php endwhile; ?>
        </div>
    </body>
    </div>
</div>
</html>

<?php } else {
    echo '<html><h3>404 - Player Not Found</h3></html>';
} ?>
