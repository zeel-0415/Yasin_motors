<?php
// Include database configuration
include('includes/config.php');

$msg = "";
$assignmentHistory = [];
$drivers = []; // Initialize the $drivers variable as an empty array

// Fetch driver data from the database
try {
    $sql = "SELECT * FROM driver_assignment_history";
    $query = $dbh->prepare($sql);
    $query->execute();
    $drivers = $query->fetchAll(PDO::FETCH_OBJ);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Fetch driver assignment history
try {
    $sqlHistory = "SELECT booking.BookingNumber, booking.VehicleId, 
                   booking.FromDate, booking.ToDate, booking.KmtoTravel, 
                   booking.message, booking.Status, 
                   driver.DriverName, booking.RentalType, booking.RequiresDriver 
                   FROM tblbooking AS booking
                   JOIN driver_assignment_history AS driver ON booking.DriverName = driver.DriverName";
    $queryHistory = $dbh->prepare($sqlHistory);
    $queryHistory->execute();
    $assignmentHistory = $queryHistory->fetchAll(PDO::FETCH_OBJ);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Handle form submission to add a new driver
if (isset($_POST['addDriver'])) {
    $driverName = $_POST['driverName'];
    $licenseImage = $_FILES["licenseImage"]["name"];
    $tmp_dir = $_FILES["licenseImage"]["tmp_name"];
    $imageFolder = "assets/images/";

    // Ensure the image folder exists and is writable
    if (!is_dir($imageFolder)) {
        mkdir($imageFolder, 0755, true);
    }

    // Move the uploaded file
    if (move_uploaded_file($tmp_dir, $imageFolder . $licenseImage)) {
        // Insert driver into the database
        try {
            if (isset($_POST['VehicleId']) && !empty($_POST['VehicleId'])) {
                $vehicleId = $_POST['VehicleId'];

                $sql = "INSERT INTO driver_assignment_history (DriverName, LicenseImage, VehicleId) VALUES (:driverName, :licenseImage, :vehicleId)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':driverName', $driverName, PDO::PARAM_STR);
                $query->bindParam(':licenseImage', $licenseImage, PDO::PARAM_STR);
                $query->bindParam(':vehicleId', $vehicleId, PDO::PARAM_INT);
                $query->execute();
                $msg = "Driver added successfully!";
            } else {
                echo "Invalid Vehicle ID.";
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Failed to upload image.";
    }
}

// Handle driver deletion
if (isset($_POST['deleteDriver'])) {
    $driverId = $_POST['driverId'];

    try {
        $sql = "DELETE FROM driver_assignment_history WHERE id = :driverId";
        $query = $dbh->prepare($sql);
        $query->bindParam(':driverId', $driverId, PDO::PARAM_INT);
        $query->execute();

        // Send a success response
        echo json_encode(['status' => 'success']);
        exit();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Information</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            padding: 20px;
        }
        h1, h2 {
            color: #343a40;
        }
        .table img {
            border-radius: 5px;
        }
        .home-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
        }
        .home-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center mb-4">Driver Information</h1>

    <?php if ($msg) { echo "<div class='alert alert-success'>$msg</div>"; } ?>

    <a href="dashboard.php" class="home-btn">Home</a>

    <form method="POST" enctype="multipart/form-data" class="mb-4">
        <div class="form-row">
            <div class="col">
                <input type="text" name="driverName" class="form-control" placeholder="Driver Name" required>
            </div>
            <div class="col">
                <input type="file" name="licenseImage" class="form-control" accept="image/*" required>
            </div>
            <div class="col">
                <input type="number" name="VehicleId" class="form-control" placeholder="Vehicle ID" required>
            </div>
            <div class="col">
                <button type="submit" name="addDriver" class="btn btn-primary">Add Driver</button>
            </div>
        </div>
    </form>

    <h2 class="mt-5">Drivers List</h2>
    <table class="table table-bordered table-hover" id="driversTable">
        <thead class="thead-dark">
            <tr>
                <th>Driver Name</th>
                <th>License Image</th>
                <th>Vehicle ID</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($drivers) > 0) : ?>
                <?php foreach ($drivers as $driver) : ?>
                    <tr id="driver-<?php echo htmlspecialchars($driver->id); ?>">
                        <td><?php echo htmlspecialchars($driver->DriverName); ?></td>
                        <td><img src="<?php echo htmlspecialchars($imageFolder . $driver->LicenseImage); ?>" alt="License Image" width="100"></td>
                        <td><?php echo htmlspecialchars($driver->VehicleId); ?></td>
                        <td>
                            <button class="btn btn-danger btn-sm deleteDriver" data-id="<?php echo htmlspecialchars($driver->id); ?>">
                               Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No drivers found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2 class="mt-5">Driver Assignment History</h2>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Booking Number</th>
                <th>Vehicle ID</th>
                <th>From Date</th>
                <th>To Date</th>
                <th>Km to Travel</th>
                <th>Message</th>
                <th>Status</th>
                <th>Driver Name</th>
                <th>Rental Type</th>
                <th>Requires Driver</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($assignmentHistory) > 0) : ?>
                <?php foreach ($assignmentHistory as $assignment) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment->BookingNumber); ?></td>
                        <td><?php echo htmlspecialchars($assignment->VehicleId); ?></td>
                        <td><?php echo htmlspecialchars($assignment->FromDate); ?></td>
                        <td><?php echo htmlspecialchars($assignment->ToDate); ?></td>
                        <td><?php echo htmlspecialchars($assignment->KmtoTravel); ?></td>
                        <td><?php echo htmlspecialchars($assignment->message); ?></td>
                        <td><?php echo htmlspecialchars($assignment->Status); ?></td>
                        <td><?php echo htmlspecialchars($assignment->DriverName); ?></td>
                        <td><?php echo htmlspecialchars($assignment->RentalType); ?></td>
                        <td><?php echo htmlspecialchars($assignment->RequiresDriver); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10">No assignment history found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        $('.deleteDriver').click(function (e) {
            e.preventDefault(); // Prevent default form submission
            var driverId = $(this).data('id'); // Get driver ID

            if (confirm('Are you sure you want to delete this driver?')) {
                $.ajax({
                    type: 'POST',
                    url: '', // Keep it as empty to post to the same page
                    data: { deleteDriver: true, driverId: driverId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#driver-' + driverId).fadeOut(); // Remove the row
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function () {
                        alert('An error occurred while processing your request.');
                    }
                });
            }
        });
    });     
</script>
</body>
</html>
