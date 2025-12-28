# Avro Phonetic

[![Latest Version on Packagist](https://img.shields.io/packagist/v/eru/avro-phonetic.svg?style=flat-square)](https://packagist.org/packages/eru/avro-phonetic)
[![Total Downloads](https://img.shields.io/packagist/dt/eru/avro-phonetic.svg?style=flat-square)](https://packagist.org/packages/eru/avro-phonetic)
[![License](https://img.shields.io/packagist/l/eru/avro-phonetic.svg?style=flat-square)](https://packagist.org/packages/eru/avro-phonetic)
[![PHP Version](https://img.shields.io/packagist/php-v/eru/avro-phonetic.svg?style=flat-square)](https://packagist.org/packages/eru/avro-phonetic)

A high-performance **Avro Phonetic** transliteration library for PHP. Convert Banglish (Romanized Bengali) to Bengali script with blazing-fast Trie-based pattern matching and context-sensitive rule engine.

```
ami banglay gan gai → আমি বাংলায় গান গাই
```

## ✨ Features

- 🚀 **Trie-Based Pattern Matching** — O(m) lookup time instead of O(n×m) linear search
- 🎯 **Context-Sensitive Rules** — Intelligent conversion based on surrounding characters
- 🔌 **Laravel Ready** — Auto-discovery, Facade, and config publishing out of the box
- 📦 **Standalone Compatible** — Works with any PHP project, no framework required
- 🧪 **Fully Tested** — 180+ tests covering all functionality
- ⚡ **Memory Efficient** — Singleton pattern with lazy-loaded grammar
- 🔧 **PHP 5.6+** — Compatible with PHP 5.6 through 8.x

## 🎯 What Makes This Unique?

### Trie Data Structure for Fast Lookups

Unlike traditional implementations that linearly scan through hundreds of patterns, we use a **Trie (prefix tree)** data structure. This provides:

- **O(m) lookup time** where m is the pattern length (vs O(n×m) for linear search)
- **Efficient longest-match finding** — automatically finds the longest matching pattern
- **Minimal memory overhead** — shared prefixes are stored only once

### Context-Sensitive Rule Engine

Our rule engine understands context! The same input can produce different outputs based on what comes before or after:

```php
Avro::to('rri');   // ঋ (at word start)
Avro::to('krri');  // কৃ (after consonant)

Avro::to('OI');    // ঐ (standalone)
Avro::to('kOI');   // কৈ (after consonant)
```

**Supported rule scopes:**
- `vowel` / `!vowel` — Check if prefix/suffix is a vowel
- `consonant` / `!consonant` — Check if prefix/suffix is a consonant  
- `punctuation` / `!punctuation` — Check for word boundaries
- `exact` / `!exact` — Match specific characters

## 📦 Installation

```bash
composer require eru/avro-phonetic
```

## 🚀 Quick Start

### Basic Usage

```php
use Eru\AvroPhonetic\Avro;

// Static method
echo Avro::to('ami banglay gan gai');
// Output: আমি বাংলায় গান গাই

// Instance method
$avro = new Avro();
echo $avro->parse('amar sonar bangla');
// Output: আমার সনার বাংলা
```

### Helper Functions

```php
// Quick conversion
echo avro('bangladesh');  // বাংলাদেশ
echo bangla('dhaka');     // ঢাকা

// Get instance for chaining
$result = avro()->parse('tumi kemon acho');
```

### Laravel Usage

The package auto-registers with Laravel 5.5+. No additional setup required!

**Using the Facade:**

```php
use Eru\AvroPhonetic\Facades\Avro;

// In your controller
public function store(Request $request)
{
    $bengaliText = Avro::to($request->input('text'));
    // ...
}
```

**In Blade templates:**

```blade
<p>{{ avro('ami tomake bhalobashi') }}</p>
<p>{{ bangla('sundor bangladesh') }}</p>
```

**Publish configuration (optional):**

```bash
php artisan vendor:publish --tag=avro-phonetic-config
```

This creates `config/avro-phonetic.php` where you can customize the grammar file path.

## 📖 API Reference

### Avro Class

```php
use Eru\AvroPhonetic\Avro;

// Static conversion
Avro::to(string $text): string

// Instance methods
$avro = new Avro();
$avro->parse(string $text): string
$avro->convert(string $text): string  // Alias for parse()

// Factory methods
Avro::getInstance(): Avro                        // Singleton
Avro::withGrammar(array $grammar): Avro          // Custom grammar
Avro::fromGrammarFile(string $path): Avro        // Load from file

// Access parser
$avro->getParser(): PhoneticParser
```

### Helper Functions

```php
// Convert text or get instance
avro(?string $text = null): Avro|string

// Always converts text
bangla(string $text): string
```

## ⚙️ Advanced Usage

### Custom Grammar

You can provide your own grammar rules:

```php
$customGrammar = [
    'patterns' => [
        ['find' => 'ph', 'replace' => 'ফ', 'rules' => []],
        // ... more patterns
    ],
    'vowel' => 'aeiou',
    'consonant' => 'bcdfghjklmnpqrstvwxyz',
    'number' => '1234567890',
    'casesensitive' => 'oiudgjnrstyz',
];

$avro = Avro::withGrammar($customGrammar);
echo $avro->parse('phone'); // ফনে
```

### Load Grammar from File

```php
$avro = Avro::fromGrammarFile('/path/to/custom-grammar.json');
```

### Direct Parser Access

For performance-critical applications, access the parser directly:

```php
use Eru\AvroPhonetic\PhoneticParser;

$grammar = json_decode(file_get_contents('grammar.json'), true);
$parser = new PhoneticParser($grammar);

// Parse multiple texts
foreach ($texts as $text) {
    $results[] = $parser->parse($text);
}
```

## 🏎️ Performance

Benchmarks on typical hardware (PHP 8.x):

| Operation | Time |
|-----------|------|
| Single word conversion | ~0.05ms |
| Sentence (10 words) | ~0.1ms |
| Paragraph (100 words) | ~0.8ms |
| Trie initialization | ~15ms (one-time) |

The Trie is built once and reused via singleton pattern, making subsequent conversions extremely fast.

## 🧪 Testing

```bash
# Run all tests
composer test

# Or directly with PHPUnit
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite Parser
./vendor/bin/phpunit --testsuite Performance

# With coverage report
./vendor/bin/phpunit --coverage-html coverage
```

## 📁 Project Structure

```
avro-phonetic/
├── src/
│   ├── Avro.php                    # Main entry point
│   ├── PhoneticParser.php          # Core parser with rule engine
│   ├── Trie.php                    # Trie data structure
│   ├── TrieNode.php                # Trie node class
│   ├── AvroPhoneticServiceProvider.php  # Laravel service provider
│   ├── Facades/
│   │   └── Avro.php                # Laravel facade
│   └── helpers.php                 # Global helper functions
├── config/
│   └── avro-phonetic.php           # Laravel config
├── resources/
│   └── grammar.json                # Avro phonetic rules
└── tests/
    ├── AvroTest.php
    ├── PhoneticParserTest.php
    ├── RuleEngineTest.php
    ├── TrieTest.php
    └── ...
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Write tests for your changes
4. Ensure all tests pass (`composer test`)
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## 📜 License

This package is open-sourced software licensed under the [GPL-3.0 License](LICENSE).

## 🙏 Credits

- Based on the [Avro Phonetic](https://avro.im) keyboard layout
- Rule engine ported from [pyAvroPhonetic](https://github.com/kaustavdm/pyAvroPhonetic) by Kaustav Das Modak
- Developed by [Erfan Ahmed Siam](https://github.com/imerfanahmed)

---

<p align="center">
  Made with ❤️ for the Bengali language
</p>
