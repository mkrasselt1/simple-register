# Mehrsprachigkeit (Internationalization) für Simple Register

## Übersicht

Die Simple Register Anwendung unterstützt jetzt Mehrsprachigkeit mit einem einfachen, aber effektiven System basierend auf PHP-Arrays.

## Struktur

```
languages/
├── en.php      # Englische Übersetzungen
└── de.php      # Deutsche Übersetzungen
```

## Verwendung in PHP-Code

```php
// In Controllern
$lang = $this->lang(); // Language-Instanz bekommen
$text = $this->t('key_name'); // Übersetzung bekommen

// Global in Templates/Views
echo $__('key_name'); // Übersetzung ausgeben
echo $__('key_name', 'Fallback text'); // Mit Fallback
```

## Verwendung in Templates

```php
<!-- Titel übersetzen -->
<title><?php echo $__('page_title'); ?></title>

<!-- Labels übersetzen -->
<label><?php echo $__('username'); ?></label>

<!-- Sprachauswahl einbauen -->
<?php include '_language_selector.php'; ?>
```

## Sprache wechseln

- **URL-Parameter**: `?lang=de` oder `?lang=en`
- **Session**: Wird automatisch gespeichert
- **Fallback**: Englisch als Standardsprache

## Neue Sprachen hinzufügen

1. Neue Sprachdatei erstellen: `languages/[code].php`
2. Sprachcode zu `Language::getAvailableLanguages()` hinzufügen
3. Alle Schlüssel aus der englischen Datei übersetzen

## Neue Übersetzung hinzufügen

1. Schlüssel zu allen Sprachdateien hinzufügen
2. In Templates/Controllers verwenden

## Beispiel für eine neue Übersetzung

```php
// In languages/en.php
'new_feature' => 'New Feature',

// In languages/de.php
'new_feature' => 'Neue Funktion',

// In Template
<button><?php echo $__('new_feature'); ?></button>
```

## Vorteile dieses Ansatzes

- ✅ Einfach zu warten
- ✅ Keine externen Abhängigkeiten
- ✅ Schnell (keine Datenbank-Abfragen)
- ✅ Einfach zu erweitern
- ✅ Fallback-Mechanismus
- ✅ Session-basierte Sprachauswahl