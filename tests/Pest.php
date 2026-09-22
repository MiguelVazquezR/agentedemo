<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Respuesta simulada del modelo con una llamada a herramienta.
 *
 * @param  array<string, mixed>  $argumentos
 * @return array<string, mixed>
 */
function respuestaDeHerramienta(string $herramienta, array $argumentos): array
{
    return [
        'choices' => [[
            'message' => [
                'role' => 'assistant',
                'content' => '',
                'tool_calls' => [[
                    'id' => 'call_'.Str::random(10),
                    'type' => 'function',
                    'function' => [
                        'name' => $herramienta,
                        'arguments' => json_encode($argumentos),
                    ],
                ]],
            ],
        ]],
        'usage' => ['prompt_tokens' => 1500, 'completion_tokens' => 40],
    ];
}

/**
 * Respuesta simulada del modelo con el texto final.
 *
 * @return array<string, mixed>
 */
function respuestaDeTexto(string $contenido): array
{
    return [
        'choices' => [['message' => ['role' => 'assistant', 'content' => $contenido]]],
        'usage' => ['prompt_tokens' => 1600, 'completion_tokens' => 90],
    ];
}
