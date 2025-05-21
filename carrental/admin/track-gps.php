<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('includes/config.php');

// Check if the user is logged in
if (strlen($_SESSION['alogin']) == 0) {	
    header('location:index.php');
} else {
    // Fetch all vehicles' GPS data
    $stmt = $dbh->prepare("SELECT vehicle_id, latitude, longitude FROM vehicle_gps");
    $stmt->execute();
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pass vehicle data to JavaScript
    $vehiclesJSON = json_encode($vehicles);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Vehicle Tracking">
    <title>Track Car GPS</title>

    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDu4UQM32MB-5N9mDf8IvqukjRpeA6OL8s&callback=initMap" async defer></script>

    <!-- Bootstrap for better UI -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        var map;
        var vehicles = <?php echo $vehiclesJSON; ?>; // Get vehicle data from PHP
        var userMarker; // Marker for the user's location

        // Initialize the map
        function initMap() {
            var defaultLocation = {lat: 19.1133, lng: 72.8311}; // Default to Andheri, Mumbai

            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 14,
                center: defaultLocation
            });

            // Check if vehicle data exists
            if (vehicles && vehicles.length) {
                // Add markers for each vehicle
                vehicles.forEach(function(vehicle) {
                    var marker = new google.maps.Marker({
                        position: {lat: parseFloat(vehicle.latitude), lng: parseFloat(vehicle.longitude)},
                        map: map,
                        title: 'Vehicle ID: ' + vehicle.vehicle_id,
                        icon: {
                            url: 'pat.png', // Replace with your car image path
                            scaledSize: new google.maps.Size(40, 40) // Resize image as needed
                        }
                    });
                });
            } else {
                console.error('No vehicle data available.');
            }

            // Get the user's location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    // Add a marker for the user's location
                    userMarker = new google.maps.Marker({
                        position: userLocation,
                        map: map,
                        title: 'Your Location',
                        icon: {
                            url: 'pat.png', // Replace with your user location icon path
                            scaledSize: new google.maps.Size(40, 40) // Resize image as needed
                        }
                    });

                    // Center the map on the user's location
                    map.setCenter(userLocation);
                }, function() {
                    handleLocationError(true, map.getCenter());
                });
            } else {
                // Browser doesn't support Geolocation
                handleLocationError(false, map.getCenter());
            }
        }

        // Handle location error
        function handleLocationError(browserHasGeolocation, pos) {
            var message = browserHasGeolocation ?
                'Error: The Geolocation service failed.' :
                'Error: Your browser doesn\'t support geolocation.';
            alert(message);
        }
    </script>

    <style>
        #map {
            height: 500px; /* Set a height */
            width: 100%;   /* Set width to 100% */
        }
        .tracking-container {
            margin-top: 20px;
            text-align: center; /* Center content */
        }
        .home-button {
            margin: 20px auto; /* Center button */
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff; /* Bootstrap primary color */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: inline-block; /* Allows centering */
        }
        .home-button:hover {
            background-color: #0056b3; /* Darker shade on hover */
        }
        .logo {
            max-width: 150px; /* Set a maximum width for the logo */
            margin-bottom: 20px; /* Space below the logo */
        }
    </style>
</head>
<body>
    <div class="container tracking-container">
        <!-- Logo -->
        <img src="favicon.png" alt="Website Logo" class="logo">

        <h2>YASIN MOTORS Track Vehicle GPS Location in Andheri, Mumbai</h2>

        <!-- Home Button -->
        <a href="dashboard.php" class="home-button">Home</a>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div id="map"></div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
