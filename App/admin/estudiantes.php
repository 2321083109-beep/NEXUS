<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'delete') {
            $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND rol = 'estudiante'")->execute([(int)$_POST['usuario_id']]);
            flash('ok', 'Estudiante eliminado.');
        } else {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['password'] ?? '';
            $matricula = trim($_POST['matricula'] ?? '');
            $grado = trim($_POST['grado'] ?? '');
            $grupo = trim($_POST['grupo'] ?? '');
            if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $matricula === '') {
                throw new RuntimeException('Completa nombre, correo válido y matrícula.');
            }
            if ($action === 'create') {
                if (strlen($pass) < 6) {
                    throw new RuntimeException('La contraseña debe tener al menos 6 caracteres.');
                }
                $pdo->beginTransaction();
                $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?,?,?, 'estudiante')")
                    ->execute([$nombre, $email, password_hash($pass, PASSWORD_DEFAULT)]);
                $uid = (int)$pdo->lastInsertId();
                $pdo->prepare('INSERT INTO estudiantes (usuario_id, matricula, grado, grupo) VALUES (?,?,?,?)')
                    ->execute([$uid, $matricula, $grado, $grupo]);
                $pdo->commit();
                flash('ok', 'Estudiante registrado.');
            } elseif ($action === 'update') {
                $uid = (int)$_POST['usuario_id'];
                $pdo->beginTransaction();
                $pdo->prepare('UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?')->execute([$nombre, $email, $uid]);
                if ($pass !== '') {
                    if (strlen($pass) < 6) {
                        throw new RuntimeException('La contraseña debe tener al menos 6 caracteres.');
                    }
                    $pdo->prepare('UPDATE usuarios SET password = ? WHERE id = ?')->execute([password_hash($pass, PASSWORD_DEFAULT), $uid]);
                }
                $pdo->prepare('UPDATE estudiantes SET matricula = ?, grado = ?, grupo = ? WHERE usuario_id = ?')
                    ->execute([$matricula, $grado, $grupo, $uid]);
                $pdo->commit();
                flash('ok', 'Estudiante actualizado.');
            }
        }
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        flash('err', $ex->getCode() === '23000' ? 'El correo o la matrícula ya existen.' : 'Error de base de datos.');
    } catch (RuntimeException $ex) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        flash('err', $ex->getMessage());
    }
    redirect('/admin/estudiantes.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT u.id AS uid, u.nombre, u.email, e.matricula, e.grado, e.grupo FROM estudiantes e JOIN usuarios u ON u.id = e.usuario_id WHERE u.id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}
$lista = $pdo->query('SELECT u.id AS uid, u.nombre, u.email, e.matricula, e.grado, e.grupo FROM estudiantes e JOIN usuarios u ON u.id = e.usuario_id ORDER BY u.nombre')->fetchAll();

$pageTitle = 'Estudiantes';
require __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h3><?= $edit ? 'Editar estudiante' : 'Nuevo estudiante' ?></h3>
  <form method="post" class="grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
    <?php if ($edit): ?><input type="hidden" name="usuario_id" value="<?= (int)$edit['uid'] ?>"><?php endif; ?>
    <div><label>Nombre completo</label><input name="nombre" required value="<?= e($edit['nombre'] ?? '') ?>"></div>
    <div><label>Correo</label><input type="email" name="email" required value="<?= e($edit['email'] ?? '') ?>"></div>
    <div><label>Contraseña <?= $edit ? '(vacío = no cambiar)' : '' ?></label><input type="password" name="password" <?= $edit ? '' : 'required' ?>></div>
    <div><label>Matrícula</label><input name="matricula" required value="<?= e($edit['matricula'] ?? '') ?>"></div>
    <div><label>Grado</label><input name="grado" value="<?= e($edit['grado'] ?? '') ?>"></div>
    <div><label>Grupo</label><input name="grupo" value="<?= e($edit['grupo'] ?? '') ?>"></div>
    <div>
      <button class="btn"><?= $edit ? 'Guardar cambios' : 'Registrar' ?></button>
      <?php if ($edit): ?><a class="btn gris" href="estudiantes.php">Cancelar</a><?php endif; ?>
    </div>
  </form>
</div>
<div class="card">
  <table>
    <tr><th>Matrícula</th><th>Nombre</th><th>Correo</th><th>Grado</th><th>Grupo</th><th>Acciones</th></tr>
    <?php foreach ($lista as $r): ?>
      <tr>
        <td><?= e($r['matricula']) ?></td><td><?= e($r['nombre']) ?></td><td><?= e($r['email']) ?></td>
        <td><?= e($r['grado']) ?></td><td><?= e($r['grupo']) ?></td>
        <td>
          <a class="btn sm" href="?edit=<?= (int)$r['uid'] ?>">Editar</a>
          <form method="post" class="inline" onsubmit="return confirm('¿Eliminar a este estudiante?')">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="usuario_id" value="<?= (int)$r['uid'] ?>">
            <button class="btn rojo sm">Eliminar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$lista): ?><tr><td colspan="6">Sin registros.</td></tr><?php endif; ?>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
