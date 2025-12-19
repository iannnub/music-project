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
}
?>