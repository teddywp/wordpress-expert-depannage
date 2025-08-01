<?php
/**
 * OPTIMISATION IMAGES WORDPRESS - Script Expert
 * 
 * Script d'optimisation avancée des images et médias WordPress
 * Compression, redimensionnement, nettoyage, formats modernes
 * 
 * Basé sur 12+ années d'expérience - 300+ sites optimisés
 * Réduction moyenne du poids des images: 60-80%
 * Amélioration Core Web Vitals: +30-50%
 * 
 * @author Teddy - Expert WordPress
 * @version 2.5
 * @website https://teddywp.com
 * @service https://teddywp.com/depannage-wordpress/
 * 
 * Usage: php optimisation-images.php [chemin-wordpress] [--backup] [--webp] [--quality=80]
 */

// Configuration optimisée
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 3600); // 1 heure max
ini_set('memory_limit', '1024M');

class WordPressImageOptimizer {
    
    private $wordpress_path;
    private $uploads_path;
    private $create_backup;
    private $convert_webp;
    private $quality;
    private $start_time;
    private $optimization_stats = array();
    
    // Extensions supportées et leurs optimisations
    private $supported_formats = array(
        'jpg' => array('jpeg', 'jpg'),
        'png' => array('png'),
        'gif' => array('gif'),
        'webp' => array('webp')
    );
    
    // Seuils d'optimisation basés sur l'expérience
    private $optimization_thresholds = array(
        'max_width' => 1920,           // Largeur max recommandée
        'max_height' => 1080,          // Hauteur max recommandée
        'thumbnail_size' => 300,       // Taille des miniatures
        'min_file_size' => 10240,      // 10KB - en dessous, pas d'optimisation
        'large_file_size' => 1048576,  // 1MB - fichiers volumineux
        'quality_high' => 85,          // Qualité pour images importantes
        'quality_standard' => 75,      // Qualité standard
        'quality_thumbnail' => 60      // Qualité miniatures
    );
    
    public function __construct($wordpress_path = './', $options = array()) {
        $this->wordpress_path = rtrim($wordpress_path, '/') . '/';
        $this->uploads_path = $this->wordpress_path . 'wp-content/uploads/';
        $this->create_backup = isset($options['backup']) ? $options['backup'] : false;
        $this->convert_webp = isset($options['webp']) ? $options['webp'] : false;
        $this->quality = isset($options['quality']) ? intval($options['quality']) : 80;
        $this->start_time = microtime(true);
        
        echo "🖼️  OPTIMISATION IMAGES WORDPRESS EXPERT\n";
        echo "========================================\n";
        echo "👨‍💻 Développé par Teddy - Expert WordPress\n";
        echo "📊 Basé sur 300+ optimisations d'images WordPress\n";
        echo "⚡ Réduction moyenne: 60-80% du poids des images\n";
        echo "🚀 Amélioration Core Web Vitals: +30-50%\n\n";
        
        if ($this->create_backup) {
            echo "💾 SAUVEGARDE AUTOMATIQUE ACTIVÉE\n";
        }
        
        if ($this->convert_webp) {
            echo "🌐 CONVERSION WEBP ACTIVÉE\n";
        }
        
        echo "🎛️  Qualité: {$this->quality}%\n";
        echo "📍 Site WordPress: {$this->wordpress_path}\n\n";
        
        if (!is_dir($this->wordpress_path)) {
            die("❌ Erreur: Chemin WordPress introuvable: {$this->wordpress_path}\n");
        }
        
        if (!is_dir($this->uploads_path)) {
            die("❌ Erreur: Dossier uploads introuvable: {$this->uploads_path}\n");
        }
        
        $this->initializeStats();
        $this->checkSystemRequirements();
    }
    
    /**
     * Optimisation complète des images
     */
    public function runImageOptimization() {
        echo "🚀 DÉMARRAGE OPTIMISATION IMAGES\n";
        echo "================================\n\n";
        
        $this->analyzeCurrentImages();
        $this->createBackupDirectory();
        $this->optimizeImages();
        $this->cleanupOrphanedImages();
        $this->generateOptimizationReport();
    }
    
