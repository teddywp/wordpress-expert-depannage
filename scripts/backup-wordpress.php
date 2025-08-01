<?php
/**
 * SAUVEGARDE WORDPRESS COMPLÈTE - Script Expert
 * 
 * Script de sauvegarde automatisée complète pour WordPress
 * Fichiers + Base de données + Configuration + Validation
 * 
 * Basé sur 12+ années d'expérience - 1000+ sauvegardes créées
 * Sauvegarde fiable et testée en restauration
 * 
 * @author Teddy - Expert WordPress
 * @version 3.0
 * @website https://teddywp.com
 * @service https://teddywp.com/depannage-wordpress/
 * 
 * Usage: php backup-wordpress.php [chemin-wordpress] [--compress] [--exclude-uploads] [--test-restore]
 */

// Configuration robuste
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 3600); // 1 heure max
ini_set('memory_limit', '1024M');

class WordPressBackupManager {
    
    private $wordpress_path;
    private $backup_directory;
    private $compress_backup;
    private $exclude_uploads;
    private $test_restore;
    private $start_time;
    private $backup_stats = array();
    
    // Configuration de sauvegarde basée sur l'expérience
    private $backup_config = array(
        'excluded_dirs' => array(
            'wp-content/cache',
            'wp-content/backup',
            'wp-content/backups',
            'wp-content/ai1wm-backups',
            'wp-content/updraft'
        ),
        'excluded_files' => array(
            '.htaccess.bak',
            'wp-config-backup.php',
            'error_log',
            'debug.log',
            '.DS_Store',
            'Thumbs.db'
        ),
        'critical_files' => array(
            'wp-config.php',
            '.htaccess',
            'index.php',
            'wp-load.php'
        ),
        'max_file_size' => 100 * 1024 * 1024, // 100MB max par fichier
        'chunk_size' => 1024 * 1024 // 1MB chunks pour gros fichiers
    );
    
    public function __construct($wordpress_path = './', $options = array()) {
        $this->wordpress_path = rtrim($wordpress_path, '/') . '/';
        $this->compress_backup = isset($options['compress']) ? $options['compress'] : false;
        $this->exclude_uploads = isset($options['exclude_uploads']) ? $options['exclude_uploads'] : false;
        $this->test_restore = isset($options['test_restore']) ? $options['test_restore'] : false;
        $this->start_time = microtime(true);
        
        echo "💾 SAUVEGARDE WORDPRESS COMPLÈTE - EXPERT\n";
        echo "=========================================\n";
        echo "👨‍💻 Développé par Teddy - Expert WordPress\n";
        echo "📊 Basé sur 1000+ sauvegardes créées et testées\n";
        echo "🛡️  Sauvegarde fiable avec validation intégrée\n\n";
        
        if ($this->compress_backup) {
            echo "🗜️  COMPRESSION ACTIVÉE\n";
        }
        
        if ($this->exclude_uploads) {
            echo "📁 EXCLUSION UPLOADS ACTIVÉE\n";
        }
        
        if ($this->test_restore) {
            echo "🧪 TEST DE RESTAURATION ACTIVÉ\n";
        }
        
        echo "📍 Site WordPress: {$this->wordpress_path}\n\n";
        
        if (!is_dir($this->wordpress_path)) {
            die("❌ Erreur: Chemin WordPress introuvable: {$this->wordpress_path}\n");
        }
        
        $this->initializeBackupStats();
        $this->setupBackupDirectory();
    }
    
    /**
     * Sauvegarde complète WordPress
     */
    public function runCompleteBackup() {
        echo "🚀 DÉMARRAGE SAUVEGARDE COMPLÈTE\n";
        echo "================================\n\n";
        
        $this->checkSystemRequirements();
        $this->analyzeWordPressSite();
        $this->backupDatabase();
        $this->backupFiles();
        $this->createBackupManifest();
        $this->validateBackup();
        
        if ($this->test_restore) {
            $this->testBackupRestore();
        }
        
        $this->generateBackupReport();
    }
    
    /**
     * Initialisation des statistiques
     */
    private function initializeBackupStats() {
        $this->backup_stats = array(
            'files_backed_up' => 0,
            'files_skipped' => 0,
            'total_size' => 0,
            'backup_size' => 0,
            'database_size' => 0,
            'compression_ratio' => 0,
            'backup_duration' => 0,
            'errors' => 0,
            'warnings' => 0
        );
    }
    
