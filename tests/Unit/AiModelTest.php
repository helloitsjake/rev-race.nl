<?php

namespace Tests\Unit;

use App\Support\AiModel;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AiModelTest extends TestCase
{
    public function test_het_ingestelde_model_wordt_gebruikt(): void
    {
        config(['services.openai.model' => 'gpt-5.4-nano']);

        $this->assertSame('gpt-5.4-nano', AiModel::resolve());
    }

    #[DataProvider('dureModellen')]
    public function test_dure_modellen_worden_geblokkeerd(string $model): void
    {
        config(['services.openai.model' => $model]);

        $this->assertSame(AiModel::FALLBACK, AiModel::resolve());
    }

    public static function dureModellen(): array
    {
        return [
            'pro' => ['gpt-5.4-pro'],
            'gpt-5.5' => ['gpt-5.5'],
            'gpt-5.5 met hoofdletters en spaties' => ['  GPT-5.5-2026-04-23  '],
            'gpt-5.6' => ['gpt-5.6-terra'],
            'o3' => ['o3'],
            'deep research' => ['o4-mini-deep-research'],
        ];
    }

    public function test_leeg_model_valt_terug_op_de_standaard(): void
    {
        config(['services.openai.model' => '']);

        $this->assertSame(AiModel::FALLBACK, AiModel::resolve());
    }
}
