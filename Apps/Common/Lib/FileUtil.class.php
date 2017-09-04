<?php
namespace Common\Lib;

class FileUtil {
    /**
     * 建立文件夹
     *
     * @param    string $aimUrl
     * @return   viod
     */
	function createDir($aimUrl) {
		$aimUrl = str_replace('', '/', $aimUrl);
		$aimDir = '';
		$arr = explode('/', $aimUrl);
		foreach ($arr as $str) {
			$aimDir .= $str . '/';
			if (!file_exists($aimDir)) {
				mkdir($aimDir);
			}
		}
	}

    /**
     * 建立文件
     *
     * @param    string    $aimUrl
     * @param    boolean    $overWrite 该参数控制是否覆盖原文件
     * @return   boolean
     */
    function createFile($aimUrl, $overWrite = false) {
		if (file_exists($aimUrl) && $overWrite == false) {
			return false;
		} elseif (file_exists($aimUrl) && $overWrite == true) {
			$this->unlinkFile($aimUrl);
		}
		$aimDir = dirname($aimUrl);
		$this->createDir($aimDir);
		touch($aimUrl);
		return true;
	}
    
	/**
     * 读取文件
     *
     * @param    string    $file_dir 读取文件
     * @return   string
     */
	function readFile($file_dir) {
		$fp = fopen($file_dir, "r");
		$content = fread($fp, filesize($file_dir));//读文件
		fclose($fp);
		return $content;
	}
    
	/**
     * 写入文件
     *
     * @param    string    $file 目标文件
     * @param    string    $source 写入内容
     * @return   boolean
     */
	function writeFile($file, $source) {
        if(!file_exists($file)) $this->createFile($file);
		if($fp = fopen($file, 'w')) {
			$filesource = fwrite($fp, $source);
			fclose($fp);
			return $filesource;
		}
		else {
			return false;
		}
	}
	
	function makeDir($path) {
		mkdir($path);
	}

