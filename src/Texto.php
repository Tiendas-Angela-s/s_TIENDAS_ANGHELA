<?php
declare(strict_types=1);

final class Texto
{
    private const MAYUSCULAS = [
        'à' => 'À', 'á' => 'Á', 'â' => 'Â', 'ã' => 'Ã', 'ä' => 'Ä', 'å' => 'Å',
        'æ' => 'Æ', 'ç' => 'Ç', 'è' => 'È', 'é' => 'É', 'ê' => 'Ê', 'ë' => 'Ë',
        'ì' => 'Ì', 'í' => 'Í', 'î' => 'Î', 'ï' => 'Ï', 'ð' => 'Ð', 'ñ' => 'Ñ',
        'ò' => 'Ò', 'ó' => 'Ó', 'ô' => 'Ô', 'õ' => 'Õ', 'ö' => 'Ö', 'ø' => 'Ø',
        'ù' => 'Ù', 'ú' => 'Ú', 'û' => 'Û', 'ü' => 'Ü', 'ý' => 'Ý', 'þ' => 'Þ',
        'ÿ' => 'Ÿ',
    ];

    private const MINUSCULAS = [
        'À' => 'à', 'Á' => 'á', 'Â' => 'â', 'Ã' => 'ã', 'Ä' => 'ä', 'Å' => 'å',
        'Æ' => 'æ', 'Ç' => 'ç', 'È' => 'è', 'É' => 'é', 'Ê' => 'ê', 'Ë' => 'ë',
        'Ì' => 'ì', 'Í' => 'í', 'Î' => 'î', 'Ï' => 'ï', 'Ð' => 'ð', 'Ñ' => 'ñ',
        'Ò' => 'ò', 'Ó' => 'ó', 'Ô' => 'ô', 'Õ' => 'õ', 'Ö' => 'ö', 'Ø' => 'ø',
        'Ù' => 'ù', 'Ú' => 'ú', 'Û' => 'û', 'Ü' => 'ü', 'Ý' => 'ý', 'Þ' => 'þ',
        'Ÿ' => 'ÿ',
    ];

    public static function mayusculas(string $texto): string
    {
        return function_exists('mb_strtoupper')
            ? mb_strtoupper($texto, 'UTF-8')
            : strtr(strtoupper($texto), self::MAYUSCULAS);
    }

    public static function minusculas(string $texto): string
    {
        return function_exists('mb_strtolower')
            ? mb_strtolower($texto, 'UTF-8')
            : strtr(strtolower($texto), self::MINUSCULAS);
    }

    public static function subcadena(string $texto, int $inicio, int $longitud): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($texto, $inicio, $longitud, 'UTF-8');
        }

        $caracteres = preg_split('//u', $texto, -1, PREG_SPLIT_NO_EMPTY);
        if ($caracteres === false) {
            return substr($texto, $inicio, $longitud);
        }

        return implode('', array_slice($caracteres, $inicio, $longitud));
    }
}
