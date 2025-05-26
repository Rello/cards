<?php
// generate_image.php

function generateImage($cardData, $userDir) {
	$backgroundImage = loadBackgroundImage('background.png');
	$fontPath = __DIR__ . '/fonts/arial.ttf';
	$textElements = defineTextElements($cardData);

	placeUploadedImage($backgroundImage, $cardData, $userDir);
	addIconsToImage($backgroundImage);
	writeTextOnImage($backgroundImage, $textElements, $fontPath);

	return saveImageToVariable($backgroundImage);
}

function addIconsToImage($backgroundImage) {
	$baseDir = __DIR__ . '/assets/';
	$icons = [
		['path' => $baseDir . 'life-icon.png', 'position' => ['x' => 250, 'y' => 0], 'size' => ['width' => 200, 'height' => 200]],
		['path' => $baseDir . 'Attack-icon2.png', 'position' => ['x' => 20, 'y' => 400], 'size' => ['width' => 200, 'height' => 200]],
		['path' => $baseDir . 'Damage-icon.png', 'position' => ['x' => 200, 'y' => 400], 'size' => ['width' => 200, 'height' => 200]],
	];

	foreach ($icons as $icon) {
		$iconImage = loadUploadedImage($icon['path']);
		if ($iconImage) {
			$resizedIcon = resizeImage($iconImage, $icon['size']['width'], $icon['size']['height']);
			imagecopy($backgroundImage, $resizedIcon, $icon['position']['x'], $icon['position']['y'], 0, 0, $icon['size']['width'], $icon['size']['height']);
			imagedestroy($resizedIcon);
			imagedestroy($iconImage);
		}
	}
}

function defineTextElements($cardData) {
	$rarityColors = [
		'Gewöhnlich'    => [200, 200, 200], // grau
		'Ungewöhnlich'  => [  0, 200,   0], // grün
		'Episch'      => [  0,   0, 200], // blau
		'Heroisch'      => [128,   0, 128], // lila
		'Legendär' => [255, 215,   0], // gold
	];

	$rarityColor = $rarityColors[$cardData['seltenheitsform']] ?? [0, 0, 0];

	return [
		'name' => [
			'text' => $cardData['name'],
			'position' => ['x' => 30, 'y' => 50],
			'font_size' => 24,
			'color' => [0, 0, 0],
		],
		'seltenheitsform' => [
			'text' => $cardData['seltenheitsform'] ,
			'position' => ['x' => 100, 'y' => 90],
			'font_size' => 20,
			'color' => $rarityColor,
		],
		'kosten' => [
			'text' => $cardData['kosten'] . " ",
			'position' => ['x' => 250, 'y' => 450],
			'font_size' => 20,
			'color' => [166, 247, 187],
		],
		'leben' => [
			'text' => $cardData['leben'] ,
			'position' => ['x' => 350, 'y' => 105],
			'font_size' => 20,
			'color' => [32, 59, 9],
		],
		'superangriff' => [
			'text' =>  $cardData['superangriff'],
			'position' => ['x' => 123, 'y' => 510],
			'font_size' => 20,
			'color' => [163, 128, 13],
		],
		'schaden' => [
			'text' =>  $cardData['schaden'],
			'position' => ['x' => 303, 'y' => 510],
			'font_size' => 20,
			'color' => [255, 0, 0],
		],
	];
}



// other stuff

function loadBackgroundImage($path) {
	return imagecreatefrompng($path);
}

function writeTextOnImage($image, $textElements, $fontPath) {
	foreach ($textElements as $element) {
		$colorRGB = $element['color'];
		$textColor = imagecolorallocate($image, $colorRGB[0], $colorRGB[1], $colorRGB[2]);

		// Calculate the bounding box of the text
		$bbox = imagettfbbox($element['font_size'], 0, $fontPath, $element['text']);
		$textWidth = $bbox[2] - $bbox[0];

		// Adjust x-coordinate to center the text
		$centeredX = round($element['position']['x'] - ($textWidth / 2));

		// Write the text on the image
		imagettftext($image, $element['font_size'], 0, $centeredX, $element['position']['y'], $textColor, $fontPath, $element['text']);
	}
}

function loadUploadedImage($imagePath) {
	$imageInfo = getimagesize($imagePath);
	$mimeType = $imageInfo['mime'];

	switch ($mimeType) {
		case 'image/png':
			return imagecreatefrompng($imagePath);
		case 'image/jpeg':
			return imagecreatefromjpeg($imagePath);
		case 'image/webp':
			return function_exists('imagecreatefromwebp') ? imagecreatefromwebp($imagePath) : null;
		default:
			return null;
	}
}

function placeUploadedImage($backgroundImage, $cardData, $userDir) {
	if (!empty($cardData['bild']) && file_exists($userDir . '/' . $cardData['bild'])) {
		$uploadedImage = loadUploadedImage($userDir . '/' . $cardData['bild']);
		if ($uploadedImage) {
			// 1. Größe anpassen
			$resized = resizeImage($uploadedImage, 280, 283);
			// 2. Ecken abrunden
			$rounded = roundImageCorners($resized, 20);
			// 3. Auf den Hintergrund kopieren und Rahmen zeichnen

			$textElements = defineTextElements($cardData);
			$frameColor = defineTextElements($cardData)['seltenheitsform']['color'];

			positionAndCopyImage($backgroundImage, $rounded, 150, $frameColor);
			imagedestroy($rounded);
			imagedestroy($resized);
			imagedestroy($uploadedImage);
		}
	}
}

