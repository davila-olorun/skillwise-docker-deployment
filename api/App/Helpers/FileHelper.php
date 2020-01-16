<?php

class FileHelper {

    public static function createdir(array $architecture, $dir = _FILESDIRPATH_) {
        $count = 0;
        $path = $dir;
        $relativePath = null;

        foreach ($architecture as $key => $value) {

            $path .= trim(strtolower(utf8_decode(ApiController::removeAccents($value)))) . DS;
            $relativePath .= trim(strtolower(utf8_decode(ApiController::removeAccents($value)))) . DS;

            if (file_exists($path)) {
                $count++;
                continue;
            } else {
                $isCreated = mkdir($path);
                if ($isCreated == true) {
                    $count++;
                } else {
                    return false;
                }
            }
        }

        if ($count == count($architecture)) {
            return $relativePath;
        } else {
            return false;
        }
    }

    /**
     * Permet d'enregistrer un fichier
     * 
     * @param string $file le fichier
     * @param string $prefix_name la chaine qui précède le nom du fichier
     * @param string $sortie chemin de sortie de l'image redimensionnée
     * @return boolean|string la méthode retourne false si c'est un échec ou le nom du fichier si c'est un succès
     */
    public static function saveFile($file, $prefix_name = "", $sortie = _FILESDIRPATH_) {
        if (isset($file) && !$file['error']) {
            $source = $file['tmp_name'];
            $name = basename($file['name']);

            $extTable = explode('.', $name);

            if (!is_array($extTable)) {
                $name = $prefix_name . uniqid() . '-' . $name;
                $sortie .= $name;
            } else {
                $name = $prefix_name . uniqid() . '.' . $extTable[count($extTable) - 1];
                $sortie .= $name;
            }

            if (move_uploaded_file($source, $sortie)) {
                return $name;
            } else {
                unlink($file['tmp_name']);
                unset($file);
                return false;
            }
        } else {
            return false;
        }
    }

    public static function deleteDir($dirPath) {
        if(!is_dir($dirPath)){
            return false;
        }
        
        $dir = opendir($dirPath);

        while ($file = readdir($dir)) {
            
            if (is_file($dirPath . $file)) {
                self::deleteFile($file,$dirPath);
            }
        }
        return rmdir($dirPath);
    }

