<?php
/**
 * Simple View/Template Engine
 */

class View {
    private static $viewPath = __DIR__ . '/../views/';
    
    /**
     * Render a view template with data
     * @param string $template Template name (without .php extension)
     * @param array $data Data to pass to the view
     * @return string Rendered HTML
     */
    public static function render($template, $data = []) {
        // Extract data to make variables available in template
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the template file
        $templateFile = self::$viewPath . $template . '.php';
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            throw new Exception("View template not found: {$template}");
        }
        
        // Return the buffered content
        return ob_get_clean();
    }
    
    /**
     * Render and output a view template
     * @param string $template Template name
     * @param array $data Data to pass to the view
     */
    public static function display($template, $data = []) {
        echo self::render($template, $data);
    }
    
    /**
     * Escape HTML special characters
     * @param string $string String to escape
     * @return string Escaped string
     */
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
