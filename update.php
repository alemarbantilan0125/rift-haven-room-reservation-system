<?php
include 'db_connect.php';

$id = intval($_GET['id'] ?? 0);

// Fetch existing record
$result = mysqli_query($conn, "SELECT * FROM reservations WHERE id = $id");
if (mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit();
}
$row = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guest_name = mysqli_real_escape_string($conn, $_POST['guest_name']);$destination = mysqli_real_escape_string($conn,$_POST['destination']);
    $hotel_name = mysqli_real_escape_string($conn, $_POST['hotel_name']);$adults = intval($_POST['adults']);$children = intval($_POST['children']);$rooms = intval($_POST['rooms']);$check_in = $_POST['check_in_date'];$check_out = $_POST['check_out_date'];$status = mysqli_real_escape_string($conn,$_POST['status']);
    
    // Pricing calculation
    $room_rate = floatval($_POST['room_rate']);$d1 = new DateTime($check_in);$d2 = new DateTime($check_out);$nights = max(1, $d1->diff($d2)->days);
    $total_price =$room_rate * $rooms * $nights;

    $sql = "UPDATE reservations SET 
            guest_name = '$guest_name', 
            destination = '$destination', 
            hotel_name = '$hotel_name', 
            adults = $adults, 
            children = $children, 
            rooms = $rooms, 
            check_in_date = '$check_in', 
            check_out_date = '$check_out', 
            status = '$status', 
            room_rate = $room_rate, 
            total_price = $total_price 
            WHERE id = $id";
    
    if (mysqli_query($conn,$sql)) {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reservation - Rift Haven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f5f9;
        }
        .stepper-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            font-weight: bold;
            font-size: 1.2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: all 0.2s ease;
        }
        .stepper-btn:active {
            transform: scale(0.95);
        }
        .stepper-input {
            width: 60px;
            text-align: center;
            font-weight: 600;
            font-size: 1.1rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            background-color: #fff;
        }
        .form-control, .form-select {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
<div class="container px-3 py-4 my-md-5" style="max-width: 650px;">
    <!-- Brand Header -->
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark mb-1">Rift Haven</h3>
        <p class="text-muted small">Edit Room Reservation</p>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-primary text-white fw-semibold py-3 px-4 rounded-top-4 fs-5">
            ✏️ Update Reservation Record
        </div>
        <div class="card-body p-4 p-md-5">
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Guest Name</label>
                    <input type="text" name="guest_name" class="form-control" value="<?= htmlspecialchars($row['guest_name']) ?>" required>
                </div>
                
                <!-- Destination Field -->
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Destination</label>
                    <input class="form-control" list="destinationOptions" name="destination" id="destinationInput" value="<?= htmlspecialchars($row['destination']) ?>" autocomplete="off" required>
                    <datalist id="destinationOptions">
                        <option value="Boracay, Aklan, Philippines">
                        <option value="Iloilo City, Iloilo, Philippines">
                        <option value="Baguio City, Benguet, Philippines">
                        <option value="Tagaytay City, Cavite, Philippines">
                        <option value="El Nido, Palawan, Philippines">
                        <option value="Cebu City, Cebu, Philippines">
                        <option value="Siargao Island, Surigao del Norte, Philippines">
                        <option value="Manila, Metro Manila, Philippines">
                        <option value="Batangas, Luzon, Philippines">
                        <option value="Subic, Zambales, Philippines">
                        <option value="La Union, Philippines">
                    </datalist>
                </div>

                <!-- Dynamic Hotel Selection -->
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Available Hotel</label>
                    <select name="hotel_name" id="hotelSelect" class="form-select" required>
                        <option value="<?= htmlspecialchars($row['hotel_name']) ?>"><?= htmlspecialchars($row['hotel_name']) ?> (Current)</option>
                    </select>
                </div>

                <!-- Room Rate & Auto-Calculation Summary Box -->
                <div class="card p-3 p-md-4 mb-4 bg-light border-0 rounded-4 shadow-sm">
                    <h6 class="text-secondary mb-3 fw-bold fs-6">💰 Pricing & Stay Duration</h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Room Rate per Night (₱)</label>
                        <input type="number" step="0.01" name="room_rate" id="room_rate" class="form-control bg-white" value="<?= htmlspecialchars($row['room_rate'] ?? '2500.00') ?>" required>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 border">
                        <span class="small fw-semibold text-muted" id="calculation_breakdown">1 night × 1 room</span>
                        <div class="text-end">
                            <span class="small text-muted d-block">Estimated Total:</span>
                            <span class="fw-bold text-success fs-4" id="total_preview_text">₱0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Touch-Friendly Guests and Rooms Section -->
                <div class="card p-3 p-md-4 mb-4 bg-white border rounded-4 shadow-sm">
                    <h6 class="text-secondary mb-3 fw-bold fs-6">👥 Guests and Rooms</h6>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <span class="fw-semibold d-block">Adults</span>
                            <small class="text-muted">Ages 13 or above</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-primary stepper-btn" onclick="stepValue('adults', -1, 1)">−</button>
                            <input type="number" name="adults" id="adults" class="form-control stepper-input" value="<?= intval($row['adults']) ?>" min="1" readonly>
                            <button type="button" class="btn btn-outline-primary stepper-btn" onclick="stepValue('adults', 1, 1)">+</button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <span class="fw-semibold d-block">Children</span>
                            <small class="text-muted">Ages 0 – 12</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-primary stepper-btn" onclick="stepValue('children', -1, 0)">−</button>
                            <input type="number" name="children" id="children" class="form-control stepper-input" value="<?= intval($row['children']) ?>" min="0" readonly>
                            <button type="button" class="btn btn-outline-primary stepper-btn" onclick="stepValue('children', 1, 0)">+</button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-semibold d-block">Rooms</span>
                            <small class="text-muted">Number of rooms</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-primary stepper-btn" onclick="stepValue('rooms', -1, 1); calculateTotal();">−</button>
                            <input type="number" name="rooms" id="rooms" class="form-control stepper-input"