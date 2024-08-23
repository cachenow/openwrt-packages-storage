<?php

include './cfg.php';


$themeDir = "$neko_www/assets/theme";
$tmpPath = "$neko_www/lib/selected_config.txt";
$arrFiles = array();
$arrFiles = glob("$themeDir/*.css");

for($x=0;$x<count($arrFiles);$x++) $arrFiles[$x] = substr($arrFiles[$x], strlen($themeDir)+1);

if(isset($_POST['themechange'])){
    $dt = $_POST['themechange'];
    shell_exec("echo $dt > $neko_www/lib/theme.txt");
    $neko_theme = $dt;
}
if(isset($_POST['fw'])){
    $dt = $_POST['fw'];
    if ($dt == 'enable') shell_exec("uci set neko.cfg.new_interface='1' && uci commit neko");
    if ($dt == 'disable') shell_exec("uci set neko.cfg.new_interface='0' && uci commit neko");
}
$fwstatus=shell_exec("uci get neko.cfg.new_interface");
?>
<?php
function getSingboxVersion() {
    $singBoxPath = '/usr/bin/sing-box'; 
    $command = "$singBoxPath version 2>&1";
    exec($command, $output, $returnVar);
    
    if ($returnVar === 0) {
        foreach ($output as $line) {
            if (strpos($line, 'version') !== false) {
                $parts = explode(' ', $line);
                return end($parts);
            }
        }
    }
    
    return '未知版本';
}

$singBoxVersion = getSingboxVersion();
?>
<!doctype html>
<html lang="en" data-bs-theme="<?php echo substr($neko_theme,0,-4) ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings - Neko</title>
    <link rel="icon" href="./assets/img/favicon.png">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/theme/<?php echo $neko_theme ?>" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet">
    <script type="text/javascript" src="./assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="./assets/js/feather.min.js"></script>
    <script type="text/javascript" src="./assets/js/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="./assets/js/neko.js"></script>
  </head>
  <body>
