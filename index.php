<?php
session_start();

// Handle Enter Website action
if (isset($_POST['enter_site'])) {
    $_SESSION['entered'] = true;
    header("Location: index.php");
    exit();
}

// Handle Exit/Logout back to welcome screen
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// 1. SHOW WELCOME SCREEN IF NOT YET ENTERED
if (!isset($_SESSION['entered']) || $_SESSION['entered'] !== true):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Rift Haven Room Reservation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(10, 20, 40, 0.85), rgba(5, 10, 20, 0.95)),
                        url('Background.png') center/cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .welcome-card {
            width: 100%;
            max-width: 440px;
            border-radius: 1.25rem;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            animation: fadeInScale 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.92) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>
</head>
<body>

<div class="card border-0 welcome-card p-4 text-center">
    <div class="card-body">
        <div class="mb-3">
            <img src="RiftHaven.png" alt="Rift Haven Logo" class="img-fluid rounded-circle shadow-sm border border-2 border-primary p-1" style="max-height: 110px; width: 110px; object-fit: cover;">
        </div>
        <h3 class="fw-bold text-dark mb-0">Rift Haven</h3>
        <p class="text-primary mb-3 small fw-bold">HOTEL & RESORT RESERVATION</p>
        
        <div class="bg-light border rounded-pill py-2 px-3 small mb-4 text-muted shadow-sm">
            📅 <span id="welcome-clock" class="fw-semibold">Loading clock...</span>
        </div>

        <form method="POST">
            <div class="d-grid">
                <button type="submit" name="enter_site" class="btn btn-primary btn-lg fw-semibold py-3 shadow rounded-pill">
                    Enter the Website &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function updateWelcomeClock() {
        const now = new Date();
        const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('welcome-clock').innerText = now.toLocaleDateString('en-US', options);
    }
    setInterval(updateWelcomeClock, 1000);
    updateWelcomeClock();
</script>

</body>
</html>
<?php
exit();
endif;

// 2. SHOW RESERVATION DASHBOARD ONCE ENTERED
include 'db_connect.php';

// Calculate KPI Metrics
$kpi_query = mysqli_query($conn, "SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed_count,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status != 'Cancelled' THEN total_price ELSE 0 END) as total_revenue
    FROM reservations");
$kpi = mysqli_fetch_assoc($kpi_query);

// Handle Search & Filter Inputs
$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? '');

$where_clauses = [];
if ($search !== '') {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $where_clauses[] = "(guest_name LIKE '%$safe_search%' OR destination LIKE '%$safe_search%' OR hotel_name LIKE '%$safe_search%')";
}
if ($status_filter !== '') {
    $safe_status = mysqli_real_escape_string($conn, $status_filter);
    $where_clauses[] = "status = '$safe_status'";
}

