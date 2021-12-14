<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conf = include('config.php');

$username = $conf['username'];
$password = $conf['password'];
$host = $conf['host'];
$dbname = $conf['dbname'];
$port = $conf['port'];

if(array_key_exists("teamid", $_POST)){
    $_SESSION["gamesteamid"] = $_POST["teamid"];
}
if(array_key_exists("gamesteamid", $_SESSION)){
    $thisteam = $_SESSION["gamesteamid"];
}
else{
    $thisteam = 1;
}

if(array_key_exists("year", $_POST)){
    $_SESSION["gamesyear"] = $_POST["year"];
}
if(array_key_exists("gamesyear", $_SESSION)){
    $thisyear = $_SESSION["gamesyear"];
}
else{
    $thisyear = "2021";
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
    $gamesQuery = "SELECT G.gameid, G.hometeam, G.awayteam, G.kickoff, S.homescore, S.awayscore, T.name AS homename, T.city AS homecity, T2.name AS awayname, T2.city AS awaycity FROM games G LEFT JOIN scores S ON G.gameid=S.gameid INNER JOIN teams T ON T.teamid=G.hometeam INNER JOIN teams T2 ON T2.teamid=G.awayteam WHERE (G.hometeam=? OR G.awayteam=?) AND YEAR(G.kickoff)=?";
	$gamesStatement = $conn->prepare($gamesQuery);
    $gamesStatement->execute(array($thisteam, $thisteam, $thisyear));
    $teamsQuery = 'SELECT * FROM teams ORDER BY city';
    $teamsResults = $conn->query($teamsQuery);
    $teamsResults->setFetchMode(PDO::FETCH_ASSOC);
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
        <h1 id="mainTitle">Games</h1>
        <div class="row">
            <div class="col50" style="overflow-y:auto;">
                <form action="games.php" method="post">
                    <select onchange="this.form.submit()" class="dataInput" name="teamid" id="teamid" required>
                        <?php while($team = $teamsResults->fetch()) : ?>
                            <option value="<?php 
                            echo htmlspecialchars($team['teamid']) . '"';
                            if($thisteam == $team["teamid"]){
                                echo "selected";
                            }
                            ?>><?php echo htmlspecialchars($team['city']) . " " . htmlspecialchars($team['name'])?></option>
                        <?php endwhile; ?>
                    </select>
                </form>
                <form action="games.php" method="post">
                    <select onchange="this.form.submit()" class="dataInput" name="year" id="year" required>
                        <?php for($y = 2020; $y <= 2030; $y++){ ?>
                            <option value="<?php 
                            echo $y . '"';
                            if($thisyear == $y){
                                echo "selected";
                            }
                            ?>><?php echo $y; ?></option>
                        <?php } ?>
                    </select>
                </form>
                <h4 class="subTitle">Schedule</h4>
                <div class="dataTable">
                    <div class="headerRow">
                        <span class="dataItem" style="flex:25%;">Away</span>
                        <span class="dataItem" style="flex:25%;">Home</span>
                        <span class="dataItem" style="flex:25%;">Kickoff</span>
                        <span class="dataItem" style="flex:25%;">Result</span>
                    </div>
                    <?php if($gamesStatement->rowCount() == 0){ ?>
                        <span class="dataItem" style="flex:100%;">No Games Scheduled in <?php echo $thisyear; ?>!</span>
                    <?php } else { while ($game = $gamesStatement->fetch()): ?>
                    <div class="dataRow">
                        <span class="dataItem" style="flex:25%;"><?php echo $game['awaycity'] . ' ' . $game['awayname'] ?></span>
                        <span class="dataItem" style="flex:25%;"><?php echo $game['homecity'] . ' ' . $game['homename'] ?></span>
                        <span class="dataItem" style="flex:25%;"><?php echo $game['kickoff'] ?></span>
                        <span class="dataItem" style="flex:25%;">
                            <?php if(is_null($game['homescore'])){
                                echo 'TBD';
                            } else{
                                if(intval($game['homescore']) > intval($game['awayscore'])){
                                    echo $game['awayname'] . ' ' . $game['awayscore'] . ' - <b>' . $game['homename'] . ' ' . $game['homescore'] . '</b>';
                                }
                                else if(intval($game['homescore']) < intval($game['awayscore'])){
                                    echo '<b>' . $game['awayname'] . ' ' . $game['awayscore'] . '</b> - ' . $game['homename'] . ' ' . $game['homescore'] . '';
                                }
                                else{
                                    echo '' . $game['awayname'] . ' ' . $game['awayscore'] . '<b> - </b>' . $game['homename'] . ' ' . $game['homescore'] . '';
                                }
                            } ?>
                        </span>
                    </div>
                    <?php endwhile; } ?>
                </div>
            </div>
            <div class="col50">
                <h4 class="subTitle">Generate Schedule</h4>
                <form action="generateSchedule.php" method="post" class="dataForm">
                    <p class="formLabel">Year</p>
                    <input class="dataInput" type="number" id="year" minlength="4" name="year" value="" required>
                    <br>
                    <input class="formButton" type="submit" value="Generate">
                </form>
            </div>
        </div>
    </body>
    </div>
</div>
</html>