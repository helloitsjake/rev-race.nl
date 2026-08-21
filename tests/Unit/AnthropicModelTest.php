<?php

namespace Tests\Unit;

use App\Support\AnthropicModel;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AnthropicModelTest extends TestCase
{
    public function test_een_normaal_model_uit_de_config_wordt_gewoon_gebruikt(): void
    {
        config(['services.anthropic.model' => 'claude-sonnet-4-6']);

        $this->assertSame('claude-sonnet-4-6', AnthropicModel::resolve());
    }

    #[DataProvider('geblokkeerdeModellen')]
    public function test_geblokkeerde_modellen_vallen_terug_op_de_fallback(string $model): void
    {
        config(['services.anthropic.model' => $model]);

        $this->assertSame(AnthropicModel::FALLBACK, AnthropicModel::resolve());
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function geblokkeerdeModellen(): array
    {
        return [
            'fable 5' => ['claude-fable-5'],
            'fable met hoofdletters' => ['Claude-Fable-5'],
            'fable met spaties' => ['  claude-fable-5  '],
            'fable met datumsuffix' => ['claude-fable-5-20260101'],
            'toekomstige fable' => ['claude-fable-6'],
            'mythos 5' => ['claude-mythos-5'],
        ];
    }

    public function test_een_lege_config_valt_terug_op_de_fallback(): void
    {
        config(['services.anthropic.model' => '']);

        $this->assertSame(AnthropicModel::FALLBACK, AnthropicModel::resolve());
    }
}
