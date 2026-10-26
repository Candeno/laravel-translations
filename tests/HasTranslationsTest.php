<?php

namespace Candeno\LaravelTranslations\Tests;

use Candeno\LaravelTranslations\Models\Translation;
use Candeno\LaravelTranslations\Tests\Fixtures\Post;

// uses real database — no mocks
class HasTranslationsTest extends TestCase
{
    public function test_it_returns_the_translation_for_the_requested_locale(): void
    {
        $this->fail('STUB (red phase): the package does not install on Laravel 13');
        $post = Post::create(['title' => 'Raw title']);
        $post->setTranslation('title', 'uk', 'Назва');

        $this->assertSame('Назва', $post->getTranslation('title', 'uk', 'da'));
    }

    public function test_it_falls_back_to_the_fallback_locale(): void
    {
        $post = Post::create(['title' => 'Raw title']);
        $post->setTranslation('title', 'da', 'Titel');

        $this->assertSame('Titel', $post->getTranslation('title', 'uk', 'da'));
    }

    public function test_it_falls_back_to_the_model_attribute_when_no_translation_exists(): void
    {
        $post = Post::create(['title' => 'Raw title']);

        $this->assertSame('Raw title', $post->getTranslation('title', 'uk', 'da'));
    }

    public function test_setting_a_translation_twice_keeps_one_row_with_the_latest_value(): void
    {
        $post = Post::create(['title' => 'Raw title']);
        $post->setTranslation('title', 'uk', 'First');
        $post->setTranslation('title', 'uk', 'Second');

        $this->assertSame(1, Translation::count());
        $this->assertSame('Second', $post->getTranslation('title', 'uk'));
    }

    public function test_it_returns_all_translations_of_a_field_keyed_by_locale(): void
    {
        $post = Post::create(['title' => 'Raw title']);
        $post->setTranslations('title', ['da' => 'Titel', 'uk' => 'Назва']);

        $this->assertEqualsCanonicalizing(['da' => 'Titel', 'uk' => 'Назва'], $post->getTranslations('title'));
    }

    public function test_localized_content_is_written_through_the_field_map_and_skips_empty_values(): void
    {
        $post = Post::create(['title' => 'Raw title']);
        $post->setLocalizedContent(
            ['da' => ['title' => 'Titel', 'description' => ''], 'uk' => ['description' => 'Опис']],
            ['title' => 'title', 'description' => 'subtitle'],
        );

        $this->assertSame(['da' => 'Titel'], $post->getTranslations('title'));
        $this->assertSame(['uk' => 'Опис'], $post->getTranslations('subtitle'));
    }
}