<head>
    <meta charset="UTF-8">
   <head>
    <meta charset="UTF-8">
      <title>双击显示图标</title>
    <style>
        .container-sm {
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <div class="container-sm text-center col-8">
        <img src="./assets/img/neko.png" class="img-fluid mb-5" style="display: none;">
    </div>

    <script>
        function toggleImage() {
            var img = document.querySelector('.container-sm img');
            var btn = document.getElementById('showHideButton');
            if (img.style.display === 'none') {
                img.style.display = 'block';
                btn.innerText = '隐藏图标';
            } else {
                img.style.display = 'none';
                btn.innerText = '显示图标';
            }
        }

        function hideIcon() {
            var img = document.querySelector('.container-sm img');
            var btn = document.getElementById('showHideButton');
            if (img.style.display === 'block') {
                img.style.display = 'none';
                btn.innerText = '显示图标';
            }
        }

        document.body.ondblclick = function() {
            toggleImage();
        };
    </script>

    <div class="container-sm container-bg text-center callout border border-3 rounded-4 col-11">
        <div class="row">
            <a href="./" class="col btn btn-lg">首页</a>
            <a href="./dashboard.php" class="col btn btn-lg">面板</a>
            <a href="./configs.php" class="col btn btn-lg">配置</a>
            <a href="#" class="col btn btn-lg">设定</a>
        </div>
    </div>
    <div class="container text-left p-3">
    <div class="container container-bg border border-3 rounded-4 col-12 mb-4">
        <h2 class="text-center p-2 mb-3">主题设定</h2>
            <form action="settings.php" method="post">
                <div class="container text-center justify-content-md-center">
                    <div class="row justify-content-md-center">
                        <div class="col mb-3 justify-content-md-center">
                          <select class="form-select" name="themechange" aria-label="themex">
                                <option selected>Change Theme (<?php echo $neko_theme ?>)</option>
                                <?php foreach ($arrFiles as $file) echo "<option value=\"".$file.'">'.$file."</option>" ?>
                          </select>
                        </div>
                        <div class="row justify-content-md-center">
                            <div class="col justify-content-md-center mb-3">
                              <input class="btn btn-info" type="submit" value="更改主题">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
<h2 class="text-center p-2 mb-3">软体资讯</h2>
<table class="table table-borderless mb-3">
    <tbody>
        <tr>
            <td class="col-2">自动重新载入防火墙</td>
            <form action="settings.php" method="post">
                <td class="d-grid">
                    <div class="btn-group col" role="group" aria-label="ctrl">
                        <button type="submit" name="fw" value="enable" class="btn btn<?php if($fwstatus==1) echo "-outline" ?>-success <?php if($fwstatus==1) echo "disabled" ?> d-grid">启用</button>
                        <button type="submit" name="fw" value="disable" class="btn btn<?php if($fwstatus==0) echo "-outline" ?>-danger <?php if($fwstatus==0) echo "disabled" ?> d-grid">停用</button>
                    </div>
                </td>
            </form>
        </tr>
        <tr>
<td class="col-2">客户端版本</td>
<td class="col-4">
    <div class="form-control text-center" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <div style="font-family: monospace; flex-grow: 1; text-align: left;">
            <?php
            $package_name = "luci-app-neko"; 
            $installed_version = trim(shell_exec("opkg list-installed | grep $package_name | awk '{print $3}'"));
            echo htmlspecialchars($installed_version ?: '-'); 
            ?>
        </div>
        <button id="updateButton" class="button" style="flex-shrink: 0;">更新到最新版本</button>
    </div>
    <div id="logOutput"></div>
</td>
</tr>
<tr>
    <td class="col-2">Sing-box核心版本</td>
    <td class="col-4">
        <div class="form-control text-center" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <div style="font-family: monospace; flex-grow: 1; text-align: left;" id="singBoxCorever">
                <?php echo htmlspecialchars($singBoxVersion); ?>
            </div>
            <div style="display: flex; gap: 10px; flex-shrink: 0;">
                <button id="updateSingboxButton" class="button">更新Singbox内核</button>
            </div>
        </div>
    </td>
</tr>
    <td class="col-2">Mihomo核心版本</td>
    <td class="col-4">
        <div class="form-control text-center" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <div style="font-family: monospace; flex-grow: 1; text-align: left;" id="corever">-</div>
            <div style="display: flex; gap: 10px; flex-shrink: 0;">              
                <button id="updateNekoButton" class="button">切换NeKo内核</button>
                <button id="updateCoreButton" class="button">切换Mihomo内核</button>
            </div>
        </div>
    </td>
</tr>
<tr>
 </tbody>
 </table>
<style>
    .button {
        background-color: #4169E1; 
        color: white; 
        border: none; 
        height: 40px; 
        padding: 10px 20px; 
        border-radius: 5px; 
        cursor: pointer; 
        transition: background-color 0.3s; 
        text-align: center; 
    }

    .button:hover {
        background-color: #FF00FF; 
    }

    #updateCoreButton:hover {
        background-color: darkgreen; 
    }

    #updateNekoButton:hover {
        background-color: darkorange; 
    }
</style>

<script>
    document.getElementById('updateButton').addEventListener('click', function() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_script.php', true); 
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        document.getElementById('logOutput').innerHTML = '开始下载更新...';

        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('logOutput').innerHTML += '\n更新完成！';
                document.getElementById('logOutput').innerHTML += '\n' + xhr.responseText; 
            } else {
                document.getElementById('logOutput').innerHTML += '\n发生错误：' + xhr.statusText;
            }
        };

        xhr.send(); 
    });

    document.getElementById('updateSingboxButton').addEventListener('click', function() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'singbox.php', true); 
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        document.getElementById('logOutput').innerHTML = '开始下载核心更新...';

        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('logOutput').innerHTML += '\n核心更新完成！';
                document.getElementById('logOutput').innerHTML += '\n' + xhr.responseText; 
            } else {
                document.getElementById('logOutput').innerHTML += '\n发生错误：' + xhr.statusText;
            }
        };

        xhr.send(); 
    });

    document.getElementById('updateCoreButton').addEventListener('click', function() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'core.php', true); 
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        document.getElementById('logOutput').innerHTML = '开始下载核心更新...';

        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('logOutput').innerHTML += '\n核心更新完成！';
                document.getElementById('logOutput').innerHTML += '\n' + xhr.responseText; 
            } else {
                document.getElementById('logOutput').innerHTML += '\n发生错误：' + xhr.statusText;
            }
        };

        xhr.send(); 
    });


    document.getElementById('updateNekoButton').addEventListener('click', function() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'neko.php', true); 
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        document.getElementById('logOutput').innerHTML = '开始下载核心更新...';

        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('logOutput').innerHTML += '\n核心更新完成！';
                document.getElementById('logOutput').innerHTML += '\n' + xhr.responseText; 
            } else {
                document.getElementById('logOutput').innerHTML += '\n发生错误：' + xhr.statusText;
            }
        };

        xhr.send(); 
    });
