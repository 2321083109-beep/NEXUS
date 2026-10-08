<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');
$pdo = db();
$tot = [
    'Estudiantes' => (int)$pdo->query('SELECT COUNT(*) FROM estudiantes')->fetchColumn(),
    'Docentes' => (int)$pdo->query('SELECT COUNT(*) FROM docentes')->fetchColumn(),
    'Materias' => (int)$pdo->query('SELECT COUNT(*) FROM materias')->fetchColumn(),
    'Inscripciones' => (int)$pdo->query('SELECT COUNT(*) FROM inscripciones')->fetchColumn(),
];
$ultimos = $pdo->query("SELECT u.nombre, e.matricula, e.grado, e.grupo FROM estudiantes e JOIN usuarios u ON u.id=e.usuario_id ORDER BY e.id DESC LIMIT 5")->fetchAll();
$pageTitle = 'Panel de administrador';
require __DIR__ . '/../includes/header.php';
?>
<div class="stats">
  <?php foreach ($tot as $label => $n): ?>
    <div class="stat"><b><?= $n ?></b><?= e($label) ?></div>
  <?php endforeach; ?>
</div>
<div class="card">
  <h3>Últimos estudiantes registrados</h3>
  <table>
    <tr><th>Nombre</th><th>Matrícula</th><th>Grado</th><th>Grupo</th></tr>
    <?php foreach ($ultimos as $r): ?>
      <tr><td><?= e($r['nombre']) ?></td><td><?= e($r['matricula']) ?></td><td><?= e($r['grado']) ?></td><td><?= e($r['grupo']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$ultimos): ?><tr><td colspan="4">Aún no hay estudiantes.</td></tr><?php endif; ?>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
