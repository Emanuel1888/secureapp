<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Verificar Token</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f0f2f5;
      margin: 0;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 1rem;
    }
    .container {
      background: #fff;
      padding: 2rem 2.5rem;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 420px;
    }
    h2 {
      text-align: center;
      margin-bottom: 1.8rem;
      color: #2c3e50;
      font-weight: 700;
    }
    form {
      display: flex;
      flex-direction: column;
    }
    label {
      font-weight: 600;
      margin-bottom: 0.4rem;
      color: #34495e;
    }
    input[type="text"] {
      padding: 12px 15px;
      margin-bottom: 1.2rem;
      border: 1.5px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.25s ease;
    }
    input[type="text"]:focus {
      border-color: #007bff;
      outline: none;
      box-shadow: 0 0 6px rgba(0, 123, 255, 0.5);
    }
    button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 14px 0;
      font-size: 1.1rem;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.3s ease;
      margin-top: 0.5rem;
    }
    button:hover {
      background-color: #0056b3;
    }
    #message {
      margin-top: 1.5rem;
      font-weight: 600;
      text-align: center;
      min-height: 1.4em;
      user-select: none;
      white-space: pre-wrap;
      word-break: break-word;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Verificar Token JWT</h2>
    <form id="tokenForm">
      <label for="token">Introduce el token:</label>
      <input type="text" id="token" name="token" required />
      <button type="submit">Verificar</button>
    </form>
    <div id="message"></div>
  </div>

<script>
  const form = document.getElementById('tokenForm');
  const messageDiv = document.getElementById('message');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const token = document.getElementById('token').value.trim();
    if (!token) {
      messageDiv.textContent = "Por favor, ingresa un token.";
      messageDiv.style.color = "red";
      return;
    }

    try {
      const res = await fetch('https://secureapp-q3uk.onrender.com/auth/verify_token.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json',  'Authorization': `Bearer ${token}` },
        body: JSON.stringify({ token })
      });

      const data = await res.json();

      if (res.ok && data.success) {
        // Guardar el token en localStorage para futuras peticiones
        localStorage.setItem('jwt_token', token);

        messageDiv.style.color = 'green';
        messageDiv.textContent = `${data.message}\nUser ID: ${data.user_id}`;

        setTimeout(() => {
          window.location.href = 'list_files.php';
        }, 1000);
      } else {
        messageDiv.style.color = 'red';
        messageDiv.textContent = data.error || 'Token inválido o expirado.';
      }
    } catch (error) {
      messageDiv.style.color = 'red';
      messageDiv.textContent = 'Error de conexión con el servidor.';
    }
  });
</script>



</body>
</html>