    public static function zipDir($zipName, $dirPath) {
        $zipRealName = '-' . $zipName . '.zip';
        
        $path = realpath($dirPath).$zipRealName;

        $zip = new ZipArchive();
        
        if(!is_dir($dirPath)){
            return false;
        }
        
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) == true) {
            $dir = opendir($dirPath);

            while ($file = readdir($dir)) {
                if (is_file($dirPath . $file)) {
                    $zip->addFile($dirPath . $file, $file);
                }
            }
            return $zip->close() ? $zipRealName : false;
        } else {
            return false;
        }
    }

    /**
     * Permet d'enregistrer plusieurs fichiers
     * 
     * @param string $file le fichier
     * @param string $index la position du fichier dans le tableau
     * @param string $prefix_name la chaine qui précède le nom du fichier
     * @param string $sortie chemin de sortie du fichier
     * @param boolean $rename true pour renommer le fichier ou false pour garder le nom
     * @return boolean|string la méthode retourne false si c'est un échec ou le nom de l'image rédimensionnée si c'est un succès
     */
    public static function saveMultipleFiles($file, $index, $prefix_name = "", $sortie = _FILESDIRPATH_, $rename = false) {

        if (isset($file) && !$file['error'][$index]) {
            $source = $file['tmp_name'][$index];
            $name = basename($file['name'][$index]);

            if ($rename === true) {
                $extTable = explode('.', $name);

                if (!is_array($extTable)) {
                    $name = $prefix_name . uniqid() . '-' . $name;
                    $sortie .= $name;
                } else {
                    $name = $prefix_name . uniqid() . '.' . $extTable[count($extTable) - 1];
                    $sortie .= $name;
                }
            } else {
                $sortie .= $name;
            }

            if (move_uploaded_file($source, $sortie)) {
                return $name;
            } else {
                unlink($file['tmp_name'][$index]);
                unset($file);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Permet d'enregistrer une image de profile
     * 
     * @param string $file le fichier image
     * @param string $sortie chemin de sortie de l'image redimensionnée
     * @return boolean|string la méthode retourne false si c'est un échec ou le nom de l'image rédimensionnée si c'est un succès
     */
    public static function saveImage($file, $sortie = _AVATARIMGDIRPATH_) {
        if (isset($file) && !$file['error']) {

            $img_content = getimagesize($file['tmp_name']);

            if ($img_content && $img_content[2] < 4) {

                $source = $file['tmp_name'];
                $ext = strtolower($img_content['mime']);

                return self::RedimensionnerImage($source, $sortie, $ext, 200, 200);
            } else {
                unlink($file['tmp_name']);
                unset($file);
            }
        }
    }

    /**
     * Permet de redimensionner une image et l'enregistrer sur le serveur
     * 
     * @param string $source la source de l'image
     * @param string $sortie chemin de sortie de l'image redimensionnée
     * @param string $ext extension de l'image redimensionnée exple : 'image/jpg','image/jpeg', 'image/png', 'image/gif'
     * @param int $_width largeur de l'image rédimensionnée
     * @param int $_height hauteur de l'image rédimensionnée
     * @param string $imgPrefix préfixe de l'image rédimensionnée
     * @param int $compression qualité de la la redimension
     * @return boolean|string la méthode retourne false si c'est un échec ou le nom de l'image rédimensionnée si c'est un succès
     */
    public static function RedimensionnerImage($source, $sortie, $ext, $_width = 300, $_height = 300, $imgPrefix = "img-profile-", $compression = 70) {

        /*
          Récupération des dimensions de l'image afin de vérifier
          que ce fichier correspond bel et bien à un fichier image.
          Stockage dans deux variables le cas échéant.
         */

        if (!( list($source_largeur, $source_hauteur) = getimagesize($source) )) {
            return false;
        }

        $nouv_largeur = $_width;
        $nouv_hauteur = $_height;

        if ($ext == 'image/jpg') {
            $source_image = imagecreatefromjpeg($source);
            $img_name = uniqid($imgPrefix) . '.jpg';
        } else if ($ext == 'image/jpeg') {
            $source_image = imagecreatefromjpeg($source);
            $img_name = uniqid($imgPrefix) . '.jpeg';
        } else if ($ext == 'image/png') {
            $source_image = imagecreatefrompng($source);
            $img_name = uniqid($imgPrefix) . '.png';
        } else if ($ext == 'image/gif') {
            $source_image = imagecreatefromgif($source);
            $img_name = uniqid($imgPrefix) . '.gif';
        }

        //$source_image = imagecreatefromstring(file_get_contents($source));

        $image = imagecreatetruecolor($nouv_largeur, $nouv_hauteur);

        imagecopyresampled($image, $source_image, 0, 0, 0, 0, $nouv_largeur, $nouv_hauteur, $source_largeur, $source_hauteur);

        $sortie .= $img_name;

        if (strlen($sortie) > 0) {

            if ($ext == 'image/jpg' || $ext == 'image/jpeg') {
                imagejpeg($image, $sortie, $compression);
                return $img_name;
            } else if ($ext == 'image/png') {
                imagepng($image, $sortie, 7);
                return $img_name;
            } else if ($ext == 'image/gif') {
                imagegif($image, $sortie);
                return $img_name;
            }
        } else {

            return false;
            //header("Content-Type: image/jpeg");
            //imagejpeg($image, NULL, $compression);
        }

        //Libération de la mémoire allouée aux deux images (sources et nouvelle).

        imagedestroy($image);
        imagedestroy($source_image);
    }

    public static function deleteFiles($tableOfFiles, $dir = _FILESDIRPATH_) {
        if (is_array($tableOfFiles) AND ! empty($tableOfFiles)) {
            foreach ($tableOfFiles as $key => $value) {
                $this->deleteFile($value, $dir);
            }
            return true;
        }
        return false;
    }

    public static function deleteFile($fileName, $dir = _FILESDIRPATH_) {
        $path = $dir . $fileName;
        if (file_exists($path) AND ! is_dir($path)) {
            unlink($path);
            return true;
        }
        return false;
    }

}
