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
        <div style="width:100%;">
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
    </body>
    </div>
</div>
</html>