<?php
/**
 * Language selector component
 * Include this in your views where you want the language selector
 */

// Get current language and available languages
$currentLang = $lang->getLanguage();
$availableLangs = $lang->getAvailableLanguages();
?>

<div class="language-selector" style="display: inline-block; margin-left: 10px;">
    <select id="language-select" onchange="changeLanguage(this.value)" style="padding: 4px 8px; border: 1px solid #4ecca3; border-radius: 4px; background: #1a1a2e; color: white; font-size: 0.9em;">
        <?php foreach ($availableLangs as $code => $name): ?>
            <option value="<?php echo View::escape($code); ?>" <?php echo $code === $currentLang ? 'selected' : ''; ?>>
                <?php echo View::escape($name); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<script>
function changeLanguage(lang) {
    // Add language parameter to current URL
    const url = new URL(window.location);
    url.searchParams.set('lang', lang);
    window.location.href = url.toString();
}
</script>