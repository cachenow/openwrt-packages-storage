<?php
$configDir = '/etc/neko/config/';

ini_set('memory_limit', '256M');

if (!is_dir($configDir)) {
    mkdir($configDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['configFileInput'])) {
        $file = $_FILES['configFileInput'];
        $uploadFilePath = $configDir . basename($file['name']);

        if ($file['error'] === UPLOAD_ERR_OK) {
            if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
                echo '配置文件上传成功：' . htmlspecialchars(basename($file['name']));
            } else {
                echo '配置文件上传失败！';
            }
        } else {
            echo '上传错误：' . $file['error'];
        }
    }

    if (isset($_POST['deleteConfigFile'])) {
        $fileToDelete = $configDir . basename($_POST['deleteConfigFile']);
        if (file_exists($fileToDelete) && unlink($fileToDelete)) {
            echo '配置文件删除成功：' . htmlspecialchars(basename($_POST['deleteConfigFile']));
        } else {
            echo '配置文件删除失败！';
        }
    }

    if (isset($_POST['oldFileName'], $_POST['newFileName'], $_POST['fileType'])) {
        $oldFileName = basename($_POST['oldFileName']);
        $newFileName = basename($_POST['newFileName']);
    
        if ($_POST['fileType'] === 'config') {
            $oldFilePath = $configDir . $oldFileName;
            $newFilePath = $configDir . $newFileName;
        } else {
            echo '无效的文件类型';
            exit;
        }

        if (file_exists($oldFilePath) && !file_exists($newFilePath)) {
            if (rename($oldFilePath, $newFilePath)) {
                echo '文件重命名成功：' . htmlspecialchars($oldFileName) . ' -> ' . htmlspecialchars($newFileName);
            } else {
                echo '文件重命名失败！';
            }
        } else {
            echo '文件重命名失败，文件不存在或新文件名已存在。';
        }
    }

    if (isset($_POST['editFile']) && isset($_POST['fileType'])) {
        $fileToEdit = $configDir . basename($_POST['editFile']);
        $fileContent = '';
        $editingFileName = htmlspecialchars($_POST['editFile']);

        if (file_exists($fileToEdit)) {
            $handle = fopen($fileToEdit, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $fileContent .= htmlspecialchars($line);
                }
                fclose($handle);
            } else {
                echo '无法打开文件';
            }
        }
    }

    if (isset($_POST['saveContent'], $_POST['fileName'], $_POST['fileType'])) {
        $fileToSave = $configDir . basename($_POST['fileName']);
        $contentToSave = $_POST['saveContent'];
        file_put_contents($fileToSave, $contentToSave);
        echo '<p>文件内容已更新：' . htmlspecialchars(basename($fileToSave)) . '</p>';
    }

    if (isset($_GET['customFile'])) {
        $customDir = rtrim($_GET['customDir'], '/') . '/';
        $customFilePath = $customDir . basename($_GET['customFile']);
        if (file_exists($customFilePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($customFilePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($customFilePath));
            readfile($customFilePath);
            exit;
        } else {
            echo '文件不存在！';
        }
    }
}

$configFiles = scandir($configDir);

if ($configFiles !== false) {
    $configFiles = array_diff($configFiles, array('.', '..'));
} else {
    $configFiles = []; 
}

function formatSize($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $unit = 0;
    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }
    return round($size, 2) . ' ' . $units[$unit];
}
?>

<?php
$subscriptionPath = '/etc/neko/config/';
$dataFile = $subscriptionPath . 'subscription_data.json';

$message = "";
$defaultSubscriptions = [
    [
        'url' => '',
        'file_name' => 'config.json',
    ],
    [
        'url' => '',
        'file_name' => '',
    ],
    [
        'url' => '',
        'file_name' => '',
    ]
];

