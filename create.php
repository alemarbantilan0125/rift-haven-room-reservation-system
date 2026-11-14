<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guest_name = mysqli_real_escape_string($conn, $_POST['guest_name']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $hotel_name = mysqli_real_escape_string($conn, $_POST['hotel_name']);
    $adults = intval($_POST['adults']);
    $children = intval($_POST['children']);
    $rooms = intval($_POST['rooms']);
    $check_in = $_POST['check_in_date'];
    $check_out = $_POST['check_out_date'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Pricing calculation
    $room_rate = floatval($_POST['room_rate']);
    $d1 = new DateTime($check_in);
    $d2 = new DateTime($check_out);
    $nights = max(1, $d1->diff($d2)->days);
    $total_price = $room_rate * $rooms * $nights;

    $sql = "INSERT INTO reservations (guest_name, destination, hotel_name, adults, children, rooms, check_in_date, check_out_date, status, room_rate, total_price) 
            VALUES ('$guest_name', '$destination', '$hotel_name', $adults, $children, $rooms, '$check_in', '$check_out', '$status', $room_rate, $total_price)";
    
    if (mysqli_query($conn, $sql)) {
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
    <title>Add Reservation - Rift Haven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(15, 23, 42, 0.82), rgba(15, 23, 42, 0.88)), 
                        url('Background.png') center/cover no-repeat fixed;
            min-height: 100vh;
            color: #333;
        }
        .form-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            border-radius: 1.25rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .stepper-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-weight: bold;
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: all 0.2s ease;
        }
        .stepper-btn:active {
            transform: scale(0.92);
        }
        .stepper-input {
            width: 55px;
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
            border: 1px solid #d1d5db;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .summary-box {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 5px solid #0d6efd;
            border-radius: 0.75rem;
        }
    </style>
</head>
<body class="py-4 py-md-5">
<div class="container" style="max-width: 750px;">
    
    <!-- Top Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 text-white px-2">
        <div class="d-flex align-items-center gap-3">
            <img src="RiftHaven.png" alt="Logo" height="45" width="45" class="rounded-circle bg-white p-1 shadow-sm object-fit-cover">
            <div>
                <h4 class="fw-bold mb-0 text-white">Rift Haven</h4>
                <small class="text-white-50">New Guest Reservation Portal</small>
            </div>
        </div>
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill px-3">&larr; Back to Dashboard</a>
    </div>

    <!-- Main Form Card -->
    <div class="card form-card border-0 p-4 p-md-5">
        <div class="mb-4 pb-3 border-bottom">
            <h3 class="fw-bold text-dark mb-1">📝 Create Reservation</h3>
            <p class="text-muted small mb-0">Fill in the guest details, stay dates, and room selection below.</p>
        </div>

        <form method="POST">
            <!-- Guest Name -->
            <div class="mb-4">
                <label class="form-label fw-semibold text-secondary small text-uppercase">Guest Full Name</label>
                <input type="text" name="guest_name" class="form-control form-control-lg fs-6" placeholder="e.g., Juan Dela Cruz" required>
            </div>
            
            <!-- Destination & Hotel Row -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Destination</label>
                    <input class="form-control" list="destinationOptions" name="destination" id="destinationInput" placeholder="Where to? (e.g., Boracay)" autocomplete="off" required>
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
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Available Hotel</label>
                    <select name="hotel_name" id="hotelSelect" class="form-select" required>
                        <option value="">-- Select destination first --</option>
                    </select>
                </div>
            </div>

            <!-- Guests and Rooms Stepper Card -->
            <div class="card p-4 mb-4 bg-light border-0 rounded-4 shadow-sm">
                <h6 class="fw-bold text-dark mb-3">👥 Guests & Room Configuration</h6>
                
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <span class="fw-semibold d-block text-dark">Adults</span>
                        <small class="text-muted">Ages 13+</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary stepper-btn" onclick="stepValue('adults', -1, 1)">−</button>
                        <input type="number" name="adults" id="adults" class="form-control stepper-input" value="2" min="1" readonly>
                        <button type="button" class="btn btn-outline-primary stepper-btn" onclick="stepValue('adults', 1, 1)">+</button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <span class="fw-semibold d-block text-dark">Children</span>
                        <small class="text-muted">Ages 0 - 12</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary stepper-btn" onclick="stepValue('children', -1, 0)">−</button>
                        <input type="number" name="children" id="children" class="form-control stepper-input" value="0" min="0" readonly>
                        <button type="button" class="btn btn-outline-primary stepper-btn" onclick="stepValue('children', 1, 0)">+</button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-semibold d-block text-dark">Rooms</span>
                        <small class="text-muted">Total rooms reserved</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary stepper-btn" onclick="stepValue('rooms', -1, 1); calculateTotal();">−</button>
                        <input type="number" name="rooms" id="rooms" class="form-control stepper-input" value="1" min="1" readonly>
                        <button type="button" class="btn btn-outline-primary stepper-btn" onclick="stepValue('rooms', 1, 1); calculateTotal();">+</button>
                    </div>
                </div>
            </div>

            <!-- Dates and Rate Row -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Check-In Date</label>
                    <input type="date" name="check_in_date" id="check_in_date" class="form-control" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Check-Out Date</label>
                    <input type="date" name="check_out_date" id="check_out_date" class="form-control" required>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Room Rate per Night (₱)</label>
                    <input type="number" step="0.01" name="room_rate" id="room_rate" class="form-control" value="2500.00" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Booking Status</label>
                    <select name="status" class="form-select">
                        <option value="Confirmed">Confirmed</option>
                        <option value="Pending">Pending</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Live Summary Box -->
            <div class="summary-box p-3 p-md-4 mb-4 d-flex justify-content-between align-items-center shadow-sm">
                <div>
                    <span class="text-uppercase small fw-bold text-muted d-block">Estimated Calculation</span>
                    <span class="fw-semibold text-dark fs-6" id="calculation_breakdown">1 night(s) × 1 room(s)</span>
                </div>
                <div class="text-end">
                    <span class="text-uppercase small fw-bold text-muted d-block">Total Cost</span>
                    <span class="fw-bold text-success fs-3" id="total_preview_text">₱2,500.00</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2 d-sm-flex pt-2">
                <button type="submit" class="btn btn-success btn-lg fs-6 flex-grow-1 py-3 fw-semibold shadow-sm rounded-pill">💾 Save Reservation</button>
                <a href="index.php" class="btn btn-outline-secondary btn-lg fs-6 py-3 px-4 rounded-pill">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function stepValue(id, delta, minVal) {
    const input = document.getElementById(id);
    let current = parseInt(input.value) || minVal;
    let next = current + delta;
    if (next >= minVal) {
        input.value = next;
        calculateTotal();
    }
}

