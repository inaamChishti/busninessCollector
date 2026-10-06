<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'encrypted', 'group'];
    protected function casts(): array { return ['encrypted' => 'boolean']; }

    public static function valueOf(string $key, mixed $default = null): mixed
    {
        $record = static::query()->where('key', $key)->first();
        if (! $record || $record->value === null) return $default;
        $value = $record->encrypted ? Crypt::decryptString($record->value) : $record->value;
        if ($value === 'true' || $value === 'false') return $value === 'true';
        return is_numeric($value) ? $value + 0 : $value;
    }

    public static function put(string $key, mixed $value, string $group = 'general', bool $encrypted = false): void
    {
        $stored = $encrypted && filled($value) ? Crypt::encryptString((string) $value) : (string) $value;
        static::query()->updateOrCreate(['key' => $key], ['value' => $stored, 'group' => $group, 'encrypted' => $encrypted]);
    }
}
