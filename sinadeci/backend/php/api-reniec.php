<?php
ob_start();

// ===== CONFIGURAR SESIÓN SISRIS =====
if (session_status() === PHP_SESSION_NONE) {
    session_name('SISRIS_APP_SESSION');
    session_start();
}

// Configurar encabezado JSON
header('Content-Type: application/json');

// Verificar si hay sesión activa SISRIS
if (!isset($_SESSION['sisris_rol']) || !isset($_SESSION['sisris_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no válida para SISRIS']);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

try {
    // CAPTURAR DATOS DEL JSON (en lugar de $_POST)
    $input = json_decode(file_get_contents('php://input'), true);
    $numdni = $input['dni'] ?? null;

    // Validar DNI
    if (!$numdni || strlen($numdni) !== 8 || !ctype_digit($numdni)) {
        echo json_encode(['status' => 'error', 'message' => 'DNI debe tener 8 dígitos']);
        exit;
    }

    // 🔥 NUEVA API DE CONSULTASPERU.COM (reemplaza PIDE)
    $url = 'https://api.consultasperu.com/api/v1/query';
    $token = '3a107bbac572e9f71bdce73bd69909c72d4fdff8e6e9beacebf5aaaea3706e17';

    // Body de la petición para la nueva API
    $fields = [
        'token' => $token,
        'type_document' => 'dni',
        'document_number' => $numdni
    ];

    // Enviar petición con cURL a la nueva API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout de 30 segundos

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Verificar error de conexión
    if ($response === false) {
        throw new Exception('Error de conexión: ' . $error);
    }

    // Decodificar respuesta
    $result = json_decode($response, true);

    // 🔥 PROCESAR RESPUESTA DE LA NUEVA API
    if ($httpCode === 200 && isset($result['success']) && $result['success'] === true && isset($result['data'])) {
        // ✅ PERSONA ENCONTRADA EN LA NUEVA API
        $datosPersona = $result['data'];

        // Guardar en sesión SISRIS para el registro posterior (mapeo actualizado)
        $_SESSION['sisris_tipoDoc'] = 1;
        $_SESSION['sisris_doc'] = $numdni;
        $_SESSION['sisris_apePat'] = $datosPersona['first_last_name'] ?? '';
        $_SESSION['sisris_apeMat'] = $datosPersona['second_last_name'] ?? '';
        $_SESSION['sisris_nom'] = $datosPersona['name'] ?? '';
        $_SESSION['sisris_direcc'] = $datosPersona['address'] ?? 'No especificado';

        // 🔄 DEVOLVER DATOS EN FORMATO COMPATIBLE CON SISRIS
        $response = [
            'status' => 'success',
            'message' => 'Persona encontrada en RENIEC',
            'data' => [
                'dni' => $numdni,
                'apellidoPaterno' => $datosPersona['first_last_name'] ?? '',
                'apellidoMaterno' => $datosPersona['second_last_name'] ?? '',
                'nombres' => $datosPersona['name'] ?? '',
                'foto' => '', // La nueva API no devuelve foto
                'estadoCivil' => $datosPersona['civil_status'] ?? '',
                'direccion' => $datosPersona['address'] ?? '',
                'restriccion' => '', // Campo no disponible en nueva API
                'ubigeo' => $datosPersona['ubigeo'] ?? '',
                'fechaNacimiento' => $datosPersona['date_of_birth'] ?? '',
                'genero' => $datosPersona['gender'] ?? ''
            ],
            'source' => 'consultasperu.com' // Para identificar la fuente
        ];

        echo json_encode($response);
    } else {
        // ❌ ERROR EN LA CONSULTA DE LA NUEVA API
        $errorMessage = 'No se encontró información para el DNI ingresado';

        // Si la API devuelve mensaje específico, usarlo
        if (isset($result['message'])) {
            $errorMessage = $result['message'];
        }

        echo json_encode([
            'status' => 'error',
            'message' => $errorMessage,
            'api_response' => $result // Para debug (opcional)
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en la consulta: ' . $e->getMessage()
    ]);
}

ob_end_flush();
