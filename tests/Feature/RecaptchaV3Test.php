<?php

namespace Tests\Feature;

use App\Rules\RecaptchaV3 as RecaptchaV3Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RecaptchaV3Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('recaptcha.enabled', true);
        config()->set('recaptcha.secret_key', 'test-secret');
        config()->set('recaptcha.score_threshold', 0.5);
    }

    public function test_request_without_token_fails_validation(): void
    {
        $data = ['g-recaptcha-response' => null];
        $rule = new RecaptchaV3Rule('contact_form');

        $validator = Validator::make($data, [
            'g-recaptcha-response' => [$rule],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('g-recaptcha-response', $validator->errors()->toArray());
    }

    public function test_invalid_token_fails(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $data = ['g-recaptcha-response' => 'bad-token'];
        $validator = Validator::make($data, [
            'g-recaptcha-response' => [new RecaptchaV3Rule('contact_form')],
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_low_score_fails(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.1,
                'action' => 'contact_form',
            ], 200),
        ]);

        $data = ['g-recaptcha-response' => 'low-score-token'];
        $validator = Validator::make($data, [
            'g-recaptcha-response' => [new RecaptchaV3Rule('contact_form')],
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_success_passes(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.9,
                'action' => 'contact_form',
            ], 200),
        ]);

        $data = ['g-recaptcha-response' => 'ok-token'];
        $validator = Validator::make($data, [
            'g-recaptcha-response' => [new RecaptchaV3Rule('contact_form')],
        ]);

        $this->assertFalse($validator->fails());
    }
}
