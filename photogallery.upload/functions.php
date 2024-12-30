<?
if (!function_exists("ImageCreateFromBMP"))
{
	function ImageCreateFromBMP($filename)
	{
		//Ouverture du fichier en mode binaire
		if (! $f1 = fopen($filename,"rb")) return FALSE;
		
		//1 : Chargement des ent?tes FICHIER
		$FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
		if ($FILE['file_type'] != 19778) return FALSE;
		
		//2 : Chargement des ent?tes BMP
		$BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
		     '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
		     '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
		$BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
		
		if ($BMP['size_bitmap'] == 0) 
			$BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
		$BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
		$BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
		$BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] = 4-(4*$BMP['decal']);
		if ($BMP['decal'] == 4) 
			$BMP['decal'] = 0;
		
		//3 : Chargement des couleurs de la palette
		$PALETTE = array();
		if ($BMP['colors'] < 16777216)
		{
			$PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
		}
		
		//4 : Cr?ation de l'image
		$IMG = fread($f1,$BMP['size_bitmap']);
		$VIDE = chr(0);
		
		$res = imagecreatetruecolor($BMP['width'],$BMP['height']);
		$P = 0;
		$Y = $BMP['height']-1;
		while ($Y >= 0)
		{
			$X=0;
			while ($X < $BMP['width'])
			{
				if ($BMP['bits_per_pixel'] == 24)
					$COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
				elseif ($BMP['bits_per_pixel'] == 16)
				{  
					$COLOR = unpack("n",substr($IMG,$P,2));
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 8)
				{  
					$COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 4)
				{
					$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
					if (($P*2)%2 == 0) 
						$COLOR[1] = ($COLOR[1] >> 4) ; 
					else 
						$COLOR[1] = ($COLOR[1] & 0x0F);
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 1)
				{
					$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
					if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
					elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
					elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
					elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
					elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
					elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
					elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
					elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				else
					return FALSE;
				imagesetpixel($res,$X,$Y,$COLOR[1]);
				$X++;
				$P += $BMP['bytes_per_pixel'];
			}
			$Y--;
			$P+=$BMP['decal'];
		}
		
		//Fermeture du fichier
		fclose($f1);
		
		return $res;
	}	
}

