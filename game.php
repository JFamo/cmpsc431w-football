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
                        if(dir == "up"){
                            this.y -= 1;
                        }
                        if(dir == "down"){
                            this.y += 1;
                        }
                    }
                    setToBall(ball, offset){
                        this.x = ball[0] + offset[0];
                        this.y = ball[1] + offset[1];
                    }
                }

                class GameState {
                    constructor(){
                        this.down = 0;
                        this.distance = 100;
                        this.possession = "home";
                        this.awayscore = 0;
                        this.homescore = 0;
                    }
                    setOffense(){
                        
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
                var p_ball = [250,200];

                // Single possession setup
                players.push(new Player("home", "qb", 1, "Jones"));
                players.push(new Player("home", "ol", 2, "Bracken"));
                players.push(new Player("home", "wr", 3, "Edwards"));
                players.push(new Player("home", "hb", 4, "Weeden"));
                players.push(new Player("away", "mlb", 5, "Sanders"));
                players.push(new Player("away", "dline", 6, "Polick"));

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

                // Setup active player
                var user = players[0];

                for(let p in players){
                    p.setToBall(p_ball, [(Math.random()-0.5)*50, (Math.random()-0.5)*5]);
                }

                // Setup lines
                var p_los = [0,50];
                var p_fd = [0,150];
                
                // Start game drawing cycle
                var gameCycle = setInterval(draw, 100);

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
                        default:
                            break;
                    }

                }, true);

                // Function to convert game position to canvas position
                function gpToCp(pos){
                    return [(pos[0] / fieldw) * cwidth, (pos[1] / fieldh) * cheight];
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
                    ctx.fillRect(0,gpToCp(p_los)[1],cwidth,3);
                    // First down line
                    ctx.fillStyle = "#dbd809";
                    ctx.fillRect(0,gpToCp(p_fd)[1],cwidth,3);
                    // Endzones
                    ctx.fillStyle = "#ffffff";
                    ctx.fillRect(0,gpToCp([0,fieldh-100])[1],cwidth,3);
                    ctx.fillRect(0,gpToCp([0,100])[1],cwidth,3);
                    // Sidelines
                    ctx.fillRect(0,0,3,cheight);
                    ctx.fillRect(gpToCp([fieldw-3,0]),0,3,cheight);

                    // Draw players
                    players.forEach(drawPlayer);
                }

                // Function to draw an individual player
                function drawPlayer(player, index){

                    // DEBUG
                    console.log("Drawing player " + player.getName());

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
                        // DEBUG
                        console.log("Got player at " + thisPos + " with image " + JSON.stringify(thisImage));
                        ctx.drawImage(thisImage, thisPos[0], thisPos[1], 10, 10);
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