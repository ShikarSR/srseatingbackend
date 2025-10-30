<?php
// validation.php

function validateForm($data) {
    if (empty(trim($data['name'] ?? ''))) {
        return "Name is required";
    }
    if (empty(trim($data['companyname'] ?? ''))) {
        return "Company name is required";
    }
    if (empty(trim($data['email'] ?? '')) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return "Valid email is required";
    }
    if (empty(trim($data['phone'] ?? '')) || substr($data['phone'], 0, 1) !== '+') {
        return "Valid phone with country code starting with + is required";
    }
    return true;
}

function validateOtp($otp) {
    if (empty(trim($otp)) || !preg_match('/^\d{6}$/', $otp)) {
        return "OTP must be 6 digits";
    }
    return true;
}
?>
