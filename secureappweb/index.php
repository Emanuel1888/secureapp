<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Login Test</title>
  <style>
    /* Reset y base */
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
      max-width: 400px;
    }
    h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #2c3e50;
      font-weight: 700;
    }
    form {
      display: flex;
      flex-direction: column;
    }
    input[type="text"],
    input[type="password"] {
      padding: 12px 15px;
      margin-bottom: 1rem;
      border: 1.5px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.25s ease;
    }
    input[type="text"]:focus,
    input[type="password"]:focus {
      border-color: #007bff;
      outline: none;
      box-shadow: 0 0 6px rgba(0, 123, 255, 0.5);
    }
    button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 12px 0;
      font-size: 1.1rem;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #0056b3;
    }
    #response {
      margin-top: 1.5rem;
      font-weight: 600;
      text-align: center;
      min-height: 1.4em;
      user-select: none;
    }
    /* Estilo para el enlace de registro */
    .register-link {
      display: block;
      margin-top: 1.8rem;
      text-align: center;
      font-weight: 600;
      color: #007bff;
      cursor: pointer;
      text-decoration: none;
      transition: color 0.25s ease;
    }
    .register-link:hover {
      color: #0056b3;
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Iniciar sesión</h2>
    <form id="loginForm">
      <input type="text" id="username" placeholder="Usuario" required />
      <input type="password" id="password" placeholder="Contraseña" required />
      <button type="submit">Iniciar sesión</button>
    </form>

    <div id="response"></div>

    <a href="registro.php" class="register-link">¿No tienes cuenta? Regístrate aquí</a>
  </div>

  <script>
 const form = document.getElementById('loginForm'); // declara aquí
  form.addEventListener('submit', async (e) => {
  e.preventDefault();

  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;

  try {
    const response = await fetch('https://secureapp-q3uk.onrender.com/auth/login.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ username, password })
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.success === true && data.message === 'Token enviado al correo electrónico.') {
      window.location.href = 'verify_token.html';
    } else {
      alert(data.message || 'Error al iniciar sesión.');
    }

  } catch (error) {
    console.error('Error de red:', error);
    alert('No se pudo conectar con el servidor.');
  }
});
  </script>
</body>
</html>
