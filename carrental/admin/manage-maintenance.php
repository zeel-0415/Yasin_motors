<?php
include('includes/config.php'); // Ensure this path is correct

// Fetch vehicles in maintenance
$sql = "SELECT * FROM tblvehicles WHERE maintenance_status = 'In Maintenance'";
$query = $dbh->prepare($sql);
$query->execute();
$vehicles = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Maintenance</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        button {
            padding: 8px 12px;
            color: white;
            background-color: #e74c3c;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background-color: #c0392b;
        }

        .home-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            color: white;
            background-color: #3498db;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
        }

        .home-button:hover {
            background-color: #2980b9;
        }

        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 300px; 
            text-align: center;
        }

        .modal-button {
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
        }

        .modal-button.confirm {
            background-color: #e74c3c;
        }

        .modal-button.cancel {
            background-color: #3498db;
        }

    </style>
    <script>
        let currentVehicleId;

        function confirmRemoval(vehicleId) {
            currentVehicleId = vehicleId;
            document.getElementById("confirmationModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("confirmationModal").style.display = "none";
        }

        function removeVehicle() {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        const row = document.getElementById("vehicle-" + currentVehicleId);
                        if (row) {
                            row.remove();
                        }
                        alert("Vehicle maintenance status updated successfully.");
                    } else {
                        alert("An error occurred. Please try again.");
                    }
                }
            };
            xhr.send("remove_id=" + currentVehicleId);
            closeModal();
        }
    </script>
</head>
<body>
    <h1>Cars Maintenance Status</h1>
    
    <table>
        <thead>
            <tr>
                <th>Vehicle ID</th>
                <th>Title</th>
                <th>Brand</th>
                <th>Maintenance Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($vehicles)): ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr id="vehicle-<?php echo htmlentities($vehicle['id']); ?>">
                        <td><?php echo htmlentities($vehicle['id']); ?></td>
                        <td><?php echo htmlentities($vehicle['VehiclesTitle']); ?></td>
                        <td><?php echo htmlentities($vehicle['VehiclesBrand']); ?></td>
                        <td><?php echo htmlentities($vehicle['maintenance_status']); ?></td>
                        <td>
                            <button onclick="confirmRemoval(<?php echo $vehicle['id']; ?>)">Remove</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No vehicles in maintenance.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Modal for Confirmation -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Removal</h3>
            <p>Are you sure you want to remove this vehicle from maintenance?</p>
            <button class="modal-button confirm" onclick="removeVehicle()">Yes</button>
            <button class="modal-button cancel" onclick="closeModal()">No</button>
        </div>
    </div>

    <!-- Move the home button below the table -->
    <a class="home-button" href="dashboard.php">Home</a>

    <?php
    // Handle the AJAX request to remove the vehicle
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
        $vehicleId = intval($_POST['remove_id']); // Get the vehicle ID from the POST request

        // Update the maintenance status of the vehicle to 'Available'
        $sql = "UPDATE tblvehicles SET maintenance_status = 'Available' WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $vehicleId, PDO::PARAM_INT);
        $success = $query->execute();

        // Return JSON response
        echo json_encode(['success' => $success]);
        exit(); // Stop further execution
    }
    ?>
</body>
</html>
