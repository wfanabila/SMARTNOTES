<?php
// ====================================================================
// 1. DATABASE CONNECTION
// ====================================================================
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "smartnotes";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ====================================================================
// 2. SESSION & VALIDATION LOCK
// ====================================================================
session_start();

// Validate active student profile record context
$targetID = isset($_SESSION['studentID']) ? (int)$_SESSION['studentID'] : 1;
$checkUser = $conn->query("SELECT studentID, studentName, studentEmail FROM student WHERE studentID = " . $targetID);

if ($checkUser && $checkUser->num_rows > 0) {
    $userRow = $checkUser->fetch_assoc();
    $studentID = (int)$userRow['studentID'];
    $studentName = $userRow['studentName'];
    $studentEmail = $userRow['studentEmail'];
} else {
    $studentID = 1;
    $studentName = "Wafa Nabila";
    $studentEmail = "wafa@email.com";
}

// Grab target note metadata from URL query parameters
$noteID = isset($_GET['id']) ? (int)$_GET['id'] : 4; 

if ($noteID <= 0) {
    die("Error: Invalid or missing note identifier.");
}

// ====================================================================
// 3. FETCH NOTE DETAIL FOR SUMMARY CARD
// ====================================================================
$note_query = "SELECT title, price FROM notes WHERE noteID = ? LIMIT 1";
$stmt = $conn->prepare($note_query);
$stmt->bind_param("i", $noteID);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$note) {
    die("Error: The requested note resource does not exist.");
}

$price = (float)$note['price'];
$message = "";
$is_success = false;

// ====================================================================
// 4. POST OPERATION HANDLER: RECORD TRANSACTION
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_purchase'])) {
    $paymentMethod = "Credit / Debit Card";
    $paymentStatus = "Completed";

    // Fixed type binder string mismatch count from "sdssii" to "sdssi"
    $insert_query = "INSERT INTO payment (paymentDate, paymentMethod, paymentAmount, paymentStatus, studentID, noteID) 
                     VALUES (CURRENT_TIMESTAMP, ?, ?, ?, ?, ?)";
    
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("sdssi", $paymentMethod, $price, $paymentStatus, $studentID, $noteID);

    if ($insert_stmt->execute()) {
        $is_success = true;
    } else {
        $message = "Payment Error: " . $conn->error;
    }
    $insert_stmt->close();
}

$conn->close();

