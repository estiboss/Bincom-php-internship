<?php
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'bincom';
// Connect to the database
$connection = mysqli_connect($hostname, $username, $password, $database);

if (!$connection) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Get results for polling unit with ID 25
$polling_unit_id = 25;
$query = "SELECT party_score FROM announced_pu_results WHERE polling_unit_uniqueid = $polling_unit_id";
$result = mysqli_query($connection, $query);

// Display the results (using mysqli_fetch_assoc)
echo '<h1>Results for Polling Unit ' . $polling_unit_id . '</h1>';
echo '<ul>';
while ($row = mysqli_fetch_assoc($result)) {
  // Access data by column names as selected in the query
  echo '<li>Score: ' . $row['party_score'] . '</li>';
} 
   
echo '</ul>';


// Close the database connection
mysqli_close($connection);
?>
