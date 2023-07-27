<?php

// Função para salvar a proposta no arquivo JSON
function saveProposal($proposal) {
  // Ler as propostas existentes do arquivo JSON
  $proposalsJson = file_exists("proposta.json") ? file_get_contents("proposta.json") : "[]";
  $proposals = json_decode($proposalsJson, true);

  // Gerar o hash da idade de cada beneficiário
  foreach ($proposal['beneficiarios'] as &$beneficiary) {
    $beneficiary['idade_hash'] = password_hash($beneficiary['idade'], PASSWORD_BCRYPT);
  }

  // Adicionar a nova proposta à lista de propostas
  $proposals[] = $proposal;

  // Salvar a lista atualizada de propostas no arquivo JSON
  file_put_contents("proposta.json", json_encode($proposals));
}

// Função para carregar as propostas do arquivo JSON
function loadProposals() {
  // Ler as propostas do arquivo JSON
  $proposalsJson = file_exists("proposta.json") ? file_get_contents("proposta.json") : "[]";
  $proposals = json_decode($proposalsJson, true);

  // Verificar o hash da idade de cada beneficiário
  foreach ($proposals as &$proposal) {
    foreach ($proposal['beneficiarios'] as &$beneficiary) {
      // Verificar se a idade corresponde ao hash
      if (!password_verify($beneficiary['idade'], $beneficiary['idade_hash'])) {
        // Idade inválida, remover o beneficiário
        unset($beneficiary);
      }
      // Remover o hash da idade para não ser exibido na página
      unset($beneficiary['idade_hash']);
    }
    // Remover beneficiários vazios (com idade inválida)
    $proposal['beneficiarios'] = array_values($proposal['beneficiarios']);
  }

  return $proposals;
}

// Rotas da API

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Verificar se os dados foram enviados corretamente
  if (isset($_POST['quantidade_beneficiarios'], $_POST['beneficiarios'], $_POST['registro_plano'])) {
    // Coletar os dados do formulário
    $quantidadeBeneficiarios = $_POST['quantidade_beneficiarios'];
    $beneficiarios = $_POST['beneficiarios'];
    $registroPlano = $_POST['registro_plano'];

    // Montar a proposta
    $proposal = [
      'quantidade_beneficiarios' => $quantidadeBeneficiarios,
      'beneficiarios' => $beneficiarios,
      'registro_plano' => $registroPlano,
    ];

    // Salvar a proposta no arquivo JSON
    saveProposal($proposal);

    // Retornar uma resposta de sucesso
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Proposta salva com sucesso!']);
    exit;
  } else {
    // Caso algum dado esteja faltando, retornar uma mensagem de erro
    header('Content-Type: application/json', true, 400);
    echo json_encode(['errors' => ['Campos obrigatórios não foram preenchidos.']]);
    exit;
  }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // Carregar as propostas existentes e retornar como resposta
  $proposals = loadProposals();
  header('Content-Type: application/json');
  echo json_encode($proposals);
  exit;
} else {
  // Método HTTP não suportado
  header('Content-Type: application/json', true, 405);
  echo json_encode(['errors' => ['Método não suportado.']]);
  exit;
}
