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

$skipAll = false;

// Check for play or sim
if(array_key_exists("gameid", $_POST)){

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);

        // Query for home and away teams
        $teamsQuery = "SELECT * FROM games G WHERE G.gameid=?";
        $teamsStatement = $conn->prepare($teamsQuery);
        $teamsStatement->execute(array($_POST['gameid']));
        $gameObj = $teamsStatement->fetch();
        $_POST["home"] = $gameObj['hometeam'];
        $_POST["away"] = $gameObj['awayteam'];

        // Query for team rosters
        $rosterQuery = "SELECT P.playerid, P.fname, P.mname, P.lname, PO.abbr, P.age, T.teamid FROM players P INNER JOIN playsposition PP ON PP.playerid=P.playerid INNER JOIN positions PO ON PO.posid=PP.posid INNER JOIN activeroster AR ON AR.playerid=P.playerid INNER JOIN teams T ON T.teamid=AR.teamid WHERE T.teamid=? ORDER BY PO.posid, P.playerid";
        $rosterStatement = $conn->prepare($rosterQuery);
        $rosterStatement->execute(array($_POST['home']));
        $homeTeam = $rosterStatement->fetchAll();
        $rosterStatement->execute(array($_POST['away']));
        $awayTeam = $rosterStatement->fetchAll();

    } catch (PDOException $e) {
        die("Could not connect to the database $dbname :" . $e->getMessage());
    }

    if($_POST["option"] == "sim"){
        $skipAll = true;

        // Handle simulating game
        try {
            // Setup vars
            $score = array("home"=>0, "away"=>0);
            $offense = 'home';
            $defense = "away";
            $roster = array("home"=>array(), "away"=>array());

            // Find rostered players
            foreach($homeTeam as $homePlayer){
                $roster['home'][$homePlayer['abbr']] = array("fname"=>$homePlayer['fname'], "lname"=>$homePlayer['lname'], "playerid"=>$homePlayer['playerid']);
            }
            foreach($awayTeam as $awayPlayer){
                $roster['away'][$awayPlayer['abbr']] = array("fname"=>$awayPlayer['fname'], "lname"=>$awayPlayer['lname'], "playerid"=>$awayPlayer['playerid']);
            }

            // Define functions
            function change_stat(&$roster, $player, $team, $stat, $change){
                if(array_key_exists($player, $roster[$team])){
                    if(array_key_exists($stat, $roster[$team][$player])){
                        $roster[$team][$player][$stat] += $change;
                    }
                    else{
                        $roster[$team][$player][$stat] = $change;
                    }
                }
            }
            function turnover(&$offense, &$defense){
                $temp = $offense;
                $offense = $defense;
                $defense = $temp;
            }

            function getStat(&$player, $stat){
                if(array_key_exists($stat, $player)){
                    return $player[$stat];
                }
                else{
                    return 0;
                }
            }

            // Iterate through plays of game
            $plays = rand(45,75);
            for($play=0; $play<$plays; $play++){
                // Decide play outcome
                // Could be rush for x yards, pass for x yards, pass for TD, run for TD, sack, strip sack, punt, pass for INT, run for FUMB, incomplete pass, FG make, FG miss
                $playChoice = rand(1,100);
                if($playChoice < 15){ // Rush for x yards
                    $yards = rand(-15,75);
                    if(rand(1,10) < 9){
                        change_stat($roster, "HB", $offense, "rushing", $yards);
                        change_stat($roster, "HB", $offense, "carries", 1);
                    }
                    else{
                        change_stat($roster, "FB", $offense, "rushing", $yards);
                        change_stat($roster, "FB", $offense, "carries", 1);
                    }
                    change_stat($roster, "MLB", $defense, "tackles", 1);
                    if($yards < 0){
                        change_stat($roster, "MLB", $defense, "tacklesforloss", 1);
                    }
                }
                else if($playChoice < 40){ // pass for x yards
                    $yards = rand(-15,75);
                    if(rand(1,10) < 7){
                        change_stat($roster, "WR", $offense, "receiving", $yards);
                        change_stat($roster, "WR", $offense, "targets", 1);
                        change_stat($roster, "WR", $offense, "receptions", 1);
                    }
                    else{
                        change_stat($roster, "TE", $offense, "receiving", $yards);
                        change_stat($roster, "TE", $offense, "targets", 1);
                        change_stat($roster, "TE", $offense, "receptions", 1);
                    }
                    change_stat($roster, "QB", $offense, "passing", $yards);
                    change_stat($roster, "QB", $offense, "attempts", 1);
                    change_stat($roster, "QB", $offense, "completions", 1);
                    if(rand(1,10) < 7){
                        change_stat($roster, "CB", $defense, "tackles", 1);
                        if($yards < 0){
                            change_stat($roster, "CB", $defense, "tacklesforloss", 1);
                        }
                    }
                    else{
                        change_stat($roster, "SS", $defense, "tackles", 1);
                        if($yards < 0){
                            change_stat($roster, "SS", $defense, "tacklesforloss", 1);
                        }
                    }
                }
                else if($playChoice < 50){ // incomplete pass
                    change_stat($roster, "QB", $offense, "attempts", 1);
                    if(rand(1,10) < 7){
                        change_stat($roster, "WR", $offense, "targets", 1);
                    }
                    else{
                        change_stat($roster, "TE", $offense, "targets", 1);
                    }
                }
                else if($playChoice < 55){ // sack
                    if(rand(1,10) < 5){
                        change_stat($roster, "OLB", $defense, "sacks", 1);
                        change_stat($roster, "OLB", $defense, "tackles", 1);
                    }
                    else{
                        change_stat($roster, "DE", $defense, "sacks", 1);
                        change_stat($roster, "OLB", $defense, "tackles", 1);
                    }
                }
                else if($playChoice < 60){ // strip sack
                    if(rand(1,10) < 5){
                        change_stat($roster, "OLB", $defense, "sacks", 1);
                        change_stat($roster, "OLB", $defense, "tackles", 1);
                        change_stat($roster, "OLB", $defense, "forcedfumbles", 1);
                    }
                    else{
                        change_stat($roster, "DE", $defense, "sacks", 1);
                        change_stat($roster, "DE", $defense, "tackles", 1);
                        change_stat($roster, "DE", $defense, "forcedfumbles", 1);
                    }
                    turnover($offense, $defense);
                }
                else if($playChoice < 65){ // punt
                    turnover($offense, $defense);
                }
                else if($playChoice < 70){ // interception
                    if(rand(1,10) < 5){
                        change_stat($roster, "CB", $defense, "interceptions", 1);
                    }
                    else{
                        change_stat($roster, "FS", $defense, "interceptions", 1);
                    }
                    change_stat($roster, "QB", $offense, "pinterceptions", 1);
                    change_stat($roster, "WR", $offense, "targets", 1);
                    turnover($offense, $defense);
                }
                else if($playChoice < 75){ // rush fumble
                    change_stat($roster, "DT", $defense, "forcedfumbles", 1);
                    change_stat($roster, "HB", $offense, "fumbles", 1);
                    change_stat($roster, "HB", $offense, "carries", 1);
                    turnover($offense, $defense);
                }
                else if($playChoice < 81){ // rushing TD
                    $yards = rand(1,40);
                    change_stat($roster, "HB", $offense, "rtouchdowns", 1);
                    change_stat($roster, "HB", $offense, "carries", 1);
                    change_stat($roster, "HB", $offense, "rushing", $yards);
                    change_stat($roster, "K", $offense, "xpa", 1);
                    $score[$offense] += 6;
                    if(rand(1,10) < 10){
                        change_stat($roster, "K", $offense, "xpm", 1);
                        $score[$offense] += 1;
                    }
                    turnover($offense, $defense);
                }
                else if($playChoice < 89){ // receiving TD
                    $yards = rand(1,40);
                    change_stat($roster, "WR", $offense, "ctouchdowns", 1);
                    change_stat($roster, "QB", $offense, "ptouchdowns", 1);
                    change_stat($roster, "WR", $offense, "receiving", $yards);
                    change_stat($roster, "WR", $offense, "targets", 1);
                    change_stat($roster, "WR", $offense, "receptions", 1);
                    change_stat($roster, "QB", $offense, "attempts", 1);
                    change_stat($roster, "QB", $offense, "completions", 1);
                    change_stat($roster, "QB", $offense, "passing", $yards);
                    change_stat($roster, "K", $offense, "xpa", 1);
                    $score[$offense] += 6;
                    if(rand(1,10) < 10){
                        change_stat($roster, "K", $offense, "xpm", 1);
                        $score[$offense] += 1;
                    }
                    turnover($offense, $defense);
                }
                else if($playChoice < 96){ // made FG
                    $yards = rand(1,65);
                    change_stat($roster, "K", $offense, "tries", 1);
                    change_stat($roster, "K", $offense, "makes", 1);
                    change_stat($roster, "K", $offense, "longest", $yards);
                    $score[$offense] += 3;
                    turnover($offense, $defense);
                }
                else { // missed FG
                    change_stat($roster, "K", $offense, "tries", 1);
                    turnover($offense, $defense);
                }
            }

            // Add all player stats
            $conn->beginTransaction();
            foreach(['home', 'away'] as $team){
                foreach($roster[$team] as $pos => $homePlayer){

                    // Add to gameroster
                    $rosterQuery = "INSERT INTO gameroster (gameid, playerid, teamid) VALUES (?,?,?)";
                    $rosterStatement = $conn->prepare($rosterQuery);
                    $rosterStatement->execute(array($_POST['gameid'], $homePlayer['playerid'], $_POST[$team]));
                    $perfid = $conn->lastInsertId();

                    // Add to passing
                    $statQuery = "INSERT INTO passing (performanceid, yards, attempts, completions, touchdowns, interceptions) VALUES (?,?,?,?,?,?)";
                    $statStatement = $conn->prepare($statQuery);
                    $statStatement->execute(array($perfid, getStat($homePlayer, 'passing'), getStat($homePlayer, 'attempts'), getStat($homePlayer, 'completions'), getStat($homePlayer, 'ptouchdowns'), getStat($homePlayer, 'pinterceptions')));
                
                    // Add to rushing
                    $statQuery = "INSERT INTO rushing (performanceid, yards, carries, touchdowns, fumbles) VALUES (?,?,?,?,?)";
                    $statStatement = $conn->prepare($statQuery);
                    $statStatement->execute(array($perfid, getStat($homePlayer, 'rushing'), getStat($homePlayer, 'carries'), getStat($homePlayer, 'rtouchdowns'), getStat($homePlayer, 'fumbles')));

                    // Add to receiving
                    $statQuery = "INSERT INTO receiving (performanceid, yards, receptions, touchdowns, targets) VALUES (?,?,?,?,?)";
                    $statStatement = $conn->prepare($statQuery);
                    $statStatement->execute(array($perfid, getStat($homePlayer, 'receiving'), getStat($homePlayer, 'receptions'), getStat($homePlayer, 'ctouchdowns'), getStat($homePlayer, 'targets')));

                    // Add to kicking
                    $statQuery = "INSERT INTO kicking (performanceid, makes, tries, longest, xpa, xpm) VALUES (?,?,?,?,?,?)";
                    $statStatement = $conn->prepare($statQuery);
                    $statStatement->execute(array($perfid, getStat($homePlayer, 'makes'), getStat($homePlayer, 'tries'), getStat($homePlayer, 'longest'), getStat($homePlayer, 'xpa'), getStat($homePlayer, 'xpm')));
                    
                    // Add to defense
                    $statQuery = "INSERT INTO defense (performanceid, tackles, tacklesforloss, sacks, forcedfumbles, interceptions) VALUES (?,?,?,?,?,?)";
                    $statStatement = $conn->prepare($statQuery);
                    $statStatement->execute(array($perfid, getStat($homePlayer, 'tackles'), getStat($homePlayer, 'tacklesforloss'), getStat($homePlayer, 'sacks'), getStat($homePlayer, 'forcedfumbles'), getStat($homePlayer, 'interceptions')));
                    
                }
            }
            // Add game score
            $rosterQuery = "INSERT INTO scores (gameid, homescore, awayscore) VALUES (?,?,?)";
            $rosterStatement = $conn->prepare($rosterQuery);
            $rosterStatement->execute(array($_POST['gameid'], $score['home'], $score['away']));

            $conn->commit();

            ?>
            <style>
            <?php include './styles.css'; ?>
            </style>
            <?php

            echo "<div style='width:100%;margin:2rem;'><a class='menuButton' style='margin:2rem;' href='./games.php'>Return</a></div><div class='row'><div class='col50'><h3>Home - " . $score['home'] . "</h3>";
            foreach($roster['home'] as $pos => $homePlayer){
                echo "<p>" . $pos . " " . $homePlayer['fname'] . " " . $homePlayer['lname'] . ' </p>';
                foreach($homePlayer as $stat => $value){
                    if(!($stat == "fname" || $stat == "lname" || $stat == "playerid")){
                        echo "<span style='padding-left:10px;'>" . $stat . ' - ' . $value . "</span>";
                    }
                }
            }

            echo "</div><div class='col50'><h3>Away - " . $score['away'] . "</h3>";
            foreach($roster['away'] as $pos => $awayPlayer){
                echo "<p>" . $pos . " " . $awayPlayer['fname'] . " " . $awayPlayer['lname'] . ' </p>';
                foreach($awayPlayer as $stat => $value){
                    if(!($stat == "fname" || $stat == "lname" || $stat == "playerid")){
                        echo "<span style='padding-left:10px;'>" . $stat . ' - ' . $value . "</span>";
                    }
                }
            }
            echo "</div></div>";

        } catch (PDOException $e) {
            $conn->rollBack();
            die("Could not connect to the database $dbname :" . $e->getMessage());
        }

    }

}

