<?php

session_start();
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Account - Fitness Buddy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .header {
            background-color: #555;
            color: white;
            padding: 10px;
            font-weight: bold;
        }

        .subheader {
            background-color: #ddd;
            padding: 10px;
            border-bottom: 1px solid #bbb;
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ccc;
            background-color: white;
        }

        .form-section {
            padding: 20px;
            text-align: center;
        }

        .tier-section {
            display: flex;
            border-top: 1px solid #ccc;
        }

        .tier-option {
            flex: 1;
            padding: 20px;
            border-right: 1px solid #ccc;
        }

        .tier-option:last-child {
            border-right: none;
        }

        .tier-box {
            border: 1px solid #999;
            padding: 15px;
            margin: 10px auto;
            max-width: 250px;
            text-align: center;
        }

        .tier-features {
            text-align: left;
            list-style-type: none;
            padding-left: 0;
        }

        .tier-features li {
            margin-bottom: 5px;
            padding-left: 20px;
            position: relative;
        }

        .tier-features li:before {
            content: "•";
            position: absolute;
            left: 5px;
        }

        .footer-section {
            padding: 15px;
            text-align: center;
            border-top: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        .create-button {
            background-color: #333;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <div class="header">
            Fitness Buddy
        </div>
        <div class="subheader">
            Create Your Account
        </div>

        <form id="registerForm">
		<input type="hidden" id="csrfToken" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>" >
            <div class="form-section">
                <div style="max-width: 400px; margin: 0 auto;">
                    <div class="mb-3">
                        <label for="email" class="form-label d-block text-center">Email Address:</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label d-block text-center">Password</label>
                        <input type="password" class="form-control" id="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label d-block text-center">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" required>
                    </div>
                </div>
            </div>

            <div class="subheader">
                Choose Your Membership Plan:
            </div>

            <div class="tier-section">
                <div class="tier-option">
                    <div class="tier-box">
                        <h4>Free Tier</h4>
                        <ul class="tier-features">
                            <li>Basic profile creation</li>
                            <li>Limited matching</li>
                            <li>Basic consistency tracker</li>
                            <li>Forum access (view only)</li>
                        </ul>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="radio" name="membershipTier" id="freeTier" value="free"
                            checked>
                        <label class="form-check-label" for="freeTier">
                            Select Free Tier
                        </label>
                    </div>
                </div>

                <div class="tier-option">
                    <div class="tier-box">
                        <h4>Premium Tier</h4>
                        <ul class="tier-features">
                            <li>All tier 1 features</li>
                            <li>Advanced profile creation</li>
                            <li>Advanced matching filters</li>
                            <li>Ad-free experience</li>
                            <li>Full forum participation</li>
                        </ul>
                    </div>
                    <div class="price mb-2">
                        $9.99/month or $99.99 annually
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="membershipTier" id="premiumTier"
                            value="premium">
                        <label class="form-check-label" for="premiumTier">
                            Select Premium Tier
                        </label>
                    </div>
                </div>
            </div>

            <div class="footer-section">
                <p>By signing up, you agree to our Terms of Service and Privacy Policy</p>
                <button type="submit" class="create-button">Create Account</button>
                <div class="mt-3">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>
                <div id="registerMessage" class="mt-3"></div>
            </div>
        </form>
    </div>

    <script>
        document.getElementById("registerForm").addEventListener("submit", function (event) {
            event.preventDefault();

            // Password validation
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirmPassword").value;

            if (password !== confirmPassword) {
                document.getElementById("registerMessage").innerHTML =
                    '<div class="alert alert-danger">Passwords do not match!</div>';
                return;
            }

            const email = document.getElementById("email").value;
            const membershipTier = document.querySelector('input[name="membershipTier"]:checked').value;

            // Create a FormData object for sending data as JSON
            const data = {
                email: email,
                password: password,
                membershipTier: membershipTier
            };

            fetch("api_register.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json" // Jag~
					'X-CSRF-Token': document.getElementById('csrfToken').value
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(data => {
                    const messageBox = document.getElementById("registerMessage");
                    if (data.status === "success") {
                        messageBox.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        setTimeout(() => window.location.href = data.redirect || "profileSetup.php", 2000); // Jag~ 
                    } else {
                        messageBox.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    </script>
</body>

</html>