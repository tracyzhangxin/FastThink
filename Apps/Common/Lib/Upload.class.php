<?php
namespace Common\Lib;

class Upload
{
	/*文件名 上传时原文件名称*/
	public $fileName;

	/*文件类型*/
	public $fileType;

	/*文件后缀*/
	public $fileExt;

	/*文件大小*/
	public $fileSize;

	/*上传文件提示信息*/
	public $message;

	/*只允许的类型*/
	public $allowType = array();

	/*只允许的后缀*/
	public $allowExt = array();

	/*允许的文件大小*/
	public $allowSize = 2147483648; //byte
	
	/*受限制的类型*/
	public $limitType = array();

	/*受限制的后缀*/
	public $limitExt = array('php', 'exe', 'html', 'htm', 'xhtml', 'chm', 'sh', 'tar', 'py', 'zip');
	
	/*错误代码*/
	private $errorCode;

	/*多文件上传*/
	private $multi;

	/*次序*/
	private $order;

	public function createDir($aimUrl) {
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
	
	public function uploadify($fileData = 'Filedata', $targetFolder = './Public/Upload', $oriFileName = true, $thumb = false, $detail = false) {
		$verifyToken = md5('unique_salt' . $_POST['timestamp']);

        if (!empty($_FILES)) {
		//if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
			$tempFile = $_FILES[$fileData]['tmp_name'];
			//$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
			$targetPath = rtrim($targetFolder, '/') . '/' . date('Ym');
			
			// Validate the file type
			$fileTypes = array('jpg', 'jpeg', 'gif', 'png', 'doc', 'pdf', 'xls', 'xlsx', 'docx'); // File extensions
			$fileParts = pathinfo($_FILES[$fileData]['name']);
			
			if (in_array($fileParts['extension'], $fileTypes)) {
				$uploadResult = $this->uploadOneFile($fileData, $targetPath, $oriFileName, $thumb);
                if($detail) {
                    if($uploadResult) {
                        $finalPath = ltrim($targetPath . '/' . $uploadResult, '.');
                        $imgInfo = getimagesize($finalPath);
                        $arr = array(
                            'path' => $finalPath,
                            'width' => $imgInfo[0],
                            'height' => $imgInfo[1]
                        );
                        return json_encode($arr);
                    }else {
                        return false;
                    }
                }else {
                    return $uploadResult ? ltrim($targetPath . '/' . $uploadResult, '.') : false;
                }
			} else {
				return false;
			}
		}
    }

	/**
	 *上传文件,成功返回目标文件名，失败返回false
	 *
	 *@name string 控件名称
	 *@destination string 目标路径
	 *@filename string or boolean目标文件名 true:保留原名 false:程序自动设置 string:自定义名字
	 *@return string or false 
	 */
	public function uploadOneFile($name, $destination = './', $filename = false, $thumb = false) {
		if(!is_dir($destination)) $this->createDir($destination);
		$upload_name = $_FILES[$name]["name"];
		$file_tmp_name = $_FILES[$name]["tmp_name"];

		$place = strrpos($upload_name, '.');

		$this->multi = false;
		$this->fileName = substr($upload_name, 0, $place);
		$this->fileExt =  substr($upload_name, $place + 1);

		$this->fileType = $_FILES[$name]["type"];
		$this->fileSize = $_FILES[$name]["size"];
		$this->errorCode = $_FILES[$name]["error"];

		//$filename = $filename === true ? $this->fileName : ($filename === false ?  md5($upload_name.time()) : $filename);
        //$filename = $filename === true ? iconv("UTF-8","GB2312", $this->fileName) : ($filename === false ?  date('YmdHis') . floor(microtime() * 1000) : $filename);
        $filename = $filename === true ? $this->fileName : ($filename === false ?  date('YmdHis') . floor(microtime() * 1000) : $filename);
		$dest_filename = trim($filename . '.' . $this->fileExt, '.');
		$dest_file = $destination . "/" . $dest_filename;
		$dest_file = strtr($dest_file, '//', '/');

		if($this->checkUpload() && $this->copyFile($file_tmp_name, $dest_file)) {
            if( $thumb && isset($thumb['width']) && isset($thumb['height']) ) {
                $isCut = isset($thumb['cut']) && $thumb['cut']? 1 : 0;
                $targetFileName = $destination .'/' . trim($filename . '_thumb.' . $this->fileExt, '.');
                $imgInfo = getimagesize($dest_file);
                if($isCut) {
                    if( isset($thumb['cut_min_width']) && $thumb['cut_min_width'] && isset($thumb['cut_min_height']) && $thumb['cut_min_height'] ) {
                        if( $thumb['cut_min_width'] > $imgInfo[0] || $thumb['cut_min_height'] > $imgInfo[1] ) {
                            $isCut = 0;
                        }
                    }
                }
                $this->img2thumb($dest_file, $targetFileName, $thumb['width'], $thumb['height'], $isCut);
            }
            //$dest_filename = iconv('GB2312', 'UTF-8', $dest_filename);
			return $dest_filename;
		}
		return false;
	}

	/**
	 *上传文件,成功返回目标文件名数组，失败返回false
	 *
	 *@name string 控件名称
	 *@destination string 目标路径
	 *@filename array or boolean目标文件名 true:保留原名 false:程序自动设置 array:自定义名字顺序与控件顺序一致
	 *@trans boolean 事务上传 true:全部成功才返回成功，false:一个成功就返回成功
	 *@return array or false
	 */
	public function uploadMultiFile($name, $destination = './', $filename = false, $trans = true) {
		$upload_names = $_FILES[$name]["name"];
		$upload_list = array();
		$dest_list = array();
		$is_true = false;

		if(is_array ($upload_names)) {
			$this->multi = true;
			foreach ($upload_names as $k => $upload_name) {
				$place = strrpos($upload_name, '.');
				$file_tmp_names[$k] = $_FILES[$name]["tmp_name"][$k];

				$this->fileName[$k] = substr($upload_name, 0, $place);
				$this->fileExt[$k] =  substr($upload_name, $place + 1);

				$this->fileType[$k] = $_FILES[$name]["type"][$k];
				$this->fileSize[$k] = $_FILES[$name]["size"][$k];
				$this->errorCode[$k] = $_FILES[$name]["error"][$k];
				$this->order = $k;

				$to_name = $filename === true ? $this->fileName[$k] : 
				($filename === false || empty($filename[$k]) ?  uniqid(rand(), true) : $filename);
				$dest_filename = trim($to_name . '.' . $this->fileExt[$k], '.');

				if($this->checkUpload()) {
					$upload_list[$k] = array('from' => $file_tmp_names[$k], 'to' => $dest_filename);
				} 	
				else {
					if ($trans) return false;
				}
			}

			foreach ($upload_list as $k => $list) {
				$dest_file = $destination . "/" . $list['to'];
				$dest_file = str_replace('//', '/', $dest_file);

				$this->order = $k;

				if($this->copyFile($list['from'], $dest_file)) {
					$is_true = true;
					$dest_list[$k] = $list['to'];
				}
				else {
					if ($trans) {
						for($i = $k - 1; $i >=0; $i --) {
							$this->unlink($destination . '/' . $dest_list[$i]);
						}
						return false;
					}
				}
			}
		}
		else {
			return $this->uploadOneFile($name, $destination, $filename);
		}
		
		if ($is_true) return $dest_list;

		return false;
	}

	public function unlink($file) {
		$file = str_replace('//', '/', $file);
		return !empty($file) && @unlink($file);
	}

	private function copyFile($orig_file, $dest_file) {
		if(@move_uploaded_file($orig_file, $dest_file) || @copy($orig_file, $dest_file)) {
			$this->setMsg('文件上传成功');
			return true;
		}
		
		$this->setMsg('文件上传失败');
		return false;
	}

	private function checkUpload() {
		if($this->multi) {
			$order = $this->order;
			$filename = $this->fileName[$order];
			$error_code = $this->errorCode[$order];
			$file_type = $this->fileType[$order];
			$file_ext = $this->fileExt[$order];
			$file_size = $this->fileSize[$order];
		}
		else {
			$filename = $this->fileName;
			$error_code = $this->errorCode;
			$file_type = $this->fileType;
			$file_ext = $this->fileExt;
			$file_size = $this->fileSize;		
		}

		if(empty($filename)) {
			$this->setMsg('没有文件被上传');
			return false;
		}

		$bool = true;
		switch ($error_code) {
			case 0:
				//其值为 0，没有错误发生，文件上传成功。 
				break;
			case 1:
				$this->setMsg('上传的文件超过了服务器限制的值');
				$bool = false;
				break;
			case 2:
				$this->setMsg('上传文件的大小超过了指定选项指定的值');
				$bool = false;
				break;
			case 3:
				$this->setMsg('文件只有部分被上传');
				$bool = false;
				break;
			case 4:
				$this->setMsg('没有文件被上传');
				$bool = false;
				break;
			case 6:
				$this->setMsg('找不到临时文件夹');
				$bool = false;
				break;
			case 7:
				$this->setMsg('文件写入失败');
				$bool = false;
				break;
		}

		if(!$bool) 
			return false;

		if(!empty($this->allowType)) {
			if(!in_array($file_type, $this->allowType)) {
				$this->setMsg('不支持此文件类型，请重新选择');
				return false;
			}
		}

		if(!empty($this->allowExt)) {
			if(!in_array($file_ext, $this->allowExt)) {
				$this->setMsg('不支持的后缀名，请重新选择');
				return false;
			}
		}

		if(in_array($file_type, $this->limitType)) {
			$this->setMsg('不支持此文件类型，请重新选择');
			return false;
		}

		if(in_array($file_ext, $this->limitExt)) {
			$this->setMsg('不支持的后缀名，请重新选择');
			return false;
		}

		if($file_size > $this->allowSize) {
			$this->setMsg('文件容量太大');
			return false;
		}
		
		return true;
	}

	private function setMsg($msg) {
		if ($this->multi)
			$this->message[$this->order] = $msg;
		else
			$this->message = $msg;
	}

    /**
     * 上传图片后生成缩略图
     * @param string     源图绝对完整地址{带文件名及后缀名}
     * @param string     目标图绝对完整地址{带文件名及后缀名}
     * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
     * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
     * @param int        是否裁切{宽,高必须非0}
     * @param int/float  缩放{0:不缩放, 0<this<1:缩放到相应比例(此时宽高限制和裁切均失效)}
     * @return boolean
     */
    public function img2thumb($src_img, $dst_img, $width = 75, $height = 75, $cut = 0, $proportion = 0) {
        if(!is_file($src_img)) {
            return false;
        }
        $ot = pathinfo($dst_img, PATHINFO_EXTENSION);
        $otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
        $srcinfo = getimagesize($src_img);
        $src_w = $srcinfo[0];
        $src_h = $srcinfo[1];
        $type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
        $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);

        $dst_h = $height;
        $dst_w = $width;
        $x = $y = 0;

        /**
         * 缩略图不超过源图尺寸（前提是宽或高只有一个）
         */
        if(($width> $src_w && $height> $src_h) || ($height> $src_h && $width == 0) || ($width> $src_w && $height == 0)) {
            $proportion = 1;
        }
        if($width> $src_w) {
            $dst_w = $width = $src_w;
        }
        if($height> $src_h) {
            $dst_h = $height = $src_h;
        }

        if(!$width && !$height && !$proportion) {
            return false;
        }
        if(!$proportion) {
            if($cut == 0) {
                if($dst_w && $dst_h) {
                    if($dst_w/$src_w> $dst_h/$src_h) {
                        $dst_w = $src_w * ($dst_h / $src_h);
                        $x = 0 - ($dst_w - $width) / 2;
                    }
                    else {
                        $dst_h = $src_h * ($dst_w / $src_w);
                        $y = 0 - ($dst_h - $height) / 2;
                    }
                }
                else if($dst_w xor $dst_h) {
                    if($dst_w && !$dst_h) {  //有宽无高
                        $propor = $dst_w / $src_w;
                        $height = $dst_h  = $src_h * $propor;
                    }
                    else if(!$dst_w && $dst_h) {  //有高无宽
                        $propor = $dst_h / $src_h;
                        $width  = $dst_w = $src_w * $propor;
                    }
                }
            }
            else {
                if(!$dst_h) {  //裁剪时无高
                    $height = $dst_h = $dst_w;
                }
                if(!$dst_w) {  //裁剪时无宽
                    $width = $dst_w = $dst_h;
                }
                $propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
                $dst_w = (int)round($src_w * $propor);
                $dst_h = (int)round($src_h * $propor);
                $x = ($width - $dst_w) / 2;
                $y = ($height - $dst_h) / 2;
            }
        }
        else {
            $proportion = min($proportion, 1);
            $height = $dst_h = $src_h * $proportion;
            $width  = $dst_w = $src_w * $proportion;
        }

        $src = $createfun($src_img);
        $dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);

        if(function_exists('imagecopyresampled')) {
            imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        else {
            imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        $otfunc($dst, $dst_img);
        imagedestroy($dst);
        imagedestroy($src);
        return true;
    }
}