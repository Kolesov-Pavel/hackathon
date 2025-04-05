<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$dataFile = 'hm.json';
$imageDir = 'gisimage/';

// Создаем директорию для изображений, если ее нет
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0777, true);
}

// Получаем данные запроса
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'save':
            // Сохранение данных в JSON
            file_put_contents($dataFile, json_encode($input['data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            echo json_encode(['success' => true]);
            break;
            
        case 'upload':
        default:
            // Загрузка изображения
            if (isset($_FILES['photo'])) {
                $id = $_POST['id'] ?? uniqid();
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = "{$id}_" . uniqid() . ".{$ext}";
                $targetPath = $imageDir . $filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                    echo json_encode([
                        'success' => true,
                        'filename' => $filename
                    ]);
                } else {
                    throw new Exception('Ошибка загрузки файла');
                }
            } else {
                throw new Exception('Файл не получен');
            }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