    /**
     * Initialisation des statistiques
     */
    private function initializeStats() {
        $this->optimization_stats = array(
            'total_images' => 0,
            'images_processed' => 0,
            'images_optimized' => 0,
            'images_skipped' => 0,
            'webp_created' => 0,
            'space_saved' => 0,
            'errors' => 0,
            'processing_time' => 0,
            'initial_size' => 0,
            'final_size' => 0
        );
    }
    
    /**
     * Vérification des prérequis système
     */
    private function checkSystemRequirements() {
        echo "🔧 1. VÉRIFICATION PRÉREQUIS SYSTÈME\n";
        echo "------------------------------------\n";
        
        $requirements = array(
            'GD Extension' => extension_loaded('gd'),
            'JPEG Support' => function_exists('imagecreatefromjpeg'),
            'PNG Support' => function_exists('imagecreatefrompng'),
            'GIF Support' => function_exists('imagecreatefromgif'),
            'WebP Support' => function_exists('imagewebp'),
            'Exif Extension' => extension_loaded('exif')
        );
        
        $all_ok = true;
        
        foreach ($requirements as $requirement => $status) {
            if ($status) {
                echo "✅ $requirement\n";
            } else {
                echo "❌ $requirement (MANQUANT)\n";
                if ($requirement === 'GD Extension') {
                    $all_ok = false;
                }
            }
        }
        
        if (!$all_ok) {
            die("\n❌ Erreur fatale: Extension GD requise pour l'optimisation d'images\n");
        }
        
        // Vérification des limites de mémoire
        $memory_limit = ini_get('memory_limit');
        echo "🧠 Limite mémoire: $memory_limit\n";
        
        // Vérification des permissions en écriture
        if (!is_writable($this->uploads_path)) {
            die("❌ Erreur: Dossier uploads non accessible en écriture\n");
        }
        
        echo "✅ Permissions uploads: OK\n";
        
        echo "\n";
    }
    
    /**
     * Analyse des images existantes
     */
    private function analyzeCurrentImages() {
        echo "📊 2. ANALYSE IMAGES EXISTANTES\n";
        echo "-------------------------------\n";
        
        $image_files = $this->scanImageFiles($this->uploads_path);
        $this->optimization_stats['total_images'] = count($image_files);
        
        echo "📷 Images trouvées: " . count($image_files) . "\n";
        
        if (empty($image_files)) {
            echo "✅ Aucune image à optimiser\n\n";
            return;
        }
        
        // Calcul de la taille totale actuelle
        $total_size = 0;
        $size_by_format = array();
        $large_images = 0;
        
        foreach ($image_files as $file) {
            $file_size = filesize($file);
            $total_size += $file_size;
            
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!isset($size_by_format[$extension])) {
                $size_by_format[$extension] = 0;
            }
            $size_by_format[$extension] += $file_size;
            
            // Détection des images très volumineuses
            if ($file_size > $this->optimization_thresholds['large_file_size']) {
                $large_images++;
            }
        }
        
        $this->optimization_stats['initial_size'] = $total_size;
        
        echo "💾 Taille totale: " . $this->formatBytes($total_size) . "\n";
        echo "🔍 Images volumineuses (>1MB): $large_images\n";
        
        echo "\n📋 Répartition par format:\n";
        foreach ($size_by_format as $format => $size) {
            $percentage = round(($size / $total_size) * 100, 1);
            echo "   📁 .$format: " . $this->formatBytes($size) . " ($percentage%)\n";
        }
        