if(!function_exists("__UnEscape"))
{
	function __UnEscape(&$item, $key)
	{
		if(is_array($item))
			array_walk($item, '__UnEscape');
		else
		{
			$item = $GLOBALS["APPLICATION"]->ConvertCharset($item, "UTF-8", SITE_CHARSET);
		}
	}
}
if(!function_exists("__Escape"))
{
	function __Escape(&$item, $key)
	{
		if(is_array($item))
			array_walk($item, '__Escape');
		else
		{
			$item = $GLOBALS["APPLICATION"]->ConvertCharset($item, SITE_CHARSET, "UTF-8");
		}
	}
}
if (!function_exists("__ResizeImage"))
{
	/**
	 * Enter description here...
	 *
	 * $File array
	 * 
	 * $arRealFile array     
	 * 		[tmp_name] => C:\WINDOWS\TEMP\php5AC.tmp - full path to image
	 * 		[basename] => clock9
	 * 		[image] => image soure
	 */
	function __ResizeImage(&$File, &$arRealFile, $Sight, $iStrongResize=1, $arWaterMark=array())
	{
		static $bGD2 = false;
		static $bGD2Initial = false;

		if (!$bGD2Initial && function_exists("gd_info"))
		{
			$arGDInfo = gd_info();
			$bGD2 = ((strpos($arGDInfo['GD Version'], "2.") !== false) ? true : false);
			$bGD2Initial = true;
		}
		
		$imageInput = false;
		$bNeedCreatePicture = false;
		$picture = false;
		$iStrongResize = intVal($iStrongResize);
		$src = array(
			"x" => 0, "y" => 0, 
			"width" => 0, "height" => 0);
		$dst = array(
			"x" => 0, "y" => 0, 
			"width" => 0, "height" => 0);
		if (empty($arRealFile["pathinfo"]))
		{
			$arRealFile["pathinfo"] = array(
				"extension" => substr($arRealFile["name"], strrpos($arRealFile["name"], ".") + 1));
		}
		
		CheckDirPath($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/");
		
		if (CopyDirFiles($arRealFile["tmp_name"], $File["tmp_name"]))
		{
			if (!is_array($arWaterMark))
				$arWaterMark = array();
			if (!$arRealFile["image"])
			{
				switch (strToLower($arRealFile["pathinfo"]["extension"]))
				{
					case 'gif':
						$imageInput = @imagecreatefromgif($arRealFile["tmp_name"]);
					break;
					case 'png':
						$imageInput = @imagecreatefrompng($arRealFile["tmp_name"]);
					break;
					case 'bmp':
						$imageInput = @imagecreatefrombmp($arRealFile["tmp_name"]);
					break;
					default:
						$imageInput = @imagecreatefromjpeg($arRealFile["tmp_name"]);
					break;
				}
				$arRealFile["image"] = $imageInput;
				$arRealFile["width"] = intVal(@imagesx($imageInput));
				$arRealFile["height"] = intVal(@imagesy($imageInput));
			}
			if ($arRealFile["width"] > 0 && $arRealFile["height"] > 0)
			{
				if ($Sight["width"] !== false && $Sight["height"] !== false)
				{
					switch ($iStrongResize)
					{
						case 2:
							$bNeedCreatePicture = true;
							$width = max($arRealFile["width"], $arRealFile["height"]);
							$height = min($arRealFile["width"], $arRealFile["height"]);
							
							$iResizeCoeff = max($Sight["width"]/$width, $Sight["height"]/$height);
							
							$dst["width"] = intVal($Sight["width"]);
							$dst["height"] = intVal($Sight["height"]);
							
							if ($iResizeCoeff > 0)
							{
								$src["x"] = ((($arRealFile["width"]*$iResizeCoeff - $Sight["width"])/2)/$iResizeCoeff);
								$src["y"] = ((($arRealFile["height"]*$iResizeCoeff - $Sight["height"])/2)/$iResizeCoeff);
								$src["width"] = $Sight["width"] / $iResizeCoeff;
								$src["height"] = $Sight["height"] / $iResizeCoeff;
							}
							
							break;
						default:
							if ($iStrongResize <= 0)
							{
								$width = max($arRealFile["width"], $arRealFile["height"]);
								$height = min($arRealFile["width"], $arRealFile["height"]);
							}
							else
							{
								$width = $arRealFile["width"];
								$height = $arRealFile["height"];
							}
							$ResizeCoeff["width"] = $Sight["width"]/$width;
							$ResizeCoeff["height"] = $Sight["height"]/$height;
							
							$iResizeCoeff = min($ResizeCoeff["width"], $ResizeCoeff["height"]);
							$iResizeCoeff = ((0 < $iResizeCoeff) && ($iResizeCoeff < 1) ? $iResizeCoeff : 1);
							$bNeedCreatePicture = ($iResizeCoeff != 1 ? true : false);
							
							$dst["width"] = intVal($iResizeCoeff * $arRealFile["width"]);
							$dst["height"] = intVal($iResizeCoeff * $arRealFile["height"]);
							
							$src["x"] = 0;
							$src["y"] = 0;
							$src["width"] = $arRealFile["width"];
							$src["height"] = $arRealFile["height"];
							break;
					}
				}
				else 
				{
					$src = array(
						"x" => 0, "y" => 0, 
						"width" => $arRealFile["width"], "height" => $arRealFile["height"]);
					$dst = array(
						"x" => 0, "y" => 0, 
						"width" => $arRealFile["width"], "height" => $arRealFile["height"]);

					$Sight["width"] = $arRealFile["width"];
					$Sight["height"] = $arRealFile["height"];
				}
				
				$bNeedCreatePicture = (!empty($arWaterMark["text"]) ? true : $bNeedCreatePicture);
				
				if ($bNeedCreatePicture)
				{
					if ($bGD2)
					{
						$picture = ImageCreateTrueColor($dst["width"], $dst["height"]);
						imagecopyresampled($picture, $arRealFile["image"], 
							0, 0, $src["x"], $src["y"], 
							$dst["width"], $dst["height"], $src["width"], $src["height"]);
					}
					else
					{
						$picture = ImageCreate($dst["width"], $dst["height"]);
						imagecopyresized($picture, $arRealFile["image"], 
							0, 0, $src["x"], $src["y"], 
							$dst["width"], $dst["height"], $src["width"], $src["height"]);
					}
				}
				
				if (!empty($arWaterMark["text"]) && !empty($arWaterMark["path_to_font"]) && $dst["width"] >= $arWaterMark["min_size_picture"]/* && 
					$dst["height"] >= $arWaterMark["min_size_picture"]*/ && file_exists($arWaterMark["path_to_font"]))
				{
					$arColor = array("red" => 255, "green" => 255, "blue" => 255);
					$sColor = preg_replace("/[^a-z0-9]/is", "", $arWaterMark["color"]);
					if (strLen($sColor) == 6)
					{
						$arColor = array(
							"red" => hexdec(substr($sColor, 0, 2)), 
							"green" => hexdec(substr($sColor, 2, 2)), 
							"blue" => hexdec(substr($sColor, 4, 2)));
						$barColor = array(
							"red" => substr($sColor, 0, 2), 
							"green" => substr($sColor, 2, 2), 
							"blue" => substr($sColor, 4, 2));
					}
					if ($arWaterMark["size"] == "big")
					{
						$iSize = $Sight["width"] * 0.07;
						$iSize = ($iSize > 75 ? 75 : $iSize);
					}
					elseif ($arWaterMark["size"] == "small")
					{
						$iSize = $Sight["width"] * 0.03;
						$iSize = ($iSize > 35 ? 35 : $iSize);
					}
					else 
					{
						$iSize = $Sight["width"] * 0.05;
						$iSize = ($iSize > 55 ? 55 : $iSize);
					}
					if ($iSize * strLen($arWaterMark["text"])*0.7 > $dst["width"])
					{
						$iSize = intVal($dst["width"] / (strLen($arWaterMark["text"])*0.7));
					}
					
					if ($iSize < 8)
						$iSize = 8;
						
					$watermark_position = array(
						"x" => 5,
						"y" => $iSize + 5,
						"width" => (strLen($arWaterMark["text"])*0.7 + 1)*$iSize,
						"height" => $iSize);
					if (!$bGD2)
					{
						$watermark_position["width"] = strLen($arWaterMark["text"])*imagefontwidth(5);
						$watermark_position["height"] = imagefontheight(5);
					}

					if (substr($arWaterMark["position"], 0, 1) == "m")
					{
						$watermark_position["y"] = intVal(($dst["height"] - $watermark_position["height"]) / 2);
						if ($watermark_position["y"] <= 0)
							$watermark_position["y"] = $watermark_position["height"];
					}
					elseif (substr($arWaterMark["position"], 0, 1) == "b")
					{
						$watermark_position["y"] = intVal(($dst["height"] - $watermark_position["height"]));
						if ($watermark_position["y"] <= 0)
							$watermark_position["y"] = $watermark_position["height"];
					}
					
					if (substr($arWaterMark["position"], 1, 1) == "c")
					{
						$watermark_position["x"] = intVal(($dst["width"] - $watermark_position["width"]) / 2);
						if ($watermark_position["x"] <= 0)
							$watermark_position["x"] = 5;
					}
					elseif (substr($arWaterMark["position"], 1, 1) == "r")
					{
						$watermark_position["x"] = intVal(($dst["width"] - $watermark_position["width"]));
						if ($watermark_position["x"] <= 0)
							$watermark_position["x"] = 5;
					}
					
					$text_color = imagecolorallocate($picture, $arColor["red"], $arColor["green"], $arColor["blue"]);
					if ($bGD2)
					{
						if (function_exists("utf8_encode"))
						{
							$text = $GLOBALS["APPLICATION"]->ConvertCharset($arWaterMark["text"], SITE_CHARSET, "UTF-8");
							if ($arWaterMark["use_copyright"] != "N")
								$text = utf8_encode("&#169;").$text;
						}
						else 
						{
							$text = $GLOBALS["APPLICATION"]->ConvertCharset($arWaterMark["text"], SITE_CHARSET, "UTF-8");
							if ($arWaterMark["use_copyright"] != "N")
								$text = "©".$text;
						}
						
						imagettftext($picture, $iSize, 0, $watermark_position["x"], $watermark_position["y"], $text_color, $arWaterMark["path_to_font"], $text);
					}
					else 
					{
						imagestring($picture, 3, $watermark_position["x"], $watermark_position["y"], $arWaterMark["text"], $text_color);
					}
				}
				
				if ($bNeedCreatePicture)
				{
					switch (strToLower($arRealFile["pathinfo"]["extension"]))
					{
						case 'gif':
							@imagegif($picture, $File["tmp_name"]);
						break;
						case 'png':
							@imagepng($picture, $File["tmp_name"]);
						break;
						default:
							if ($arRealFile["pathinfo"]["extension"] == "bmp")
								$File["name"] = preg_replace("/(\.)bmp$/is", "$1jpg", $File["name"]);
							imagejpeg($picture, $File["tmp_name"]);
						break;
					}
					@imagedestroy($picture);
					$File["size"] = filesize($File["tmp_name"]);
				}
			}
		}
	}
}
?>