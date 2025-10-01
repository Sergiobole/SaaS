<?php
function handle_photo_upload($file_input, $upload_subdir, $old_photo_path = null) {
    // Diretório base de uploads, acessível publicamente
    $base_upload_dir = __DIR__ . '/../public/uploads/';
    $upload_path = $base_upload_dir . $upload_subdir . '/';

    // Se um arquivo antigo existe e um novo foi enviado, remove o antigo
    if ($old_photo_path && isset($file_input) && $file_input['error'] === UPLOAD_ERR_OK) {
        $old_file_full_path = __DIR__ . '/../' . $old_photo_path;
        if (file_exists($old_file_full_path)) {
            unlink($old_file_full_path);
        }
    }

    if (isset($file_input) && $file_input['error'] === UPLOAD_ERR_OK) {
        // Cria o diretório se não existir
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $file_info = pathinfo($file_input['name']);
        $file_ext = strtolower($file_info['extension']);
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($file_ext, $allowed_exts)) {
            // Extensão não permitida, poderia retornar um erro mais específico
            return null; 
        }

        // Limite de 5MB
        if ($file_input['size'] > 5 * 1024 * 1024) {
            // Arquivo muito grande
            return null;
        }

        $unique_name = bin2hex(random_bytes(16)) . '.' . $file_ext;
        $destination = $upload_path . $unique_name;

        if (move_uploaded_file($file_input['tmp_name'], $destination)) {
            // Retorna o caminho relativo para ser salvo no banco
            return 'public/uploads/' . $upload_subdir . '/' . $unique_name;
        }
    }
    
    // Se nenhum arquivo novo foi enviado, mantém o caminho antigo
    return $old_photo_path;
}
?>