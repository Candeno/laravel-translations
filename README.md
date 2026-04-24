# candeno/laravel-translations

EAV-based per-locale translations for Eloquent models. Stores translated field values in a shared `translations` table with a `HasTranslations` trait for get/set/bulk-write operations.

## Installation

```bash
composer require candeno/laravel-translations
```

Publish and run the migration:

```bash
php artisan vendor:publish --tag=candeno-translations-migrations
php artisan migrate
```

## Usage

Add the trait to any Eloquent model:

```php
use Candeno\LaravelTranslations\Concerns\HasTranslations;

class Activity extends Model
{
    use HasTranslations;

    protected array $translatable = ['title', 'subtitle', 'why_text'];
}
```

### Get a translation

```php
// Falls back: requested locale → fallback locale → raw model attribute
$activity->getTranslation('title', 'uk', 'da');
```

### Set a translation

```php
$activity->setTranslation('title', 'uk', 'Назва');
```

### Set all locales for a field

```php
$activity->setTranslations('title', [
    'da' => 'Titel',
    'uk' => 'Назва',
]);
```

### Bulk-write from a localizedContent map

Useful when consuming the automation form payload directly:

```php
// $localizedContent = ['da' => ['title' => '...', 'description' => '...'], 'uk' => [...]]
$activity->setLocalizedContent($localizedContent, [
    'title'       => 'title',
    'description' => 'subtitle',
    'buttonText'  => 'action_label',
]);
```

## Schema

| column       | type                 | notes                   |
|--------------|----------------------|-------------------------|
| `table_name` | `varchar`            | Eloquent model table    |
| `row_id`     | `varchar`            | Model primary key (int or UUID) |
| `locale`     | `varchar(10)`        | e.g. `da`, `uk`, `en`  |
| `field`      | `varchar`            | Model field name        |
| `value`      | `text`               | Translated value        |

A unique index on `(table_name, row_id, locale, field)` ensures upserts are safe.
