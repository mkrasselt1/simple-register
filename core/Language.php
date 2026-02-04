<?php
/**
 * Language/I18n Class for Simple Register
 */

class Language {
    private static $instance = null;
    private $language = 'en';
    private $translations = [];

    private function __construct() {
        $this->loadLanguage();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set the current language
     */
    public function setLanguage($lang) {
        $this->language = $lang;
        $this->loadLanguage();
        // Store in session
        $_SESSION['language'] = $lang;
    }

    /**
     * Get the current language
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * Get a translated string
     */
    public function get($key, $default = null) {
        return $this->translations[$key] ?? $default ?? $key;
    }

    /**
     * Get all available languages
     */
    public function getAvailableLanguages() {
        return [
            'en' => 'English',
            'de' => 'Deutsch'
        ];
    }

    /**
     * Load language file
     */
    private function loadLanguage() {
        // Check session first
        if (isset($_SESSION['language'])) {
            $this->language = $_SESSION['language'];
        }

        // Check GET parameter for language switching
        if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $this->getAvailableLanguages())) {
            $this->language = $_GET['lang'];
            $_SESSION['language'] = $this->language;
        }

        $langFile = __DIR__ . '/../languages/' . $this->language . '.php';

        if (file_exists($langFile)) {
            $this->translations = include $langFile;
        } else {
            // Fallback to English
            $fallbackFile = __DIR__ . '/../languages/en.php';
            if (file_exists($fallbackFile)) {
                $this->translations = include $fallbackFile;
            }
        }
    }
}

/**
 * Global translation function
 */
function __($key, $default = null) {
    return Language::getInstance()->get($key, $default);
}