<?php

namespace App\Support;

/**
 * Lee numeros escritos como se usan en Colombia y como los muestra la app:
 * punto para miles y coma para decimales. Asi "7.547" es siete mil quinientos
 * cuarenta y siete, no siete coma cinco, y se puede copiar de la tabla o del
 * Excel sin que el valor se encoja mil veces.
 *
 * Tiene su gemelo en JavaScript (partials/localized-number-script) para la
 * vista previa del formulario; los dos deben leer igual.
 */
final class LocalizedNumber
{
    /**
     * Devuelve el numero con punto decimal y sin separadores ("7547", "5959.82"),
     * o null si el texto no se puede leer sin adivinar.
     *
     * Un punto seguido de grupos de exactamente tres digitos es de miles
     * ("7.547", "1.234.567"); con cualquier otra cantidad es decimal ("85.6").
     * Si hay punto y coma, el ultimo de los dos es el decimal ("5.959,82").
     */
    public static function parse(mixed $value): ?string
    {
        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        // Espacios (tambien el duro que pega Excel) y el signo de pesos.
        $text = preg_replace('/[\s\x{00A0}$]/u', '', $value);

        if ($text === '' || $text === null) {
            return null;
        }

        $sign = '';

        if ($text[0] === '-' || $text[0] === '+') {
            $sign = $text[0] === '-' ? '-' : '';
            $text = substr($text, 1);
        }

        $normalized = self::normalize($text);

        if ($normalized === null) {
            return null;
        }

        return $normalized === '0' ? '0' : $sign . $normalized;
    }

    private static function normalize(string $text): ?string
    {
        $hasDot = str_contains($text, '.');
        $hasComma = str_contains($text, ',');

        if ($hasDot && $hasComma) {
            $decimal = strrpos($text, ',') > strrpos($text, '.') ? ',' : '.';
            $thousands = $decimal === ',' ? '.' : ',';
            $pattern = '/^\d{1,3}(' . preg_quote($thousands, '/') . '\d{3})*' . preg_quote($decimal, '/') . '\d+$/';

            if (! preg_match($pattern, $text)) {
                return null;
            }

            $number = str_replace([$thousands, $decimal], ['', '.'], $text);
        } elseif ($hasComma) {
            if (substr_count($text, ',') === 1) {
                // Una sola coma es decimal: "6081,039617", "85,6".
                if (! preg_match('/^\d*,\d+$/', $text)) {
                    return null;
                }

                $number = str_replace(',', '.', $text);
            } else {
                if (! preg_match('/^\d{1,3}(,\d{3})+$/', $text)) {
                    return null;
                }

                $number = str_replace(',', '', $text);
            }
        } elseif ($hasDot) {
            if ($text[0] !== '0' && preg_match('/^\d{1,3}(\.\d{3})+$/', $text)) {
                $number = str_replace('.', '', $text);
            } elseif (preg_match('/^\d*\.\d+$/', $text)) {
                $number = $text;
            } else {
                return null;
            }
        } else {
            if (! preg_match('/^\d+$/', $text)) {
                return null;
            }

            $number = $text;
        }

        return self::canonical($number);
    }

    /** Sin ceros sobrantes: "0.0" -> "0", "7547.50" -> "7547.5", "007" -> "7". */
    private static function canonical(string $number): string
    {
        if (str_contains($number, '.')) {
            $number = rtrim(rtrim($number, '0'), '.');
        }

        $number = ltrim($number, '0');

        return ($number === '' || $number[0] === '.') ? '0' . $number : $number;
    }
}
