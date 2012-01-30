<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
//
//		File:
//			file.php
//		Description:
//			This class manipulates files and driectories.
//		Actions:
//			1) manipulate files
//			2) read directories
//		Date:
//			Added on July 30th 2007 for ternstyle (tm) v2.0.0
//		Version:
//			2.3
//		Copyright:
//			Copyright (c) 2010 Matthew Praetzel.
//		License:
//			This software is licensed under the terms of the GNU Lesser General Public License v3
//			as published by the Free Software Foundation. You should have received a copy of of
//			the GNU Lesser General Public License along with this software. In the event that you
//			have not, please visit: http://www.gnu.org/licenses/gpl-3.0.txt
//
////////////////////////////////////////////////////////////////////////////////////////////////////

/****************************************Commence Script*******************************************/

if(!class_exists('fileClass')) {
//
class fileClass {

	var $magic = '/usr/share/file/magic';
	
	var $ma = array('image/gif'=>'image','image/jpeg'=>'image','image/png'=>'image',
					'text/plain'=>'file','text/rtf'=>'file','application/msword'=>'file','application/pdf'=>'file',
					'application/x-shockwave-flash'=>'flash');
	
	function fileClass() {
		global $ftp_host,$ftp_host_directory,$ftp_username,$ftp_password;
		$this->h = $ftp_host;
		$this->d = $ftp_host_directory;
		$this->u = $ftp_username;
		$this->p = $ftp_password;
	}
	function contents($d) {
		return file_get_contents($d);
	}
	function mimeType($d) {
		if(function_exists('finfo_open')) {
			$f = finfo_open(FILEINFO_MIME,$this->magic);
			return finfo_file($f,$d);
		}
		else {
			return mime_content_type($d);
		}
	}
	function cleanType($m) {
		$p = strpos($m,';');
		if($p !== false) {
			return substr($m,0,$p);
		}
		return $m;
	}
	function isMime($d,$a=false,$r=true) {
		$t = $this->cleanType($this->mimetype($d));
		$a = empty($a) ? $this->ma : $a;
		foreach($a as $k => $v) {
			if($k == $t) {
				if($r === true) {
					return true;
				}
				elseif($r == 'type') {
					return $v;
				}
				elseif($r == 'mime') {
					return $t;
				}
			}
		}
		return false;
	}
	function isWritableDirectory($d) {
		if(!is_dir($d)) {
			if(!@mkdir($d,0777)) {
				return false;
			}
		}
		if(!is_writable($d)) {
			if(!@chmod($d,0777)) {
				return false;
			}
		}
		return true;
	}
	function directoryList($b) {
	
		$b = array_merge(array(
			'dir'	=>	'/',
			'rec'	=>	false,
			'flat'	=>	true,
			'depth'	=>	'*'
		),$b);
		$b['dir'] = substr($b['dir'],-1) != '/' ? $b['dir'].'/' : $b['dir'];
		
		if(@is_dir($b['dir'])) {
			$a = array();
			if($p = @opendir($b['dir'])) {
				while(($f = @readdir($p)) !== false) {
					$n = $b['dir'].$f;
					if(is_file($n)) {
						$a[$f] = $n;
					}
					elseif(is_dir($n) and $f != '.' and $f != '..' and $b['rec'] and ($b['depth'] == '*' or $b['depth'] != 1)) {
						$x = array_merge($b,array('dir'=>$n.'/','depth'=>$b['depth'] !== '*' ? ($b['depth']-1) : $b['depth']));
						if($b['flat']) {
							$a = array_merge($a,fileClass::directoryList($x));
						}
						else {
							$a[$n] = fileClass::directoryList($x);
						}
					}
				}
				closedir($p);
				return $a;
			}
		}
		return false;
	}
	function createFile($n,$c,$d) {
		if(!is_dir($d)) {
			if(!@mkdir($d,0777)) {
				return 'directory does not exist';
			}
		}
		if(!is_writable($d)) {
			if(!@chmod($d,0777)) {
				return 'directory is not writable';
			}
		}
		$h = @fopen($d . '/' . $n,'w');
		if(!$h) {
			return 'unable to create file';
		}
		if(@fwrite($h,$c)) {
			fclose($h);
			return true;
		}
		return false;
	}
	function deleteFile($d) {
		if(!@unlink($d)) {
			return 'unable to delete file';
		}
		return true;
	}
	function uploadFile($f,$n,$d) {
		$a = $this->cleanDir($d.'/'.$n);
		if(@is_uploaded_file($f)) {
			if(move_uploaded_file($f,$a)) {
				return true;
			}
			else {
				$c = @ftp_connect($this->h);
				$l = @ftp_login($c,$this->u,$this->p);
				@ftp_chdir($c,$d);
				if(@ftp_put($c,$a,$f,FTP_BINARY)) {
					return true;
				}
			}
		}
		return false;
	}
	function renameFile($d,$f,$n) {
		$f = $this->cleanDir($d.'/'.$f);
		$n = $this->cleanDir($d.'/'.$n);
		if(is_file($f)) {
			if(rename($f,$n)) {
				return true;
			}
			else {
				$c = @ftp_connect($this->h);
				$l = @ftp_login($c,$this->u,$this->p);
				@ftp_chdir($c,$d);
				if(@ftp_rename($c,$f,$n)) {
					return true;
				}
			}
		}
		return false;
	}
	function cleanDir($d) {
		return str_replace('//','/',$d);
	}

}
//
}

/****************************************Terminate Script******************************************/
?>