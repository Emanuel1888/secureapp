<!-- list_files.html -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mis Archivos</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      padding: 2rem;
      color: #333;
    }
    h1 {
      color: #444;
    }
    .file-list {
      margin-top: 2rem;
      border-collapse: collapse;
      width: 100%;
    }
    .file-list th, .file-list td {
      border: 1px solid #ccc;
      padding: 0.75rem;
      text-align: left;
    }
    .file-list th {
      background-color: #f0f0f0;
    }
    .message {
      padding: 1rem;
      margin-top: 1rem;
      border-radius: 5px;
    }
    .success {
      background-color: #e0ffe0;
      border: 1px solid #8bc34a;
    }
    .error {
      background-color: #ffe0e0;
      border: 1px solid #f44336;
    }
  </style>
</head>
<body>
  <h1>Archivos del Usuario</h1>

  <div id="message" class="message" style="display: none;"></div>

  <table id="filesTable" class="file-list" style="display: none;">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre Original</th>
        <th>Nombre en Servidor</th>
        <th>Tipo MIME</th>
        <th>Tamaño (bytes)</th>
        <th>Subido en</th>
      </tr>
    </thead>
    <tbody id="filesBody">
    </tbody>
  </table>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('jwt_token');

    const message = document.getElementById('message');
    const table = document.getElementById('filesTable');
    const tbody = document.getElementById('filesBody');

    if (!token) {
      message.textContent = 'Token no encontrado. Inicia sesión.';
      message.className = 'message error';
      message.style.display = 'block';
      return;
    }

    fetch('http://192.168.43.106:3000/controllers/list_files.php', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + token
      }
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        data.files.forEach(file => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${file.id}</td>
            <td>${file.original_name}</td>
            <td>${file.filename}</td>
            <td>${file.mime_type}</td>
            <td>${(file.size / 1024).toFixed(2)} KB</td>
            <td>${file.uploaded_at}</td>
          `;
          tbody.appendChild(row);
        });
        table.style.display = 'table';
      } else {
        message.textContent = data.error || 'Error al cargar archivos.';
        message.className = 'message error';
        message.style.display = 'block';
      }
    })
    .catch(err => {
      message.textContent = 'Error de red: ' + err.message;
      message.className = 'message error';
      message.style.display = 'block';
    });
  });
</script>

</body>
</html>