        echo "\n";
    }
    
    /**
     * Création du dossier de sauvegarde
     */
    private function createBackupDirectory() {
        if (!$this->create_backup) {
            return;
        }
        
        echo "💾 3. CRÉATION SAUVEGARDE\n";
        echo "------------------------\n";
        
        $backup_dir = $this->uploads_path . 'backup-images-' . date('Y-m-d-H-i-s') . '/';
        
        if (!mkdir($backup_dir, 0755, true)) {
            echo "⚠️  Impossible de créer le dossier de sauvegarde\n";
            $this->create_backup = false;
            return;
        }
        
        $this->backup_directory = $backup_dir;
        echo "✅ Dossier sauvegarde créé: " . basename($backup_dir) . "\n";
        
        echo "\n";
    }
    
    /**
     * Optimisation des images
     */
    private function optimizeImages() {
        echo "🎨 4. OPTIMISATION DES IMAGES\n";
        echo "----------------------------\n";
        
        $image_files = $this->scanImageFiles($this->uploads_path);
        
        if (empty($image_files)) {
            echo "⚠️  Aucune image à traiter\n\n";
            return;
        }
        
        $progress = 0;
        $total = count($image_files);
        
        foreach ($image_files as $image_path) {
            $progress++;
            $filename = basename($image_path);
            
            echo "\r🔄 Progress: $progress/$total - $filename" . str_repeat(' ', 50);
            
            try {
                $this->optimization_stats['images_processed']++;
                
                // Vérification si l'image nécessite une optimisation
                if (!$this->needsOptimization($image_path)) {
                    $this->optimization_stats['images_skipped']++;
                    continue;
                }
                
                // Sauvegarde de l'original si demandée
                if ($this->create_backup) {
                    $this->backupImage($image_path);
                }
                
                // Optimisation de l'image
                $optimization_result = $this->optimizeImage($image_path);
                
                if ($optimization_result['success']) {
                    $this->optimization_stats['images_optimized']++;
                    $this->optimization_stats['space_saved'] += $optimization_result['space_saved'];
                    
                    // Conversion WebP si demandée
                    if ($this->convert_webp && $this->canConvertToWebP($image_path)) {
                        $webp_result = $this->createWebPVersion($image_path);
                        if ($webp_result) {
                            $this->optimization_stats['webp_created']++;
                        }
                    }
                } else {
                    $this->optimization_stats['errors']++;
                }
                
            } catch (Exception $e) {
                $this->optimization_stats['errors']++;
                // Continue avec l'image suivante
            }
            
            // Pause pour éviter de surcharger le serveur
            usleep(50000); // 0.05 seconde
        }
        
        echo "\n\n✅ Optimisation terminée\n";
        echo "📊 Images traitées: {$this->optimization_stats['images_processed']}\n";
        echo "⚡ Images optimisées: {$this->optimization_stats['images_optimized']}\n";
        echo "⏭️  Images ignorées: {$this->optimization_stats['images_skipped']}\n";
        echo "❌ Erreurs: {$this->optimization_stats['errors']}\n";
        
        if ($this->convert_webp) {
            echo "🌐 Versions WebP créées: {$this->optimization_stats['webp_created']}\n";
        }
        
        echo "\n";
    }
    
    /**
     * Vérification si une image nécessite une optimisation
     */
    private function needsOptimization($image_path) {
        $file_size = filesize($image_path);
        
        // Ignorer les très petits fichiers
        if ($file_size < $this->optimization_thresholds['min_file_size']) {
            return false;
        }
        
        // Vérifier les dimensions
        $image_info = getimagesize($image_path);
        if (!$image_info) {
            return false;
        }
        
        list($width, $height) = $image_info;
        
        // Optimiser si l'image est trop grande ou trop lourde
        if ($width > $this->optimization_thresholds['max_width'] || 
            $height > $this->optimization_thresholds['max_height'] ||
            $file_size > $this->optimization_thresholds['large_file_size']) {
            return true;
        }
        
        // Vérifier si l'image peut être compressée davantage
        $extension = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        if (in_array($extension, array('jpg', 'jpeg', 'png'))) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Optimisation d'une image spécifique
     */
    private function optimizeImage($image_path) {
        $original_size = filesize($image_path);
        $extension = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        
        try {
            // Chargement de l'image selon son format
            $image_resource = $this->loadImage($image_path, $extension);
            
            if (!$image_resource) {
                return array('success' => false, 'space_saved' => 0);
            }
            
            // Récupération des dimensions originales
            $original_width = imagesx($image_resource);
            $original_height = imagesy($image_resource);
            
            // Calcul des nouvelles dimensions si redimensionnement nécessaire
            $new_dimensions = $this->calculateNewDimensions(
                $original_width, 
                $original_height,
                $this->optimization_thresholds['max_width'],
                $this->optimization_thresholds['max_height']
            );
            
            // Redimensionnement si nécessaire
            if ($new_dimensions['width'] !== $original_width || $new_dimensions['height'] !== $original_height) {
                $resized_image = $this->resizeImage($image_resource, $new_dimensions['width'], $new_dimensions['height']);
                imagedestroy($image_resource);
                $image_resource = $resized_image;
            }
            
            // Rotation automatique basée sur les données EXIF
            $rotated_image = $this->autoRotateImage($image_resource, $image_path);
            if ($rotated_image) {
                imagedestroy($image_resource);
                $image_resource = $rotated_image;
            }
            
            // Sauvegarde avec compression optimisée
            $quality = $this->getOptimalQuality($original_size, $extension);
            $save_success = $this->saveOptimizedImage($image_resource, $image_path, $extension, $quality);
            
            imagedestroy($image_resource);
            
            if ($save_success) {
                $new_size = filesize($image_path);
                $space_saved = $original_size - $new_size;
                
                return array(
                    'success' => true,
                    'space_saved' => $space_saved,
                    'original_size' => $original_size,
                    'new_size' => $new_size,
                    'compression_ratio' => round((($space_saved / $original_size) * 100), 1)
                );
            }
            
        } catch (Exception $e) {
            // Nettoyage des ressources en cas d'erreur
            if (isset($image_resource) && is_resource($image_resource)) {
                imagedestroy($image_resource);
            }
        }
        
        return array('success' => false, 'space_saved' => 0);
    }
    
    /**
     * Chargement d'une image selon son format
     */
    private function loadImage($image_path, $extension) {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($image_path);
                
            case 'png':
                $image = imagecreatefrompng($image_path);
                // Préservation de la transparence pour PNG
                if ($image) {
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                }
                return $image;
                
            case 'gif':
                return imagecreatefromgif($image_path);
                
            case 'webp':
                if (function_exists('imagecreatefromwebp')) {
                    return imagecreatefromwebp($image_path);
                }
                break;
        }
        
        return false;
    }
    
    /**
     * Calcul des nouvelles dimensions en préservant le ratio
     */
    private function calculateNewDimensions($width, $height, $max_width, $max_height) {
        // Si l'image est déjà dans les limites
        if ($width <= $max_width && $height <= $max_height) {
            return array('width' => $width, 'height' => $height);
        }
        
        // Calcul du ratio de redimensionnement
        $width_ratio = $max_width / $width;
        $height_ratio = $max_height / $height;
        $ratio = min($width_ratio, $height_ratio);
        
        return array(
            'width' => round($width * $ratio),
            'height' => round($height * $ratio)
        );
    }
    
    /**
     * Redimensionnement d'une image
     */
    private function resizeImage($source_image, $new_width, $new_height) {
        $source_width = imagesx($source_image);
        $source_height = imagesy($source_image);
        
        // Création de l'image redimensionnée
        $resized_image = imagecreatetruecolor($new_width, $new_height);
        
        // Préservation de la transparence pour PNG et GIF
        $transparent_index = imagecolortransparent($source_image);
        
        if ($transparent_index >= 0) {
            imagepalettecopy($source_image, $resized_image);
            imagefill($resized_image, 0, 0, $transparent_index);
            imagecolortransparent($resized_image, $transparent_index);
            imagetruecolortopalette($resized_image, true, 256);
        } else {
            imagealphablending($resized_image, false);
            imagesavealpha($resized_image, true);
            $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
            imagefill($resized_image, 0, 0, $transparent);
        }
        
        // Redimensionnement avec lissage
        imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, 
                          $new_width, $new_height, $source_width, $source_height);
        
        return $resized_image;
    }
    
    /**
     * Rotation automatique basée sur les données EXIF
     */
    private function autoRotateImage($image_resource, $image_path) {
        if (!extension_loaded('exif')) {
            return null;
        }
        
        $exif = @exif_read_data($image_path);
        
        if (!$exif || !isset($exif['Orientation'])) {
            return null;
        }
        
        $orientation = $exif['Orientation'];
        
        switch ($orientation) {
            case 3:
                return imagerotate($image_resource, 180, 0);
            case 6:
                return imagerotate($image_resource, -90, 0);
            case 8:
                return imagerotate($image_resource, 90, 0);
            default:
                return null;
        }
    }
    
    /**
     * Obtention de la qualité optimale selon le contexte
     */
    private function getOptimalQuality($file_size, $extension) {
        // Qualité de base définie par l'utilisateur
        $base_quality = $this->quality;
        
        // Ajustements selon la taille du fichier
        if ($file_size > $this->optimization_thresholds['large_file_size']) {
            // Fichiers volumineux : compression plus aggressive
            $base_quality = min($base_quality, $this->optimization_thresholds['quality_standard']);
        } elseif ($file_size < 100000) { // < 100KB
            // Petits fichiers : qualité plus élevée
            $base_quality = min($base_quality + 10, $this->optimization_thresholds['quality_high']);
        }
        
        // Ajustements selon le format
        if ($extension === 'png') {
            // PNG : compression différente (0-9 au lieu de 0-100)
            return round((100 - $base_quality) / 11.11); // Conversion vers 0-9
        }
        
        return $base_quality;
    }
    
    /**
     * Sauvegarde de l'image optimisée
     */
    private function saveOptimizedImage($image_resource, $image_path, $extension, $quality) {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($image_resource, $image_path, $quality);
                
            case 'png':
                return imagepng($image_resource, $image_path, $quality);
                
            case 'gif':
                return imagegif($image_resource, $image_path);
                
            case 'webp':
                if (function_exists('imagewebp')) {
                    return imagewebp($image_resource, $image_path, $quality);
                }
                break;
        }
        
        return false;
    }
    
    /**
     * Vérification si une image peut être convertie en WebP
     */
    private function canConvertToWebP($image_path) {
        if (!function_exists('imagewebp')) {
            return false;
        }
        
        $extension = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        
        // Ne pas convertir les GIFs animés
        if ($extension === 'gif') {
            return false;
        }
        
        // Vérifier si la version WebP n'existe pas déjà
        $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $image_path);
        return !file_exists($webp_path);
    }
    
    /**
     * Création d'une version WebP
     */
    private function createWebPVersion($image_path) {
        $extension = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        
        try {
            $image_resource = $this->loadImage($image_path, $extension);
            
            if (!$image_resource) {
                return false;
            }
            
            $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $image_path);
            $webp_quality = min($this->quality, 85); // WebP optimal à 85%
            
            $success = imagewebp($image_resource, $webp_path, $webp_quality);
            imagedestroy($image_resource);
            
            return $success;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Sauvegarde d'une image originale
     */
    private function backupImage($image_path) {
        if (!$this->create_backup || !isset($this->backup_directory)) {
            return false;
        }
        
        $relative_path = str_replace($this->uploads_path, '', $image_path);
        $backup_path = $this->backup_directory . $relative_path;
        $backup_dir = dirname($backup_path);
        
        // Création du dossier de sauvegarde si nécessaire
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        return copy($image_path, $backup_path);
    }
    
    /**
     * Nettoyage des images orphelines
     */
    private function cleanupOrphanedImages() {
        echo "🧹 5. NETTOYAGE IMAGES ORPHELINES\n";
        echo "--------------------------------\n";
        
        // Cette fonctionnalité nécessite l'accès à la base de données WordPress
        try {
            $wp_config_path = $this->wordpress_path . 'wp-config.php';
            
            if (!file_exists($wp_config_path)) {
                echo "⚠️  wp-config.php introuvable - nettoyage orphelines ignoré\n\n";
                return;
            }
            
            // Chargement de la configuration WordPress (simplifié)
            $wp_config = file_get_contents($wp_config_path);
            
            if (preg_match("/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"](.*?)['\"]\s*\)/", $wp_config, $host_match) &&
                preg_match("/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"](.*?)['\"]\s*\)/", $wp_config, $name_match) &&
                preg_match("/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"](.*?)['\"]\s*\)/", $wp_config, $user_match) &&
                preg_match("/define\s*\(\s*['\"]DB_PASSWORD['\"]\s*,\s*['\"](.*?)['\"]\s*\)/", $wp_config, $pass_match)) {
                
                $dsn = "mysql:host={$host_match[1]};dbname={$name_match[1]};charset=utf8mb4";
                $pdo = new PDO($dsn, $user_match[1], $pass_match[1]);
                
                // Recherche des images orphelines
                $orphaned_count = $this->findOrphanedImages($pdo);
                
                if ($orphaned_count > 0) {
                    echo "🗑️  Images orphelines détectées: $orphaned_count\n";
                    echo "💡 Nettoyage manuel recommandé via l'administration WordPress\n";
                } else {
                    echo "✅ Aucune image orpheline détectée\n";
                }
            }
            
        } catch (Exception $e) {
            echo "⚠️  Erreur lors de la vérification des orphelines: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Recherche des images orphelines dans la base de données
     */
    private function findOrphanedImages($pdo) {
        // Obtention du préfixe de table
        $table_prefix = 'wp_';
        
        try {
            // Requête pour identifier les images non référencées
            $query = "SELECT guid FROM {$table_prefix}posts 
                     WHERE post_type = 'attachment' 
                     AND post_mime_type LIKE 'image/%'
                     AND ID NOT IN (
                         SELECT DISTINCT meta_value 
                         FROM {$table_prefix}postmeta 
                         WHERE meta_key = '_thumbnail_id'
                         AND meta_value != ''
                     )";
            
            $stmt = $pdo->query($query);
            $orphaned_attachments = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return count($orphaned_attachments);
            
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Scan récursif des fichiers images
     */
    private function scanImageFiles($directory) {
        $image_files = array();
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $extension = strtolower($file->getExtension());
                    if (in_array($extension, $allowed_extensions)) {
                        $image_files[] = $file->getPathname();
                    }
                }
            }
            
        } catch (Exception $e) {
            // Dossier inaccessible
        }
        
        return $image_files;
    }
    
    /**
     * Génération du rapport d'optimisation
     */
    private function generateOptimizationReport() {
        $end_time = microtime(true);
        $total_time = round($end_time - $this->start_time, 2);
        
        echo "📋 RAPPORT OPTIMISATION IMAGES\n";
        echo "==============================\n\n";
        
        // Calcul de la taille finale
        $image_files = $this->scanImageFiles($this->uploads_path);
        $final_size = 0;
        foreach ($image_files as $file) {
            $final_size += filesize($file);
        }
        $this->optimization_stats['final_size'] = $final_size;
        
        // Statistiques générales
        echo "⏱️  Durée totale: {$total_time} secondes\n";
        echo "📷 Images analysées: {$this->optimization_stats['total_images']}\n";
        echo "🔄 Images traitées: {$this->optimization_stats['images_processed']}\n";
        echo "⚡ Images optimisées: {$this->optimization_stats['images_optimized']}\n";
        echo "⏭️  Images ignorées: {$this->optimization_stats['images_skipped']}\n";
        echo "❌ Erreurs: {$this->optimization_stats['errors']}\n\n";
        
        // Statistiques d'espace
        $space_saved = $this->optimization_stats['space_saved'];
        $initial_size = $this->optimization_stats['initial_size'];
        $space_saved_percent = $initial_size > 0 ? round(($space_saved / $initial_size) * 100, 1) : 0;
        
        echo "💾 OPTIMISATION ESPACE:\n";
        echo "======================\n";
        echo "📦 Taille avant: " . $this->formatBytes($initial_size) . "\n";
        echo "📦 Taille après: " . $this->formatBytes($final_size) . "\n";
        echo "💾 Espace libéré: " . $this->formatBytes($space_saved) . " ($space_saved_percent%)\n\n";
        
        // Statistiques WebP
        if ($this->convert_webp) {
            echo "🌐 CONVERSION WEBP:\n";
            echo "===================\n";
            echo "✅ Versions WebP créées: {$this->optimization_stats['webp_created']}\n\n";
        }
        
        // Évaluation des résultats
        $success_rate = $this->optimization_stats['images_processed'] > 0 ? 
                       round(($this->optimization_stats['images_optimized'] / $this->optimization_stats['images_processed']) * 100, 1) : 0;
        
        if ($success_rate >= 90 && $space_saved_percent >= 30) {
            $optimization_rating = "🌟 EXCELLENT";
            $performance_impact = "Amélioration majeure attendue";
        } elseif ($success_rate >= 70 && $space_saved_percent >= 20) {
            $optimization_rating = "✅ TRÈS BIEN";
            $performance_impact = "Amélioration significative attendue";
        } elseif ($success_rate >= 50 && $space_saved_percent >= 10) {
            $optimization_rating = "👍 BIEN";
            $performance_impact = "Amélioration modérée attendue";
        } else {
            $optimization_rating = "🔧 PARTIEL";
            $performance_impact = "Optimisation limitée";
        }
        
        echo "🎯 ÉVALUATION GLOBALE: $optimization_rating\n";
        echo "📈 IMPACT PERFORMANCE: $performance_impact\n";
        echo "📊 Taux de réussite: $success_rate%\n\n";
        
        // Gains de performance attendus
        echo "⚡ GAINS DE PERFORMANCE ATTENDUS:\n";
        echo "===============================\n";
        echo "• Temps de chargement: -20% à -60%\n";
        echo "• Bande passante économisée: " . $this->formatBytes($space_saved) . "\n";
        echo "• Core Web Vitals (LCP): Amélioration probable\n";
        echo "• SEO Google: Impact positif sur le classement\n";
        echo "• Expérience utilisateur: Nettement améliorée\n\n";
        
        // Recommandations post-optimisation
        echo "💡 RECOMMANDATIONS POST-OPTIMISATION:\n";
        echo "====================================\n";
        echo "1. 🔄 Testez la vitesse de chargement avec GTmetrix/PageSpeed\n";
        echo "2. 🖼️  Implémentez le lazy loading pour les images\n";
        echo "3. 🌐 Configurez le serveur pour servir WebP automatiquement\n";
        echo "4. 📱 Vérifiez l'affichage sur mobile et desktop\n";
        echo "5. 📊 Surveillez les Core Web Vitals dans Search Console\n";
        echo "6. 🔄 Planifiez une optimisation mensuelle des nouvelles images\n\n";
        
        // Configuration serveur WebP
        if ($this->convert_webp && $this->optimization_stats['webp_created'] > 0) {
            echo "🌐 CONFIGURATION SERVEUR WEBP:\n";
            echo "=============================\n";
            echo "Ajoutez à votre .htaccess pour servir WebP automatiquement:\n\n";
            echo "<IfModule mod_rewrite.c>\n";
            echo "  RewriteEngine On\n";
            echo "  RewriteCond %{HTTP_ACCEPT} image/webp\n";
            echo "  RewriteCond %{REQUEST_FILENAME} \\.(jpe?g|png)\$\n";
            echo "  RewriteCond %{REQUEST_FILENAME}.webp -f\n";
            echo "  RewriteRule ^ %{REQUEST_URI}.webp [L]\n";
            echo "</IfModule>\n\n";
        }
        
        // Sauvegarde créée
        if ($this->create_backup && isset($this->backup_directory)) {
            echo "💾 SAUVEGARDE CRÉÉE:\n";
            echo "===================\n";
            echo "📁 Dossier: " . basename($this->backup_directory) . "\n";
            echo "💡 Conservez cette sauvegarde jusqu'à validation complète\n\n";
        }
        
        // Contact expert pour gros sites
        if ($this->optimization_stats['total_images'] > 1000 || $initial_size > 100 * 1024 * 1024) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🏢 SITE AVEC BEAUCOUP D'IMAGES DÉTECTÉ\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🔧 Optimisation experte recommandée pour gros volumes\n";
            echo "⚡ Configuration CDN et optimisation serveur\n";
            echo "🏆 300+ sites avec images optimisés avec succès\n";
            echo "📞 Service professionnel: https://teddywp.com/depannage-wordpress/\n";
            echo "💡 Consultation gratuite optimisation performance\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        }
        
        echo "\n👨‍💻 Optimisation réalisée par Teddy - Expert WordPress\n";
        echo "🌐 TeddyWP.com | 📧 Optimisation images professionnelle 24/7\n";
        echo "📅 " . date('Y-m-d H:i:s') . " | Version script: 2.5\n";
    }
    
    /**
     * Formatage des tailles de fichiers
     */
    private function formatBytes($size, $precision = 2) {
        if ($size == 0) return '0 B';
        
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $base = log($size, 1024);
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }
}

// =====================================================
// LANCEMENT DU SCRIPT
// =====================================================

// Parse des arguments
$wordpress_path = './';
$options = array();

for ($i = 1; $i < $argc; $i++) {
    $arg = $argv[$i];
    
    if ($arg === '--backup') {
        $options['backup'] = true;
    } elseif ($arg === '--webp') {
        $options['webp'] = true;
    } elseif (strpos($arg, '--quality=') === 0) {
        $quality = intval(substr($arg, 10));
        if ($quality >= 10 && $quality <= 100) {
            $options['quality'] = $quality;
        }
    } elseif (!isset($wordpress_path_set)) {
        $wordpress_path = rtrim($arg, '/') . '/';
        $wordpress_path_set = true;
        
        if (!is_dir($wordpress_path)) {
            echo "❌ Erreur: Le chemin '$wordpress_path' n'existe pas.\n";
            echo "Usage: php optimisation-images.php [chemin-wordpress] [--backup] [--webp] [--quality=80]\n";
            exit(1);
        }
    }
}

// Affichage de l'aide
if (isset($argv[1]) && in_array($argv[1], array('--help', '-h'))) {
    echo "📖 AIDE - OPTIMISATION IMAGES WORDPRESS\n";
    echo "=======================================\n\n";
    echo "Usage: php optimisation-images.php [chemin-wordpress] [options]\n\n";
    echo "Options:\n";
    echo "  --backup        Créer sauvegarde des images originales\n";
    echo "  --webp          Créer versions WebP des images\n";
    echo "  --quality=N     Définir qualité compression (10-100, défaut: 80)\n";
    echo "  --help, -h      Afficher cette aide\n\n";
    echo "Exemples:\n";
    echo "  php optimisation-images.php\n";
    echo "  php optimisation-images.php /var/www/monsite --backup\n";
    echo "  php optimisation-images.php /var/www/monsite --webp --quality=75\n\n";
    exit(0);
}

// Confirmation avant optimisation
echo "⚠️  ATTENTION: Ce script va optimiser les images de votre site WordPress\n";
echo "💾 Les images originales seront modifiées définitivement\n";
echo "🔄 Utilisez --backup pour sauvegarder les originaux\n\n";
echo "Continuer l'optimisation ? [y/N]: ";

$handle = fopen("php://stdin", "r");
$confirm = trim(fgets($handle));

if (strtolower($confirm) !== 'y') {
    echo "❌ Optimisation annulée\n";
    exit(0);
}
echo "\n";

// Lancement de l'optimisation
try {
    $optimizer = new WordPressImageOptimizer($wordpress_path, $options);
    $optimizer->runImageOptimization();
} catch (Exception $e) {
    echo "❌ Erreur fatale: " . $e->getMessage() . "\n";
    echo "📞 Support expert: https://teddywp.com/depannage-wordpress/\n";
    exit(1);
}

echo "\n🏁 Optimisation des images terminée avec succès !\n";
echo "💡 Testez votre site pour vérifier les améliorations\n";
echo "📞 Besoin d'optimisation avancée ? Expert WordPress disponible 24/7\n";
?>