if (!file_exists($subscriptionPath)) {
    mkdir($subscriptionPath, 0755, true);
}

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode(['subscriptions' => $defaultSubscriptions], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$subscriptionData = json_decode(file_get_contents($dataFile), true);

if (!isset($subscriptionData['subscriptions']) || !is_array($subscriptionData['subscriptions'])) {
    $subscriptionData['subscriptions'] = $defaultSubscriptions;
}

if (isset($_POST['update_index'])) {
    $index = intval($_POST['update_index']);
    $subscriptionUrl = $_POST["subscription_url_$index"] ?? '';
    $customFileName = ($_POST["custom_file_name_$index"] ?? '') ?: 'config.json';

    if ($index < 0 || $index >= count($subscriptionData['subscriptions'])) {
        $message = "无效的订阅索引！";
    } elseif (empty($subscriptionUrl)) {
        $message = "订阅 $index 的链接为空！";
    } else {
        $subscriptionData['subscriptions'][$index]['url'] = $subscriptionUrl;
        $subscriptionData['subscriptions'][$index]['file_name'] = $customFileName;
        $finalPath = $subscriptionPath . $customFileName;

        $originalContent = file_exists($finalPath) ? file_get_contents($finalPath) : '';

        $ch = curl_init($subscriptionUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $fileContent = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($fileContent === false) {
            $message = "订阅 $index 无法下载文件。cURL 错误信息: " . $error;
        } else {
            $fileContent = str_replace("\xEF\xBB\xBF", '', $fileContent);

            $parsedData = json_decode($fileContent, true);
            if ($parsedData === null && json_last_error() !== JSON_ERROR_NONE) {
                file_put_contents($finalPath, $originalContent);
                $message = "订阅 $index 解析 JSON 数据失败！错误信息: " . json_last_error_msg();
            } else {
                if (isset($parsedData['inbounds'])) {
                    $newInbounds = [];

                    foreach ($parsedData['inbounds'] as $inbound) {
                        if (isset($inbound['type']) && $inbound['type'] === 'mixed' && $inbound['tag'] === 'mixed-in') {
                            $newInbounds[] = $inbound;
                        } elseif (isset($inbound['type']) && $inbound['type'] === 'tun') {
                            continue;
                        }
                    }

                    $newInbounds[] = [
                        "type" => "mixed",
                        "tag" => "SOCKS-in",
                        "listen" => "::",
                        "listen_port" => 4673
                    ];

                    $newInbounds[] = [
                        "auto_route" => true,
                        "domain_strategy" => "prefer_ipv4",
                        "endpoint_independent_nat" => true,
                        "inet4_address" => "172.19.0.1/30",
                        "inet6_address" => "2001:0470:f9da:fdfa::1/64",
                        "mtu" => 9000,
                        "sniff" => true,
                        "sniff_override_destination" => true,
                        "stack" => "system",
                        "strict_route" => true,
                        "type" => "tun"
                    ];

                    $parsedData['inbounds'] = $newInbounds;
                }

                if (isset($parsedData['experimental']['clash_api'])) {
                    $parsedData['experimental']['clash_api'] = [
                        "external_ui" => "/etc/neko/ui/",
                        "external_controller" => "0.0.0.0:9090",
                        "secret" => "Akun"
                    ];
                }

                $fileContent = json_encode($parsedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                if (file_put_contents($finalPath, $fileContent) === false) {
                    $message = "订阅 $index 无法保存文件到: $finalPath";
                } else {
                    $message = "订阅 $index 更新成功！文件已保存到: {$finalPath}，并成功解析和替换 JSON 数据。";
                }
            }
        }

        file_put_contents($dataFile, json_encode($subscriptionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
?>

 <!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sing-box文件管理器</title>
  <style>
        body {
            display: flex;
            flex-direction: column;
            margin: 0;
            min-height: 100vh;
            align-items: center;
            justify-content: flex-start;
            color: #E0E0E0; 
            background-color: red;
            font-family: Arial, sans-serif;
            background: url('/nekoclash/assets/img/1.jpg') no-repeat center center fixed; 
            background-size: cover; 
        }
        .container {
            display: flex;
            flex-direction: column;
            width: 90%;
            max-width: 900px; 
            padding: 20px;
            box-sizing: border-box;
            align-items: center;
            text-align: center;
            background: rgba(30, 30, 30, 0.8); 
            border-radius: 10px;
            margin-top: 50px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); 
        }
        h1, h2, .help-text {
            color: #00FF7F; 
        }
        .form-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .form-inline .form-control-file {
            flex: 1;
        }
        .file-upload-button {
            padding: 10px 20px;
            background-color: #03DAC6; 
            color: #121212;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .file-upload-button:hover {
            background-color: #018786; 
        }
        .list-group {
            width: 100%;
            margin-top: 20px;
            padding: 0;
            list-style: none;
        }
        .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #2C2C2C; 
            border-bottom: 1px solid #444;
        }
        .list-group-item a {
            color: #BB86FC; 
            text-decoration: none;
        }
        .button-group form {
            display: inline;
        }
        .button-group .btn {
            margin-left: 5px;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }
        .btn-danger {
            background-color: #CF6679; 
            color: #121212;
        }
        .btn-danger:hover {
            background-color: #B00020; 
        }
        .btn-success {
            background-color: #03DAC6; 
            color: #121212;
        }
        .btn-success:hover {
            background-color: #018786; 
        }
        .btn-warning {
            background-color: #F4B400; 
            color: #121212;
        }
        .btn-warning:hover {
            background-color: #C79400; 
        }
        .editor {
            height: 300px; 
            width: 90%; 
            min-width: 800px; 
            max-width: 800px; 
            background-color: #2C2C2C; 
            color: #E0E0E0; 
            padding: 15px; 
            border: 1px solid #444;
            border-radius: 5px;
            font-family: monospace;
            margin-top: 20px;
            overflow: auto; 
        }
        .nav-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .nav-buttons .btn {
            padding: 10px 20px;
            background-color: #03DAC6; 
            color: #121212;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }
        .nav-buttons .btn:hover {
            background-color: #018786; 
        }
        .input-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
        }
        .input-group label {
            margin-right: 10px;
            white-space: nowrap;
            color: #00FF7F;
        }
        .input-group input {
            flex: 1;
            padding: 5px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #2C2C2C;
            color: #E0E0E0;
        }
        button[name="update"] {
            background-color: #FF6347;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        button[name="update"]:hover {
            background-color: darkgreen;
        }
        .form-spacing {
            margin-bottom: 30px;
        }
        button {
        background-color: #4CAF50; 
        color: white;
        border: none;
        padding: 5px 10px; 
        text-align: center; 
        text-decoration: none; 
        display: inline-block; 
        cursor: pointer; 
        border-radius: 4px; 
        }
        button:hover {
        background-color: darkgreen; 
        }
    </style>
</head>
<body>
  <div class="container">
    <h1>Sing-box文件管理器</h1>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <h2>配置文件管理</h2>
    <form action="" method="post" enctype="multipart/form-data" class="form-inline">
        <input type="file" name="configFileInput" class="form-control-file" required>
        <button type="submit" class="file-upload-button">上传配置文件</button>
    </form>
    <ul class="list-group">
        <?php foreach ($configFiles as $file): ?>
            <?php $filePath = $configDir . $file; ?>
             <li class="list-group-item">
                <div class="list-group-item-content">
                    <a href="download.php?file=<?php echo urlencode($file); ?>"><?php echo htmlspecialchars($file); ?></a>
                    <span>(大小： <?php echo file_exists($filePath) ? formatSize(filesize($filePath)) : '文件不存在'; ?>)</span>
                </div>
                <div class="button-group">
                    <form action="" method="post">
                        <input type="hidden" name="deleteConfigFile" value="<?php echo htmlspecialchars($file); ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('确定要删除这个文件吗？');">删除</button>
                    </form>

                    <form action="" method="post">
                        <input type="hidden" name="oldFileName" value="<?php echo htmlspecialchars($file); ?>">
                        <input type="text" name="newFileName" placeholder="新文件名" class="form-control form-control-sm" required>
                        <input type="hidden" name="fileType" value="config">
                        <button type="submit" class="btn btn-success">重命名</button>
                    </form>

                    <form action="" method="post">
                        <input type="hidden" name="editFile" value="<?php echo htmlspecialchars($file); ?>">
                        <input type="hidden" name="fileType" value="config">
                        <button type="submit" class="btn btn-warning">编辑</button>
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if (isset($fileContent)): ?>
        <?php if (isset($_POST['editFile'])): ?>
            <?php $fileToEdit = $configDir . basename($_POST['editFile']); ?>
            <h2 class="mt-5">编辑文件: <?php echo $editingFileName; ?></h2>
            <p>最后更新日期: <?php echo date('Y-m-d H:i:s', filemtime($fileToEdit)); ?></p>
            <form action="" method="post">
               <textarea name="saveContent" id="editor" rows="15" class="editor"><?php echo $fileContent; ?></textarea><br>
               <input type="hidden" name="fileName" value="<?php echo htmlspecialchars($_POST['editFile']); ?>">
               <input type="hidden" name="fileType" value="<?php echo htmlspecialchars($_POST['fileType']); ?>">
               <button type="submit" class="btn btn-primary mt-2" onclick="checkJsonSyntax()">保存内容</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
<h1>Sing-box订阅程序</h1>
<p class="help-text">
    您可以输入 Sing-box 订阅链接或手动上传配置文件，配置切换时，您可自行命名，方便管理。<br>
</p>

<?php if ($message): ?>
    <p><?php echo nl2br(htmlspecialchars($message)); ?></p>
<?php endif; ?>

<h2>订阅链接设置</h2>
<form method="post">
    <?php for ($i = 0; $i < 3; $i++): ?>
        <div class="input-group">
            <label for="subscription_url_<?php echo $i; ?>">订阅链接 <?php echo $i + 1; ?>:</label>
            <input type="text" name="subscription_url_<?php echo $i; ?>" id="subscription_url_<?php echo $i; ?>" value="<?php echo htmlspecialchars($subscriptionData['subscriptions'][$i]['url'] ?? ''); ?>">
        </div>
        <div class="input-group">
            <label for="custom_file_name_<?php echo $i; ?>">自定义文件名 <?php echo ($i === 0) ? '(固定为 config.json)' : ':'; ?></label>
            <input type="text" name="custom_file_name_<?php echo $i; ?>" id="custom_file_name_<?php echo $i; ?>" value="<?php echo htmlspecialchars($subscriptionData['subscriptions'][$i]['file_name'] ?? ($i === 0 ? 'config.json' : '')); ?>" <?php echo ($i === 0) ? 'readonly' : ''; ?>>
        </div>
        <button type="submit" name="update_index" value="<?php echo $i; ?>">更新订阅 <?php echo $i + 1; ?></button>
        <hr>
    <?php endfor; ?>
</form>
    <div class="nav-buttons">
        <a href="javascript:history.back()" class="btn">返回上一级菜单</a>
        <a href="/nekoclash/upload_sb.php" class="btn">返回当前菜单</a>
        <a href="/nekoclash/configs.php" class="btn">返回配置菜单</a>
        <a href="/nekoclash" class="btn">返回主菜单</a>
    </div>
</div>
</body>
</html>
