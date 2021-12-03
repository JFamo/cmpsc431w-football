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
<!DOCTYPE html>
<html>
    <head>
        <title>FootballSimXYZ</title>
    </head>
    <body>
		<p>
			<?php 
				echo "Deleting player: " . $_POST["fname"] . " " . $_POST["lname"] . "..."; 
				try {
					// Create PDO
					$conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					// Start transaction
					$conn->beginTransaction();
					// Attempt create player
					$sql1 = "INSERT INTO players (fname, mname, lname, age) VALUES (?,?,?,?)";
					$st1 = $conn->prepare($sql1);
					$st1->execute(array($_POST["fname"], $_POST["mname"], $_POST["lname"], $_POST["age"]));
					$playerid = $conn->lastInsertId();
					// Attempt to add team
					$sql2 = "INSERT INTO playerfor (playerid, teamid, fromdate) VALUES (?, ?, NOW())";
					$st2 = $conn->prepare($sql2);
					$st2->execute(array($playerid, $_POST["team"]));
					// Attempt to add position
					$sql3 = "INSERT INTO playsposition (playerid, posid) VALUES (?, ?)";
					$st3 = $conn->prepare($sql3);
					$st3->execute(array($playerid, $_POST["position"]));
					// Commit transaction
					$conn->commit();
					echo "Successfully created new player";
			?>
				<p>You will be redirected in 2 seconds</p>
				<script>
					var timer = setTimeout(function() {
						window.location='players.php'
					}, 2000);
				</script>
			<?php
				} catch(PDOException $e) {
					// Rollback on failure
					$conn->rollBack();
					echo $sql . "<br>" . $e->getMessage();
					echo '<a id="backButton" href="./home.php">Home</a>';
				}
				$conn = null;
			?>
		</p>
    </body>
</div>
</html>
