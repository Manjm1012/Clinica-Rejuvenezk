<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use BelongsToClinic;

    protected $fillable = ['clinic_id', 'group', 'key', 'value', 'type', 'label'];

    protected static function booted(): void
    {
        static::saved(function (SiteSetting $setting): void {
            static::clearCache($setting->group, $setting->key);
        });

        static::deleted(function (SiteSetting $setting): void {
            static::clearCache($setting->group, $setting->key);
        });
    }

    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        $clinicId = app()->bound(\App\Support\CurrentClinic::class)
            ? app(\App\Support\CurrentClinic::class)->id()
            : 'global';
        $cacheKey = "setting:{$clinicId}:{$group}:{$key}";

        return Cache::rememberForever($cacheKey, function () use ($group, $key, $default) {
            $setting = static::where('group', $group)->where('key', $key)->first();
            if (! $setting) {
                return $default;
            }

            return match ($setting->type) {
                'boolean' => (bool) $setting->value,
                'integer' => (int) $setting->value,
                'json'    => json_decode($setting->value, true),
                default   => $setting->value,
            };
        });
    }

    public static function set(string $group, string $key, mixed $value, string $type = 'string', string $label = ''): static
    {
        $stored = is_array($value) ? json_encode($value) : $value;
        $clinicId = app()->bound(\App\Support\CurrentClinic::class)
            ? app(\App\Support\CurrentClinic::class)->id()
            : null;

        $match = ['group' => $group, 'key' => $key];
        if ($clinicId) {
            $match['clinic_id'] = $clinicId;
        }

        $setting = static::updateOrCreate(
            $match,
            ['clinic_id' => $clinicId, 'value' => $stored, 'type' => $type, 'label' => $label]
        );

        $clinicId = app()->bound(\App\Support\CurrentClinic::class)
            ? app(\App\Support\CurrentClinic::class)->id()
            : 'global';
        Cache::forget("setting:{$clinicId}:{$group}:{$key}");
        Cache::forget("settings_group:{$clinicId}:{$group}");

        return $setting;
    }

    public static function group(string $group): array
    {
        $clinicId = app()->bound(\App\Support\CurrentClinic::class)
            ? app(\App\Support\CurrentClinic::class)->id()
            : 'global';
        return Cache::rememberForever("settings_group:{$clinicId}:{$group}", function () use ($group) {
            $results = [];
            foreach (static::where('group', $group)->get() as $setting) {
                $results[$setting->key] = match ($setting->type) {
                    'boolean' => (bool) $setting->value,
                    'integer' => (int) $setting->value,
                    'json'    => json_decode($setting->value, true),
                    default   => $setting->value,
                };
            }
            return $results;
        });
    }

    public static function clearCache(string $group = null, string $key = null): void
    {
        $clinicId = app()->bound(\App\Support\CurrentClinic::class)
            ? app(\App\Support\CurrentClinic::class)->id()
            : 'global';

        if ($group && $key) {
            Cache::forget("setting:{$clinicId}:{$group}:{$key}");
            Cache::forget("settings_group:{$clinicId}:{$group}");
        } elseif ($group) {
            Cache::forget("settings_group:{$clinicId}:{$group}");
            foreach (static::where('group', $group)->pluck('key') as $k) {
                Cache::forget("setting:{$clinicId}:{$group}:{$k}");
            }
        }
    }
}
