<?php

namespace Candeno\LaravelTranslations\Concerns;

use Candeno\LaravelTranslations\Models\Translation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Adds per-locale translations stored in the shared `translations` table.
 *
 * Usage in a model:
 *   use HasTranslations;
 *   protected array $translatable = ['title', 'subtitle', 'why_text'];
 *
 * The model's own columns serve as the default-locale fallback for rows
 * that predate the translations table.
 */
trait HasTranslations
{
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'row_id', $this->getKeyName())
            ->where('table_name', $this->getTable());
    }

    /**
     * Get a single translated value.
     * Falls back to: $locale → $fallbackLocale → raw model attribute.
     */
    public function getTranslation(string $field, string $locale, string $fallbackLocale = ''): string
    {
        $loaded = $this->getLoadedTranslations();

        $value = $loaded->first(
            fn(Translation $t) => $t->field === $field && $t->locale === $locale
        )?->value;

        if ($value !== null && $value !== '') {
            return $value;
        }

        if ($fallbackLocale !== '' && $fallbackLocale !== $locale) {
            $value = $loaded->first(
                fn(Translation $t) => $t->field === $field && $t->locale === $fallbackLocale
            )?->value;

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return (string) ($this->getRawOriginal($field) ?? $this->getAttribute($field) ?? '');
    }

    /**
     * Get all translations for a field, keyed by locale.
     */
    public function getTranslations(string $field): array
    {
        return $this->getLoadedTranslations()
            ->filter(fn(Translation $t) => $t->field === $field)
            ->pluck('value', 'locale')
            ->all();
    }

    /**
     * Persist a single translation. Upserts so it is safe to call repeatedly.
     */
    public function setTranslation(string $field, string $locale, string $value): static
    {
        Translation::updateOrCreate(
            [
                'table_name' => $this->getTable(),
                'row_id'     => $this->getKey(),
                'locale'     => $locale,
                'field'      => $field,
            ],
            ['value' => $value]
        );

        $this->unsetRelation('translations');

        return $this;
    }

    /**
     * Persist all locales for a field at once.
     *
     * @param  array<string, string>  $translations  ['da' => '...', 'uk' => '...']
     */
    public function setTranslations(string $field, array $translations): static
    {
        foreach ($translations as $locale => $value) {
            $this->setTranslation($field, $locale, (string) $value);
        }

        return $this;
    }

    /**
     * Bulk-write a localizedContent map as returned by the automation form.
     *
     * @param  array<string, array<string, string>>  $localizedContent  ['da' => ['title' => '...'], 'uk' => [...]]
     * @param  array<string, string>  $fieldMap  localizedContent key → model field name
     */
    public function setLocalizedContent(array $localizedContent, array $fieldMap): static
    {
        foreach ($localizedContent as $locale => $fields) {
            foreach ($fieldMap as $sourceKey => $modelField) {
                $value = $fields[$sourceKey] ?? null;
                if ($value !== null && $value !== '') {
                    $this->setTranslation($modelField, $locale, (string) $value);
                }
            }
        }

        return $this;
    }

    private function getLoadedTranslations(): Collection
    {
        if (!$this->relationLoaded('translations')) {
            $this->load('translations');
        }

        return $this->getRelation('translations');
    }
}
