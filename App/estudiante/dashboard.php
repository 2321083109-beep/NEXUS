<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('estudiante');
$pdo = db();

$st = $pdo->prepare('SELECT e.id, e.matricula, e.grado, e.grupo, u.email FROM estudiantes e JOIN usuarios u ON u.id = e.usuario_id WHERE u.id = ?');
$st->execute([$_SESSION['user']['id']]);
$perfil = $st->fetch();

$cal = [];
if ($perfil) {
    $st = $pdo->prepare('SELECT m.clave, m.nombre, ud.nombre AS docente, i.parcial1, i.parcial2, i.parcial3
        FROM inscripciones i
        JOIN materias m ON m.id = i.materia_id
        LEFT JOIN docentes d ON d.id = m.docente_id
        LEFT JOIN usuarios ud ON ud.id = d.usuario_id
        WHERE i.estudiante_id = ? ORDER BY m.nombre');
    $st->execute([$perfil['id']]);
    $cal = $st->fetchAll();
}

function promedio(array $r): ?float
{
    $v = array_filter([$r['parcial1'], $r['parcial2'], $r['parcial3']], fn($x) => $x !== null);
    return $v ? array_sum($v) / count($v) : null;
}

$pageTitle = 'Panel del estudiante';
require __DIR__ . '/../includes/header.php';
?>
<?php if ($perfil): ?>
<div class="card">
  <b>Matrícula:</b> <?= e($perfil['matricula']) ?> &nbsp;|&nbsp;
  <b>Grado:</b> <?= e($perfil['grado']) ?> &nbsp;|&nbsp;
  <b>Grupo:</b> <?= e($perfil['grupo']) ?> &nbsp;|&nbsp;
  <b>Correo:</b> <?= e($perfil['email']) ?>
</div>
<?php endif; ?>
<div class="card">
  <h3>Mis calificaciones</h3>
  <table>
    <tr><th>Clave</th><th>Materia</th><th>Docente</th><th>P1</th><th>P2</th><th>P3</th><th>Promedio</th></tr>
    <?php foreach ($cal as $r): $p = promedio($r); ?>
      <tr>
        <td><?= e($r['clave']) ?></td><td><?= e($r['nombre']) ?></td><td><?= e($r['docente'] ?? '—') ?></td>
        <td><?= $r['parcial1'] !== null ? e($r['parcial1']) : '—' ?></td>
        <td><?= $r['parcial2'] !== null ? e($r['parcial2']) : '—' ?></td>
        <td><?= $r['parcial3'] !== null ? e($r['parcial3']) : '—' ?></td>
        <td class="<?= $p === null ? '' : ($p >= 6 ? 'aprobado' : 'reprobado') ?>"><?= $p === null ? '—' : number_format($p, 2) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$cal): ?><tr><td colspan="7">No tienes materias inscritas.</td></tr><?php endif; ?>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
