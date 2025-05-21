<?php
// Database connection
$host = 'localhost';
$dbname = 'carrental';  // Update if necessary
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Handle form submission for maintenance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vehicle_id'])) {
    $vehicleId = $_POST['vehicle_id'];
    $maintenanceOptions = isset($_POST['maintenance_options']) ? $_POST['maintenance_options'] : [];

    // Update the maintenance status in the database
    $maintenanceStatus = 'In Maintenance';
    $sqlUpdate = "UPDATE tblvehicles SET maintenance_status = :maintenanceStatus WHERE id = :vehicleId";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bindParam(':maintenanceStatus', $maintenanceStatus);
    $stmtUpdate->bindParam(':vehicleId', $vehicleId);
    
    if ($stmtUpdate->execute()) {
        echo "<script>alert('Vehicle sent for maintenance successfully.');</script>";
    } else {
        echo "<script>alert('Failed to send vehicle for maintenance.');</script>";
    }
}

// Fetch vehicle data from tblvehicles
$sql = "SELECT * FROM tblvehicles"; // Use the correct table name here
$stmt = $conn->prepare($sql);
$stmt->execute();
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .card {
            transition: transform 0.2s;
            overflow: hidden;
        }
        .card img {
            transition: transform 0.3s;
            height: 200px; /* Adjust height as needed */
            object-fit: cover; /* Maintain aspect ratio */
        }
        .card:hover img {
            transform: scale(1.1); /* Enlarge image on hover */
        }
        .modal-header .icon {
            margin-right: 10px; /* Space between icon and title */
        }
        .btn-back {
            margin: 40px 0; /* Margin to push the button down */
        }
        .btn-back:hover {
            background-color: #0056b3; /* Darker shade of primary color */
            color: white; /* Change text color on hover */
        }
        .btn-maintenance {
            display: flex;
            align-items: center; /* Center the icon vertically with text */
            transition: background-color 0.3s; /* Smooth transition for background color */
        }
        .btn-maintenance:hover {
            background-color: #0056b3; /* Darker blue on hover */
            color: white; /* Change text color on hover */
        }
        .btn-maintenance i {
            margin-right: 5px; /* Space between icon and text */
        }
    </style>
    <script>
        function validateForm(event) {
            // Get all checkboxes within the current form
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            // Check if at least one checkbox is checked
            const isChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);

            if (!isChecked) {
                // Prevent form submission
                event.preventDefault();
                // Display alert
                alert('Please select at least one maintenance option before submitting.');
            }
        }
    </script>
</head>
<body>

<div class="container">
    <h1 class="text-center my-4">Vehicle Maintenance</h1>

    <div class="row">
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <?php
                        // Display the first available image (Vimage6 to Vimage10)
                        $image = $vehicle['Vimage6'] ?: $vehicle['Vimage7'] ?: $vehicle['Vimage8'] ?: $vehicle['Vimage9'] ?: $vehicle['Vimage10'];
                        if ($image): ?>
                            <img src="<?php echo $image; ?>" class="img-fluid" alt="Vehicle Image">
                        <?php endif; ?>
                        <h5 class="card-title mt-3"><?php echo $vehicle['VehiclesTitle']; ?></h5>
                        <p class="card-text">
                            <strong>Brand:</strong> <?php echo $vehicle['VehiclesBrand']; ?><br>
                            <strong>Fuel Type:</strong> <?php echo $vehicle['FuelType']; ?><br>
                            <strong>Price Per Day:</strong> $<?php echo $vehicle['PricePerDay']; ?><br>
                            <strong>Model Year:</strong> <?php echo $vehicle['ModelYear']; ?><br>
                            <strong>Seating Capacity:</strong> <?php echo $vehicle['SeatingCapacity']; ?> seats<br>
                            <strong>Maintenance Status:</strong> <?php echo isset($vehicle['maintenance_status']) ? $vehicle['maintenance_status'] : 'Available'; ?>
                        </p>
                        <!-- Button trigger modal with icon -->
                        <button type="button" class="btn btn-primary w-100 btn-maintenance" data-bs-toggle="modal" data-bs-target="#maintenanceModal<?php echo $vehicle['id']; ?>">
                            <i class="fas fa-wrench"></i> Send for Maintenance
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal for each vehicle -->
            <div class="modal fade" id="maintenanceModal<?php echo $vehicle['id']; ?>" tabindex="-1" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <i class="fas fa-wrench icon"></i> <!-- Repair icon -->
                            <h5 class="modal-title" id="maintenanceModalLabel">Send <?php echo $vehicle['VehiclesTitle']; ?> for Maintenance</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Select maintenance options:</p>
                            <form method="POST" action="" onsubmit="validateForm(event);">
                                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="option1<?php echo $vehicle['id']; ?>" name="maintenance_options[]" value="Oil Change">
                                    <label class="form-check-label" for="option1<?php echo $vehicle['id']; ?>">Oil Change</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="option2<?php echo $vehicle['id']; ?>" name="maintenance_options[]" value="Tire Rotation">
                                    <label class="form-check-label" for="option2<?php echo $vehicle['id']; ?>">Tire Rotation</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="option3<?php echo $vehicle['id']; ?>" name="maintenance_options[]" value="Brake Inspection">
                                    <label class="form-check-label" for="option3<?php echo $vehicle['id']; ?>">Brake Inspection</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="option4<?php echo $vehicle['id']; ?>" name="maintenance_options[]" value="Fluid Check">
                                    <label class="form-check-label" for="option4<?php echo $vehicle['id']; ?>">Fluid Check</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="option5<?php echo $vehicle['id']; ?>" name="maintenance_options[]" value="Battery Check">
                                    <label class="form-check-label" for="option5<?php echo $vehicle['id']; ?>">Battery Check</label>
                                </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Send for Maintenance</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="dashboard.php" class="btn btn-secondary btn-back">Back to Home</a> <!-- Back to Home button -->
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
