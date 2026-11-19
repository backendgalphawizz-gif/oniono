<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SiteSettingsService
{
    public function seedDefaults(): void
    {
        foreach (config('site_settings.defaults', []) as $key => $meta) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $meta['value'],
                    'type' => $meta['type'],
                    'group' => $meta['group'],
                ]
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function allForForm(): array
    {
        $values = [];
        foreach (array_keys(config('site_settings.defaults', [])) as $key) {
            $meta = config("site_settings.defaults.{$key}");
            $default = $meta['value'] ?? null;
            if (($meta['type'] ?? 'string') === 'boolean') {
                $values[$key] = (bool) Setting::getValue($key, filter_var($default, FILTER_VALIDATE_BOOLEAN));
            } else {
                $values[$key] = Setting::getValue($key, $default);
            }
        }

        return $values;
    }

    public function assetUrl(?string $storedPath, string $fallbackAsset): string
    {
        if (is_string($storedPath) && $storedPath !== '') {
            return asset('storage/'.$storedPath);
        }

        return asset($fallbackAsset);
    }

    public function logoUrl(): string
    {
        return $this->assetUrl(Setting::getValue('site_logo'), 'images/oniono-logo.jpg');
    }

    public function faviconUrl(): string
    {
        return $this->assetUrl(Setting::getValue('site_favicon'), 'images/oniono-logo.jpg');
    }

    public function siteName(): string
    {
        return (string) Setting::getValue('platform_name', 'Oniono');
    }

    public function updateFromRequest(Request $request): void
    {
        $stringRules = [
            'platform_name' => ['nullable', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:500'],
            'site_email' => ['nullable', 'email', 'max:255'],
            'site_phone' => ['nullable', 'string', 'max:50'],
            'site_address' => ['nullable', 'string', 'max:1000'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'social_facebook' => ['nullable', 'string', 'max:500'],
            'social_instagram' => ['nullable', 'string', 'max:500'],
            'social_twitter' => ['nullable', 'string', 'max:500'],
            'social_youtube' => ['nullable', 'string', 'max:500'],
            'social_linkedin' => ['nullable', 'string', 'max:500'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'currency_symbol' => ['nullable', 'string', 'max:8'],
            'timezone' => ['nullable', 'timezone:all'],
            'theme_primary_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'theme_accent_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'footer_text' => ['nullable', 'string', 'max:1000'],
        ];

        $imageRules = [];
        foreach (config('site_settings.image_fields', []) as $field => $meta) {
            $imageRules[$field] = ['nullable', 'file', 'max:'.$meta['max'], 'mimes:'.$meta['mimes']];
        }

        $validated = $request->validate(array_merge($stringRules, $imageRules, [
            'maintenance_mode' => ['nullable', 'boolean'],
            'remove_site_logo' => ['nullable', 'boolean'],
            'remove_site_logo_dark' => ['nullable', 'boolean'],
            'remove_site_favicon' => ['nullable', 'boolean'],
        ]));

        foreach (array_keys($stringRules) as $key) {
            $this->persist($key, $request->input($key, ''));
        }

        Setting::setValue('maintenance_mode', $request->boolean('maintenance_mode'), 'boolean', 'site');

        if ($request->boolean('remove_site_logo')) {
            $this->deleteStoredImage('site_logo');
        }
        if ($request->boolean('remove_site_logo_dark')) {
            $this->deleteStoredImage('site_logo_dark');
        }
        if ($request->boolean('remove_site_favicon')) {
            $this->deleteStoredImage('site_favicon');
        }

        foreach (array_keys(config('site_settings.image_fields', [])) as $field) {
            if ($request->hasFile($field)) {
                $this->storeUploadedImage($field, $request->file($field));
            }
        }
    }

    protected function persist(string $key, mixed $value): void
    {
        $meta = config("site_settings.defaults.{$key}", ['type' => 'string', 'group' => 'site']);
        Setting::setValue($key, $value, $meta['type'] ?? 'string', $meta['group'] ?? 'site');
    }

    protected function storeUploadedImage(string $key, UploadedFile $file): void
    {
        $this->deleteStoredImage($key);

        $path = $file->store(config('site_settings.directory', 'site'), config('site_settings.disk', 'public'));
        Setting::setValue($key, $path, 'string', 'site');
    }

    protected function deleteStoredImage(string $key): void
    {
        $existing = Setting::getValue($key);
        if (is_string($existing) && $existing !== '') {
            Storage::disk(config('site_settings.disk', 'public'))->delete($existing);
        }
        Setting::setValue($key, '', 'string', 'site');
    }
}
