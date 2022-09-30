<?php

declare(strict_types = 1);

namespace CarValue;

require_once __DIR__ . '/VehicleMarketValue.php';

/**
 * CarValue Trial Project 2.
 *
 * @category Class
 * @author   Avinesh Bangar <avinesh@shaw.ca>
 *
 * This is called from the front-end (CarValue.html) using Fetch API.
 */

$vehicle = new VehicleMarketValue();

$action = $_POST['action'];

// Ensure we have an 'action' that states 'fetch_data'.
if ($action === 'fetch_data') {
    // Ensure we have a value for 'vehicle' or use a default value.
    $postVehicle = (isset($_POST['vehicle']) && $_POST['vehicle'] != '') ? $_POST['vehicle'] : '2015 Toyota Camry';
    // Ensure we have a value for 'mileage', or use null.
    $postMileage = (isset($_POST['mileage']) && $_POST['mileage'] != '') ? $_POST['mileage'] : null;
    // Return JSON response; fetches the estimated market price and up to 100 rows of vehicle data.
    echo $vehicle->predictMarketValue($postVehicle, $postMileage);
}
