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

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
    $teamsQuery = 'SELECT * FROM teams ORDER BY city';
    $teamsResults = $conn->query($teamsQuery);
    $teamsResults->setFetchMode(PDO::FETCH_ASSOC);
    $teams = $teamsResults->fetchAll();
    $gamesQuery = 'SELECT G.gameid, G.hometeam, G.awayteam, G.kickoff, T.name AS homename, T.city AS homecity, T2.name AS awayname, T2.city AS awaycity FROM games G INNER JOIN teams T ON T.teamid=G.hometeam INNER JOIN teams T2 ON T2.teamid=G.awayteam WHERE G.gameid NOT IN (SELECT S.gameid FROM scores S) AND YEAR(G.kickoff)=YEAR(NOW())';
    $gamesResults = $conn->query($gamesQuery);
    $gamesResults->setFetchMode(PDO::FETCH_ASSOC);
    $games = $gamesResults->fetchAll();
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
        <h1 id="mainTitle">Play</h1>
        <div class="row">
            <div class="col50">
                <h4 class="subTitle">Scheduled Games</h4>
                <form action="game.php" method="post">
                    <select class="dataInput" name="gameid" id="gameid" required>
                        <?php foreach($games as $game) { ?>
                            <option value="<?php echo $game['gameid']; ?>"><?php echo $game['awayname'] . ' @ ' . $game['homename']?></option>
                        <?php } ?>
                    </select>
                    <br><br>
                    <input type="radio" id="playOption" name="option" value="play">
                    <label for="playOption">Play Game</label><br>
                    <input type="radio" id="simOption" name="option" value="sim" checked>
                    <label for="simOption">Simulate Game</label><br>
                    <br>
                    <input class="playButton" type="submit" value="Go">
                </form>
            </div>
            <div class="col50">
                <h4 class="subTitle">Free Play</h4>
                <form action="game.php" method="post">
                    <select class="dataInput" name="away" id="away" required>
                        <?php foreach($teams as $team) { ?>
                            <option value="<?php echo htmlspecialchars($team['teamid']) . '"'; if($team['teamid']==1){ echo "selected"; }?>><?php echo htmlspecialchars($team['city']) . " " . htmlspecialchars($team['name'])?></option>
                        <?php } ?>
                    </select>
                    <p> @ </p>
                    <select class="dataInput" name="home" id="home" required>
                        <?php foreach($teams as $team) { ?>
                            <option value="<?php echo htmlspecialchars($team['teamid']) . '"'; if($team['teamid']==2){ echo "selected"; }?>><?php echo htmlspecialchars($team['city']) . " " . htmlspecialchars($team['name'])?></option>
                        <?php } ?>
                    </select>
                    <br><br>
                    <input class="playButton" type="submit" value="Play">
                </form>
            </div>
        </div>
    </body>
    </div>
</div>
</html>