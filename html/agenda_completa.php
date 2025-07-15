<?php
session_start();
require_once 'conexao.php';

// VERIFICA SE USUARIO TEM PERMISSÃO DE ADM
if ($_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!');window.location.href='principal.php';</script>";
    exit();
}

$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

$edita_id = null;
$edita_agendamento = null;

// BUSCA HORARIOS DISPONIVEIS
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

// BUSCA PROFISSIONAIS DO BANCO PELO id_procedimento
function buscarProfissionais($pdo, $id_procedimento) {
    $stmt = $pdo->prepare("SELECT nome FROM funcionarios WHERE id_procedimento = ?");
    $stmt->execute([$id_procedimento]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// BUSCA NOME DO PROCEDIMENTO PELO ID
function nomeProcedimento($pdo, $id_procedimento) {
    $stmt = $pdo->prepare("SELECT nome FROM procedimento WHERE id_procedimento = ?");
    $stmt->execute([$id_procedimento]);
    return $stmt->fetchColumn() ?: '';
}

// PROCESSA FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];
    $nome = trim($_POST['nome'] ?? '');
    $id_procedimento = $_POST['procedimento'] ?? '';
    $profissional = trim($_POST['profissional'] ?? '');
    $data = $_POST['data'] ?? '';
    $hora = $_POST['hora'] ?? '';

    if ($nome === '' || $id_procedimento === '' || $profissional === '' || !$data || !$hora) {
        $_SESSION['msg'] = "❌ Preencha todos os campos corretamente.";
        header("Location: agenda_completa.php");
        exit;
    }

    $procNome = nomeProcedimento($pdo, $id_procedimento);

    if ($acao === 'cadastrar') {
        $verifica = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data = ? AND hora = ?");
        $verifica->execute([$data, $hora]);
        if ($verifica->fetchColumn() > 0) {
            $_SESSION['msg'] = "❌ Horário já ocupado.";
        } else {
            $ins = $pdo->prepare("INSERT INTO agendamentos (nome, procedimento, id_funcionario, data, hora) VALUES (?, ?, ?, ?, ?)");
            $_SESSION['msg'] = $ins->execute([$nome, $procNome, $profissional, $data, $hora])
                ? "✅ Agendamento cadastrado com sucesso!"
                : "❌ Erro ao cadastrar.";
        }
        header("Location: agenda_completa.php");
        exit;
    }

    if ($acao === 'alterar' && isset($_POST['id_agendamento'])) {
        $id = (int)$_POST['id_agendamento'];
        $verifica = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data = ? AND hora = ? AND id_agendamento != ?");
        $verifica->execute([$data, $hora, $id]);
        if ($verifica->fetchColumn() > 0) {
            $_SESSION['msg'] = "❌ Horário já ocupado.";
        } else {
            $upd = $pdo->prepare("UPDATE agendamentos SET nome=?, procedimento=?, id_funcionario=?, data=?, hora=? WHERE id_agendamento=?");
            $_SESSION['msg'] = $upd->execute([$nome, $procNome, $profissional, $data, $hora, $id])
                ? "✅ Agendamento atualizado com sucesso!"
                : "❌ Erro ao atualizar.";
        }
        header("Location: agenda_completa.php");
        exit;
    }
}

// EXCLUIR
if (isset($_GET['excluir'])) {
    $idDel = (int)$_GET['excluir'];
    $del = $pdo->prepare("DELETE FROM agendamentos WHERE id_agendamento=?");
    $_SESSION['msg'] = $del->execute([$idDel])
        ? "✅ Agendamento excluído!"
        : "❌ Erro ao excluir.";
    header("Location: agenda_completa.php");
    exit;
}

// CARREGA DADOS PARA EDITAR
if (isset($_GET['editar'])) {
    $edita_id = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE id_agendamento=?");
    $stmt->execute([$edita_id]);
    $edita_agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
}

// BUSCA PROCEDIMENTOS DO BANCO
$stmt = $pdo->query("SELECT id_procedimento, nome FROM procedimento ORDER BY nome");
$procedimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// BUSCAR AGENDAMENTOS
$busca = $_GET['busca'] ?? '';
$filtro = $busca ? " WHERE nome LIKE ? OR procedimento LIKE ? OR data LIKE ?" : '';
$params = $busca ? ["%$busca%", "%$busca%", "%$busca%"] : [];

$sql = "SELECT * FROM agendamentos $filtro ORDER BY data, hora";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// AGRUPA POR DATA
$agenda = [];
foreach ($agendamentos as $a) {
    $agenda[$a['data']][] = $a;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Íris Essence - Agenda Completa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body class="cadastro-fundo">
<header>
    <nav>
        <ul>
            <a href="../html/index.html"><img src="../imgs/logo.jpg" class="logo" alt="Logo"></a>
            <li><a href="../html/index.html">HOME</a></li>
            <li>
                <a href="#">PROCEDIMENTOS FACIAIS</a>
                <div class="submenu">
                    <a href="../html/limpezapele.html">Limpeza de Pele</a>
                    <a href="../html/labial.html">Preenchimento Labial</a>
                    <a href="../html/microagulhamento.html">Microagulhamento</a>
                    <a href="../html/botoxfacial.html">Botox</a>
                    <a href="../html/acne.html">Tratamento para Acne</a>
                    <a href="../html/rinomodelacao.html">Rinomodelação</a>
                </div>
            </li>
            <li>
                <a href="#">PROCEDIMENTOS CORPORAIS</a>
                <div class="submenu">
                    <a href="../html/massagemmodeladora.html">Massagem Modeladora</a>
                    <a href="../html/drenagemlinfatica.html">Drenagem Linfática</a>
                    <a href="../html/depilacaolaser.html">Depilação a Laser</a>
                    <a href="../html/depilacaocera.html">Depilação de Cera</a>
                    <a href="../html/massagemrelaxante.html">Massagem Relaxante</a>
                </div>
            </li>
            <li><a href="../html/produtos.php">PRODUTOS</a></li>|
            <li><a href="../html/login.php">LOGIN</a></li>|
            <li><a href="../html/cadastro.html">CADASTRO</a></li>|
            <div class="logout">
                <form action="logout.php" method="POST">
                    <button type="submit">Logout</button>
                </form>
            </div>
        </ul>
    </nav>
</header>
<br><br>

<div class="formulario">
<?php if ($msg): ?>
    <script>alert("<?= addslashes($msg) ?>");</script>
<?php endif; ?>

<fieldset>
    <legend><?= $edita_agendamento ? "Editar Agendamento #{$edita_agendamento['id_agendamento']}" : "Cadastrar Novo Agendamento" ?></legend>
    <form action="agenda_completa.php<?= $edita_agendamento ? "?editar={$edita_agendamento['id_agendamento']}" : '' ?>" method="POST" id="form-agendamento">
        <input type="hidden" name="acao" value="<?= $edita_agendamento ? 'alterar' : 'cadastrar' ?>" />
        <?php if ($edita_agendamento): ?>
            <input type="hidden" name="id_agendamento" value="<?= $edita_agendamento['id_agendamento'] ?>" />
        <?php endif; ?>

        <div class="mb-3">
            <label for="nome" class="form-label">Nome do Cliente:</label>
            <input type="text" id="nome" name="nome" class="form-control" required
                value="<?= htmlspecialchars($edita_agendamento['nome'] ?? '') ?>" />
        </div>

        <div class="mb-3">
            <label for="procedimento" class="form-label">Procedimento:</label>
            <select id="procedimento" name="procedimento" class="form-select" required>
                <option value="">Selecione</option>
                <?php
                $procSelecionado = $edita_agendamento['procedimento'] ?? '';
                // Para comparação, precisamos pegar o id do procedimento que tem o nome igual (se editar)
                $procSelId = '';
                foreach ($procedimentos as $proc) {
                    $sel = '';
                    if ($procSelecionado !== '') {
                        // Se editar, comparo o nome do procedimento com o da lista para marcar selected
                        if (strcasecmp($proc['nome'], $procSelecionado) === 0) {
                            $sel = 'selected';
                            $procSelId = $proc['id_procedimento'];
                        }
                    }
                    echo "<option value=\"{$proc['id_procedimento']}\" $sel>" . htmlspecialchars($proc['nome']) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="profissional" class="form-label">Profissional:</label>
            <select id="profissional" name="profissional" class="form-select" required>
                <?php
                // Se estiver editando, carrega os profissionais do procedimento selecionado
                if ($procSelId !== '') {
                    $profs = buscarProfissionais($pdo, $procSelId);
                    $profSelecionado = $edita_agendamento['profissional'] ?? '';
                    foreach ($profs as $prof) {
                        $sel = ($profSelecionado === $prof) ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($prof) . "\" $sel>" . htmlspecialchars($prof) . "</option>";
                    }
                } else {
                    echo '<option value="">Selecione um procedimento primeiro</option>';
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="data" class="form-label">Data:</label>
            <input type="date" id="data" name="data" class="form-control" required min="<?= date('Y-m-d') ?>"
                value="<?= htmlspecialchars($edita_agendamento['data'] ?? '') ?>" />
        </div>

        <div class="mb-3">
            <label for="hora" class="form-label">Hora:</label>
            <select id="hora" name="hora" class="form-select" required>
                <?php
                $horaSelecionada = $edita_agendamento ? substr($edita_agendamento['hora'], 0, 5) : null;
                $dataSelecionada = $edita_agendamento ? $edita_agendamento['data'] : null;
                $horariosDisponiveis = [];
                if ($dataSelecionada) {
                    $horariosDisponiveis = fetchHorariosDisponiveis($pdo, $dataSelecionada, $horaSelecionada);
                    if (!in_array($horaSelecionada, $horariosDisponiveis)) {
                        $horariosDisponiveis[] = $horaSelecionada;
                        sort($horariosDisponiveis);
                    }
                }
                foreach ($horariosDisponiveis as $h) {
                    $sel = ($h === $horaSelecionada) ? 'selected' : '';
                    echo "<option value=\"$h\" $sel>$h</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary"><?= $edita_agendamento ? 'Salvar Alterações' : 'Cadastrar' ?></button>
        <?php if ($edita_agendamento): ?>
            <a href="agenda_completa.php" class="btn btn-secondary ms-2">Cancelar</a>
        <?php endif; ?>
    </form>
</fieldset>

<hr>

<fieldset>
    <legend>Buscar Agendamentos</legend>
    <form method="GET" action="agenda_completa.php" class="mb-3 d-flex gap-2">
        <input type="text" name="busca" class="form-control" placeholder="Nome, procedimento ou data (YYYY-MM-DD)" value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>" />
        <button type="submit" class="btn btn-info">Buscar</button>
        <?php if (!empty($_GET['busca'])): ?>
            <a href="agenda_completa.php" class="btn btn-secondary">Limpar</a>
        <?php endif; ?>
    </form>
</fieldset>

<fieldset>
    <legend>Agenda</legend>
    <?php if (empty($agenda)): ?>
        <p>Nenhum agendamento encontrado.</p>
    <?php else: ?>
        <?php foreach ($agenda as $data => $agendamentosDia): ?>
            <div class="agenda-dia mb-4">
                <h4 class="mb-3"><?= date('d/m/Y', strtotime($data)) ?></h4>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Hora</th>
                            <th>Cliente</th>
                            <th>Procedimento</th>
                            <th>Profissional</th>
                            <th style="width: 110px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agendamentosDia as $ag): ?>
                            <tr>
                                <td><?= substr($ag['hora'], 0, 5) ?></td>
                                <td><?= htmlspecialchars($ag['nome']) ?></td>
                                <td><?= htmlspecialchars($ag['procedimento']) ?></td>
                                <td><?= htmlspecialchars($ag['id_funcionario']) ?></td>
                                <td>
                                    <a href="?editar=<?= $ag['id_agendamento'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <a href="?excluir=<?= $ag['id_agendamento'] ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Confirmar exclusão?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <br>
    <button type="button" class="voltar-button btn btn-secondary" onclick="window.location.href='principal.php'">Voltar</button>
</fieldset>
<br><br>
</div>

<script>
    // Ao mudar o procedimento, carrega os profissionais via fetch
    document.getElementById('procedimento').addEventListener('change', function() {
        const procedimentoId = this.value;
        const profSelect = document.getElementById('profissional');
        profSelect.innerHTML = '<option>Carregando...</option>';

        if (!procedimentoId) {
            profSelect.innerHTML = '<option value="">Selecione um procedimento primeiro</option>';
            return;
        }

        fetch('buscar_profissionais.php?id_procedimento=' + procedimentoId)
            .then(res => res.json())
            .then(profs => {
                profSelect.innerHTML = '';
                if (profs.length === 0) {
                    profSelect.innerHTML = '<option value="">Nenhum profissional disponível</option>';
                } else {
                    profs.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.nome;
                        opt.textContent = p.nome;
                        profSelect.appendChild(opt);
                    });
                }
            })
            .catch(() => {
                profSelect.innerHTML = '<option value="">Erro ao carregar profissionais</option>';
            });
    });

    // Ao mudar a data, carrega horários disponíveis
    document.getElementById('data').addEventListener('change', function () {
        const data = this.value;
        const horaSelect = document.getElementById('hora');
        horaSelect.innerHTML = '<option>Carregando...</option>';

        fetch('horarios_disponiveis.php?data=' + data)
            .then(resp => resp.json())
            .then(horarios => {
                horaSelect.innerHTML = '';
                if (horarios.length === 0) {
                    horaSelect.innerHTML = '<option value="">Nenhum horário disponível</option>';
                } else {
                    horarios.forEach(h => {
                        const opt = document.createElement('option');
                        opt.value = h;
                        opt.textContent = h;
                        horaSelect.appendChild(opt);
                    });
                }
            })
            .catch(() => {
                horaSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
            });
    });
</script>
</body>
</html>