if(! $skipAll){
// Handle free play
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
    $rosterQuery = "SELECT P.playerid, P.fname, P.mname, P.lname, PO.abbr, P.age, T.teamid FROM players P INNER JOIN playsposition PP ON PP.playerid=P.playerid INNER JOIN positions PO ON PO.posid=PP.posid INNER JOIN activeroster AR ON AR.playerid=P.playerid INNER JOIN teams T ON T.teamid=AR.teamid WHERE T.teamid=? ORDER BY PO.posid, P.playerid";
	$rosterStatement = $conn->prepare($rosterQuery);
    $rosterStatement->execute(array($_POST['home']));
    $homeTeam = $rosterStatement->fetchAll();
    $rosterStatement->execute(array($_POST['away']));
    $awayTeam = $rosterStatement->fetchAll();
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

        <img id="aoline" src="aoline.png" class="gameImage">
        <img id="adline" src="adline.png" class="gameImage">
        <img id="asec" src="asec.png" class="gameImage">
        <img id="askill" src="askill.png" class="gameImage">
        <img id="holine" src="holine.png" class="gameImage">
        <img id="hdline" src="hdline.png" class="gameImage">
        <img id="hsec" src="hsec.png" class="gameImage">
        <img id="hskill" src="hskill.png" class="gameImage">
    </head> 
    <body style=""> 
    <div style="text-align:center; margin: 1rem 1rem 1rem 1rem;">
        <a id="backButton" href="./home.php">Home</a>
        <h1 id="mainTitle">Just Another Football Sim</h1>
        <div>
            <canvas id="gameCanvas" width="1024" height="768"></canvas>
            <script>
                // Player object
                class Player {
                    constructor(team, position, playerid, lname){
                        this.team = team;
                        this.x = -1;
                        this.y = -1;
                        this.playerid = playerid;
                        this.position = position;
                        this.lname = lname;
                        this.stats = {};
                        this.accel = 0.2;
                        this.maxspeed = 2;
                        this.xspeed = 0;
                        this.yspeed = 0;
                    }
                    pos(){
                        return [this.x, this.y];
                    }
                    getPosition(){
                        return this.position;
                    }
                    getTeam(){
                        return this.team;
                    }
                    getName(){
                        return this.lname;
                    }
                    onField(){
                        if(this.x < 0 || this.y < 0){
                            return false;
                        }
                        return true;
                    }
                    move(dir){
                        if(this.xspeed > this.maxspeed){
                            this.xspeed = this.maxspeed;
                        }
                        if(this.xspeed < -this.maxspeed){
                            this.xspeed = -this.maxspeed;
                        }
                        if(this.yspeed > this.maxspeed){
                            this.yspeed = this.maxspeed;
                        }
                        if(this.yspeed < -this.maxspeed){
                            this.yspeed = -this.maxspeed;
                        }
                        if(dir == "up"){
                            this.yspeed -= this.accel;
                            this.y += this.yspeed;
                        }
                        if(dir == "down"){
                            this.yspeed += this.accel;
                            this.y += this.yspeed;
                        }
                        if(dir == "left"){
                            this.xspeed -= this.accel;
                            this.x += this.xspeed;
                        }
                        if(dir == "right"){
                            this.xspeed += this.accel;
                            this.x += this.xspeed;
                        }
                        if(dir == null){
                            if(this.xspeed > 0){
                                this.xspeed -= this.accel;
                            }
                            if(this.xspeed < 0){
                                this.xspeed += this.accel;
                            }
                            if(this.yspeed > 0){
                                this.yspeed -= this.accel;
                            }
                            if(this.yspeed < 0){
                                this.yspeed += this.accel;
                            }
                        }
                    }
                    bench(){
                        this.x = -1;
                        this.y = -1;
                    }
                    setToBall(ball, offset){
                        this.x = ball[0] + offset[0];
                        this.y = ball[1] + offset[1];
                    }
                    getStats(){
                        return this.stats;
                    }
                    changeStat(stat, change){
                        if(this.stats.hasOwnProperty(stat)){
                            this.stats[stat] += change;
                        }
                        else{
                            this.stats[stat] = change;
                        }
                    }
                }

                class GameState {
                    positions = {"offense": {"qb":[0,50], "wr1":[200,10], "wr2":[-200,10], "hb":[10,40], "fb":[-10,40], "te":[35,10], "c":[0,5], "og1":[-10,5], "og2":[10,5], "ot1":[20,5], "ot2":[-20,5]},
                                "defense": ["mlb", "olb", "olb", "de", "de", "dt", "dt", "ss", "fs", "cb", "cb"]};
                    constructor(){
                        this.down = 1;
                        this.distance = 100;
                        this.possession = "home";
                        this.awayscore = 0;
                        this.homescore = 0;
                        this.ball = [0,0];
                        this.lineofscrim = 0;
                    }
                    setPlayers(set, team, players){
                        for(let player of players){
                            if(player.getTeam() == team){
                                player.bench(); 
                            }
                        }
                        for(let position in this.positions[set]){
                            for(let player of players){
                                if(player.getPosition() == position.replace(/\d+/g, '')){
                                    if(!player.onField()){
                                        player.setToBall(this.ball, this.positions[set][position]);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    resetDrive(players){
                        this.ball = [250, 800];
                        this.lineofscrim = 800;
                        this.distance = 100;
                        this.down = 1;
                        this.setPlayers("offense", this.possession, players);
                    }
                    getLineOfScrimmage(){
                        return [0, this.lineofscrim];
                    }
                    getFirstDown(){
                        return [0, this.lineofscrim - this.distance];
                    }
                }

                // Setup canvas
                var c = document.getElementById("gameCanvas");
                var ctx = c.getContext("2d");
                var cheight = c.height;
                var cwidth = c.width;

                // Setup game object
                var fieldh = 1200;
                var fieldw = 500;
                
                // Setup positional objects
                var players = [];

                // Setup players
                <?php
                    foreach($homeTeam as $player){
                        echo 'players.push(new Player("home", "' . strtolower($player['abbr']) . '", ' . $player['playerid'] . ', "' . $player['lname'] . '"));';
                    }
                    foreach($awayTeam as $player){
                        echo 'players.push(new Player("away", "' . strtolower($player['abbr']) . '", ' . $player['playerid'] . ', "' . $player['lname'] . '"));';
                    }
                ?>

                // Setup game images
                const aoline = document.getElementById('aoline');
                const adline = document.getElementById('adline');
                const asec = document.getElementById('asec');
                const askill = document.getElementById('askill');
                const holine = document.getElementById('holine');
                const hdline = document.getElementById('hdline');
                const hsec = document.getElementById('hsec');
                const hskill = document.getElementById('hskill');
                const awayImages = {"qb":askill,"fb":aoline,"hb":askill,"wr":askill,"og":aoline,"ot":aoline,"c":aoline,"te":askill,"dt":adline,"de":adline,"olb":asec,"mlb":asec,"fs":asec,"ss":asec,"cb":asec,"k":askill,"p":askill,"kr":askill,"pr":askill,"ls":aoline};
                const homeImages = {"qb":hskill,"fb":holine,"hb":hskill,"wr":hskill,"og":holine,"ot":holine,"c":holine,"te":hskill,"dt":hdline,"de":hdline,"olb":hsec,"mlb":hsec,"fs":hsec,"ss":hsec,"cb":hsec,"k":hskill,"p":hskill,"kr":hskill,"pr":hskill,"ls":holine};

                const playerWidth = 15;
                const playerHeight = 15;

                // Setup active player
                var user = players[0];

                console.log("Players " + JSON.stringify(players));

                // Setup lines
                var p_los = [0,50];
                var p_fd = [0,150];

                // Setup game state
                var game = new GameState();
                game.resetDrive(players);
                
                // Start game drawing cycle
                var gameCycle = setInterval(process, 100);

                // Setup listeners
                window.addEventListener("keydown", function(e) {
                    if(e.defaultPrevented || e.isComposing){
                        return
                    }
                    // Handle keyboard input
                    switch(e.key){
                        // Move QB
                        case "w":
                            user.move("up");
                            break;
                        case "s":
                            user.move("down");
                            break;
                        case "a":
                            user.move("left");
                            break;
                        case "d":
                            user.move("right");
                            break;
                        default:
                            break;
                    }

                }, true);

                // Function to convert game position to canvas position
                function gpToCp(pos){
                    return [(pos[0] / fieldw) * cwidth, (pos[1] / fieldh) * cheight];
                }

                // Function to handle the game cycle
                function process(){
                    gameUpdate();
                    draw();
                }

                // Function to handle updating the game each step
                function gameUpdate(){
                    user.move(null);
                }

                // Function to handle drawing the field onto the canvas
                function draw(){
                    // Reset canvas
                    ctx.clearRect(0,0,cwidth,cheight);

                    // Draw field
                    ctx.fillStyle = "#1d451b";
                    ctx.fillRect(0,0,cwidth,cheight);

                    // Draw lines
                    // Line of scrimmage
                    ctx.fillStyle = "#262926";
                    ctx.fillRect(0,gpToCp(game.getLineOfScrimmage())[1],cwidth,3);
                    // First down line
                    ctx.fillStyle = "#dbd809";
                    ctx.fillRect(0,gpToCp(game.getFirstDown())[1],cwidth,3);
                    // Endzones
                    ctx.fillStyle = "#ffffff";
                    ctx.fillRect(0,gpToCp([0,fieldh-100])[1],cwidth,3);
                    ctx.fillRect(0,gpToCp([0,100])[1],cwidth,3);
                    // Sidelines
                    ctx.fillRect(0,0,3,cheight);
                    ctx.fillRect(gpToCp([fieldw-3,0]),0,3,cheight);

                    // Set font
                    ctx.font = "8px Arial";
                    ctx.textAlign = "center";

                    // Draw players
                    players.forEach(drawPlayer);
                }

                // Function to draw an individual player
                function drawPlayer(player, index){
                    if(player.onField()){
                        var thisImage;
                        var thisPos;
                        if(player.getTeam() == "home"){
                            thisImage = homeImages[player.getPosition()];
                        }
                        else{
                            thisImage = awayImages[player.getPosition()];
                        }
                        thisPos = gpToCp(player.pos());
                        if(user == player){
                            ctx.beginPath();
                            ctx.strokeStyle = "#06bf1e";
                            ctx.arc(thisPos[0] + (playerWidth / 2), thisPos[1] + (playerHeight / 2), (playerWidth / 2) + 1, 0, 2 * Math.PI);
                            ctx.stroke();
                        }
                        ctx.drawImage(thisImage, thisPos[0], thisPos[1], playerWidth, playerHeight);
                        ctx.fillText(player.getName(), thisPos[0], thisPos[1] + 20);
                    }
                }
                // Function to stop the game
                function stop(){
                    clearInterval(gameCycle);
                }
            </script>
        </div>
    </body>
    </div>
</div>
</html>
<?php } ?>