include_once("sidebar.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link href='https://fonts.googleapis.com/css?family=Inter:wght@400;500;600;700;800&display=swap' rel='stylesheet'>
    <link rel='stylesheet' href="sidebar.css">
    <style>
        /* ── Page Layout Defaults ───────────────────────── */
        body {
            margin: 0;
            background-color: #ffffff;
            font-family: 'Inter', sans-serif;
            color: #000000;
        }

        .main {
            margin-left: 70px; 
            padding: 40px 60px;
            box-sizing: border-box;
            transition: margin-left 0.15s ease;
        }

        .sidebar:hover ~ .main {
            margin-left: 220px;
        }

        .checkout-title {
            font-size: 36px;
            font-weight: 700;
            margin: 0 0 8px 0;
            letter-spacing: -0.02em;
        }

        .checkout-subtitle {
            font-size: 15px;
            color: #000000;
            margin: 0 0 32px 0;
        }

        /* ── Success Banner Message Notification Block ──── */
        .alert-banner {
            background-color: #eefaf2;
            border: 1px solid #16a34a;
            color: #14532d;
            border-radius: 4px;
            padding: 16px 20px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 24px;
            max-width: 1200px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── Grid Container Structural Framework ──────── */
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 40px;
            max-width: 1200px;
        }

        /* ── Left Billing Information Column ───────────── */
        .billing-area {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .section-box {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 32px;
        }

        .section-box h2 {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 24px 0;
            color: #000000;
        }

        .form-row-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row-3col {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 15px;
            font-weight: 600;
            color: #000000;
        }

        .form-input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 12px 16px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
            background-color: #ffffff;
        }

        .payment-method-tab {
            background-color: #f3effc; 
            border: 2px solid #6D3BD7;
            border-radius: 4px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .payment-method-tab svg {
            width: 28px;
            height: 28px;
            fill: #000000;
        }

        .payment-method-tab span {
            font-size: 14px;
            font-weight: 600;
            color: #000000;
        }

        /* ── Right Column Order Summary Widget ────────── */
        .summary-panel {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            height: fit-content;
        }

        .summary-panel h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 20px 0;
        }

        .product-preview-row {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 1px solid #f3f4f6;
            margin-bottom: 20px;
        }

        .file-icon-box {
            width: 48px;
            height: 56px;
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .file-icon-box svg {
            width: 24px;
            height: 24px;
            fill: #4338ca;
        }

        .product-meta-text {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .product-title {
            font-size: 14px;
            font-weight: 600;
            line-height: 1.4;
            color: #000000;
            margin: 0;
        }

        .product-price {
            font-size: 14px;
            font-weight: 600;
            color: #000000;
        }

        .pricing-calc-line {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 24px;
            color: #000000;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 24px;
            padding-top: 4px;
            color: #000000;
        }

        .btn-complete-purchase {
            width: 100%;
            background-color: #6D3BD7;
            color: #ffffff;
            border: none;
            padding: 14px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-complete-purchase:hover {
            background-color: #5a2cc2;
        }

        .btn-complete-purchase svg {
            width: 14px;
            height: 14px;
            fill: currentColor;
        }

        @media (max-width: 960px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="main">
    <h1 class="checkout-title">Checkout</h1>
    <p class="checkout-subtitle">Complete your purchase to gain instant access to premium notes.</p>

    <?php if ($is_success): ?>
        <div class="alert-banner">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            Payment successful! Your order has been processed. Redirecting to your notes shortly...
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "view_note.php?id=<?= $noteID ?>";
            }, 2500); // 2.5 second delay allows them to read the green confirmation alert banner comfortably
        </script>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <p style="color: #dc2626; font-weight: 600; margin-bottom: 20px;"><?= $message ?></p>
    <?php endif; ?>

    <form action="" method="POST" <?php if ($is_success) echo 'style="opacity: 0.5; pointer-events: none;"'; ?>>
        <div class="checkout-grid">
            
            <div class="billing-area">
                
                <div class="section-box">
                    <h2>Customer Information</h2>
                    <div class="form-row-2col">
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" class="form-input" value="<?= htmlspecialchars($studentName) ?>" placeholder="e.g. Wafa Nabila" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" class="form-input" value="<?= htmlspecialchars($studentEmail) ?>" placeholder="name@email.com" required>
                        </div>
                    </div>
                </div>

                <div class="section-box">
                    <h2>Payment Method</h2>
                    
                    <div class="payment-method-tab">
                        <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.1.89 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                        <span>Credit / Debit Card</span>
                    </div>

                    <div class="form-row-2col">
                        <div class="form-group" style="grid-column: span 2;">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" class="form-input" placeholder="0000 0000 0000 0000" maxlength="19" required>
                        </div>
                    </div>
                    
                    <div class="form-row-3col">
                        <div class="form-group">
                            <label for="expire_date">Expire Date</label>
                            <input type="text" id="expire_date" class="form-input" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="password" id="cvv" class="form-input" placeholder="123" maxlength="3" required>
                        </div>
                    </div>
                </div>

            </div>

            <div class="summary-panel">
                <h3>Order Summary</h3>
                
                <div class="product-preview-row">
                    <div class="file-icon-box">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                    </div>
                    <div class="product-meta-text">
                        <h4 class="product-title"><?= htmlspecialchars($note['title']) ?></h4>
                        <div class="product-price">RM<?= number_format($price, 2) ?></div>
                    </div>
                </div>

                <div class="pricing-calc-line">
                    <span>Subtotal</span>
                    <span>RM<?= number_format($price, 2) ?></span>
                </div>

                <div class="total-line">
                    <span>Total</span>
                    <span>RM<?= number_format($price, 2) ?></span>
                </div>

                <button type="submit" name="complete_purchase" class="btn-complete-purchase">
                    <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                    Complete Purchase
                </button>
            </div>

        </div>
    </form>
</div>

</body>
</html>