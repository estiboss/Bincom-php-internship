<?php
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'bincom';


$connection = mysqli_connect($hostname, $username, $password, $database);

if (!$connection) {
    die('Database connection failed: ' . mysqli_connect_error());
}


$lga_query = "SELECT lga_id, lga_name FROM lga";
$lga_result = mysqli_query($connection, $lga_query);

if (!$lga_result) {
    die('Query failed: ' . mysqli_error($connection));
}


if (isset($_POST['lga'])) {
    $selected_lga = mysqli_real_escape_string($connection, $_POST['lga']);

    
    $pu_query = "SELECT uniqueid FROM polling_unit WHERE lga_id = $selected_lga";
    $pu_result = mysqli_query($connection, $pu_query);

    if (!$pu_result) {
        die('Query failed: ' . mysqli_error($connection));
    }

    
    $total_score = 0;

    
    while ($pu_row = mysqli_fetch_assoc($pu_result)) {
        $polling_unit_id = $pu_row['uniqueid'];

        
        $score_query = "SELECT SUM(party_score) AS total_score FROM announced_pu_results WHERE polling_unit_uniqueid = $polling_unit_id";
        $score_result = mysqli_query($connection, $score_query);
        
        if ($score_result) {
            $score_row = mysqli_fetch_assoc($score_result);

            if ($score_row['total_score'] !== null) {
                $total_score += $score_row['total_score'];
            }
        }
    }


    $lga_name_query = "SELECT lga_name FROM lga WHERE lga_id = $selected_lga";
    $lga_name_result = mysqli_query($connection, $lga_name_query);
    if ($lga_name_result) {
        $lga_name_row = mysqli_fetch_assoc($lga_name_result);
        $lga_name = $lga_name_row['lga_name'];
    } else {
        $lga_name = 'Unknown';
    }

    echo "<h1>Summed Total Result for Local Government: " . htmlspecialchars($lga_name) . "</h1>";
    echo "<p>The total score for all polling units under this local government is: <strong>" . htmlspecialchars($total_score) . "</strong></p>";

} else {
    echo "<h1>Select Local Government</h1>";
    echo "<form method='post'>";
    echo "<select name='lga'>";
    while ($lga_row = mysqli_fetch_assoc($lga_result)) {
        $lga_id = $lga_row['lga_id'];
        $lga_name = $lga_row['lga_name'];
        echo "<option value='$lga_id'>" . htmlspecialchars($lga_name) . "</option>";
    }
    echo "</select>";
    echo "<button type='submit'>Submit</button>";
    echo "</form>";
}

// Close the database connection
mysqli_close($connection);
?>