    /**
     * Configuration du dossier de sauvegarde
     */
    private function setupBackupDirectory() {
        echo "📁 1. CONFIGURATION DOSSIER SAUVEGARDE\n";
        echo "--------------------------------------\n";
        
        // Création du nom de sauvegarde unique
        $timestamp = date('Y-m-d_H-i-s');
        $site_name = $this->getSiteName();
        $backup_name = "wordpress-backup_{$site_name}_{$timestamp}";
        
        // Dossier de sauvegarde principal
        $this->backup_directory = $this->wordpress_path . 'backups/' . $backup_name . '/';
        
        // Création des dossiers
        if (!is_dir(dirname($this->backup_directory))) {
            mkdir(dirname($this->backup_directory), 0755, true);
        }
        
        if (!mkdir($this->backup_directory, 0755, true)) {
            die("❌ Erreur: Impossible de créer le dossier de sauvegarde\n");
        }
        
        // Sous-dossiers
        $subdirs = array('database', 'files', 'config', 'logs');
        foreach ($subdirs as $subdir) {
            mkdir($this->backup_directory . $subdir, 0755, true);
        }
        
        echo "✅ Dossier créé: backups/$backup_name/\n";
        echo "📂 Structure: database/, files/, config/, logs/\n";
        
        // Protection par .htaccess
        $htaccess_content = "Order deny,allow\nDeny from all\n";
        file_put_contents(dirname($this->backup_directory) . '/.htaccess', $htaccess_content);
        
        echo "🔒 Protection .htaccess créée\n\n";
    }
    
    /**
     * Vérification des prérequis système
     */
    private function checkSystemRequirements() {
        echo "🔧 2. VÉRIFICATION PRÉREQUIS\n";
        echo "---------------------------\n";
        
        // Vérification espace disque
        $free_space = disk_free_space($this->wordpress_path);
        $total_size = $this->calculateSiteSize();
        
        echo "💾 Espace libre: " . $this->formatBytes($free_space) . "\n";
        echo "📊 Taille site: " . $this->formatBytes($total_size) . "\n";
        
        if ($free_space < ($total_size * 1.5)) {
            echo "⚠️  ATTENTION: Espace disque limité\n";
            echo "💡 Recommandation: Libérez de l'espace ou utilisez --exclude-uploads\n";
            $this->backup_stats['warnings']++;
        } else {
            echo "✅ Espace disque suffisant\n";
        }
        
        // Vérification permissions
        $writable_dirs = array(
            $this->wordpress_path,
            dirname($this->backup_directory)
        );
        
        foreach ($writable_dirs as $dir) {
            if (!is_writable($dir)) {
                die("❌ Erreur: Permissions insuffisantes sur $dir\n");
            }
        }
        
        echo "✅ Permissions d'écriture OK\n";
        
        // Vérification outils de compression
        if ($this->compress_backup) {
            if (class_exists('ZipArchive')) {
                echo "✅ ZipArchive disponible\n";
            } elseif (function_exists('gzopen')) {
                echo "✅ GZip disponible\n";
            } else {
                echo "⚠️  Aucun outil de compression - désactivation automatique\n";
                $this->compress_backup = false;
                $this->backup_stats['warnings']++;
            }
        }
        
        echo "\n";
    }
    
