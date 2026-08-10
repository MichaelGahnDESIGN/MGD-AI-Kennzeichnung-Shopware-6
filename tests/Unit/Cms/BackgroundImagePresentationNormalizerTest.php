<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Cms;

use MGDAIImageLabels\Cms\BackgroundImage\BackgroundImagePresentationNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Content\Cms\DataResolver\FieldConfigCollection;
use Shopware\Core\Content\Media\MediaEntity;

/** Prüft die alleinige serverseitige Grenze für alle CMS-Darstellungswerte. */
final class BackgroundImagePresentationNormalizerTest extends TestCase
{
    public function testValidValuesAndEditorialAltTextBecomeFixedPresentationValues(): void
    {
        $presentation = (new BackgroundImagePresentationNormalizer())->normalize(
            $this->config([
                'minHeight' => '640px',
                'horizontalPosition' => 'right',
                'verticalPosition' => 'bottom',
                'fallbackColor' => 'neutral-dark',
                'decorative' => false,
                'altText' => '  Redaktionell gepflegter Alternativtext  ',
            ]),
            $this->media('Medien-Alt', 'Medientitel'),
        );

        self::assertSame('mgd-ai-background-image--height-640', $presentation->heightClass);
        self::assertSame('mgd-ai-background-image--horizontal-right', $presentation->horizontalClass);
        self::assertSame('mgd-ai-background-image--vertical-bottom', $presentation->verticalClass);
        self::assertSame('mgd-ai-background-image--fallback-neutral-dark', $presentation->fallbackClass);
        self::assertFalse($presentation->decorative);
        self::assertSame('Redaktionell gepflegter Alternativtext', $presentation->altText);
    }

    /** @param array<mixed>|bool|float|int|string|object|null $unsafe */
    #[DataProvider('unsafeValues')]
    public function testEveryManipulatedFieldTypeFallsBackWithoutDynamicClasses(
        mixed $unsafe,
        bool $expectedDecorative,
        string $expectedAltText,
    ): void {
        $presentation = (new BackgroundImagePresentationNormalizer())->normalize(
            $this->config([
                'minHeight' => $unsafe,
                'horizontalPosition' => $unsafe,
                'verticalPosition' => $unsafe,
                'fallbackColor' => $unsafe,
                'decorative' => $unsafe,
                'altText' => $unsafe,
            ]),
            $this->media(null, null),
        );

        self::assertSame('mgd-ai-background-image--height-320', $presentation->heightClass);
        self::assertSame('mgd-ai-background-image--horizontal-center', $presentation->horizontalClass);
        self::assertSame('mgd-ai-background-image--vertical-center', $presentation->verticalClass);
        self::assertSame('mgd-ai-background-image--fallback-neutral-light', $presentation->fallbackClass);
        self::assertSame($expectedDecorative, $presentation->decorative);
        self::assertSame($expectedAltText, $presentation->altText);
    }

    /** @return iterable<string, array{mixed, bool, string}> */
    public static function unsafeValues(): iterable
    {
        yield 'Array' => [['<script>'], false, ''];
        yield 'Objekt' => [new \stdClass(), false, ''];
        yield 'Boolean ist ausschließlich für dekorativ gültig' => [true, true, ''];
        yield 'Integer' => [320, false, ''];
        yield 'Float' => [320.5, false, ''];
        yield 'Null' => [null, false, ''];
        yield 'freier String bleibt nur Alt-Text, niemals CSS-Klasse' => [
            '320px;background:url(javascript:alert(1))',
            false,
            '320px;background:url(javascript:alert(1))',
        ];
    }

    public function testAltFallbackUsesOnlyMeaningfulMediaTranslationAndNeverFilenameOrGenericText(): void
    {
        $normalizer = new BackgroundImagePresentationNormalizer();

        self::assertSame('Medien-Alt', $normalizer->normalize($this->config([]), $this->media(' Medien-Alt ', 'Titel'))->altText);
        self::assertSame('Titel', $normalizer->normalize($this->config([]), $this->media('', ' Titel '))->altText);
        self::assertSame('', $normalizer->normalize($this->config([]), $this->media('', ''))->altText);
    }

    public function testDecorativeImageAlwaysHasEmptyAltText(): void
    {
        $presentation = (new BackgroundImagePresentationNormalizer())->normalize(
            $this->config(['decorative' => true, 'altText' => 'Darf nicht erscheinen']),
            $this->media('Auch nicht', 'Ebenfalls nicht'),
        );

        self::assertTrue($presentation->decorative);
        self::assertSame('', $presentation->altText);
    }

    public function testEditorialAltTextIsPlainSingleLineText(): void
    {
        $presentation = (new BackgroundImagePresentationNormalizer())->normalize(
            $this->config(['altText' => "<strong>Hallo</strong>\nWelt"]),
            $this->media(null, null),
        );

        self::assertSame('Hallo Welt', $presentation->altText);
    }

    public function testScriptMarkupAndControlCharactersAreNotMeaningfulAltText(): void
    {
        $presentation = (new BackgroundImagePresentationNormalizer())->normalize(
            $this->config(['altText' => '<script>alert(1)</script>']),
            $this->media('<strong></strong>', "\0\x1F"),
        );

        self::assertSame('', $presentation->altText);
    }

    public function testEditorialAltTextIsLimitedOnTheServerToo(): void
    {
        $presentation = (new BackgroundImagePresentationNormalizer())->normalize(
            $this->config(['altText' => str_repeat('ä', 600)]),
            $this->media(null, null),
        );

        self::assertSame(512, mb_strlen($presentation->altText));
    }

    /** @param array<string, mixed> $values */
    private function config(array $values): FieldConfigCollection
    {
        $fields = [];
        foreach ($values as $name => $value) {
            $fields[] = $this->field($name, $value);
        }

        return new FieldConfigCollection($fields);
    }

    private function field(string $name, mixed $value): FieldConfig
    {
        $field = new FieldConfig($name, FieldConfig::SOURCE_STATIC, null);
        (new \ReflectionProperty(FieldConfig::class, 'value'))->setValue($field, $value);

        return $field;
    }

    private function media(?string $alt, ?string $title): MediaEntity
    {
        $media = new MediaEntity();
        $media->setTranslated(['alt' => $alt, 'title' => $title]);

        return $media;
    }
}
