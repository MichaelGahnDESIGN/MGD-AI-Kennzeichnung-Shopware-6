<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Cms\BackgroundImage;

use Shopware\Core\Content\Cms\DataResolver\FieldConfigCollection;
use Shopware\Core\Content\Media\MediaEntity;

/**
 * Übersetzt rohe CMS-Felder in ein geschlossenes serverseitiges Viewmodell.
 * Twig erhält dadurch weder rohe Arrays noch Objekte oder freie CSS-Werte.
 */
final class BackgroundImagePresentationNormalizer
{
    /** @var list<string> */
    private const MINIMUM_HEIGHTS = ['240px', '320px', '480px', '640px'];

    /** @var list<string> */
    private const HORIZONTAL_POSITIONS = ['left', 'center', 'right'];

    /** @var list<string> */
    private const VERTICAL_POSITIONS = ['top', 'center', 'bottom'];

    /** @var list<string> */
    private const FALLBACK_COLORS = ['neutral-light', 'neutral-dark', 'brand'];

    public function normalize(FieldConfigCollection $config, MediaEntity $media): BackgroundImagePresentation
    {
        $decorative = $this->value($config, 'decorative') === true;
        $altText = $this->plainText($this->value($config, 'altText'));
        if ($altText === '') {
            $altText = $this->plainText($media->getTranslation('alt'));
        }
        if ($altText === '') {
            $altText = $this->plainText($media->getTranslation('title'));
        }

        return BackgroundImagePresentation::fromNormalizedValues(
            $this->choice($this->value($config, 'minHeight'), self::MINIMUM_HEIGHTS, '320px'),
            $this->choice($this->value($config, 'horizontalPosition'), self::HORIZONTAL_POSITIONS, 'center'),
            $this->choice($this->value($config, 'verticalPosition'), self::VERTICAL_POSITIONS, 'center'),
            $this->choice($this->value($config, 'fallbackColor'), self::FALLBACK_COLORS, 'neutral-light'),
            $decorative,
            $altText,
        );
    }

    private function value(FieldConfigCollection $config, string $name): mixed
    {
        return $config->get($name)?->getValue();
    }

    /** @param list<string> $allowed */
    private function choice(mixed $value, array $allowed, string $fallback): string
    {
        return is_string($value) && in_array($value, $allowed, true) ? $value : $fallback;
    }

    /** Alt-Texte sind reine, einzeilige Texte und niemals HTML-Fragmente. */
    private function plainText(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        // Script- und Style-Inhalte sind keine Bildbeschreibung. Sie werden vor
        // der allgemeinen HTML-Entfernung vollständig samt Inhalt verworfen.
        $value = preg_replace('#<(script|style)\b[^>]*>.*?</\1\s*>#isu', '', $value);
        if (!is_string($value)) {
            return '';
        }

        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', strip_tags($value));

        return is_string($value) ? mb_substr(trim($value), 0, 512) : '';
    }
}
