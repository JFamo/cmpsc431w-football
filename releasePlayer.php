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
				echo "Releasing player: " . $_POST["playerid"] .  "..."; 
				try {
					// Create PDO
					$conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					// Attempt deletion from players
					$sql = "DELETE FROM activeroster WHERE playerid=?";
					$st = $conn->prepare($sql);
					$st->execute(array($_POST["playerid"]));
					echo "Successfully released player";
			?>
				<p>You will be redirected in 1 second</p>
				<script>
					var timer = setTimeout(function() {
						window.location='teams.php'
					}, 100);
				</script>
			<?php
				} catch(PDOException $e) {
					// Rollback on failure
					echo $sql . "<br>" . $e->getMessage();
					echo '<a id="backButton" href="./home.php">Home</a>';
				}
				$conn = null;
			?>
		</p>
    </body>
</div>
</html>