$sql = "SELECT * FROM reservations";
if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY id DESC";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rift Haven Room Reservation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(15, 23, 42, 0.82), rgba(15, 23, 42, 0.88)), 
                        url('Background.png') center/cover no-repeat fixed;
            min-height: 100vh;
            color: #333;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }
        /* Beautified Navbar Buttons */
        .navbar-custom .btn {
            transition: all 0.25s ease;
            font-size: 0.875rem;
        }
        .navbar-custom .btn-light {
            background-color: #ffffff;
            color: #0d6efd;
            border: none;
        }
        .navbar-custom .btn-light:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 255, 255, 0.35);
        }
        .navbar-custom .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        .navbar-custom .btn-outline-danger {
            border-color: rgba(255, 255, 255, 0.4);
            color: #fff;
        }
        .navbar-custom .btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            transform: translateY(-2px);
        }

        .kpi-card {
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .custom-card-table {
            border-radius: 1rem;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.96) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        }
        .table tbody tr {
            animation: fadeInUp 0.4s ease-out forwards;
            transition: background-color 0.2s ease;
        }
        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        /* Glassmorphism About Modal & Transparent Backdrop */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.25) !important;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        #aboutModal .modal-content {
            background: rgba(255, 255, 255, 0.45) !important;
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            border-radius: 1.25rem;
        }
        #aboutModal .modal-header {
            background: rgba(13, 110, 253, 0.75) !important;
            backdrop-filter: blur(10px);
        }
        #aboutModal .card {
            background: rgba(255, 255, 255, 0.35) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
        }
        /* Pure White Subtitle */
        #aboutModal .about-subtitle {
            color: #ffffff !important;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }

        @media (max-width: 767.98px) {
            .mobile-card-table thead { display: none; }
            .mobile-card-table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #e0e0e0;
                border-radius: 0.75rem;
                background: #fff;
                padding: 1rem;
                box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            }
            .mobile-card-table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border: none;
                padding: 0.5rem 0;
                border-bottom: 1px solid #f2f2f2;
                text-align: right;
            }
            .mobile-card-table tbody td:last-child {
                border-bottom: none;
                justify-content: flex-end;
                gap: 0.5rem;
                padding-top: 0.75rem;
            }
            .mobile-card-table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #6c757d;
                text-align: left;
                margin-right: 1rem;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-custom mb-4 py-3 sticky-top">
    <div class="container d-flex flex-wrap justify-content-between align-items-center">
        <a class="navbar-brand d-flex align-items-center gap-3 text-decoration-none" href="index.php">
            <img src="RiftHaven.png" alt="Logo" height="45" width="45" class="rounded-circle bg-white p-1 shadow-sm object-fit-cover">
            <div>
                <span class="fs-5 fw-bold text-white d-block lh-1">Rift Haven</span>
                <span class="fs-7 text-white-50 small">Room Reservation System</span>
            </div>
        </a>
        
        <div class="text-white small bg-black bg-opacity-30 px-3 py-2 rounded-pill d-none d-lg-flex align-items-center gap-2 border border-white border-opacity-15 shadow-sm">
            <span>📅</span> <span id="dashboard-clock" class="fw-medium">Loading...</span>
        </div>

        <div class="d-flex gap-2 align-items-center">
            <a href="create.php" class="btn btn-light fw-bold px-3 py-2 shadow-sm rounded-pill">+ Add Reservation</a>
            <button type="button" class="btn btn-outline-light px-3 py-2 rounded-pill fw-medium" data-bs-toggle="modal" data-bs-target="#aboutModal">
                ℹ️ About
            </button>
            <a href="index.php?logout=1" class="btn btn-outline-danger px-3 py-2 rounded-pill">Exit</a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <!-- Hero Banner using Cover.png -->
    <div class="position-relative mb-4 overflow-hidden rounded-4 shadow-lg" style="max-height: 260px;">
        <img src="Cover.png" alt="Cover Banner" class="w-100 h-100 object-fit-cover" style="max-height: 260px;">
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center p-4 p-md-5 text-white" style="background: linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.65));">
            <div>
                <span class="badge bg-primary text-white mb-2 px-3 py-1 rounded-pill fw-semibold text-uppercase fs-7 shadow-sm">🌟 Luxury Resort Portal</span>
                <h1 class="h3 fw-bold mb-1">Find Your Next Getaway at Rift Haven</h1>
                <p class="small text-light opacity-90 mb-0">Manage all your luxury resort and hotel guest bookings seamlessly in one unified dashboard.</p>
            </div>
        </div>
    </div>

    <!-- Dashboard Summary Cards (KPI Metrics) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm kpi-card p-3 border-start border-4 border-primary">
                <div class="text-muted small fw-semibold text-uppercase">Total Bookings</div>
                <div class="h3 fw-bold text-dark mb-0 mt-1"><?= number_format($kpi['total_bookings'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm kpi-card p-3 border-start border-4 border-success">
                <div class="text-muted small fw-semibold text-uppercase">Confirmed Stays</div>
                <div class="h3 fw-bold text-success mb-0 mt-1"><?= number_format($kpi['confirmed_count'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm kpi-card p-3 border-start border-4 border-warning">
                <div class="text-muted small fw-semibold text-uppercase">Pending Requests</div>
                <div class="h3 fw-bold text-warning mb-0 mt-1"><?= number_format($kpi['pending_count'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm kpi-card p-3 border-start border-4 border-info">
                <div class="text-muted small fw-semibold text-uppercase">Est. Revenue</div>
                <div class="h4 fw-bold text-primary mb-0 mt-1">₱<?= number_format($kpi['total_revenue'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 p-3" style="background: rgba(255,255,255,0.95);">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <input type="text" name="search" class="form-control" placeholder="🔍 Search guest name, destination, or hotel..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-7 col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Confirmed" <?= $status_filter === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-5 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">Filter</button>
                <?php if ($search !== '' || $status_filter !== ''): ?>
                    <a href="index.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Reservations Table -->
    <div class="card border-0 custom-card-table">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0 mobile-card-table">
                <thead class="table-dark text-uppercase fs-7">
                    <tr>
                        <th class="py-3 ps-3">#</th>
                        <th class="py-3">Guest Name</th>
                        <th class="py-3">Destination & Hotel</th>
                        <th class="py-3">Guests & Rooms</th>
                        <th class="py-3">Check-In / Out</th>
                        <th class="py-3">Total Cost</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = mysqli_query($conn, $sql);
                    $counter = 1;
                    if (mysqli_num_rows($result) === 0):
                    ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No reservations found matching your search.</td>
                        </tr>
                    <?php
                    endif;
                    while ($row = mysqli_fetch_assoc($result)) {
                        $statusColor = 'bg-secondary';
                        if ($row['status'] == 'Confirmed') $statusColor = 'bg-success-subtle text-success-emphasis border border-success-subtle';
                        elseif ($row['status'] == 'Pending') $statusColor = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                        elseif ($row['status'] == 'Cancelled') $statusColor = 'bg-danger-subtle text-danger-emphasis border border-danger-subtle';

                        // Calculate Nights for display
                        $d1 = new DateTime($row['check_in_date']);
                        $d2 = new DateTime($row['check_out_date']);
                        $nights = max(1, $d1->diff($d2)->days);
                        $total_price = number_format($row['total_price'] ?? 0, 2);

                        echo "<tr>
                            <td class='ps-3' data-label='#'><strong>{$counter}</strong></td>
                            <td data-label='Guest Name' class='fw-semibold text-dark'>{$row['guest_name']}</td>
                            <td data-label='Destination'>
                                <div>
                                    <span class='fw-semibold text-dark'>{$row['destination']}</span><br>
                                    <span class='text-primary small fw-medium'>🏨 {$row['hotel_name']}</span>
                                </div>
                            </td>
                            <td data-label='Guests & Rooms'>
                                <div class='small text-muted'>
                                    Adults: <strong class='text-dark'>{$row['adults']}</strong> | Children: <strong class='text-dark'>{$row['children']}</strong><br>
                                    Rooms: <strong class='text-dark'>{$row['rooms']}</strong>
                                </div>
                            </td>
                            <td data-label='Dates'>
                                <div class='small text-secondary'>
                                    In: <span class='fw-medium text-dark'>{$row['check_in_date']}</span><br>
                                    Out: <span class='fw-medium text-dark'>{$row['check_out_date']}</span>
                                    <span class='badge bg-light text-dark border ms-1'>{$nights}N</span>
                                </div>
                            </td>
                            <td data-label='Total Cost'>
                                <span class='fw-bold text-success'>₱{$total_price}</span>
                            </td>
                            <td data-label='Status'><span class='badge {$statusColor} px-2 py-1'>{$row['status']}</span></td>
                            <td class='text-end pe-3' data-label=''>
                                <div class='d-inline-flex gap-1'>
                                    <a href='update.php?id={$row['id']}' class='btn btn-outline-warning btn-sm px-2 py-1'>Edit</a>
                                    <a href='delete.php?id={$row['id']}' class='btn btn-outline-danger btn-sm px-2 py-1' onclick='return confirm(\"Delete this record?\")'>Delete</a>
                                </div>
                            </td>
                        </tr>";
                        $counter++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- About Modal -->
<div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="aboutModalLabel">🏨 About Rift Haven</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <img src="RiftHaven.png" alt="Logo" height="80" width="80" class="rounded-circle bg-light p-1 shadow-sm mb-3 object-fit-cover border border-primary">
                <h4 class="fw-bold text-dark mb-1">Rift Haven Room Reservation System</h4>
                <p class="small mb-4 about-subtitle">A modern luxury resort and hotel booking management web application.</p>
                
                <div class="card border-0 p-3 mb-3 text-start shadow-sm">
                    <h6 class="fw-bold text-primary mb-2">👨‍💻 Developer Information</h6>
                    <p class="mb-0 small text-dark"><strong>Web Developer:</strong> Alemar Y. Bantilan 3ANT</p>
                </div>

                <div class="card border-0 p-3 text-start shadow-sm">
                    <h6 class="fw-bold text-primary mb-2">🛠️ Tech Stack</h6>
                    <ul class="list-unstyled small mb-0 text-dark">
                        <li class="mb-1">• <strong>Backend & DB:</strong> PHP & MySQL</li>
                        <li class="mb-1">• <strong>Frontend:</strong> HTML5, CSS3, JavaScript</li>
                        <li class="mb-1">• <strong>UI Framework:</strong> Bootstrap 5 & Glassmorphism</li>
                        <li>• <strong>Hosting:</strong> InfinityFree Cloud Platform</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-secondary btn-sm px-4 rounded-pill" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function updateDashboardClock() {
        const now = new Date();
        const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        const clockEl = document.getElementById('dashboard-clock');
        if(clockEl) {
            clockEl.innerText = now.toLocaleDateString('en-US', options);
        }
    }
    setInterval(updateDashboardClock, 1000);
    updateDashboardClock();
</script>

</body>
</html>