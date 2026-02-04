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
        // If stored lang differs from current, set it
        if (storedLang !== '<?php echo $currentLang; ?>') {
            changeLanguage(storedLang, false); // false to not store again
        }
    }
});

function changeLanguage(lang, store = true) {
    if (store) {
        localStorage.setItem('language', lang);
    }
    // Send AJAX to set session
    fetch('api/language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'language=' + encodeURIComponent(lang)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to apply new language
            window.location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>