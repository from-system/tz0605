<?php
// Database connection
include("conekt.php");
session_start(); // Start session to manage user state

// Initialize variables
$sponsor_id = null;
$profile_picture = '';

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Basic Information
    $full_name = mysqli_real_escape_string($conn_users, $_POST['full_name']);
    $gender = mysqli_real_escape_string($conn_users, $_POST['gender']);
    $marital_status = mysqli_real_escape_string($conn_users, $_POST['marital_status']);
    $email = mysqli_real_escape_string($conn_users, $_POST['email']);
    $contact = mysqli_real_escape_string($conn_users, $_POST['contact']);
    
    // Demographic Information
    $region_of_residency = mysqli_real_escape_string($conn_users, $_POST['region_of_residency']);
    $district_of_residency = mysqli_real_escape_string($conn_users, $_POST['district_of_residency']);
    $ward = mysqli_real_escape_string($conn_users, $_POST['ward']);
    $village = mysqli_real_escape_string($conn_users, $_POST['village']);
    $street = mysqli_real_escape_string($conn_users, $_POST['street']);
    $postcode = mysqli_real_escape_string($conn_users, $_POST['postcode']);
    $house_no = mysqli_real_escape_string($conn_users, $_POST['house_no']);
    $nida = mysqli_real_escape_string($conn_users, $_POST['nida']);
    
    // Next of Kin
    $guarantor_name = mysqli_real_escape_string($conn_users, $_POST['guarantor_name']);
    $guarantor_location = mysqli_real_escape_string($conn_users, $_POST['guarantor_location']);
    $guarantor_contact = mysqli_real_escape_string($conn_users, $_POST['guarantor_contact']);
    
    // Profile Picture Upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/'; // Directory to save uploaded files
        $file_name = basename($_FILES['profile_picture']['name']);
        $profile_picture = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($profile_picture, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowed_types = array('jpg', 'jpeg', 'gif', 'png');

        // Check if file type is allowed and size is appropriate
        if (in_array($file_type, $allowed_types) && $_FILES['profile_picture']['size'] <= 2000000) { // Max 2MB
            // Move the uploaded file to the specified directory
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture)) {
                echo "Error uploading file.";
                exit();
            }
        } else {
            echo "Error: Only JPG, GIF, and PNG files are allowed, and the file size must be less than 2MB.";
            exit();
        }
    }

    // Insert into sponsors table using prepared statements
    $stmt = $conn_users->prepare("INSERT INTO sponsor_1 (full_name, gender, marital_status, email, contact, region_of_residency, district_of_residency, ward, village, street, postcode, house_no, nida, guarantor_name, guarantor_location, guarantor_contact, profile_picture) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssssssss", $full_name, $gender, $marital_status, $email, $contact, $region_of_residency, $district_of_residency, $ward, $village, $street, $postcode, $house_no, $nida, $guarantor_name, $guarantor_location, $guarantor_contact, $profile_picture);
    
    if ($stmt->execute()) {
        $sponsor_id = $stmt->insert_id; // Get the sponsor ID
        $_SESSION['sponsor_id'] = $sponsor_id; // Store sponsor_id in session
        echo "<p>Registration successful. Please continue to the payment method.</p>";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Handle payment method submission
if (isset($_POST['submit_payment']) && isset($_SESSION['sponsor_id'])) {
    $method = mysqli_real_escape_string($conn_users, $_POST['method']);
    $account_number = mysqli_real_escape_string($conn_users, $_POST['account_number']);
    $account_holder_name = mysqli_real_escape_string($conn_users, $_POST['account_holder_name']);
    $amount = $_POST['amount'];

    // Prepare and bind
    $stmt = $conn_users->prepare("INSERT INTO payment_methods (sponsor_id, method, account_number, account_holder_name, amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssd", $_SESSION['sponsor_id'], $method, $account_number, $account_holder_name, $amount);
    
    if ($stmt->execute()) {
        $success_message = "Payment method and amount have been successfully submitted.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="sponsor_child.css"> <!-- Link to your CSS file -->
    <title>Sponsor Registration</title>
    <!-- <style>
        /* Style for the profile picture */
        .profile-pic {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 100px;
            height: 100px;
            border-radius: 50%; /* Makes the image circular */
            overflow: hidden; /* Ensures overflow is hidden */
        }
        .profile-pic img {
            width: 100%; /* Makes the image responsive */
            height: auto; /* Maintains aspect ratio */
        }
    </style> -->
</head>
<body>
    <h1>Sponsor Registration</h1>

    <?php if ($sponsor_id): ?>
        <div class="profile-pic">
            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
        </div>
    <?php endif; ?> 
    <h3>Basic information of Sponsor</h3>
<div class="registration">
    <form action="" method="post" enctype="multipart/form-data">
        <!-- Registration Fields -->
         <div class="name">
        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="full_name" required>
    </div>
    <div class="status1">
    <div class="gender">
        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
    </div>
    <div class="status">
        <label for="marital_status">Marital Status:</label>
        <select id="marital_status" name="marital_status" required>
            <option value="Single">Single</option>
            <option value="Married">Married</option>
            <option value="Divorced">Divorced</option>
            <option value="Widowed">Widowed</option>
        </select>
    </div>
</div>
<div class="contact1">
    <div class="email">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div class="contact">
        <label for="contact">Contact Number:</label>
        <input type="text" id="contact" name="contact" required>
    </div>
</div>
<h3>Demographic Information</h3>
<div class="location1">
    <div class="region">
        <label for="region_of_residency">Region of Residency:</label>
        <input type="text" id="region_of_residency" name="region_of_residency" required>
    </div>
    <div class="district">
        <label for="district_of_residency">District of Residency:</label>
        <input type="text" id="district_of_residency" name="district_of_residency" required>
    </div>
</div>
<div class="location2">
    <div class="ward">
        <label for="ward">Ward:</label>
        <input type="text" id="ward" name="ward" required>
    </div>
    <div class="village">
        <label for="village">Village:</label>
        <input type="text" id="village" name="village" required>
    </div>
    <div class="street">
        <label for="street">Street:</label>
        <input type="text" id="street" name="street" required>
    </div>
</div>
<div class="house1">
    <div class="postcode">
        <label for="postcode">Postcode:</label>
        <input type="text" id="postcode" name="postcode" required>
    </div>
    <div class="house">
        <label for="house_no">House Number:</label>
        <input type="text" id="house_no" name="house_no" required>
    </div>
</div>
    <div class="nida">
        <label for="nida">NIDA:</label>
        <input type="text" id="nida" name="nida" required>
    </div>
    <h3>Guarantor Information</h3>
    <div class="guarantor1">
    <div class="guarantor">
        <label for="guarantor_name">Next of Kin Name:</label>
        <input type="text" id="guarantor_name" name="guarantor_name" required>
    </div>
    <div class="location">
        <label for="guarantor_location">Next of Kin Location:</label>
        <input type="text" id="guarantor_location" name="guarantor_location" required>
    </div>
    <div class="gcontact">
        <label for="guarantor_contact">Next of Kin Contact:</label>
        <input type="text" id="guarantor_contact" name="guarantor_contact" required>
    </div>
</div>
    <div class="picture">
        <label for="profile_picture">Profile Picture:</label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required>
    </div>
        <button type="submit" name="register" class="button">Register</button>
    </form>
</div>
     <?php if ($sponsor_id): ?> 
     <div class="credentials">
        <h2>Payment Method</h2>
        <form action="" method="post">
            <div class="payment">
            <label for="method">Payment Method:</label>
            <select id="method" name="method" required>
                <option value="Credit Card">Credit Card</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Mobile Payment">Mobile Payment</option>
            </select>
        </div>
        <div class="account">
            <label for="account_number">Account Number:</label>
            <input type="text" id="account_number" name="account_number" required>
        </div>
        <div class="accname">
            <label for="account_holder_name">Account Holder Name:</label>
            <input type="text" id="account_holder_name" name="account_holder_name" required>
        </div>
        <div class="amount">
            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" required>
        </div>
        
            <button type="submit" name="submit_payment" class="button2">Submit Payment Method</button>
        
        </form>
    </div>
     <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <p><?php echo $success_message; ?></p>
    <?php elseif (isset($error_message)): ?>
        <p><?php echo $error_message; ?></p>
    <?php endif; ?> 
</body>
</html>
