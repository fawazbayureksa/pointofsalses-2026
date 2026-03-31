<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use PHPUnit\Framework\TestCase;

class SettingTest extends TestCase
{
    public function test_get_typed_value_returns_string_by_default(): void
    {
        $setting        = new Setting();
        $setting->value = 'hello';
        $setting->type  = 'string';

        $this->assertSame('hello', $setting->getTypedValue());
    }

    public function test_get_typed_value_returns_integer(): void
    {
        $setting        = new Setting();
        $setting->value = '42';
        $setting->type  = 'integer';

        $result = $setting->getTypedValue();

        $this->assertIsInt($result);
        $this->assertSame(42, $result);
    }

    public function test_get_typed_value_returns_boolean_true(): void
    {
        $setting        = new Setting();
        $setting->value = 'true';
        $setting->type  = 'boolean';

        $result = $setting->getTypedValue();

        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function test_get_typed_value_returns_boolean_false(): void
    {
        $setting        = new Setting();
        $setting->value = 'false';
        $setting->type  = 'boolean';

        $result = $setting->getTypedValue();

        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    public function test_get_typed_value_returns_decoded_json_array(): void
    {
        $setting        = new Setting();
        $setting->value = '{"key":"value","count":5}';
        $setting->type  = 'json';

        $result = $setting->getTypedValue();

        $this->assertIsArray($result);
        $this->assertSame('value', $result['key']);
        $this->assertSame(5, $result['count']);
    }

    public function test_get_typed_value_returns_null_for_unknown_type(): void
    {
        $setting        = new Setting();
        $setting->value = 'some_value';
        $setting->type  = 'unknown_type';

        // Falls through to 'default' in match — returns raw string
        $this->assertSame('some_value', $setting->getTypedValue());
    }
}
