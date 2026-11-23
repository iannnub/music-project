<?php
class ImageHelper {

    // Fungsi untuk Mengompres & Upload Gambar dari $_FILES (Form biasa)
    public static function uploadAndCompress($file, $targetDir, $namePrefix) {
        
        // 1. Validasi File
        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];
        
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $validExt = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $validExt)) {
            return ['status' => false, 'msg' => 'Format file harus JPG atau PNG'];
        }

        // 2. Buat Nama Baru Unik
        $newFileName = $namePrefix . "_" . time() . "." . $ext;
        $targetFile = $targetDir . $newFileName;

        // Buat folder jika belum ada
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        // 3. PROSES KOMPRESI
        // Load gambar ke memory
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $image = imagecreatefromjpeg($fileTmp);
        } elseif ($ext == 'png') {
            $image = imagecreatefrompng($fileTmp);
        }

        if (!$image) {
            return ['status' => false, 'msg' => 'Gagal memproses gambar'];
        }

        // Cek Ukuran Asli
        $width = imagesx($image);
        $height = imagesy($image);

        // Resize jika terlalu lebar (Max Width 800px)
        $maxWidth = 800;
        if ($width > $maxWidth) {
            $ratio = $maxWidth / $width;
            $newWidth = $maxWidth;
            $newHeight = $height * $ratio;

            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Pertahankan transparansi untuk PNG
            if ($ext == 'png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $image = $newImage;
        }

        // 4. SIMPAN HASIL KOMPRESI
        // Quality: JPG (0-100, rekomen 75), PNG (0-9, rekomen 6)
        $uploadSuccess = false;
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $uploadSuccess = imagejpeg($image, $targetFile, 75); // Kompresi JPG 75%
        } elseif ($ext == 'png') {
            $uploadSuccess = imagepng($image, $targetFile, 6); // Kompresi PNG level 6
        }

        // Bersihkan memory
        imagedestroy($image);

        if ($uploadSuccess) {
            return ['status' => true, 'fileName' => $newFileName];
        } else {
            return ['status' => false, 'msg' => 'Gagal menyimpan file ke server'];
        }
    }

    /**
     * Memvalidasi, mendekode, meresize, dan mengenkode ulang gambar Base64 menjadi JPEG bersih.
     */
    public static function saveBase64Image($base64Data, $targetDir, $namePrefix, $maxDim = 800, $quality = 80, $maxBytes = 2097152) {
        if (!is_string($base64Data) || !preg_match('/^data:image\/(?:jpeg|jpg|png|webp);base64,/i', $base64Data)) {
            return ['status' => false, 'msg' => 'Format data gambar Base64 tidak valid.'];
        }

        $commaPos = strpos($base64Data, ',');
        $encoded  = str_replace(' ', '+', substr($base64Data, $commaPos + 1));
        $binary   = base64_decode($encoded, true);

        if ($binary === false || strlen($binary) === 0 || strlen($binary) > $maxBytes) {
            return ['status' => false, 'msg' => 'Ukuran atau data gambar melebihi batas aman (maksimal 2 MB).'];
        }

        $info = @getimagesizefromstring($binary);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!$info || empty($info['mime']) || !in_array($info['mime'], $allowedMimes, true)) {
            return ['status' => false, 'msg' => 'File tidak dikenali sebagai gambar JPEG, PNG, atau WebP yang sah.'];
        }

        $srcImg = @imagecreatefromstring($binary);
        if (!$srcImg) {
            return ['status' => false, 'msg' => 'Gambar rusak atau gagal diproses.'];
        }

        $origW = imagesx($srcImg);
        $origH = imagesy($srcImg);
        $newW  = $origW;
        $newH  = $origH;

        if ($maxDim > 0 && ($origW > $maxDim || $origH > $maxDim)) {
            $scale = min($maxDim / $origW, $maxDim / $origH);
            $newW  = max(1, (int)round($origW * $scale));
            $newH  = max(1, (int)round($origH * $scale));
        }

        $dstImg = imagecreatetruecolor($newW, $newH);
        $white  = imagecolorallocate($dstImg, 255, 255, 255);
        imagefill($dstImg, 0, 0, $white);
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($srcImg);

        $targetDir = rtrim($targetDir, '/\\') . '/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $fileName   = $namePrefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
        $targetFile = $targetDir . $fileName;
        $saved      = imagejpeg($dstImg, $targetFile, $quality);
        imagedestroy($dstImg);

        if (!$saved) {
            return ['status' => false, 'msg' => 'Gagal menyimpan hasil kompresi gambar ke server.'];
        }

        return ['status' => true, 'fileName' => $fileName];
    }
}
?>