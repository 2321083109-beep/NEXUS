<?php
$u = $_SESSION['user'];
$menus = [
    'admin' => [
        '/admin/dashboard.php' => 'Inicio',
        '/admin/estudiantes.php' => 'Estudiantes',
        '/admin/docentes.php' => 'Docentes',
        '/admin/materias.php' => 'Materias',
        '/admin/inscripciones.php' => 'Inscripciones',
    ],
    'docente' => [
        '/docente/dashboard.php' => 'Mis materias',
    ],
    'estudiante' => [
        '/estudiante/dashboard.php' => 'Mis calificaciones',
    ],
];
$actual = $_SERVER['SCRIPT_NAME'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Control Escolar') ?> | Control Escolar</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<aside class="sidebar">
  <h1>🎓 Control Escolar</h1>
  <p class="user"><?= e($u['nombre']) ?><br><small><?= e(ucfirst($u['rol'])) ?></small></p>
  <nav>
    <?php foreach ($menus[$u['rol']] as $url => $label): ?>
      <a href="<?= BASE_URL . $url ?>" class="<?= str_ends_with($actual, $url) ? 'active' : '' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>/logout.php" class="salir">Cerrar sesión</a>
  </nav>
</aside>
<main class="content">
<h2><?= e($pageTitle ?? '') ?></h2>
<?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert <?= $f['t'] === 'ok' ? 'ok' : 'err' ?>"><?= e($f['m']) ?></div>
<?php endif; ?>
