<?php
if (!defined('init_ajax'))
{	
	header('HTTP/1.0 404 not found');
	exit;
}

if (!$CURUSER->isOnline())
{
	echo '@AjaxError@, <br>You must be logged in.';
	die;
}

if (isset($_POST['checkFile']))
{
	$file = isset($_POST['file']) ? $_POST['file'] : false;
	
	if (file_exists($config['RootPath'] . $file))
	{
		echo 1;
	}
	else
	{
		echo 0;
	}
}
else
{
	$folder = $config['RootPath'] . '/admin/tempUploads/'; //use "/" at the end
	$maxsize = 2097152;
	$maxDemensions = 580;
	
	$error = '@AjaxError@, <br>';
	
	if (isset($_FILES["file"]))
	{
		//All file names should be lower case
		$file_title = strtolower($_FILES["file"]['name']);
		//replace white spaces
		$file_title = str_replace(' ', '_', $file_title);
		//find where the image extension begins and remove it
		$file_title = substr($file_title, 0, strrpos($file_title, '.'));
		//strip everything that isn't a safe basename character (kills double extensions, path separators, control chars)
		$file_title = preg_replace('/[^a-z0-9_\-]/', '', (string)$file_title);
		if ($file_title === '') {
			$file_title = 'image';
		}

		//cryptographically random uniquifier so attacker cannot predict the final URL
		$uniqer = bin2hex(random_bytes(8));

		//Get Unique Name
		$file_name = $uniqer . "_" . $file_title;

		//check the filesize
		if(filesize($_FILES["file"]['tmp_name']) > $maxsize)
		{
			$error .= 'The file you are uploading is too big, 2mb max.<br>';
		}

		if(!empty($_FILES["file"]['error']))
		{
			switch($_FILES["file"]['error'])
			{
				case '1':
					$error .= 'The uploaded file exceeds the upload_max_filesize directive in php.ini<br>';
					break;
				case '2':
					$error .= 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form<br>';
					break;
				case '3':
					$error .= 'The uploaded file was only partially uploaded<br>';
					break;
				case '4':
					$error .= 'No file was uploaded.<br>';
					break;
				case '6':
					$error .= 'Missing a temporary folder<br>';
					break;
				case '7':
					$error .= 'Failed to write file to disk<br>';
					break;
				case '8':
					$error .= 'File upload stopped by extension<br>';
					break;
				case '999':
				default:
					$error .= 'No error code avaiable<br>';
			}
		}

        $imageInfo = @getimagesize($_FILES["file"]['tmp_name']);
        if ($imageInfo === false) {
            echo $error . 'File is not a valid image.<br>';
            return;
        }
        list($width, $height, $type, $attr) = $imageInfo;
        $mime = image_type_to_mime_type($type);

        //cross-check via finfo so getimagesize spoofing alone isn't enough
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $finfoMime = $finfo ? finfo_file($finfo, $_FILES["file"]['tmp_name']) : false;
            if ($finfo) finfo_close($finfo);
            if ($finfoMime !== false && $finfoMime !== $mime) {
                echo $error . 'File Type not allowed.<br>';
                return;
            }
        }

		//strict whitelist; bail immediately on anything else
        $extByMime = array(
            'image/jpeg' => '.jpg',
            'image/pjpeg' => '.jpg',
            'image/jpg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif',
        );
        if (!isset($extByMime[$mime])) {
            echo $error . 'File Type not allowed.<br>';
            return;
        }
        $file_name = $file_name . $extByMime[$mime];

		$uploadfile = $folder . $file_name;
		
		//if we got no errors
		if ($error == '@AjaxError@, <br>')
		{
			//Move the file from the stored location to the new location
			if (!move_uploaded_file($_FILES["file"]['tmp_name'], $uploadfile))
			{
				$error .= "Cannot upload the file '".$uploadfile."'"; //Show error if any.
			
				if(!file_exists($folder))
				{
					$error .= " : Folder don't exist.";
				}
				elseif(!is_writable($folder))
				{
					$error .= " : Folder not writable.";
				}
				elseif(!is_writable($uploadfile))
				{
					$error .= " : File not writable.";
				}			
			}
			else
			{
				//if ($width > $maxDemensions or $height > $maxDemensions)
				//{
				//	$objImage = new ImageManipulation($uploadfile);
 				//	if ($objImage->imageok)
				//	{
				//		$objImage->setJpegQuality(100);
  				//		$objImage->resize($maxDemensions);
				//		$file_name = 'resized_' . $file_name;
  				//		$objImage->save($folder . $file_name);
 				//	}
				//}
				//upload success
				echo $file_name;
				//null the errors
				$error = '';
			}
		}

		if ($error != '')
		{
			echo $error;
		}
		
	}
}