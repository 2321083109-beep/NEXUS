<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const DB_HOST = 'localhost';
const DB_NAME = 'control_escolar';
const DB_USER = 'root';
const DB_PASS = '';
const BASE_URL = '/control_escolar';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    return $pdo;
}

function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Token CSRF inválido. Recarga la página.');
    }
}

function flash(string $tipo, string $msg): void
{
    $_SESSION['flash'] = ['t' => $tipo, 'm' => $msg];
}

function require_role(string $rol): void
{
    if (empty($_SESSION['user']) || $_SESSION['user']['rol'] !== $rol) {
        redirect('/index.php');
    }
}

function nota($v): ?float
{
    $v = trim((string)$v);
    if ($v === '') {
        return null;
    }
    $n = (float)str_replace(',', '.', $v);
    return max(0.0, min(10.0, $n));
}
