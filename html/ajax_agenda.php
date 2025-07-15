<?php
require_once 'conexao.php';

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

function getProfissionais($pdo) {
    $stmt = $pdo->query("SELECT id_funcionario, nome FROM funcionario ORDER BY nome");
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
        $sql = "SELECT a.nome, a.procedimento, a.hora, f.nome AS profissional 
                FROM agendamentos a
                LEFT JOIN funcionario f ON a.id_funcionario = f.id_funcionario
                WHERE a.data = ? ORDER BY a.hora";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data]);
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($agendamentos);
        break;

    case 'detalhes':
        $data = $_GET['data'] ?? '';
        if (!$data) {
            echo "Data inválida.";
            exit;
        }

        $sql = "SELECT a.*, f.nome AS nome_funcionario
                FROM agendamentos a
                LEFT JOIN funcionario f ON a.id_funcionario = f.id_funcionario
                WHERE a.data = ? ORDER BY a.hora";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data]);
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $profissionais = getProfissionais($pdo);
        ?>

<h5>Agendamentos para <?= date('d/m/Y', strtotime($data)) ?></h5>
<table class="table table-striped">
<thead>
<tr>
    <th>Hora</th>
    <th>Nome</th>
    <th>Procedimento</th>
    <th>Profissional</th>
    <th>Ações</th>
</tr>
</thead>
<tbody>
<?php if (count($agendamentos) === 0): ?>
<tr><td colspan="5"><em>Sem agendamentos.</em></td></tr>
<?php else: ?>
<?php foreach ($agendamentos as $ag): ?>
<tr>
    <td><?= substr($ag['hora'], 0, 5) ?></td>
    <td><?= htmlspecialchars($ag['nome']) ?></td>
    <td><?= htmlspecialchars($ag['procedimento']) ?></td>
    <td><?= htmlspecialchars($ag['nome_funcionario'] ?? 'Não informado') ?></td>
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
        <select name="procedimento" id="procedimento" class="form-select" required>
            <optgroup label="Procedimentos Faciais">
                <option value="Limpeza de Pele">Limpeza de Pele</option>
                <option value="Preenchimento Labial">Preenchimento Labial</option>
                <option value="Microagulhamento">Microagulhamento</option>
                <option value="Botox">Botox</option>
                <option value="Tratamento para Acne">Tratamento para Acne</option>
                <option value="Rinomodelação">Rinomodelação</option>
            </optgroup>
            <optgroup label="Procedimentos Corporais">
                <option value="Massagem Modeladora">Massagem Modeladora</option>
                <option value="Drenagem Linfática">Drenagem Linfática</option>
                <option value="Depilação a Laser">Depilação a Laser</option>
                <option value="Depilação a Cera">Depilação a Cera</option>
                <option value="Massagem Relaxante">Massagem Relaxante</option>
            </optgroup>
        </select>
    </div>
    <div class="mb-3">
        <label for="hora" class="form-label">Hora:</label>
        <select name="hora" id="hora" class="form-select" required>
            <?php
            $horarios = fetchHorariosDisponiveis($pdo, $data);
            foreach ($horarios as $h) {
                echo "<option value=\"$h\">$h</option>";
            }
            ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="profissional" class="form-label">Profissional:</label>
        <select name="id_funcionario" id="profissional" class="form-select" required>
            <option value="">Selecione</option>
            <?php foreach ($profissionais as $f): ?>
                <option value="<?= $f['id_funcionario'] ?>"><?= htmlspecialchars($f['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-success">Cadastrar</button>
</form>

<?php
        break;

    case 'cadastrar':
        $nome = trim($_POST['nome'] ?? '');
        $procedimento = trim($_POST['procedimento'] ?? '');
        $data = $_POST['data'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $id_funcionario = (int)($_POST['id_funcionario'] ?? 0);


        if ($nome === '' || $procedimento === '' || !$data || !$hora || $id_funcionario <= 0) {
            echo json_encode(['sucesso'=>false, 'msg'=>'Preencha todos os campos corretamente.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data = ? AND hora = ?");
        $stmt->execute([$data, $hora]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['sucesso'=>false, 'msg'=>'Horário já ocupado.']);
            exit;
        }

        $ins = $pdo->prepare("INSERT INTO agendamentos (nome, procedimento, data, hora, id_funcionario) VALUES (?, ?, ?, ?, ?)");
        if ($ins->execute([$nome, $procedimento, $data, $hora, $id_funcionario])) {
            echo json_encode(['sucesso'=>true, 'msg'=>'Agendamento cadastrado com sucesso!']);
        } else {
            echo json_encode(['sucesso'=>false, 'msg'=>'Erro ao cadastrar.']);
        }
        break;

    // ... manter os demais cases ('editar', 'alterar') com adição do campo `id_funcionario` da mesma forma ...

    default:
        echo "Ação inválida.";
        break;
}
