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
				if(intval($_POST["year"]) < 2020 || intval($_POST["year"]) > 2030){
					echo "Invalid year! Redirecting..."; ?>
					<script>
						var timer = setTimeout(function() {
							window.location='games.php'
						}, 2000);
					</script>
					<?php
				}
				else{
					echo "Generating Schedule for : " . $_POST["year"] . "..."; 
					try {
						// Create PDO
						$conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
						$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						$months = array("01", "02", "08", "09", "10", "11", "12");
						$kickoffs = array("10:00:00 AM", "01:00:00 PM", "04:20:00 PM", "07:00:00 PM");
						for($games=1; $games<=256; $games++){
							do{
								$home = rand(1,32);
								$away = rand(1,32);
							} while ($home == $away);
							$kickoff = $_POST["year"] . '-' . $months[array_rand($months)] . '-' . rand(1,28) . ' ' . $kickoffs[array_rand($kickoffs)];
							// Add game
							$sql1 = "INSERT INTO games (hometeam, awayteam, kickoff) VALUES (?,?,STR_TO_DATE(?,'%Y-%m-%e %h:%i:%s %p'))";
							$st1 = $conn->prepare($sql1);
							$st1->execute(array($home, $away, $kickoff));
						}
						echo "<br>Successfully generated schedule!<br>";
			?>
				<p>You will be redirected in 2 seconds</p>
				<script>
					var timer = setTimeout(function() {
						window.location='games.php'
					}, 2000);
				</script>
			<?php
					} catch(PDOException $e) {
						echo '<br>Failed while generating schedule!<br>';
						echo "<br>" . $e->getMessage();
						echo '<a id="backButton" href="./home.php">Home</a>';
					}
					$conn = null;
				}
			?>
		</p>
    </body>
</div>
</html>