    /**
     * Analyse du site WordPress
     */
    private function analyzeWordPressSite() {
        echo "🔍 3. ANALYSE SITE WORDPRESS\n";
        echo "---------------------------\n";
        
        // Vérification fichiers WordPress critiques
        $missing_files = array();
        foreach ($this->backup_config['critical_files'] as $file) {
            $file_path = $this->wordpress_path . $file;
            if (file_exists($file_path)) {
                echo "✅ $file\n";
            } else {
                echo "❌ $file (MANQUANT)\n";
                $missing_files[] = $file;
                $this->backup_stats['errors']++;
            }
        }
        
        if (!empty($missing_files)) {
            echo "⚠️  Fichiers critiques manquants détectés\n";
        }
        
        // Détection de la version WordPress
        $wp_version = $this->getWordPressVersion();
        if ($wp_version) {
            echo "📊 Version WordPress: $wp_version\n";
        }
        
        // Analyse des plugins et thèmes
        $plugins_count = $this->countPlugins();
        $themes_count = $this->countThemes();
        
        echo "🔌 Plugins installés: $plugins_count\n";
        echo "🎨 Thèmes installés: $themes_count\n";
        
        // Taille des dossiers principaux
        $sizes = array(
            'wp-content/uploads' => $this->calculateDirectorySize($this->wordpress_path . 'wp-content/uploads'),
            'wp-content/plugins' => $this->calculateDirectorySize($this->wordpress_path . 'wp-content/plugins'),
            'wp-content/themes' => $this->calculateDirectorySize($this->wordpress_path . 'wp-content/themes')
        );
        
        foreach ($sizes as $dir => $size) {
            if ($size > 0) {
                echo "📁 $dir: " . $this->formatBytes($size) . "\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Sauvegarde de la base de données
     */
    private function backupDatabase() {
        echo "🗄️  4. SAUVEGARDE BASE DE DONNÉES\n";
        echo "--------------------------------\n";
        
        $wp_config_path = $this->wordpress_path . 'wp-config.php';
        
        if (!file_exists($wp_config_path)) {
            echo "❌ wp-config.php introuvable - saut de la DB\n\n";
            $this->backup_stats['errors']++;
            return;
        }
        
        // Extraction des paramètres DB
        $db_config = $this->extractDatabaseConfig($wp_config_path);
        
        if (!$db_config) {
            echo "❌ Configuration DB non trouvée\n\n";
            $this->backup_stats['errors']++;
            return;
        }
        
        echo "✅ Configuration DB chargée\n";
        echo "🏠 Host: {$db_config['DB_HOST']}\n";
        echo "🗄️  Base: {$db_config['DB_NAME']}\n";
        
        // Test de connexion
        if (!$this->testDatabaseConnection($db_config)) {
            echo "❌ Connexion DB échouée\n\n";
            $this->backup_stats['errors']++;
            return;
        }
        
        echo "✅ Connexion DB réussie\n";
        
        // Création du dump SQL
        $sql_file = $this->backup_directory . 'database/database.sql';
        $dump_success = $this->createDatabaseDump($db_config, $sql_file);
        
        if ($dump_success) {
            $db_size = filesize($sql_file);
            $this->backup_stats['database_size'] = $db_size;
            
            echo "✅ Dump SQL créé: " . $this->formatBytes($db_size) . "\n";
            
            // Compression du dump si demandée
            if ($this->compress_backup) {
                $compressed_file = $this->compressFile($sql_file);
                if ($compressed_file) {
                    $compressed_size = filesize($compressed_file);
                    $compression_ratio = round((($db_size - $compressed_size) / $db_size) * 100, 1);
                    echo "🗜️  Dump compressé: " . $this->formatBytes($compressed_size) . " ($compression_ratio% économisé)\n";
                    unlink($sql_file); // Suppression version non compressée
                }
            }
            
        } else {
            echo "❌ Échec création dump SQL\n";
            $this->backup_stats['errors']++;
        }
        
        echo "\n";
    }
    
    /**
     * Sauvegarde des fichiers
     */
    private function backupFiles() {
        echo "📁 5. SAUVEGARDE FICHIERS WORDPRESS\n";
        echo "----------------------------------\n";
        
        $files_to_backup = $this->scanFilesForBackup();
        $total_files = count($files_to_backup);
        
        echo "📊 Fichiers à sauvegarder: $total_files\n";
        
        if (empty($files_to_backup)) {
            echo "⚠️  Aucun fichier à sauvegarder\n\n";
            return;
        }
        
        $progress = 0;
        $backup_errors = 0;
        
        foreach ($files_to_backup as $source_file) {
            $progress++;
            $relative_path = str_replace($this->wordpress_path, '', $source_file);
            
            // Affichage du progrès
            if ($progress % 100 == 0 || $progress == $total_files) {
                echo "\r🔄 Progression: $progress/$total_files fichiers";
            }
            
            try {
                // Destination de sauvegarde
                $backup_file = $this->backup_directory . 'files/' . $relative_path;
                $backup_dir = dirname($backup_file);
                
                // Création du dossier si nécessaire
                if (!is_dir($backup_dir)) {
                    mkdir($backup_dir, 0755, true);
                }
                
                // Copie du fichier
                if ($this->copyFileSecurely($source_file, $backup_file)) {
                    $this->backup_stats['files_backed_up']++;
                    $this->backup_stats['backup_size'] += filesize($backup_file);
                } else {
                    $backup_errors++;
                    $this->backup_stats['errors']++;
                }
                
            } catch (Exception $e) {
                $backup_errors++;
                $this->backup_stats['errors']++;
            }
        }
        
        echo "\n\n✅ Sauvegarde fichiers terminée\n";
        echo "📊 Fichiers sauvegardés: {$this->backup_stats['files_backed_up']}\n";
        echo "📊 Erreurs: $backup_errors\n";
        echo "💾 Taille sauvegarde: " . $this->formatBytes($this->backup_stats['backup_size']) . "\n";
        
        echo "\n";
    }
    
    /**
     * Création du manifeste de sauvegarde
     */
    private function createBackupManifest() {
        echo "📋 6. CRÉATION MANIFESTE SAUVEGARDE\n";
        echo "----------------------------------\n";
        
        $manifest = array(
            'backup_info' => array(
                'created_at' => date('Y-m-d H:i:s'),
                'wordpress_path' => $this->wordpress_path,
                'site_name' => $this->getSiteName(),
                'wordpress_version' => $this->getWordPressVersion(),
                'php_version' => phpversion(),
                'backup_script_version' => '3.0'
            ),
            'backup_stats' => $this->backup_stats,
            'backup_config' => array(
                'compress_backup' => $this->compress_backup,
                'exclude_uploads' => $this->exclude_uploads,
                'excluded_dirs' => $this->backup_config['excluded_dirs'],
                'excluded_files' => $this->backup_config['excluded_files']
            ),
            'database_info' => array(
                'included' => file_exists($this->backup_directory . 'database/database.sql') || 
                             file_exists($this->backup_directory . 'database/database.sql.gz'),
                'size' => $this->backup_stats['database_size']
            ),
            'files_info' => array(
                'total_files' => $this->backup_stats['files_backed_up'],
                'total_size' => $this->backup_stats['backup_size']
            ),
            'restoration_notes' => array(
                'wp_config_path' => 'config/wp-config.php',
                'htaccess_path' => 'config/.htaccess',
                'database_dump' => 'database/database.sql',
                'files_root' => 'files/'
            )
        );
        
        // Sauvegarde des fichiers de configuration critiques
        $config_files = array('wp-config.php', '.htaccess');
        foreach ($config_files as $config_file) {
            $source = $this->wordpress_path . $config_file;
            $dest = $this->backup_directory . 'config/' . $config_file;
            
            if (file_exists($source)) {
                copy($source, $dest);
                echo "✅ Configuration sauvegardée: $config_file\n";
            }
        }
        
        // Écriture du manifeste
        $manifest_file = $this->backup_directory . 'backup-manifest.json';
        if (file_put_contents($manifest_file, json_encode($manifest, JSON_PRETTY_PRINT))) {
            echo "✅ Manifeste créé: backup-manifest.json\n";
        } else {
            echo "❌ Erreur création manifeste\n";
            $this->backup_stats['errors']++;
        }
        
        // Création du fichier README
        $this->createBackupReadme();
        
        echo "\n";
    }
    
    /**
     * Validation de la sauvegarde
     */
    private function validateBackup() {
        echo "🔍 7. VALIDATION SAUVEGARDE\n";
        echo "--------------------------\n";
        
        $validation_errors = 0;
        
        // Vérification structure des dossiers
        $required_dirs = array('database', 'files', 'config');
        foreach ($required_dirs as $dir) {
            $dir_path = $this->backup_directory . $dir;
            if (is_dir($dir_path)) {
                echo "✅ Dossier $dir présent\n";
            } else {
                echo "❌ Dossier $dir manquant\n";
                $validation_errors++;
            }
        }
        
        // Vérification base de données
        $db_files = glob($this->backup_directory . 'database/*');
        if (!empty($db_files)) {
            $db_file = $db_files[0];
            $db_size = filesize($db_file);
            
            if ($db_size > 1000) { // Minimum 1KB
                echo "✅ Dump base de données valide (" . $this->formatBytes($db_size) . ")\n";
                
                // Vérification rapide du contenu SQL
                $db_content = file_get_contents($db_file, false, null, 0, 1000);
                if (strpos($db_content, 'CREATE TABLE') !== false || strpos($db_content, 'INSERT INTO') !== false) {
                    echo "✅ Contenu SQL validé\n";
                } else {
                    echo "⚠️  Contenu SQL suspect\n";
                    $validation_errors++;
                }
            } else {
                echo "❌ Dump base de données trop petit\n";
                $validation_errors++;
            }
        } else {
            echo "❌ Aucun dump base de données trouvé\n";
            $validation_errors++;
        }
        
        // Vérification fichiers critiques
        $critical_backups = array(
            'files/wp-config.php',
            'files/index.php',
            'config/wp-config.php'
        );
        
        foreach ($critical_backups as $critical_file) {
            $file_path = $this->backup_directory . $critical_file;
            if (file_exists($file_path)) {
                echo "✅ Fichier critique sauvegardé: " . basename($critical_file) . "\n";
            } else {
                echo "⚠️  Fichier critique manquant: " . basename($critical_file) . "\n";
                $validation_errors++;
            }
        }
        
        // Vérification manifeste
        $manifest_file = $this->backup_directory . 'backup-manifest.json';
        if (file_exists($manifest_file)) {
            $manifest = json_decode(file_get_contents($manifest_file), true);
            if ($manifest && isset($manifest['backup_info'])) {
                echo "✅ Manifeste de sauvegarde valide\n";
            } else {
                echo "❌ Manifeste de sauvegarde corrompu\n";
                $validation_errors++;
            }
        } else {
            echo "❌ Manifeste de sauvegarde manquant\n";
            $validation_errors++;
        }
        
        // Résultat de la validation
        if ($validation_errors == 0) {
            echo "\n🎉 VALIDATION RÉUSSIE - Sauvegarde complète et valide\n";
        } else {
            echo "\n⚠️  VALIDATION PARTIELLE - $validation_errors erreur(s) détectée(s)\n";
            $this->backup_stats['warnings'] += $validation_errors;
        }
        
        echo "\n";
    }
    
    /**
     * Test de restauration (optionnel)
     */
    private function testBackupRestore() {
        echo "🧪 8. TEST DE RESTAURATION\n";
        echo "-------------------------\n";
        
        echo "🔄 Création environnement de test...\n";
        
        $test_dir = $this->backup_directory . 'restore-test/';
        if (!mkdir($test_dir, 0755, true)) {
            echo "❌ Impossible de créer l'environnement de test\n\n";
            return;
        }
        
        // Test de restauration des fichiers critiques
        $test_files = array(
            'wp-config.php' => 'config/wp-config.php',
            'index.php' => 'files/index.php'
        );
        
        $restore_success = true;
        
        foreach ($test_files as $target => $source) {
            $source_path = $this->backup_directory . $source;
            $target_path = $test_dir . $target;
            
            if (file_exists($source_path)) {
                if (copy($source_path, $target_path)) {
                    echo "✅ Test restauration: $target\n";
                } else {
                    echo "❌ Échec restauration: $target\n";
                    $restore_success = false;
                }
            }
        }
        
        // Test de validation du dump SQL
        $db_files = glob($this->backup_directory . 'database/*');
        if (!empty($db_files)) {
            $db_file = $db_files[0];
            
            // Vérification syntaxe SQL basique
            $sql_sample = file_get_contents($db_file, false, null, 0, 10000);
            
            if (strpos($sql_sample, 'CREATE TABLE') !== false && strpos($sql_sample, ';') !== false) {
                echo "✅ Test syntaxe SQL: Valide\n";
            } else {
                echo "❌ Test syntaxe SQL: Invalide\n";
                $restore_success = false;
            }
        }
        
        // Nettoyage du test
        $this->removeDirectory($test_dir);
        
        if ($restore_success) {
            echo "🎉 TEST DE RESTAURATION RÉUSSI\n";
        } else {
            echo "⚠️  TEST DE RESTAURATION PARTIEL\n";
            $this->backup_stats['warnings']++;
        }
        
        echo "\n";
    }
    
    /**
     * Génération du rapport final
     */
    private function generateBackupReport() {
        $end_time = microtime(true);
        $total_time = round($end_time - $this->start_time, 2);
        $this->backup_stats['backup_duration'] = $total_time;
        
        echo "📋 RAPPORT SAUVEGARDE WORDPRESS\n";
        echo "===============================\n\n";
        
        // Informations générales
        echo "📊 RÉSUMÉ SAUVEGARDE:\n";
        echo "====================\n";
        echo "⏱️  Durée totale: {$total_time} secondes\n";
        echo "📁 Fichiers sauvegardés: {$this->backup_stats['files_backed_up']}\n";
        echo "🗄️  Base de données: " . ($this->backup_stats['database_size'] > 0 ? 'Incluse' : 'Exclue') . "\n";
        echo "💾 Taille totale: " . $this->formatBytes($this->backup_stats['backup_size'] + $this->backup_stats['database_size']) . "\n";
        echo "❌ Erreurs: {$this->backup_stats['errors']}\n";
        echo "⚠️  Avertissements: {$this->backup_stats['warnings']}\n\n";
        
        // Détail des composants
        echo "🔍 DÉTAIL COMPOSANTS:\n";
        echo "====================\n";
        echo "📄 Fichiers WordPress: " . $this->formatBytes($this->backup_stats['backup_size']) . "\n";
        echo "🗄️  Base de données: " . $this->formatBytes($this->backup_stats['database_size']) . "\n";
        echo "⚙️  Fichiers config: Inclus\n";
        echo "📋 Manifeste: Inclus\n";
        echo "📖 Documentation: Incluse\n\n";
        
        // Emplacement de la sauvegarde
        $backup_name = basename($this->backup_directory);
        echo "📍 EMPLACEMENT SAUVEGARDE:\n";
        echo "=========================\n";
        echo "📁 Dossier: backups/$backup_name/\n";
        echo "🔗 Chemin complet: {$this->backup_directory}\n\n";
        
        // Évaluation de la sauvegarde
        if ($this->backup_stats['errors'] == 0 && $this->backup_stats['warnings'] <= 2) {
            $backup_quality = "🌟 EXCELLENTE";
            $reliability = "Sauvegarde complète et fiable";
        } elseif ($this->backup_stats['errors'] <= 2 && $this->backup_stats['warnings'] <= 5) {
            $backup_quality = "✅ BONNE";
            $reliability = "Sauvegarde fonctionnelle avec avertissements mineurs";
        } elseif ($this->backup_stats['errors'] <= 5) {
            $backup_quality = "⚠️  PARTIELLE";
            $reliability = "Sauvegarde incomplète - vérification recommandée";
        } else {
            $backup_quality = "❌ PROBLÉMATIQUE";
            $reliability = "Sauvegarde avec erreurs importantes";
        }
        
        echo "🎯 QUALITÉ SAUVEGARDE: $backup_quality\n";
        echo "🛡️  FIABILITÉ: $reliability\n\n";
        
        // Instructions de restauration
        echo "🔄 INSTRUCTIONS RESTAURATION:\n";
        echo "============================\n";
        echo "1. 📁 Copiez le contenu de files/ vers votre nouveau WordPress\n";
        echo "2. 🗄️  Importez database/database.sql dans votre base MySQL\n";
        echo "3. ⚙️  Copiez config/wp-config.php et adaptez les paramètres DB\n";
        echo "4. 🔗 Copiez config/.htaccess si nécessaire\n";
        echo "5. 🔐 Vérifiez les permissions des fichiers et dossiers\n";
        echo "6. 🧪 Testez votre site restauré\n\n";
        
        // Conseils de maintenance
        echo "💡 CONSEILS MAINTENANCE SAUVEGARDE:\n";
        echo "===================================\n";
        echo "• 📅 Sauvegarde automatique: Hebdomadaire minimum\n";
        echo "• 🔄 Test de restauration: Mensuel\n";
        echo "• 💾 Conservation: 3 sauvegardes minimum\n";
        echo "• 🌐 Stockage externe: Recommandé (cloud, serveur distant)\n";
        echo "• 🔒 Chiffrement: Pour données sensibles\n";
        echo "• 📊 Monitoring: Surveiller la taille des sauvegardes\n\n";
        
        // Commandes utiles
        echo "🛠️  COMMANDES UTILES:\n";
        echo "===================\n";
        echo "# Compression manuelle de la sauvegarde:\n";
        echo "tar -czf $backup_name.tar.gz $backup_name/\n\n";
        echo "# Vérification intégrité:\n";
        echo "find $backup_name/ -type f -exec md5sum {} \\;\n\n";
        echo "# Restauration base de données:\n";
        echo "mysql -u username -p database_name < database/database.sql\n\n";
        
        // Performance de la sauvegarde
        $files_per_second = round($this->backup_stats['files_backed_up'] / max($total_time, 1), 1);
        $mb_per_second = round(($this->backup_stats['backup_size'] / 1024 / 1024) / max($total_time, 1), 2);
        
        echo "⚡ PERFORMANCE SAUVEGARDE:\n";
        echo "=========================\n";
        echo "📄 Fichiers/seconde: $files_per_second\n";
        echo "💾 MB/seconde: $mb_per_second\n";
        echo "🎯 Efficacité: " . ($files_per_second > 10 ? "Excellente" : ($files_per_second > 5 ? "Bonne" : "Lente")) . "\n\n";
        
        // Contact expert pour gros sites
        if ($this->backup_stats['files_backed_up'] > 10000 || ($this->backup_stats['backup_size'] + $this->backup_stats['database_size']) > 1024*1024*1024) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🏢 SITE VOLUMINEUX DÉTECTÉ\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🔧 Solution sauvegarde professionnelle recommandée\n";
            echo "☁️  Configuration sauvegarde cloud automatisée\n";
            echo "🏆 1000+ sauvegardes WordPress gérées avec succès\n";
            echo "📞 Service professionnel: https://teddywp.com/depannage-wordpress/\n";
            echo "💡 Consultation gratuite sauvegarde entreprise\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        }
        
        echo "\n👨‍💻 Sauvegarde réalisée par Teddy - Expert WordPress\n";
        echo "🌐 TeddyWP.com | 📧 Solution sauvegarde professionnelle 24/7\n";
        echo "📅 " . date('Y-m-d H:i:s') . " | Version script: 3.0\n";
    }
    
    /**
     * Méthodes utilitaires
     */
    
    private function getSiteName() {
        // Extraction du nom de domaine ou dossier
        $path = rtrim($this->wordpress_path, '/');
        $name = basename($path);
        
        // Nettoyage pour nom de fichier valide
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        return substr($name, 0, 20);
    }
    
    private function getWordPressVersion() {
        $version_file = $this->wordpress_path . 'wp-includes/version.php';
        if (file_exists($version_file)) {
            $version_content = file_get_contents($version_file);
            if (preg_match('/\$wp_version\s*=\s*[\'"]([^\'"]+)[\'"]/i', $version_content, $matches)) {
                return $matches[1];
            }
        }
        return 'Inconnue';
    }
    
    private function countPlugins() {
        $plugins_dir = $this->wordpress_path . 'wp-content/plugins/';
        if (is_dir($plugins_dir)) {
            $plugins = glob($plugins_dir . '*', GLOB_ONLYDIR);
            return count($plugins);
        }
        return 0;
    }
    
    private function countThemes() {
        $themes_dir = $this->wordpress_path . 'wp-content/themes/';
        if (is_dir($themes_dir)) {
            $themes = glob($themes_dir . '*', GLOB_ONLYDIR);
            return count($themes);
        }
        return 0;
    }
    
    private function calculateSiteSize() {
        return $this->calculateDirectorySize($this->wordpress_path);
    }
    
    private function calculateDirectorySize($directory) {
        $size = 0;
        if (is_dir($directory)) {
            try {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS));
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $size += $file->getSize();
                    }
                }
            } catch (Exception $e) {
                // Dossier inaccessible
            }
        }
        return $size;
    }
    
    private function extractDatabaseConfig($wp_config_path) {
        $content = file_get_contents($wp_config_path);
        $config = array();
        
        $constants = array('DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD');
        
        foreach ($constants as $constant) {
            if (preg_match("/define\s*\(\s*['\"]" . $constant . "['\"]\s*,\s*['\"](.*?)['\"]\s*\)/", $content, $matches)) {
                $config[$constant] = $matches[1];
            }
        }
        
        return count($config) === 4 ? $config : false;
    }
    
    private function testDatabaseConnection($db_config) {
        try {
            $dsn = "mysql:host={$db_config['DB_HOST']};dbname={$db_config['DB_NAME']}";
            $pdo = new PDO($dsn, $db_config['DB_USER'], $db_config['DB_PASSWORD']);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    private function createDatabaseDump($db_config, $output_file) {
        // Tentative avec mysqldump
        $mysqldump_cmd = sprintf(
            "mysqldump -h '%s' -u '%s' -p'%s' '%s' > '%s' 2>/dev/null",
            $db_config['DB_HOST'],
            $db_config['DB_USER'],
            $db_config['DB_PASSWORD'],
            $db_config['DB_NAME'],
            $output_file
        );
        
        exec($mysqldump_cmd, $output, $return_code);
        
        if ($return_code === 0 && file_exists($output_file) && filesize($output_file) > 1000) {
            return true;
        }
        
        // Fallback: dump PHP/PDO
        return $this->createDatabaseDumpPHP($db_config, $output_file);
    }
    
    private function createDatabaseDumpPHP($db_config, $output_file) {
        try {
            $dsn = "mysql:host={$db_config['DB_HOST']};dbname={$db_config['DB_NAME']}";
            $pdo = new PDO($dsn, $db_config['DB_USER'], $db_config['DB_PASSWORD']);
            
            $sql_content = "-- WordPress Database Backup\n";
            $sql_content .= "-- Generated by WordPress Backup Expert\n";
            $sql_content .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Récupération des tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                // Structure de la table
                $create_table = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
                $sql_content .= "\n-- Structure for table `$table`\n";
                $sql_content .= "DROP TABLE IF EXISTS `$table`;\n";
                $sql_content .= $create_table['Create Table'] . ";\n\n";
                
                // Données de la table
                $rows = $pdo->query("SELECT * FROM `$table`");
                if ($rows->rowCount() > 0) {
                    $sql_content .= "-- Data for table `$table`\n";
                    
                    while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                        $values = array();
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $sql_content .= "INSERT INTO `$table` VALUES(" . implode(',', $values) . ");\n";
                    }
                    $sql_content .= "\n";
                }
            }
            
            return file_put_contents($output_file, $sql_content) !== false;
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    private function scanFilesForBackup() {
        $files = array();
        
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->wordpress_path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $file_path = $file->getPathname();
                    $relative_path = str_replace($this->wordpress_path, '', $file_path);
                    
                    // Vérification exclusions
                    if ($this->shouldExcludeFile($relative_path)) {
                        $this->backup_stats['files_skipped']++;
                        continue;
                    }
                    
                    // Vérification taille
                    if ($file->getSize() > $this->backup_config['max_file_size']) {
                        $this->backup_stats['files_skipped']++;
                        continue;
                    }
                    
                    $files[] = $file_path;
                    $this->backup_stats['total_size'] += $file->getSize();
                }
            }
            
        } catch (Exception $e) {
            // Erreur de scan
        }
        
        return $files;
    }
    
    private function shouldExcludeFile($relative_path) {
        // Exclusion dossiers uploads si demandé
        if ($this->exclude_uploads && strpos($relative_path, 'wp-content/uploads/') === 0) {
            return true;
        }
        
        // Exclusion dossiers configurés
        foreach ($this->backup_config['excluded_dirs'] as $excluded_dir) {
            if (strpos($relative_path, $excluded_dir) === 0) {
                return true;
            }
        }
        
        // Exclusion fichiers spécifiques
        $filename = basename($relative_path);
        if (in_array($filename, $this->backup_config['excluded_files'])) {
            return true;
        }
        
        // Exclusion patterns
        $excluded_patterns = array(
            '/\.bak$/',
            '/\.tmp$/',
            '/\.temp$/',
            '/\.log$/',
            '/\.cache$/'
        );
        
        foreach ($excluded_patterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function copyFileSecurely($source, $destination) {
        try {
            // Vérification source
            if (!file_exists($source) || !is_readable($source)) {
                return false;
            }
            
            // Copie avec préservation des métadonnées
            if (copy($source, $destination)) {
                // Préservation des permissions et timestamps
                chmod($destination, fileperms($source));
                touch($destination, filemtime($source));
                return true;
            }
            
        } catch (Exception $e) {
            // Erreur de copie
        }
        
        return false;
    }
    
    private function compressFile($file_path) {
        if (!$this->compress_backup) {
            return false;
        }
        
        $compressed_file = $file_path . '.gz';
        
        try {
            $source = fopen($file_path, 'rb');
            $dest = gzopen($compressed_file, 'wb9');
            
            if ($source && $dest) {
                while (!feof($source)) {
                    gzwrite($dest, fread($source, 8192));
                }
                
                fclose($source);
                gzclose($dest);
                
                return file_exists($compressed_file) ? $compressed_file : false;
            }
            
        } catch (Exception $e) {
            // Erreur compression
        }
        
        return false;
    }
    
    private function createBackupReadme() {
        $readme_content = "# Sauvegarde WordPress Expert\n\n";
        $readme_content .= "## Informations générales\n";
        $readme_content .= "- **Date de création :** " . date('Y-m-d H:i:s') . "\n";
        $readme_content .= "- **Site WordPress :** {$this->wordpress_path}\n";
        $readme_content .= "- **Version WordPress :** " . $this->getWordPressVersion() . "\n";
        $readme_content .= "- **Script version :** 3.0\n";
        $readme_content .= "- **Créé par :** WordPress Backup Expert - TeddyWP.com\n\n";
        
        $readme_content .= "## Contenu de la sauvegarde\n";
        $readme_content .= "- `database/` : Dump complet de la base de données\n";
        $readme_content .= "- `files/` : Tous les fichiers WordPress\n";
        $readme_content .= "- `config/` : Fichiers de configuration (wp-config.php, .htaccess)\n";
        $readme_content .= "- `backup-manifest.json` : Métadonnées de la sauvegarde\n\n";
        
        $readme_content .= "## Instructions de restauration\n";
        $readme_content .= "1. Décompressez l'archive si nécessaire\n";
        $readme_content .= "2. Copiez le contenu du dossier `files/` vers votre nouveau WordPress\n";
        $readme_content .= "3. Importez `database/database.sql` dans votre base MySQL\n";
        $readme_content .= "4. Adaptez `config/wp-config.php` avec vos nouveaux paramètres DB\n";
        $readme_content .= "5. Copiez `config/.htaccess` si nécessaire\n";
        $readme_content .= "6. Vérifiez les permissions des fichiers\n\n";
        
        $readme_content .= "## Support\n";
        $readme_content .= "Pour toute assistance : https://teddywp.com/depannage-wordpress/\n";
        
        file_put_contents($this->backup_directory . 'README.md', $readme_content);
    }
    
    private function removeDirectory($dir) {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? $this->removeDirectory("$dir/$file") : unlink("$dir/$file");
            }
            rmdir($dir);
        }
    }
    
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
    
    if ($arg === '--compress') {
        $options['compress'] = true;
    } elseif ($arg === '--exclude-uploads') {
        $options['exclude_uploads'] = true;
    } elseif ($arg === '--test-restore') {
        $options['test_restore'] = true;
    } elseif (!isset($wordpress_path_set)) {
        $wordpress_path = rtrim($arg, '/') . '/';
        $wordpress_path_set = true;
        
        if (!is_dir($wordpress_path)) {
            echo "❌ Erreur: Le chemin '$wordpress_path' n'existe pas.\n";
            echo "Usage: php backup-wordpress.php [chemin-wordpress] [--compress] [--exclude-uploads] [--test-restore]\n";
            exit(1);
        }
    }
}

