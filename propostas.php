<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Histórico de Propostas</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h1>Histórico de Propostas</h1>
    <?php
    $proposalsJson = file_exists("proposta.json") ? file_get_contents("proposta.json") : "[]";
    $proposals = json_decode($proposalsJson, true);

    if (empty($proposals)) {
      echo "<p>Proposta não encontrada.</p>";
    } else {
      // Configurações de paginação
      $proposalsPerPage = 6;
      $totalProposals = count($proposals);
      $totalPages = ceil($totalProposals / $proposalsPerPage);

      // Obtém o número da página atual da URL
      $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
      if ($currentPage < 1) {
        $currentPage = 1;
      } elseif ($currentPage > $totalPages) {
        $currentPage = $totalPages;
      }

      // Define o índice inicial e final das propostas a serem exibidas na página atual
      $startIndex = ($currentPage - 1) * $proposalsPerPage;
      $endIndex = min($startIndex + $proposalsPerPage - 1, $totalProposals - 1);

      // Exibe as propostas da página atual
      echo '<div class="cards-container">';
      for ($i = $startIndex; $i <= $endIndex; $i++) {
        $proposal = $proposals[$i];
    ?>
    <div class="card">
      <h3>Proposta para <?php echo $proposal['quantidade_beneficiarios']; ?> beneficiarios</h3>
      <ul>
        <?php foreach ($proposal['beneficiarios'] as $beneficiary) { ?>
          <li>
            <strong>Nome:</strong> <?php echo $beneficiary['nome']; ?><br>
            <strong>Idade:</strong> <?php echo $beneficiary['idade']; ?><br>
            <strong>Plan:</strong> <?php echo $beneficiary['plano']['nome']; ?><br>
            <strong>Preço:</strong> R$ <?php echo $beneficiary['preco']; ?><br>
          </li>
        <?php } ?>
      </ul>
      <p><strong>Total Preço:</strong> R$ <?php echo $proposal['preco_total']; ?></p>
    </div>
    <?php
      }
      echo '</div>';

      // Exibe os links de paginação
      echo '<div class="pagination">';
      for ($page = 1; $page <= $totalPages; $page++) {
        $isActive = $page == $currentPage ? 'active' : '';
        echo '<a href="?page=' . $page . '" class="' . $isActive . '">' . $page . '</a>';
      }
      echo '</div>';
    }
    ?>
    <a href="index.html">
      <button class="redirect-button">Voltar</button>
    </a>
  </div>
</body>
</html>