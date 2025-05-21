<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/config.php');

// Check if the admin is logged in
if(strlen($_SESSION['alogin'])==0) {
    header('location:index.php');
    exit();
}

// Update driver assignment when admin submits the form
if(isset($_POST['assignDriver'])) {
    $vehicleId = intval($_POST['vehicleId']);
    $driverName = $_POST['driverName'];
    $driverLicense = $_POST['driverLicense'];

    // Prepare and execute the SQL update statement
    $sql = "UPDATE tblvehicles SET DriverAssigned = 'Yes', DriverName = :driverName, DriverLicenseNumber = :driverLicense WHERE id = :vehicleId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':driverName', $driverName, PDO::PARAM_STR);
    $query->bindParam(':driverLicense', $driverLicense, PDO::PARAM_STR);
    $query->bindParam(':vehicleId', $vehicleId, PDO::PARAM_INT);
    $query->execute();

    $msg = "Driver assigned successfully!";
}

// Fetch bookings that require drivers
$sql = "SELECT b.id AS bookingId, v.VehiclesTitle, v.VehiclesBrand, v.PricePerDay, v.ModelYear 
        FROM tblbooking b 
        JOIN tblvehicles v ON b.VehicleId = v.id 
        WHERE b.RequiresDriver = 'Yes' AND b.DriverAssigned = 'No'";
$query = $dbh->prepare($sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Manage Drivers</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Arial', sans-serif;
        }
        .content-wrapper {
            padding: 20px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .page-title {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .alert {
            margin-top: 20px;
        }
        table {
            margin-top: 20px;
        }
        th {
            background-color: #f8f9fa;
            text-align: center;
        }
        td {
            text-align: center;
        }
        .btn {
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-info {
            background-color: #17a2b8;
        }
        .btn-info:hover {
            background-color: #138496;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="ts-main-content">
        <?php include('includes/leftbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <h2 class="page-title">Manage Drivers</h2>
                <?php if(isset($msg)){ ?><div class="alert alert-success"><?php echo htmlentities($msg); ?></div><?php } ?>

                <div class="text-right mb-3">
                    <a href="dashboard.php" class="btn btn-primary">Home</a>
                </div>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vehicle Title</th>
                            <th>Brand</th>
                            <th>Price Per Day</th>
                            <th>Model Year</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($query->rowCount() > 0) {
                            foreach($results as $result) {
                        ?>
                        <tr>
                            <td><?php echo htmlentities($result->bookingId); ?></td>
                            <td><?php echo htmlentities($result->VehiclesTitle); ?></td>
                            <td><?php echo htmlentities($result->VehiclesBrand); ?></td>
                            <td><?php echo htmlentities($result->PricePerDay); ?></td>
                            <td><?php echo htmlentities($result->ModelYear); ?></td>
                            <td>
                                <button class="btn btn-info" data-toggle="modal" data-target="#viewDetailsModal<?php echo htmlentities($result->bookingId); ?>">View Details</button>
                                <button class="btn btn-success" data-toggle="modal" data-target="#assignDriverModal<?php echo htmlentities($result->bookingId); ?>">Assign Driver</button>

                                <!-- View Details Modal -->
                                <div class="modal fade" id="viewDetailsModal<?php echo htmlentities($result->bookingId); ?>" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="viewDetailsModalLabel">Vehicle Details for <?php echo htmlentities($result->VehiclesTitle); ?></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Brand:</strong> <?php echo htmlentities($result->VehiclesBrand); ?></p>
                                                <p><strong>Price Per Day:</strong> <?php echo htmlentities($result->PricePerDay); ?></p>
                                                <p><strong>Model Year:</strong> <?php echo htmlentities($result->ModelYear); ?></p>
                                                <!-- Add more vehicle details here if needed -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assign Driver Modal -->
                                <div class="modal fade" id="assignDriverModal<?php echo htmlentities($result->bookingId); ?>" tabindex="-1" role="dialog" aria-labelledby="assignDriverModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="assignDriverModalLabel">Assign Driver to <?php echo htmlentities($result->VehiclesTitle); ?></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="vehicleId" value="<?php echo htmlentities($result->bookingId); ?>">
                                                    <div class="form-group">
                                                        <label for="driverName">Driver Name</label>
                                                        <input type="text" class="form-control" name="driverName" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="driverLicense">Driver License Number</label>
                                                        <input type="text" class="form-control" name="driverLicense" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-success" name="assignDriver">Assign Driver</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                            <tr><td colspan="6">No vehicles available for driver assignment</td></tr>
                        <?php 
                        } 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
