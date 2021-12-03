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

?>

<style>
<?php include './styles.css'; ?>
</style>

<!DOCTYPE html>
<html>
    <head>
        <title>FootballSimXYZ</title>
    </head>
    <body>
		<p>
			<?php
				$conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$names = array("Mark", "Jim", "John", "James", "Mike", "Anthony", "Lynn", "Doug", "Tom", "Thomas", "Edward", "Brady", "Culpepper", "Devonta", "Smith", "Eric", "Erikssen", "Johnson", "Jones", "White", "Brown", "Cooper", "Allen", "Josh", "Derek", "Quinn", "Ryan", "Matt", "Johanes", "Blake", "George", "Paul", "Leonard", "Malik", "Jackson", "Bourne", "Green", "Terry", "Jason", "Kelce", "Peterson", "Patrick", "Neil", "Zachary", "Wiles", "Dorsey", "David", "Gaiman", "Preston", "Samuel", "Dante", "Frank", "Carter", "Riley", "Jacob", "Jake", "Mark", "Marsh", "Darius", "Malik", "Hunter", "Jamarr", "Chase", "Young", "Prince", "Hollywood", "Marquise", "Queen", "Lamar", "Jacob", "Joshua");
				$ages = array(19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44);
				for($team = 1; $team <= 32; $team++){
					for($pos = 1; $pos <= 20; $pos++){
						$player = array("fname"=>$names[array_rand($names)], "mname"=>$names[array_rand($names)], "lname"=>$names[array_rand($names)], "age"=>$ages[array_rand($ages)]);
						$sql1 = "INSERT INTO players (fname, mname, lname, age) VALUES (?,?,?,?)";
						$st1 = $conn->prepare($sql1);
						$st1->execute(array($player["fname"], $player["mname"], $player["lname"], $player["age"]));
						$playerid = $conn->lastInsertId();
						// Attempt to add team
						$sql2 = "INSERT INTO playerfor (playerid, teamid, fromdate) VALUES (?, ?, NOW())";
						$st2 = $conn->prepare($sql2);
						$st2->execute(array($playerid, $team));
						// Attempt to add position
						$sql3 = "INSERT INTO playsposition (playerid, posid) VALUES (?, ?)";
						$st3 = $conn->prepare($sql3);
						$st3->execute(array($playerid, $pos));
					}
					for($pos = 1; $pos <= 15; $pos++){
						$player = array("fname"=>$names[array_rand($names)], "mname"=>$names[array_rand($names)], "lname"=>$names[array_rand($names)], "age"=>$ages[array_rand($ages)]);
						$sql1 = "INSERT INTO players (fname, mname, lname, age) VALUES (?,?,?,?)";
						$st1 = $conn->prepare($sql1);
						$st1->execute(array($player["fname"], $player["mname"], $player["lname"], $player["age"]));
						$playerid = $conn->lastInsertId();
						// Attempt to add team
						$sql2 = "INSERT INTO playerfor (playerid, teamid, fromdate) VALUES (?, ?, NOW())";
						$st2 = $conn->prepare($sql2);
						$st2->execute(array($playerid, $team));
						// Attempt to add position
						$sql3 = "INSERT INTO playsposition (playerid, posid) VALUES (?, ?)";
						$st3 = $conn->prepare($sql3);
						$st3->execute(array($playerid, $pos));
					}
				}
				echo "Created new players";
			?>
		</p>
    </body>
</div>
</html>
