<?php
header('Access-Control-Allow-Origin:*');

// v1.1.0
// exit("fix");

$type = @$_POST["type"];
$path = @$_POST["path"];
if (strpos($path, '/../') !== false || $path == "/..") {
    exit("[]");
}
$content = @$_POST["content"];
$path = 'docs' . $path;

if ($type == "test") {
    exit("online");
} elseif ($type == "getDir") {
    if (!is_dir($path)) {
        exit("[]");
    }

    $path_dir_list = array();
    $path_file_list = array();
    
    
    $dir_handle = opendir($path);
    
    
    while (($m_file = readdir($dir_handle)) !== false) {
        if ($m_file == '..' || $m_file == '.') continue;
        $m_path = $path . '/' . $m_file;
        if (is_dir($m_path)) {
            // icon
            $m_icon = '';
            if (file_exists($m_path . '/._icon')) {
                $m_icon = '<i class="mdui-list-item-icon mdui-icon material-icons">' . file_get_contents($m_path . '/._icon') . '</i>';
            } elseif (file_exists($m_path . '/._icon.link')) {
                $m_icon = '<div class="mdui-list-item-avatar"><img src="' . file_get_contents($m_path . '/._icon.link') . '"/></div>';
            } elseif (file_exists($m_path . '/._icon.ico')) {
                $m_icon = '._icon.ico';
            } elseif (file_exists($m_path . '/._icon.png')) {
                $m_icon = '._icon.png';
            } elseif (file_exists($m_path . '/._icon.jpg')) {
                $m_icon = '._icon.jpg';
            } elseif (file_exists($m_path . '/._icon.gif')) {
                $m_icon = '._icon.gif';
            } elseif (file_exists($m_path . '/._icon.webp')) {
                $m_icon = '._icon.webp';
            }
            
            // result
            array_push($path_dir_list, array(
                'type' => 'dir',
                'name' => $m_file,
                'icon' => $m_icon
            ));
        } else {
            array_push($path_file_list, array(
                'type' => 'file',
                'name' => $m_file,
                'time' => filemtime($m_path),
                'size' => filesize($m_path)
            ));
        }
    }
    
    $parh_list = array_merge($path_dir_list, $path_file_list);
    foreach ($parh_list as $key => $value) {
        $parh_list[$key]['name'] = urlencode($parh_list[$key]['name']);
    }
    
    echo urldecode(json_encode($parh_list));
} elseif ($type == "getFile") {
    if (!file_exists($path)) {
        exit("没有这样的文件或目录");
    }
    
    $mExt = explode('.', $path);
    $mExt = strtolower(array_pop($mExt));
    if (
        false
        || $mExt == 'png' || $mExt == 'jpg' || $mExt == 'gif' || $mExt == 'webp'
        || $mExt == 'zip' || $mExt == '7z' || $mExt == 'tar' || $mExt == 'gzip' || $mExt == 'bzip2' || $mExt == 'gz' || $mExt == 'bz2'
        || $mExt == 'apk' || $mExt == 'apks' || $mExt == 'aab'
        || $mExt == 'doc' || $mExt == 'docx' || $mExt == 'ppt' || $mExt == 'xls' || $mExt == 'xlsx' || $mExt == 'pptx' || $mExt == 'pdf'
    ) {
        exit("This is a binary file");
    }
    
    // 如果不想展示PHP文件, 请解除该注释
    // if ($mExt == 'php') {
    //     exit("This is a PHP file");
    // }
    
    echo file_get_contents($path);
} elseif ($type == "search") {
    $searchPaths = array();
    $content = strtolower($content);
    $mResult = searchPath($path);
    
    foreach ($mResult as $key => $value) {
        $mResult[$key]['name'] = urlencode($mResult[$key]['name']);
        $mResult[$key]['path'] = urlencode($mResult[$key]['path']);
    }
    
    echo urldecode(json_encode($mResult));
}

function searchPath($dir) {
    global $content;
    global $searchPaths;
    if(@$handle = opendir($dir)) {
        while(($file = readdir($handle)) !== false) {
            if($file != ".." && $file != ".") {
                $allPath = $dir . "/" . $file;
                $searchIndex = strpos(strtolower($file), $content);
                if ($searchIndex !== false) {
                    // $searchPaths[count($searchPaths)] = $dir . "/" . $file;
                    array_push($searchPaths, array(
                        'name' => $file,
                        'path' => substr($allPath, 4),
                        'parent' => empty(substr(is_dir($allPath) ? $allPath : $dir, 4)) ? "/" : substr(is_dir($allPath) ? $allPath : $dir, 4),
                        'is_Dir' => is_dir($allPath),
                        'index' => $searchIndex
                    ));
                }
                if(is_dir($allPath)) { //如果是子文件夹，进行递归
                    searchPath($allPath);
                }
            }
        }
        closedir($handle);
    }
    return $searchPaths;
}
?>