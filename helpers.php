<?php
// Allowed email domains (can remove this if declared globally in config.php)
// $allowed_domains = ['gmail.com', 'gordoncollege.edu.ph'];

// Helper: Check allowed email domain (case-insensitive)
if (!function_exists('isAllowedDomain')) {
    function isAllowedDomain(string $email, array $allowed_domains): bool
    {
        $domain = strtolower(substr(strrchr($email, "@"), 1));
        return in_array($domain, array_map('strtolower', $allowed_domains));
    }
}

// Sanitize and truncate long text
if (!function_exists('truncate')) {
    function truncate($text, $maxChars = 50)
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); // Prevent XSS
        if (strlen($text) > $maxChars) {
            return substr($text, 0, $maxChars) . '...';
        }
        return $text;
    }
}

// Redirect users based on their role or super admin email
if (!function_exists('redirectUserByRole')) {
    function redirectUserByRole(array $user)
    {
        $role = strtolower($user['role'] ?? 'user');
        $email = strtolower($user['email'] ?? '');
        $superAdminEmail = strtolower(SUPER_ADMIN_EMAIL);

        if ($email === $superAdminEmail || $role === 'super_admin') {
            header("Location: super_admin/super_admin_dashboard.php");
            exit();
        }

        // Default user redirection
        header("Location: user/user-dashboard.php");
        exit();
    }
}

// Check if current session belongs to the Super Admin
if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin()
    {
        return isset($_SESSION['email']) && strtolower($_SESSION['email']) === strtolower(SUPER_ADMIN_EMAIL);
    }
}
