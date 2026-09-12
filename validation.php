<?php

function validateRequired(string $value, string $label): ?string
{
    return trim($value) === ''
        ? "$label is required."
        : null;
}


function validateName(string $value, string $label): ?string
{
    $value = trim($value);

    if ($value === '') {
        return "$label is required.";
    }

    if (strlen($value) < 2) {
        return "$label must be at least 2 characters.";
    }

    if (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]+$/", $value)) {
        return "$label contains invalid characters.";
    }

    return null;
}


function validateEmailFormat(string $value): ?string
{
    if ($value === '') {
        return "E-mail is required.";
    }

    return filter_var($value, FILTER_VALIDATE_EMAIL)
        ? null
        : "Enter a valid email address.";
}


function validatePassword(string $value): ?string
{
    if ($value === '') {
        return "Password is required.";
    }

    if (strlen($value) < 8) {
        return "Password must be at least 8 characters.";
    }

    return null;
}


function validateConfirmPassword(
    string $password,
    string $confirmPassword
): ?string {

    if ($confirmPassword === '') {
        return "Please confirm your password.";
    }

    if ($password !== $confirmPassword) {
        return "Passwords do not match.";
    }

    return null;
}


function validateUserInput(array $post): array
{
    $first_name = trim($post['first_name'] ?? '');
    $last_name = trim($post['last_name'] ?? '');
    $email = trim($post['email'] ?? '');
    $password = $post['password'] ?? '';
    $confirm_password = $post['confirm_password'] ?? '';

    $errors = array_filter([
        validateName($first_name, 'First Name'),
        validateName($last_name, 'Last Name'),
        validateEmailFormat($email),
        validatePassword($password),
        validateConfirmPassword(
            $password,
            $confirm_password
        )
    ]);

    $errors = array_values($errors);

    return [
        'errors' => $errors,

        'data' => [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password' => $password
        ]
    ];
}