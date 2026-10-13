<?php

namespace App\Support;

class ImageAlternativeText
{
    public function forImage(?string $path, ?string $alt = null, ?string $title = null, string $fallback = 'images.gallery'): string
    {
        foreach ([$alt, $title] as $text) {
            $text = trim((string) $text);
            if ($text !== '' && ! preg_match('/^(?:Galeria|Gallery|Галерея)\s+\d+$/iu', $text)) {
                return $text;
            }
        }

        // Describe only the known bundled photos; uploaded replacements need their own text.
        if (preg_match('~^(?:/?storage/)?umami/(res(?:[1-9]|1[0-5]))\.(?:png|webp)$~', (string) $path, $matches)) {
            return __('images.'.$matches[1]);
        }

        return __($fallback);
    }
}