</script>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NekoClash</title>
    <link rel="stylesheet" href="/www/nekoclash/assets/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .feature-box {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #000000;
            border-radius: 8px;
        }
        .feature-box h6 {
            margin-bottom: 15px;
        }
        .table-container {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #000000;
            border-radius: 8px;
        }
        .table {
            table-layout: fixed;
            width: 100%;
        }
        .table td, .table th {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .table thead th {
            background-color: transparent;
            color: #000000;
        }
        .btn-outline-secondary {
            border-color: transparent;
            color: #000000;
        }
        .btn-outline-secondary:hover {
            background-color: transparent;
            color: #000000;
        }
        .footer {
            padding: 15px 0;
            background-color: transparent;
            color: #000000;
        }
        .footer p {
            margin: 0;
        }
        .link-box {
            border: 1px solid #000000;
            border-radius: 8px;
            padding: 10px;
            display: block;
            text-align: center;
            width: 100%;
            box-sizing: border-box; 
            transition: background-color 0.3s ease; 
        }
        .link-box a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #000000;
        }
        .link-box:hover {
            background-color: #87CEEB; 
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center mb-4">关于 NekoClash</h2>
        <div class="feature-box text-center">
            <h5>NekoClash</h5>
            <p>NekoClash 是一款精心设计的 Mihomo 代理工具，专为家庭用户打造，旨在提供简洁而强大的代理解决方案。基于 PHP 和 BASH 技术，NekoClash 将复杂的代理配置简化为直观的操作体验，让每个用户都能轻松享受高效、安全的网络环境。</p>
        </div>

        <h5 class="text-center mb-4">核心特点</h5>
        <div class="row">
            <div class="col-md-4 mb-4 d-flex">
                <div class="feature-box text-center flex-fill">
                    <h6>简化配置</h6>
                    <p>采用用户友好的界面和智能配置功能，轻松实现 Mihomo 代理的设置与管理。</p>
                </div>
            </div>
            <div class="col-md-4 mb-4 d-flex">
                <div class="feature-box text-center flex-fill">
                    <h6>优化性能</h6>
                    <p>通过高效的脚本和自动化处理，确保最佳的代理性能和稳定性。</p>
                </div>
            </div>
            <div class="col-md-4 mb-4 d-flex">
                <div class="feature-box text-center flex-fill">
                    <h6>无缝体验</h6>
                    <p>专为家庭用户设计，兼顾易用性与功能性，确保每个家庭成员都能便捷地使用代理服务。</p>
                </div>
            </div>
        </div>

<h5 class="text-center mb-4">工具信息</h5>
<div class="d-flex justify-content-center">
    <div class="table-container">
        <table class="table table-borderless mb-5">
            <tbody>
                <tr class="text-center">
                    <td>SagerNet</td>
                    <td>MetaCubeX</td>
                </tr>
                <tr class="text-center">
                    <td>
                        <div class="link-box">
                            <a href="https://github.com/SagerNet/sing-box" target="_blank">Sing-box</a>
                        </div>
                    </td>
                    <td>
                        <div class="link-box">
                            <a href="https://github.com/MetaCubeX/mihomo" target="_blank">Mihomo</a>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
    <h5 class="text-center mb-4">外部链接</h5>
        <div class="table-container">
            <table class="table table-borderless mb-5">
                <tbody>
                    <tr class="text-center">
                        <td>Github</td>
                        <td>Github</td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            <div class="link-box">
                                <a href="https://github.com/nosignals/neko" target="_blank">nosignals</a>
                            </div>
                        </td>
                        <td>
                            <div class="link-box">
                                <a href="https://github.com/Thaolga/luci-app-nekoclash" target="_blank">Thaolga</a>
                            </div>
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td>Telegram</td>
                        <td>MetaCubeX</td>
                    </tr>
                    <tr class="text-center">
                        <td>
                            <div class="link-box">
                                <a href="https://t.me/+J55MUupktxFmMDgx" target="_blank">Telegram</a>
                            </div>
                        </td>
                        <td>
                            <div class="link-box">
                                <a href="https://github.com/MetaCubeX" target="_blank">METACUBEX</a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <footer class="footer text-center">
            <p>©2024 signdev</p>
        </footer>
    </div>

    <script src="/www/nekoclash/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
