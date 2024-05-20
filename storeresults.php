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

// Initialize variables
$lga_result = $polling_units_options = '';
$total_score = 0;

// Get all local governments
$lga_query = "SELECT lga_id, lga_name FROM lga";
$lga_result = mysqli_query($connection, $lga_query);

if (!$lga_result) {
    die('Query failed: ' . mysqli_error($connection));
}

// If LGA is selected, get the polling units for the selected LGA
if (isset($_POST['lga'])) {
    $selected_lga = mysqli_real_escape_string($connection, $_POST['lga']);
    $pu_query = "SELECT uniqueid, polling_unit_name FROM polling_unit WHERE lga_id = $selected_lga";
    $pu_result = mysqli_query($connection, $pu_query);

    if ($pu_result) {
        while ($pu_row = mysqli_fetch_assoc($pu_result)) {
            $polling_units_options .= "<option value='{$pu_row['uniqueid']}'>" . htmlspecialchars($pu_row['polling_unit_name']) . "</option>";
        }
    }
}

// If form is submitted, process the data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $polling_unit_id = mysqli_real_escape_string($connection, $_POST['polling_unit']);
    
    // Collect party scores
    $party_scores = [];
    foreach ($_POST['party_scores'] as $index => $party_score) {
        $party = mysqli_real_escape_string($connection, $party_score['party']);
        $score = mysqli_real_escape_string($connection, $party_score['score']);
        $party_scores[$party] = $score;
    }

    // Insert scores for each party
    $query_parts = [];
    foreach ($party_scores as $party => $score) {
        $query_parts[] = "('$polling_unit_id', '$party', '$score')";
    }
    $query = "INSERT INTO announced_pu_results (polling_unit_uniqueid, party_abbreviation, party_score) VALUES " . implode(',', $query_parts);

    if (mysqli_multi_query($connection, $query)) {
        echo "Results stored successfully!";
    } else {
        echo "Error: " . mysqli_error($connection);
    }
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Store Polling Unit Results</title>
    <script>
        function addPartyInput() {
            const container = document.getElementById('party_scores');
            const div = document.createElement('div');
            div.className = 'party_score';
            div.innerHTML = `
                <label>Party Abbreviation:</label>
                <input type="text" name="party_scores[][party]" required>
                <label>Score:</label>
                <input type="number" name="party_scores[][score]" required>
                <button type="button" onclick="removePartyInput(this)">Remove</button>
                <br><br>`;
            container.appendChild(div);
        }

        function removePartyInput(button) {
            const div = button.parentElement;
            div.remove();
        }
    </script>
</head>
<body>
    <h1>Store Results for a New Polling Unit</h1>
    <form action="" method="post">
        <label for="lga">Local Government Area:</label>
        <select name="lga" id="lga" onchange="this.form.submit()" required>
            <option value="">Select LGA</option>
            <?php
            if ($lga_result) {
                while ($lga_row = mysqli_fetch_assoc($lga_result)) {
                    $selected = (isset($selected_lga) && $selected_lga == $lga_row['lga_id']) ? 'selected' : '';
                    echo "<option value='{$lga_row['lga_id']}' $selected>" . htmlspecialchars($lga_row['lga_name']) . "</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="polling_unit">Polling Unit:</label>
        <select name="polling_unit" id="polling_units" required>
            <option value="">Select Polling Unit</option>
            <?php
            if (isset($polling_units_options)) {
                echo $polling_units_options;
            }
            ?>
        </select><br><br>

        <div id="party_scores">
            <div class="party_score">
                <label>Party Abbreviation:</label>
                <input type="text" name="party_scores[0][party]" required>
                <label>Score:</label>
                <input type="number" name="party_scores[0][score]" required>
                <br><br>
            </div>
        </div>
        <button type="button" onclick="addPartyInput()">Add Another Party</button><br><br>

        <button type="submit" name="submit">Submit Results</button>
    </form>
</body>
</html>
