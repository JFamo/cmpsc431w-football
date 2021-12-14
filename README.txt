CMPSC 431W Final Project
Authors - Joshua Famous (jjf5899), Cam Thorpe (cqt5263), Adam Levin (ajl6280)

This is a football statistics simulation build in PHP serving HTML, CSS, and JavaScript
It is hosted at http://e5-cse-cs431fa21s2-14.vmhost.psu.edu/home.php
The design document can be found at https://docs.google.com/document/d/1VG2tymfbuNyAhhMUsFURCRpckvuyh0ApE04vK-z1M2k/edit?usp=sharing

The only departures from the design document included correcting some errors in the large join statement provided for functionality
	Specifically, this join was altered to rename some fields so that the PDO-returned array did not mask any fields
	An error with the Player/players table name was corrected

Below are descriptions of each of the files in the application:

config.php
	Config file used to create a connection in each other file
	Contains and returns an array of username, password, host, dbname, and port
	Used to gitignore sensitive info

createPlayer.php
	File to actionably add a new player to the database
	Uses a transaction, attempting to insert into players, playerfor, and playsposition tables
	Checks for existence of team and position - rolls back on failure
	Redirects on success and offers message on error

delete.php
	Generic file provided in project example
	Deletes a user - unused in our project

deletePlayer.php
	File to actionably delete a user from the database
	Uses a transaction to ensure the user is removed from all tables
	Deletes from players, playsposition, activeroster, and gameroster
	Rollsback if the user fails to be deleted from any of the tables

game.php
	File offering the simulation of a game or HTML canvas to "play" a game
	Checks whether certain parameters are passed in to determine to sim or play
	Queries for home team, away team, and the rosters of both teams
	On simulation, uses PHP to randomly generate statistics and store in a nested array
	On a complete simulation, it loops both teams and all players and inserts into gameroster, passing, rushing, receiving, kicking, and defense tables
	On a complete game, it inserts into the scores table
	These operations are encapsulated in a transaction, which only commits after all stats and scores are inserted, and rolls back on failure
	Uses PHP to iteratively print statlines for all players
	If not simulating, it serves an HTML canvas driven by JavaScript
	Interval functions and event listeners allow the user to move their player
	Loads various png files onto canvas

games.php
	View for listing games and results
	The page saves a selected team and year in the session variable for state
	Selects all box scores and all teams
	Two select inputs are populated with teams and years and are form submitted on change
	Provides a table of all scheduled games for the given criterion
	Offers a form to generate a schedule

generateSchedule.php
	File to actionably generate the schedule for a year
	Performs server-side validation of the provided year
	Uses PHP to randomly and iteratively insert into the games table
	Shows error on failure, redirects on success

home.php
	The main menu
	Offers links to other pages

insert.php
	Generic file provided in project example
	Insert a user - unused in our project

play.php
	Launch page for starting a game
	Selects all games without scores, offers as a select input
	Redirects to game.php on choosing a game, passing in the gameid

playerPage.php
	Biographical page for a single player in the system
	Makes 8-table queries which are joined to provide per-game and career statistics
	Selects player info from a join of 5 tables, using both inner and left join
	Serves all stat info in data tables

players.php
	Offers a listing and CRUD functionality for all players in the system
	Selects players in a join of players, positions, and playsposition
	Serves a report of players in the system, which can be sorted by any of its fields
	Offers the ability to delete players, linking to delete action page
	Includes a form to create players, linking to the create action page
	Includes some client-side validation of player fields

populateTeams.php
	Script to randomly create players for each of the initial 32 teams at each position
	Used by the team to initialize the environment

releasePlayer.php
	File to actionably release a player from their current active roster
	Shows error on failure, redirects quickly on success

signPlayer.php
	File to actionably sign a player to a team's current active roster
	Shows error on failure, redirects quickly on success

start.php
	Generic file provided in project example
	Home page - unused in our project

teams.php
	View listing all of the teams in the system and their active rosters
	Also includes a table of free agents
	Queries for all teams and offers then in a select form that submits on change
	Reports the players currently signed to the selected team, which can be sorted by all fields
	Reports the players currently not on any team, which can be sorted by all fields
	Uses session storage to preserve selected team state
	Calls sign/releasePlayer to move players in and out of free agency
	Offers links to player pages via data rows

updateTeam.php
	File to actionably rebrand a team
	Takes a new name and city and updates the team table for the selected ID

styles.css
	CSS file containing universal style definitions for the application

teams.csv
	Comma Separated Values to load teams to the database
	Entries in order of teamid, team name, city
	This is the result of manual data entry

positions.csv
	Comma Separated Values to load positions to the database
	Entries in the order posid, name, abbreviation
	This is the result of manual data entry

The following images are used for rendering on the HTML game canvas
	adling.png
	aoling.png
	asec.png
	askill.png
	beaver.jfif
	beaver.jpg
	hdline.png
	holine.png
	hsec.png
	hskill.png

	