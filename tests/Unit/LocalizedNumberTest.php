<?php

namespace Tests\Unit;

use App\Support\LocalizedNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LocalizedNumberTest extends TestCase
{
    public static function numeros(): array
    {
        return [
            // Como se copian de la tabla o del Excel en Colombia.
            'miles con punto' => ['7.547', '7547'],
            'miles y decimales' => ['5.959,82', '5959.82'],
            'miles y muchos decimales' => ['6.081,039617', '6081.039617'],
            'millones' => ['1.234.567', '1234567'],
            'millones con decimales' => ['1.234.567,89', '1234567.89'],
            'coma decimal' => ['6081,039617', '6081.039617'],
            'porcentaje con coma' => ['85,6', '85.6'],
            'ratio con coma' => ['1,2', '1.2'],

            // Como se escribian hasta ahora: siguen funcionando igual.
            'entero' => ['7547', '7547'],
            'punto decimal' => ['5959.82', '5959.82'],
            'porcentaje con punto' => ['85.6', '85.6'],
            'ratio con punto' => ['1.2', '1.2'],
            'meta' => ['100', '100'],

            // Formato ingles completo, por si viene de otro Excel.
            'ingles con miles' => ['5,959.82', '5959.82'],
            'ingles solo miles' => ['1,234,567', '1234567'],

            // Punto seguido de tres digitos es de miles, igual que la app muestra sus cifras.
            'ambiguo se lee como miles' => ['1.250', '1250'],
            'cero adelante es decimal' => ['0.500', '0.5'],
            'mas de tres digitos antes del punto es decimal' => ['1234.567', '1234.567'],

            // Adornos que trae el copiar y pegar.
            'signo de pesos' => ['$ 7.547', '7547'],
            'espacios' => ['  7.547  ', '7547'],
            'espacio de miles' => ['7 547', '7547'],
            'espacio duro de Excel' => ["7\u{00A0}547", '7547'],

            // Negativos (EBITDA) y ceros.
            'negativo' => ['-734', '-734'],
            'negativo con miles' => ['-2.998', '-2998'],
            'negativo decimal' => ['-0,5', '-0.5'],
            'mas explicito' => ['+15', '15'],
            'cero' => ['0', '0'],
            'cero con decimales' => ['0,0', '0'],
            'menos cero' => ['-0', '0'],
            'ceros sobrantes' => ['100,00', '100'],
            'ceros a la izquierda' => ['007', '7'],
            'decimal sin entero con coma' => [',5', '0.5'],
            'decimal sin entero con punto' => ['.5', '0.5'],

            // Lo que llega ya como numero.
            'entero nativo' => [7547, '7547'],
            'decimal nativo' => [5959.82, '5959.82'],
        ];
    }

    public static function ilegibles(): array
    {
        return [
            'vacio' => [''],
            'solo espacios' => ['   '],
            'texto' => ['abc'],
            'grupos de miles incompletos' => ['7.5.4'],
            'comas sueltas' => ['7,5,4'],
            'grupos de dos' => ['12.34.56'],
            'miles mal formados con decimales' => ['5.959.82'],
            'separadores revueltos' => ['5.959,82,1'],
            'punto al final' => ['7547.'],
            'notacion cientifica' => ['1e5'],
            'doble signo' => ['--5'],
            'nulo' => [null],
            'arreglo' => [[]],
        ];
    }

    #[DataProvider('numeros')]
    public function test_lee_el_numero(mixed $entrada, string $esperado): void
    {
        $this->assertSame($esperado, LocalizedNumber::parse($entrada));
    }

    #[DataProvider('ilegibles')]
    public function test_no_adivina_lo_que_no_se_puede_leer(mixed $entrada): void
    {
        $this->assertNull(LocalizedNumber::parse($entrada));
    }
}
