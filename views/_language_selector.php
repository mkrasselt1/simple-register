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
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('language-select');
    const storedLang = localStorage.getItem('language');
    if (storedLang && select.querySelector(`option[value="${storedLang}"]`)) {
        select.value = storedLang;
        if (storedLang !== '<?php echo $currentLang; ?>') {
            changeLanguage(storedLang, false);
        }
    }
});

function changeLanguage(lang, store = true) {
    if (store) {
        localStorage.setItem('language', lang);
    }
    try {
        const params = new URLSearchParams(window.location.search || '');
        params.set('lang', lang);
        const newUrl = window.location.pathname + '?' + params.toString() + window.location.hash;
        window.location.href = newUrl;
    } catch (error) {
        window.location.href = window.location.pathname + '?lang=' + encodeURIComponent(lang);
    }
}
</script>