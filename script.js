function createBeneficiaryFields(count) {
    const beneficiaryFields = document.getElementById("beneficiariosFields");
    beneficiaryFields.innerHTML = ""; // Clear previous fields
  
    for (let i = 1; i <= count; i++) {
      const fields = `
        <h3>Beneficiário ${i}</h3>
        <label for="nome_${i}">Nome:</label>
        <input type="text" id="nome_${i}" name="nome_${i}" required>
  
        <label for="idade_${i}">Idade:</label>
        <input type="number" id="idade_${i}" name="idade_${i}" required>
  
        <label for="registro_plano_${i}">Registro do plano:</label>
        <select id="registro_plano_${i}" name="registro_plano_${i}">
          <option value="" selected disabled>Selecione um plano</option>
        </select>
      `;
  
      const beneficiaryElement = document.createElement("div");
      beneficiaryElement.innerHTML = fields.trim();
      beneficiaryFields.appendChild(beneficiaryElement);
    }
  }
  
  function generatePlanOptions() {
    return fetch("plans.json")
      .then(response => response.json());
  }
  
  document.getElementById("quantidade_beneficiarios").addEventListener("input", function(event) {
    const count = parseInt(event.target.value) || 0;
    createBeneficiaryFields(count);
  });
  
  document.getElementById("proposalForm").addEventListener("submit", function(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
  
    const data = {
      quantidade_beneficiarios: formData.get("quantidade_beneficiarios"),
      beneficiarios: []
    };
  
    for (let i = 1; i <= data.quantidade_beneficiarios; i++) {
      const beneficiary = {
        nome: formData.get(`nome_${i}`),
        idade: formData.get(`idade_${i}`),
        registro_plano: formData.get(`registro_plano_${i}`)
      };
      data.beneficiarios.push(beneficiary);
    }
  
    fetch("api.php", {
      method: "POST",
      body: JSON.stringify(data),
    })
    .then(response => response.json())
    .then(result => {
      if (result.errors) {
        document.getElementById("result").innerHTML = "<p>Error: " + result.errors.join(", ") + "</p>";
      } else {
        alert("Cadastro realizado com sucesso!");
        document.getElementById("proposalForm").reset();
        // Clear the "result" div
        document.getElementById("result").innerHTML = "";
      }
    });
  });
  
  generatePlanOptions()
    .then(plans => {
      createBeneficiaryFields(0);
      const planOptions = plans.map(plan => `<option value="${plan.registro}">${plan.nome}</option>`).join("");
      document.querySelectorAll("[id^='registro_plano_']").forEach(select => {
        select.innerHTML += planOptions;
      });
    })
    .catch(error => {
      console.error("Error fetching plan data:", error);
    });