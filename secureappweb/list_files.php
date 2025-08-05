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
    .btn {
      padding: 0.5rem 1rem;
      margin-right: 0.5rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .download-btn {
      background-color: #4caf50;
      color: white;
    }
    .delete-btn {
      background-color: #f44336;
      color: white;
    }
    .upload-btn {
      background-color: #2196f3;
      color: white;
    }

  </style>
</head>
<body>
  <h1>Archivos del Usuario</h1>
<!-- Formulario para subir archivo -->
<form id="uploadForm" enctype="multipart/form-data">
  <label for="fileInput">Subir imagen:</label>
  <input type="file" id="fileInput" name="file" accept="image/*" required />
  <button type="submit" class="btn upload-btn">Subir</button>
</form
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
        <th>Acciones</th>
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
    
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('fileInput');

    uploadForm.addEventListener('submit', (e) => {
  e.preventDefault();

  const token = localStorage.getItem('jwt_token');
  if (!token) {
    message.textContent = 'No hay token. Inicia sesión.';
    message.className = 'message error';
    message.style.display = 'block';
    return;
  }

  const file = fileInput.files[0];
  if (!file) {
    message.textContent = 'Selecciona un archivo antes de subir.';
    message.className = 'message error';
    message.style.display = 'block';
    return;
  }

  const formData = new FormData();
  formData.append('file', file);

  fetch('https://secureapp-q3uk.onrender.com/controllers/upload_file.php', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer ' + token
    },
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      message.textContent = data.message;
      message.className = 'message success';
      message.style.display = 'block';
      fileInput.value = '';
      setTimeout(() => location.reload(), 1000); // recargar tabla después de 1s
    } else {
      message.textContent = data.error || 'Error al subir.';
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

    if (!token) {
      message.textContent = 'Token no encontrado. Inicia sesión.';
      message.className = 'message error';
      message.style.display = 'block';
      return;
    }

    fetch('https://secureapp-q3uk.onrender.com/controllers/list_files.php', {
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
            <td>
              <button class="btn download-btn" data-id="${file.id}">Descargar</button>
              <button class="btn delete-btn" data-id="${file.id}">Eliminar</button>
            </td>
          `;
          tbody.appendChild(row);
        });
        table.style.display = 'table';

        // Acción de descarga
       document.querySelectorAll('.download-btn').forEach(button => {
          button.addEventListener('click', () => {
            const fileId = button.getAttribute('data-id');
            const token = localStorage.getItem('jwt_token');
            
            if (!token) {
              alert('No hay token de sesión. Inicia sesión primero.');
              return;
            }
        
            // Usamos una ruta protegida que valida el archivo y fuerza descarga
            const downloadUrl = `https://secureapp-q3uk.onrender.com/controllers/download_file.php?file_id=${fileId}`;
            
            // Abrimos en una nueva pestaña (la sesión se valida con el token)
            // Si tu servidor no permite encabezados Authorization en descargas,
            // puedes usar cookies o un token temporal en la URL si es necesario.
            window.open(downloadUrl, '_blank');
          });
        });

        // Acción de eliminación
        document.querySelectorAll('.delete-btn').forEach(button => {
          button.addEventListener('click', () => {
            const fileId = button.getAttribute('data-id');
            if (confirm('¿Estás seguro de que deseas eliminar este archivo?')) {
              fetch(`https://secureapp-q3uk.onrender.com/controllers/delete_file.php?file_id=${fileId}`, {
                method: 'GET',
                headers: {
                  'Authorization': 'Bearer ' + token
                }
              })
              .then(res => res.json())
              .then(response => {
                if (response.success) {
                  // Eliminar fila de la tabla sin recargar
                  button.closest('tr').remove();
                  message.textContent = response.message;
                  message.className = 'message success';
                  message.style.display = 'block';
                } else {
                  message.textContent = response.error || 'Error al eliminar.';
                  message.className = 'message error';
                  message.style.display = 'block';
                }
              })
              .catch(err => {
                message.textContent = 'Error de red: ' + err.message;
                message.className = 'message error';
                message.style.display = 'block';
              });
            }
          });
        });

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