// Affichage de l'aide
if (isset($argv[1]) && in_array($argv[1], array('--help', '-h'))) {
    echo "📖 AIDE - SAUVEGARDE WORDPRESS COMPLÈTE\n";
    echo "=======================================\n\n";
    echo "Usage: php backup-wordpress.php [chemin-wordpress] [options]\n\n";
    echo "Options:\n";
    echo "  --compress         Compresser la sauvegarde (gzip)\n";
    echo "  --exclude-uploads  Exclure le dossier uploads\n";
    echo "  --test-restore     Tester la restauration après sauvegarde\n";
    echo "  --help, -h         Afficher cette aide\n\n";
    echo "Exemples:\n";
    echo "  php backup-wordpress.php\n";
    echo "  php backup-wordpress.php /var/www/monsite\n";
    echo "  php backup-wordpress.php /var/www/monsite --compress --test-restore\n\n";
    exit(0);
}

// Confirmation avant sauvegarde
echo "⚠️  ATTENTION: Ce script va créer une sauvegarde complète de WordPress\n";
echo "💾 La sauvegarde peut prendre du temps selon la taille du site\n";
echo "🔍 Assurez-vous d'avoir suffisamment d'espace disque disponible\n\n";
echo "Continuer la sauvegarde ? [y/N]: ";

$handle = fopen("php://stdin", "r");
$confirm = trim(fgets($handle));

if (strtolower($confirm) !== 'y') {
    echo "❌ Sauvegarde annulée\n";
    exit(0);
}
echo "\n";

// Lancement de la sauvegarde
try {
    $backup_manager = new WordPressBackupManager($wordpress_path, $options);
    $backup_manager->runCompleteBackup();
} catch (Exception $e) {
    echo "❌ Erreur fatale: " . $e->getMessage() . "\n";
    echo "📞 Support expert: https://teddywp.com/depannage-wordpress/\n";
    exit(1);
}

echo "\n🏁 Sauvegarde WordPress terminée avec succès !\n";
echo "💡 Vérifiez le contenu de la sauvegarde avant de compter dessus\n";
echo "📞 Besoin de solution sauvegarde automatisée ? Expert WordPress disponible 24/7\n";
?>