    /**
     * 移动文件夹
     *
     * @param    string    $oldDir
     * @param    string    $aimDir
     * @param    boolean    $overWrite 该参数控制是否覆盖原文件
     * @return   boolean
     */
	function moveDir($oldDir, $aimDir, $overWrite = false) {
		$aimDir = str_replace('', '/', $aimDir);
		$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
		$oldDir = str_replace('', '/', $oldDir);
		$oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
		if (!is_dir($oldDir)) {
			return false;
		}
		if (!file_exists($aimDir)) {
			$this->createDir($aimDir);
		}
		@$dirHandle = opendir($oldDir);
		if (!$dirHandle) {
			return false;
		}
		while(false !== ($file = readdir($dirHandle))) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			if (!is_dir($oldDir.$file)) {
				$this->moveFile($oldDir . $file, $aimDir . $file, $overWrite);
			} else {
				$this->moveDir($oldDir . $file, $aimDir . $file, $overWrite);
			}
		}
		closedir($dirHandle);
		return rmdir($oldDir);
	}

    /**
     * 移动文件
     *
     * @param    string    $fileUrl
     * @param    string    $aimUrl
     * @param    boolean    $overWrite 该参数控制是否覆盖原文件
     * @return   boolean
     */
	function moveFile($fileUrl, $aimUrl, $overWrite = false) {
		if (!file_exists($fileUrl)) {
			return false;
		}
		if (file_exists($aimUrl) && $overWrite = false) {
			return false;
		} elseif (file_exists($aimUrl) && $overWrite = true) {
			$this->unlinkFile($aimUrl);
		}
		$aimDir = dirname($aimUrl);
		$this->createDir($aimDir);
		rename($fileUrl, $aimUrl);
		return true;
	}

    /**
     * 删除文件夹
     *
     * @param    string    $aimDir
     * @return   boolean
     */
	function unlinkDir($aimDir) {
		$aimDir = str_replace('', '/', $aimDir);
		$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir.'/';
		if (!is_dir($aimDir)) {
			return false;
		}
		$dirHandle = opendir($aimDir);
		while(false !== ($file = readdir($dirHandle))) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			if (!is_dir($aimDir.$file)) {
				$this->unlinkFile($aimDir . $file);
			} else {
				$this->unlinkDir($aimDir . $file);
			}
		}
		closedir($dirHandle);
		return rmdir($aimDir);
	}

    /**
     * 删除文件
     *
     * @param    string    $aimUrl
     * @return   boolean
     */
	function unlinkFile($aimUrl) {
		if (file_exists($aimUrl)) {
			unlink($aimUrl);
			return true;
		} else {
			return false;
		}
	}

    /**
     * 复制文件夹
     *
     * @param    string    $oldDir
     * @param    string    $aimDir
     * @param    boolean    $overWrite 该参数控制是否覆盖原文件
     * @return   boolean
     */
	function copyDir($oldDir, $aimDir, $overWrite = false) {
		$aimDir = str_replace('', '/', $aimDir);
		$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir.'/';
		$oldDir = str_replace('', '/', $oldDir);
		$oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir.'/';
		if (!is_dir($oldDir)) {
			return false;
		}
		if (!file_exists($aimDir)) {
			$this->createDir($aimDir);
		}
		$dirHandle = opendir($oldDir);
		while(false !== ($file = readdir($dirHandle))) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			if (!is_dir($oldDir . $file)) {
				$this->copyFile($oldDir . $file, $aimDir . $file, $overWrite);
			} else {
				$this->copyDir($oldDir . $file, $aimDir . $file, $overWrite);
			}
		}
		return closedir($dirHandle);
	}

    /**
     * 复制文件
     *
     * @param    string    $fileUrl
     * @param    string    $aimUrl
     * @param    boolean    $overWrite 该参数控制是否覆盖原文件
     * @return   boolean
     */
	function copyFile($fileUrl, $aimUrl, $overWrite = false) {
		if (!file_exists($fileUrl)) {
			return false;
		}
		if (file_exists($aimUrl) && $overWrite == false) {
			return false;
		} elseif (file_exists($aimUrl) && $overWrite == true) {
			$this->unlinkFile($aimUrl);
		}
		$aimDir = dirname($aimUrl);
		$this->createDir($aimDir);
		copy($fileUrl, $aimUrl);
		return true;
	}
    
    //全类型文件下载
    function downFile($sFilePath) {
        if(file_exists($sFilePath)) {
            $aFilePath = explode("/", str_replace("\\", "/", $sFilePath), $sFilePath);
            //$sFileName = $aFilePath[count($aFilePath) - 1];
            $sFileName = basename($sFilePath);
            $nFileSize = filesize ($sFilePath);
            header("Content-Disposition: attachment; filename=" . $sFileName);
            header("Content-Length: " . $nFileSize);
            header("Content-type: application/octet-stream");
            readfile($sFilePath);
        }
        else {
            return false;
        }
    }
    
    function viewDir ($directory, $ext, $children = false) {
        $dirs = array();
        if (is_dir($directory)) {
            $handle = opendir($directory);
            while ($file = readdir($handle)) {
                $subdir = $directory . '/' .$file;
                if(is_dir($subdir) && !in_array($file, array('.', '..'))) $dirs[] = $file;
                if ($file != '.' && $file !='..' && is_dir($subdir) && $children) {
                    viewDir($subdir, $ext);
                } else if( $file != '.' && $file != '..') {
                    $fileInfo = pathinfo($subdir);
                    $fileExt = $fileInfo['extension'];
                    if ($fileExt == $ext) {
                        //echo $directory.'/'.$file.'<br />';
                    }
                }
            }
            closedir($handle);
            return $dirs;
        }
    }
    
    function viewFile ($directory, $ext, $children = false, $rule = false) {
        $dirs = array();
        if (is_dir($directory)) {
            $handle = opendir($directory);
            while ($file = readdir($handle)) {
                $subdir = $directory . '/' .$file;
                if(!in_array($file, array('.', '..'))) {
                    if( ($rule && preg_match($rule, $file)) || !$rule ) {
                        $dirs[] = $file;
                    }
                }
                if ($file != '.' && $file !='..' && $children) {
                    viewDir($subdir, $ext);
                } else if( $file != '.' && $file != '..') {
                    $fileInfo = pathinfo($subdir);
                    $fileExt = $fileInfo['extension'];
                    if ($fileExt == $ext) {
                        //echo $directory.'/'.$file.'<br />';
                    }
                }
            }
            closedir($handle);
            return $dirs;
        }
    }
    
}