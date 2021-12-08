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
				echo "Deleting player: " . $_POST["playerid"] .  "..."; 
				try {
					// Create PDO
					$conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					// Start transaction
					$conn->beginTransaction();
					// Attempt deletion from players
					$sql = "DELETE FROM players WHERE playerid=?";
					$st = $conn->prepare($sql);
					$st->execute(array($_POST["playerid"]));
					// Attempt deletion from playsposition
					$sql = "DELETE FROM playsposition WHERE playerid=?";
					$st = $conn->prepare($sql);
					$st->execute(array($_POST["playerid"]));
					// Attempt deletion from activeroster
					$sql = "DELETE FROM activeroster WHERE playerid=?";
					$st = $conn->prepare($sql);
					$st->execute(array($_POST["playerid"]));
					// Attempt deletion from games
					$sql = "DELETE FROM gameroster WHERE playerid=?";
					$st = $conn->prepare($sql);
					$st->execute(array($_POST["playerid"]));
					// Commit transaction
					$conn->commit();
					echo "Successfully deleted player";
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
