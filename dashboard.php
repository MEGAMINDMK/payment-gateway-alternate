
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Connect to SQLite database
try {
    $db = new PDO('sqlite:database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch email and contact_number based on username stored in session
    $stmt = $db->prepare("SELECT email, contact_number FROM users WHERE username = :username");
    $stmt->bindParam(':username', $_SESSION['username']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $email = $user['email'];
        $contact_number = $user['contact_number'];
    } else {
        echo "User not found in database.";
        exit; // Handle appropriately, such as redirecting or error message
    }

    // Handle file upload and form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['receipt'])) {
        $username = $_SESSION['username']; // Assuming username is stored in session
        $address = $_POST['address'];
        $item = $_POST['item'];
        $receipt = $_FILES['receipt'];

        // Handle file upload logic here
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($username . "." . $contact_number . "." . $email . "." . $item . "." . $address . "." . pathinfo($receipt['name'], PATHINFO_EXTENSION));

        if (move_uploaded_file($receipt['tmp_name'], $target_file)) {
            echo "<script>alert('Successfully purchased! Your item will be delivered in 2 to 3 working days.');</script>";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            max-width: 300px;
            margin: auto;
            text-align: center;
            font-family: Arial;
        }

        .price {
            color: grey;
            font-size: 22px;
        }

        .card button {
            border: none;
            outline: 0;
            padding: 12px;
            color: white;
            background-color: #000;
            text-align: center;
            cursor: pointer;
            width: 100%;
            font-size: 18px;
        }

        .card button:hover {
            opacity: 0.7;
        }

        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: animatetop 0.4s;
        }

        @keyframes animatetop {
            from {top: -300px; opacity: 0}
            to {top: 0; opacity: 1}
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Next button styles */
        #nextBtn, #uploadReceiptBtn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 18px;
            margin-top: 20px;
        }

        #nextBtn:hover, #uploadReceiptBtn:hover {
            opacity: 0.8;
        }

        /* Form styles */
        form {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        label {
            margin-top: 10px;
        }

        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <a href="logout.php">Logout</a>

    <div class="card">
        <img src="https://hips.hearstapps.com/vader-prod.s3.amazonaws.com/1692714037-1653486854-lightweight-jean-todd-s-1653485479.jpg" alt="Denim Jeans" style="width:100%">
        <h1>Tailored Jeans</h1>
        <p class="price">Rs 1000.00</p>
        <p>Some text about the jeans. Super slim and comfy lorem ipsum lorem jeansum. Lorem jeamsun denim lorem jeansum.</p>
        <p><button id="addToCartBtn">Add to Cart</button></p>
    </div>

    <!-- The First Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p>Item added to cart!</p>
            <h1>Pay with QR CODE / IBAN</h1>
			<p>Scan the Q code to pay from your Easy Paisa / Jazz Cash app or Enter IBAN from any of your banking app to pay</p>
            <img src="qr.jpeg" width="20%"><img src="jazzqr.jpg" width="20%"><br>
            <strong>IBAN:</strong> PK15TMFB0000000047596124<br><br><hr>
			<strong>Once done click next to proceed further..!</strong>
            <button id="nextBtn">Next</button>
        </div>
    </div>

    <!-- The Second Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h1>Upload Receipt</h1>
              <form action="" method="post" enctype="multipart/form-data">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required readonly><br>
                <label for="contact">Contact:</label>
                <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($contact_number); ?>" required readonly><br>
				<label for="email">Email:</label>
                <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required readonly><br>
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" required><br>
                <label for="item">Item Name:</label>
                <input type="text" id="item" name="item" value="Tailored Jeans" required readonly><br>
                <label for="receipt">Upload Receipt:</label>
                <input type="file" id="receipt" name="receipt" required><br>
                <button type="submit" id="uploadReceiptBtn">Upload Receipt</button>
            </form>
        </div>
    </div>

    <script>
        // Get the modals
        var modal = document.getElementById("myModal");
        var uploadModal = document.getElementById("uploadModal");

        // Get the button that opens the modal
        var btn = document.getElementById("addToCartBtn");

        // Get the <span> element that closes the modal
        var closeButtons = document.getElementsByClassName("close");

        // Get the "Next" button
        var nextBtn = document.getElementById("nextBtn");

        // When the user clicks the button, open the modal 
        btn.onclick = function() {
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        for (var i = 0; i < closeButtons.length; i++) {
            closeButtons[i].onclick = function() {
                modal.style.display = "none";
                uploadModal.style.display = "none";
            }
        }

        // When the user clicks on "Next" button, open the upload modal and close the current modal
        nextBtn.onclick = function() {
            modal.style.display = "none";
            uploadModal.style.display = "block";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            } else if (event.target == uploadModal) {
                uploadModal.style.display = "none";
            }
        }
    </script>
</body>
</html>
