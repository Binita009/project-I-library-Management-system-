<?php
// Validation Class with Regex
class Validation {
    
    // Regex Patterns
    const PATTERNS = [
        'username' => '/^[a-zA-Z0-9_]{3,20}$/',
        'password' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{6,}$/',
        'email' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        'name' => '/^[a-zA-Z\s]{2,50}$/',
        'isbn' => '/^(?:\d{3}-)?\d{1,5}-\d{1,7}-\d{1,7}-\d{1,7}$|^\d{10}$|^\d{13}$/',
        'phone' => '/^[6-9]\d{9}$/',
        'number' => '/^\d+$/',
        'date' => '/^\d{4}-\d{2}-\d{2}$/'
    ];
    
    // Validate field with regex
    public static function validate($field, $value, $type) {
        if (empty(trim($value))) {
            return ["error" => "$field is required"];
        }
        
        if (!isset(self::PATTERNS[$type])) {
            return ["error" => "Invalid validation type"];
        }
        
        if (!preg_match(self::PATTERNS[$type], trim($value))) {
            $messages = [
                'username' => "Username must be 3-20 chars (letters, numbers, underscore only)",
                'password' => "Password must be min 6 chars with at least 1 letter and 1 number",
                'email' => "Please enter a valid email address",
                'name' => "Name must be 2-50 letters and spaces only",
                'isbn' => "Please enter a valid ISBN (10 or 13 digits)",
                'phone' => "Please enter a valid 10-digit mobile number",
                'number' => "Please enter a valid number",
                'date' => "Please enter a valid date (YYYY-MM-DD)"
            ];
            return ["error" => $messages[$type] ?? "Invalid format"];
        }
        
        return ["success" => true];
    }
    
    // Sanitize input
    public static function sanitize($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
        return $input;
    }
    
    // Validate book data
    public static function validateBook($data) {
        $errors = [];
        
        // Validate title
        $titleValidation = self::validate('Title', $data['title'], 'name');
        if (isset($titleValidation['error'])) {
            $errors['title'] = $titleValidation['error'];
        }
        
        // Validate author
        $authorValidation = self::validate('Author', $data['author'], 'name');
        if (isset($authorValidation['error'])) {
            $errors['author'] = $authorValidation['error'];
        }
        
        // Validate ISBN
        $isbnValidation = self::validate('ISBN', $data['isbn'], 'isbn');
        if (isset($isbnValidation['error'])) {
            $errors['isbn'] = $isbnValidation['error'];
        }
        
        // Validate copies
        if (!isset($data['copies']) || !preg_match(self::PATTERNS['number'], $data['copies']) || $data['copies'] < 1) {
            $errors['copies'] = "Please enter valid number of copies (minimum 1)";
        }
        
        return $errors;
    }
    
    // Validate user registration
    public static function validateUser($data) {
        $errors = [];
        
        // Validate username
        $userValidation = self::validate('Username', $data['username'], 'username');
        if (isset($userValidation['error'])) {
            $errors['username'] = $userValidation['error'];
        }
        
        // Validate email
        $emailValidation = self::validate('Email', $data['email'], 'email');
        if (isset($emailValidation['error'])) {
            $errors['email'] = $emailValidation['error'];
        }
        
        // Validate password
        if (!isset($data['password']) || strlen($data['password']) < 6) {
            $errors['password'] = "Password must be at least 6 characters";
        }
        
        // Validate name
        $nameValidation = self::validate('Full Name', $data['full_name'], 'name');
        if (isset($nameValidation['error'])) {
            $errors['full_name'] = $nameValidation['error'];
        }
        
        return $errors;
    }
    
    // Validate login
    public static function validateLogin($username, $password) {
        $errors = [];
        
        if (empty($username)) {
            $errors['username'] = "Username is required";
        }
        
        if (empty($password)) {
            $errors['password'] = "Password is required";
        } elseif (strlen($password) < 3) {
            $errors['password'] = "Password must be at least 3 characters";
        }
        
        return $errors;
    }
}
?>