// Live Pricing Calculation Script
function calculateTotal() {
    const rate = parseFloat(document.getElementById('room_rate').value) || 0;
    const rooms = parseInt(document.getElementById('rooms').value) || 1;
    const inVal = document.getElementById('check_in_date').value;
    const outVal = document.getElementById('check_out_date').value;

    let nights = 1;
    if (inVal && outVal) {
        const diff = (new Date(outVal) - new Date(inVal)) / (1000 * 60 * 60 * 24);
        nights = diff > 0 ? Math.round(diff) : 1;
    }

    const total = rate * rooms * nights;
    document.getElementById('total_preview_text').innerText = '₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('calculation_breakdown').innerText = `${nights} night(s) × ${rooms} room(s)`;
}

document.getElementById('room_rate').addEventListener('input', calculateTotal);
document.getElementById('check_in_date').addEventListener('change', calculateTotal);
document.getElementById('check_out_date').addEventListener('change', calculateTotal);

// Set default dates if empty
const today = new Date().toISOString().split('T')[0];
const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
if(!document.getElementById('check_in_date').value) document.getElementById('check_in_date').value = today;
if(!document.getElementById('check_out_date').value) document.getElementById('check_out_date').value = tomorrow;
calculateTotal();

const hotelsByLocation = {
    "Boracay": [
        "Shangri-La Boracay", 
        "Henann Crystal Sands Resort", 
        "The Lind Boracay", 
        "Discovery Shores Boracay", 
        "Crimson Resort & Spa Boracay", 
        "Diamond Water Edge Resort"
    ],
    "Iloilo": ["Richmonde Hotel Iloilo", "Courtyard by Marriott Iloilo", "Park Inn by Radisson Iloilo", "Seda Atria", "Zuri Hotel"],
    "Baguio": ["The Manor at Camp John Hay", "Grand Sierra Pines Baguio", "Le Monet Hotel", "Azalea Hotels & Residences Baguio"],
    "Tagaytay": ["Taal Vista Hotel", "Escala Tagaytay", "Anya Resort Tagaytay", "Twin Lakes Hotel"],
    "Palawan": ["El Nido Resorts Miniloc Island", "Cauayan Island Resort", "Seda Lio", "Huni Lio"],
    "Cebu": ["Shangri-La Mactan, Cebu", "Radisson Blu Cebu", "Seda Ayala Center Cebu", "Waterfront Cebu City Hotel"],
    "Siargao": ["Nay Palad Hideaway", "Siargao Bleu Resort & Spa", "Bravo Beach Resort", "Isla Cabana Resort"],
    "Manila": ["Okada Manila", "The Manila Hotel", "Sofitel Philippine Plaza", "Conrad Manila"],
    "Batangas": ["Pico Sands Hotel", "Acuatico Beach Resort", "Club Punta Fuego", "Canyon Cove Hotel & Spa"],
    "Subic": ["The Lighthouse Marina Resort", "Camayan Beach Resort Hotel", "Court Meridian Hotel & Suites"],
    "La Union": ["Thunderbird Resorts - Poro Point", "Kahuna Beach Resort and Spa", "Aureo La Union"]
};

const destInput = document.getElementById('destinationInput');
const hotelSelect = document.getElementById('hotelSelect');

function updateHotels() {
    const val = destInput.value.toLowerCase();
    hotelSelect.innerHTML = '<option value="">-- Choose an available hotel --</option>';
    
    let matchedHotels = [];
    for (const [key, hotels] of Object.entries(hotelsByLocation)) {
        if (val.includes(key.toLowerCase())) {
            matchedHotels = hotels;
            break;
        }
    }

    if (matchedHotels.length > 0) {
        matchedHotels.forEach(hotel => {
            const opt = document.createElement('option');
            opt.value = hotel;
            opt.textContent = hotel;
            hotelSelect.appendChild(opt);
        });
    } else if (val.trim() !== "") {
        ["Grand Plaza Hotel", "City Center Suites", "Royal Palm Resort"].forEach(hotel => {
            const opt = document.createElement('option');
            opt.value = hotel + " (" + destInput.value + ")";
            opt.textContent = hotel + " (" + destInput.value + ")";
            hotelSelect.appendChild(opt);
        });
    } else {
        hotelSelect.innerHTML = '<option value="">-- Select a destination first --</option>';
    }
}

destInput.addEventListener('input', updateHotels);
destInput.addEventListener('change', updateHotels);
</script>
</body>
</html>