function resizeImage($image, $newWidth, $newHeight) {
	return imagescale($image, $newWidth, $newHeight);
}

function positionAndCopyImage($backgroundImage, $uploadedImage, $imgPosY, $frameColor) {
	$bgWidth = imagesx($backgroundImage);
	$newWidth = imagesx($uploadedImage);
	$newHeight = imagesy($uploadedImage);
	$imgPosX = ($bgWidth - $newWidth) / 2;

	imagecopy($backgroundImage, $uploadedImage, $imgPosX, $imgPosY, 0, 0, $newWidth, $newHeight);
	drawRoundedRectangle($backgroundImage, $imgPosX, $imgPosY, $newWidth, $newHeight, $frameColor);
}

function drawRoundedRectangle($image, $x, $y, $width, $height, $frameColor) {
	$borderColor = imagecolorallocate(
		$image,
		$frameColor[0],
		$frameColor[1],
		$frameColor[2]
	);	$borderThickness = 5;
	$cornerRadius = 20;

	imageRoundedRectangle($image, $x, $y, $x + $width, $y + $height, $cornerRadius, $borderColor, $borderThickness);
}

function saveImageToVariable($image) {
	ob_start();
	imagepng($image);
	$imageData = ob_get_contents();
	ob_end_clean();
	imagedestroy($image);
	return $imageData;
}

function imageRoundedRectangle($image, $x1, $y1, $x2, $y2, $radius, $color, $thickness = 1) {
	// Setze die Linienstärke
	imagesetthickness($image, $thickness);

	// Seiten zeichnen
	imageline($image, $x1 + $radius, $y1, $x2 - $radius, $y1, $color); // Oben
	imageline($image, $x1 + $radius, $y2, $x2 - $radius, $y2, $color); // Unten
	imageline($image, $x1, $y1 + $radius, $x1, $y2 - $radius, $color); // Links
	imageline($image, $x2, $y1 + $radius, $x2, $y2 - $radius, $color); // Rechts

	// Ecken zeichnen
	imagearc($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color); // Oben links
	imagearc($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color); // Oben rechts
	imagearc($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color); // Unten links
	imagearc($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color); // Unten rechts

	// Linienstärke zurücksetzen (optional)
	imagesetthickness($image, 1);
}

/**
 * Gibt ein neues Bild zurück, bei dem das Quellbild an den Ecken abgerundet (transparent) ist.
 *
 * @param resource $src       GD-Image-Ressource
 * @param int      $radius    Radius der Abrundung in Pixeln
 * @return resource           Neues GD-Image mit abgerundeten Ecken
 */
function roundImageCorners($src, $radius) {
	$w = imagesx($src);
	$h = imagesy($src);

	// Maske anlegen (voll transparent)
	$mask = imagecreatetruecolor($w, $h);
	imagesavealpha($mask, true);
	$transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
	imagefill($mask, 0, 0, $transparent);

	// Opaque-Farbe für Maske
	$opaque = imagecolorallocatealpha($mask, 0, 0, 0, 0);

	// Körper des Rechtecks
	imagefilledrectangle($mask, $radius, 0, $w - $radius, $h,       $opaque);
	imagefilledrectangle($mask, 0,       $radius, $w, $h - $radius, $opaque);
	// Vier Rundungen
	imagefilledellipse($mask, $radius,      $radius,      $radius * 2, $radius * 2, $opaque);
	imagefilledellipse($mask, $w - $radius, $radius,      $radius * 2, $radius * 2, $opaque);
	imagefilledellipse($mask, $radius,      $h - $radius, $radius * 2, $radius * 2, $opaque);
	imagefilledellipse($mask, $w - $radius, $h - $radius, $radius * 2, $radius * 2, $opaque);

	// Neues Zielbild mit Alphakanal
	$rounded = imagecreatetruecolor($w, $h);
	imagesavealpha($rounded, true);
	imagefill($rounded, 0, 0, $transparent);

	// Pixel für Pixel kopieren, nur dort, wo die Maske opaque ist
	for ($x = 0; $x < $w; ++$x) {
		for ($y = 0; $y < $h; ++$y) {
			// Maske: alpha 0 = voll sichtbar, 127 = voll transparent
			$mcol = imagecolorat($mask, $x, $y);
			$maskAlpha = ($mcol >> 24) & 0x7F;
			if ($maskAlpha < 127) {
				$acol = imagecolorat($src, $x, $y);
				$a = ($acol >> 24) & 0x7F;
				$r = ($acol >> 16) & 0xFF;
				$g = ($acol >> 8) & 0xFF;
				$b = $acol & 0xFF;
				$col = imagecolorallocatealpha($rounded, $r, $g, $b, $a);
				imagesetpixel($rounded, $x, $y, $col);
			}
		}
	}

	imagedestroy($mask);
	return $rounded;
}
