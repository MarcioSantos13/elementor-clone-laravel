<?php
try {
    $db = new PDO('sqlite:database/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $result = $db->query('SELECT id, name, email, password FROM users');
    $users = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        echo "ID: {$u['id']}\n";
        echo "Name: {$u['name']}\n";
        echo "Email: {$u['email']}\n";
        echo "Password hash: {$u['password']}\n";
        echo "Hash prefix: " . substr($u['password'], 0, 7) . "\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
