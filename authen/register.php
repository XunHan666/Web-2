<?php
require_once '../env/config.php';
session_start();

if (isset($_SESSION['account_id'])) { header('Location: ../index.php'); exit(); }

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = mysqli_real_escape_string($db_connect, $_POST['username']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    $full_name = mysqli_real_escape_string($db_connect, $_POST['full_name']);

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (mysqli_num_rows(mysqli_query($db_connect, "SELECT id FROM accounts WHERE username='$username'")) > 0) {
        $error = 'Username already exists.';
    } else {
        $role = ($_POST['role'] == '2') ? 2 : 3;
        $sync_id = null;
        
        $phone = mysqli_real_escape_string($db_connect, $_POST['phone']);
        $email = mysqli_real_escape_string($db_connect, $_POST['email']);
        $address = mysqli_real_escape_string($db_connect, $_POST['address']);
        $dob = mysqli_real_escape_string($db_connect, $_POST['dob']);
        $gender = mysqli_real_escape_string($db_connect, $_POST['gender']);
        
        if ($role == 3) {
            $check_reader = mysqli_query($db_connect, "SELECT id, account_id FROM readers WHERE phone='$phone' OR email='$email' LIMIT 1");
            if (mysqli_num_rows($check_reader) > 0) {
                $r_data = mysqli_fetch_assoc($check_reader);
                if ($r_data['account_id'] !== null) {
                    $error = 'This phone number or email is already linked to an account. Please login or reset your password.';
                } else {
                    $sync_id = $r_data['id']; // We will sync to this profile
                }
            }
        }
        
        if (!$error) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $status = ($role == 2) ? 'inactive' : 'active';
            $ok = mysqli_query($db_connect, "INSERT INTO accounts (username, password, full_name, phone, email, address, dob, gender, role_id, status) VALUES ('$username', '$hash', '$full_name', '$phone', '$email', '$address', '$dob', '$gender', $role, '$status')");
            if ($ok) {
                $account_id = mysqli_insert_id($db_connect);
                if ($role == 2) {
                    // Librarian: create request
                    mysqli_query($db_connect, "INSERT INTO requests (type, account_id, target_id, status) VALUES ('librarian_registration', $account_id, $account_id, 'pending')");
                    $success = 'Account created successfully! Please wait for Admin approval.';
                } else {
                    // Reader: Insert or Sync into readers table
                    if ($sync_id) {
                        mysqli_query($db_connect, "UPDATE readers SET account_id=$account_id WHERE id=$sync_id");
                        $success = 'Account created and linked to your existing library profile! <a href="login.php">Login now</a>.';
                    } else {
                        mysqli_query($db_connect, "INSERT INTO readers (name, phone, email, address, dob, gender, status, account_id) VALUES ('$full_name', '$phone', '$email', '$address', '$dob', '$gender', 'active', $account_id)");
                        $success = 'Account created successfully! <a href="login.php">Login now</a>.';
                    }
                }
            } else {
                $error = 'Registration failed. Phone or Email might already be taken.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — LibraryOS</title>
    <link rel="stylesheet" href="../css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <span class="logo-text">LibraryOS</span>
            <p>Create a new account.</p>
        </div>

        <?php if ($error):   ?><div class="auth-alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success && !$error): ?><div class="auth-alert-success"><?php echo $success; ?></div><?php endif; ?>

        <?php if (!$success || $error): ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="role">Account Type</label>
                <select id="role" name="role" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 0.95rem; margin-top: 0.5rem; outline: none;">
                    <option value="3">Reader (Can borrow books)</option>
                    <option value="2">Librarian (Requires Admin Approval)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="Nguyen Van A" required autofocus>
            </div>
            
            <div id="reader_fields">
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" placeholder="0901234567" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="email@example.com" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" placeholder="123 Le Loi, HCMC" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 0.95rem; margin-top: 0.5rem; outline: none; box-sizing: border-box;">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 0.95rem; margin-top: 0.5rem; outline: none; box-sizing: border-box;">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Min. 6 chars" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat" required>
                </div>
            </div>
            <button type="submit" class="btn-submit">Create Account</button>
        </form>
        <?php endif; ?>

        <div class="auth-footer">Already have an account? <a href="login.php">Sign In</a></div>
    </div>
    
</body>
</html>