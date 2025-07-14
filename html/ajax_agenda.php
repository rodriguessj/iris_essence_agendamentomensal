<?php
require_once 'conexao.php';

// Horários fixos
function fetchHorariosDisponiveis($pdo, $data, $ignorarHora = null) {
    $grade = ["08:00","09:00","10:00","11:00","13:00","14:00","15:00","16:00","17:00"];
    $sql = "SELECT hora FROM agendamentos WHERE data = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data]);
    $ocupados = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $ocupados = array_map(fn($h) => substr(trim($h), 0, 5), $ocupados);
    if ($ignorarHora !== null) {
        $ocupados = array_filter($ocupados, fn($h) => $h !== $ignorarHora);
    }
    return array_values(array_diff($grade, $ocupados));
}

// Buscar esteticistas disponíveis por procedimento
function getEsteticistas($pdo, $procedimento = null) {
    if ($procedimento) {
        $sql = "SELECT f.id_funcionario, f.nome 
                FROM funcionario f
                JOIN procedimento p ON f.id_procedimento = p.id_procedimento
                WHERE p.nome = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$procedimento]);
    } else {
        $stmt = $pdo->query("SELECT id_funcionario, nome FROM funcionario");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'resumo':
        $data = $_GET['data'] ?? '';
        if (!$data) {
            echo json_encode([]);
            exit;
        }
        $sql = "SELECT a.hora, a.nome, a.procedimento, f.nome AS esteticista 
                FROM agendamentos a 
                LEFT JOIN funcionario f ON a.id_funcionario = f.id_funcionario 
                WHERE a.data = ? ORDER BY a.hora";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'detalhes':
        $data = $_GET['data'] ?? '';
        if (!$data) {
            echo "Data inválida.";
            exit;
        }
        $stmt = $pdo->prepare("SELECT a.*, f.nome AS esteticista FROM agendamentos a LEFT JOIN funcionario f ON a.id_funcionario = f.id_funcionario WHERE a.data = ? ORDER BY hora");
        $stmt->execute([$data]);
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h5>Agendamentos para <?= date('d/m/Y', strtotime($data)) ?></h5>

<table class="table table-striped">
<thead>
<tr>
<th>Hora</th>
<th>Cliente</th>
<th>Procedimento</th>
<th>Esteticista</th>
<th>Ações</th>
</tr>
</thead>
<tbody>
<?php if (empty($agendamentos)): ?>
<tr><td colspan="5"><em>Sem agendamentos.</em></td></tr>
<?php else: ?>
<?php foreach ($agendamentos as $ag): ?>
<tr>
    <td><?= substr($ag['hora'], 0, 5) ?></td>
    <td><?= htmlspecialchars($ag['nome']) ?></td>
    <td><?= htmlspecialchars($ag['procedimento']) ?></td>
    <td><?= htmlspecialchars($ag['esteticista']) ?></td>
    <td>
        <button class="btn btn-sm btn-warning btn-editar" data-id="<?= $ag['id_agendamento'] ?>">Editar</button>
        <button class="btn btn-sm btn-danger btn-excluir" data-id="<?= $ag['id_agendamento'] ?>">Excluir</button>
    </td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>

<hr>
<h5>Novo agendamento</h5>
<form id="form-novo-agendamento">
<input type="hidden" name="data" value="<?= htmlspecialchars($data) ?>" />
<div class="mb-3">
    <label for="nome" class="form-label">Nome:</label>
    <input type="text" name="nome" id="nome" class="form-control" required />
</div>
<div class="mb-3">
    <label for="procedimento" class="form-label">Procedimento:</label>
    <select name="procedimento" id="procedimento" class="form-select" required onchange="buscarEsteticistas(this.value)">
        <option value="">Selecione...</option>
        <?php
        $procedimentos = $pdo->query("SELECT nome FROM procedimento ORDER BY nome")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($procedimentos as $p) {
            echo "<option value=\"$p\">$p</option>";
        }
        ?>
    </select>
</div>
<div class="mb-3">
    <label for="id_funcionario" class="form-label">Esteticista:</label>
    <select name="id_funcionario" id="id_funcionario" class="form-select" required>
        <option value="">Selecione um procedimento primeiro</option>
    </select>
</div>
<div class="mb-3">
    <label for="hora" class="form-label">Hora:</label>
    <select name="hora" id="hora" class="form-select" required>
        <?php
        foreach (fetchHorariosDisponiveis($pdo, $data) as $h) {
            echo "<option value=\"$h\">$h</option>";
        }
        ?>
    </select>
</div>
<button type="submit" class="btn btn-success">Cadastrar</button>
</form>

<script>
function buscarEsteticistas(procedimento) {
    fetch('ajax_agenda.php?action=esteticistas&procedimento=' + encodeURIComponent(procedimento))
    .then(res => res.json())
    .then(data => {
        const select = document.getElementById('id_funcionario');
        select.innerHTML = '<option value="">Selecione...</option>';
        data.forEach(f => {
            const opt = document.createElement('option');
            opt.value = f.id_funcionario;
            opt.textContent = f.nome;
            select.appendChild(opt);
        });
    });
}
</script>
<?php
        break;

    case 'esteticistas':
        $procedimento = $_GET['procedimento'] ?? '';
        echo json_encode(getEsteticistas($pdo, $procedimento));
        break;

    case 'cadastrar':
        $nome = trim($_POST['nome'] ?? '');
        $procedimento = trim($_POST['procedimento'] ?? '');
        $id_funcionario = (int)($_POST['id_funcionario'] ?? 0);
        $data = $_POST['data'] ?? '';
        $hora = $_POST['hora'] ?? '';
        if (!$nome || !$procedimento || !$data || !$hora || !$id_funcionario) {
            echo json_encode(['sucesso' => false, 'msg' => 'Preencha todos os campos.']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data = ? AND hora = ?");
        $stmt->execute([$data, $hora]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['sucesso'=>false, 'msg'=>'Horário já ocupado.']);
            exit;
        }
        $ins = $pdo->prepare("INSERT INTO agendamentos (nome, procedimento, data, hora, id_funcionario) VALUES (?, ?, ?, ?, ?)");
        $ok = $ins->execute([$nome, $procedimento, $data, $hora, $id_funcionario]);
        echo json_encode(['sucesso'=>$ok, 'msg'=>$ok ? 'Agendado com sucesso!' : 'Erro ao agendar.']);
        break;

    case 'excluir':
        $id = (int)($_GET['id'] ?? 0);
        $del = $pdo->prepare("DELETE FROM agendamentos WHERE id_agendamento = ?");
        echo json_encode(['sucesso'=>$del->execute([$id]), 'msg'=>'Agendamento excluído.']);
        break;

    case 'editar':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE id_agendamento = ?");
        $stmt->execute([$id]);
        $ag = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ag) {
            echo "Agendamento não encontrado.";
            exit;
        }
        $funcionarios = getEsteticistas($pdo, $ag['procedimento']);
        $horarios = fetchHorariosDisponiveis($pdo, $ag['data'], substr($ag['hora'],0,5));
        if (!in_array(substr($ag['hora'],0,5), $horarios)) $horarios[] = substr($ag['hora'],0,5);
        sort($horarios);
?>
<h5>Editar Agendamento</h5>
<form id="form-editar-agendamento">
<input type="hidden" name="id" value="<?= $ag['id_agendamento'] ?>" />
<input type="hidden" name="data" value="<?= $ag['data'] ?>" />
<div class="mb-3">
    <label for="nome_edit" class="form-label">Nome:</label>
    <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($ag['nome']) ?>" />
</div>
<div class="mb-3">
    <label class="form-label">Procedimento:</label>
    <input type="text" name="procedimento" class="form-control" required value="<?= htmlspecialchars($ag['procedimento']) ?>" readonly />
</div>
<div class="mb-3">
    <label class="form-label">Esteticista:</label>
    <select name="id_funcionario" class="form-select" required>
        <?php foreach ($funcionarios as $f): ?>
        <option value="<?= $f['id_funcionario'] ?>" <?= $f['id_funcionario'] == $ag['id_funcionario'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($f['nome']) ?>
        </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Hora:</label>
    <select name="hora" class="form-select" required>
        <?php foreach ($horarios as $h): ?>
        <option value="<?= $h ?>" <?= $h == substr($ag['hora'],0,5) ? 'selected' : '' ?>><?= $h ?></option>
        <?php endforeach; ?>
    </select>
</div>
<button type="submit" class="btn btn-primary">Salvar</button>
</form>
<?php
        break;

    case 'alterar':
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $procedimento = trim($_POST['procedimento'] ?? '');
        $data = $_POST['data'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $id_funcionario = (int)($_POST['id_funcionario'] ?? 0);
        if (!$id || !$nome || !$procedimento || !$data || !$hora || !$id_funcionario) {
            echo json_encode(['sucesso'=>false, 'msg'=>'Preencha todos os campos.']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data = ? AND hora = ? AND id_agendamento != ?");
        $stmt->execute([$data, $hora, $id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['sucesso'=>false, 'msg'=>'Horário já ocupado.']);
            exit;
        }
        $upd = $pdo->prepare("UPDATE agendamentos SET nome=?, procedimento=?, data=?, hora=?, id_funcionario=? WHERE id_agendamento=?");
        $ok = $upd->execute([$nome, $procedimento, $data, $hora, $id_funcionario, $id]);
        echo json_encode(['sucesso'=>$ok, 'msg'=>$ok ? 'Atualizado com sucesso!' : 'Erro ao atualizar.']);
        break;

    default:
        echo "Ação inválida.";
        break;
}
