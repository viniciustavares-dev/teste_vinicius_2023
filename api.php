<?php
$plansJson = '[ /* ... Plan JSON data ... */ ]';

$pricesJson = '[ /* ... Price JSON data ... */ ]';

$plans = json_decode($plansJson, true);
$prices = json_decode($pricesJson, true);

function calculatePrice($planCode, $age) {
  global $prices;
  $ageCategory = ($age <= 17) ? 'faixa1' : (($age <= 40) ? 'faixa2' : 'faixa3');

  foreach ($prices as $price) {
    if ($price['codigo'] == $planCode && $price['minimo_vidas'] <= 1) {
      return $price[$ageCategory];
    }
  }
  return 0;
}

function validateData($data) {
  $errors = [];

  if (empty($data['quantidade_beneficiarios'])) {
    $errors[] = "Número de beneficiários não foi informado.";
  } else if (!ctype_digit($data['quantidade_beneficiarios'])) {
    $errors[] = "Número de beneficiários deve ser um valor inteiro positivo.";
  }

  return $errors;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $requestData = json_decode(file_get_contents("php://input"), true);

  $validationErrors = validateData($requestData);

  if (!empty($validationErrors)) {
    http_response_code(400);
    echo json_encode(["errors" => $validationErrors]);
    exit;
  }

  $beneficiaries = [];
  $totalPrice = 0;

  for ($i = 1; $i <= $requestData['quantidade_beneficiarios']; $i++) {
    $ageKey = "idade_$i";
    $planKey = "registro_plano_$i";

    if (empty($requestData[$ageKey]) || empty($requestData[$planKey])) {
      continue;
    }

    $age = (int) $requestData[$ageKey];
    $planCode = $requestData[$planKey];

    $selectedPlan = null;
    foreach ($plans as $plan) {
      if ($plan['registro'] === $planCode) {
        $selectedPlan = $plan;
        break;
      }
    }

    if ($selectedPlan) {
      $price = calculatePrice($selectedPlan['codigo'], $age);

      $beneficiary = [
        "nome" => $requestData["nome_$i"],
        "idade" => $age,
        "plano" => $selectedPlan,
        "preco" => $price,
      ];

      $beneficiaries[] = $beneficiary;
      $totalPrice += $price;
    } else {
      // Return error for invalid planCode
      http_response_code(400);
      echo json_encode(["error" => "Plano inválido: $planCode"]);
      exit;
    }
  }

  $proposal = [
    "quantidade_beneficiarios" => $requestData['quantidade_beneficiarios'],
    "beneficiarios" => $beneficiaries,
    "preco_total" => $totalPrice,
  ];

  $proposalsJson = file_exists("proposta.json") ? file_get_contents("proposta.json") : "[]";
  $proposals = json_decode($proposalsJson, true);
  $proposals[] = $proposal;
  file_put_contents("proposta.json", json_encode($proposals, JSON_PRETTY_PRINT));

  echo json_encode($proposal, JSON_PRETTY_PRINT);
}
?>