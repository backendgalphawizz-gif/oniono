@php
    $logoUrl = site_settings()->assetUrl($site['site_logo'] ?? '', 'images/oniono-logo.jpg');
    $logoDarkUrl = ! empty($site['site_logo_dark']) ? site_settings()->assetUrl($site['site_logo_dark'], 'images/oniono-logo.jpg') : null;
    $faviconUrl = site_settings()->assetUrl($site['site_favicon'] ?? '', 'images/oniono-logo.jpg');
@endphp

<form method="POST" action="{{ route('admin.settings.site.update') }}" enctype="multipart/form-data" class="panel-form">
    @csrf

    <x-panel-form-card title="Branding" subtitle="Name, logos, and favicon shown across customer and panel experiences.">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="platform_name">Site / platform name</label>
                <input name="platform_name" id="platform_name" class="form-control" value="{{ old('platform_name', $site['platform_name'] ?? '') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="site_tagline">Tagline</label>
                <input name="site_tagline" id="site_tagline" class="form-control" value="{{ old('site_tagline', $site['site_tagline'] ?? '') }}" placeholder="Short line under the logo">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="site_logo">Logo</label>
                <div class="site-media-preview mb-2">
                    <img src="{{ $logoUrl }}" alt="Current logo" class="site-media-preview-img">
                </div>
                <input type="file" name="site_logo" id="site_logo" class="form-control" accept="image/*,.svg">
                @if (! empty($site['site_logo']))
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_site_logo" id="remove_site_logo" value="1">
                        <label class="form-check-label" for="remove_site_logo">Remove uploaded logo (use default)</label>
                    </div>
                @endif
            </div>
            <div class="col-md-4">
                <label class="form-label" for="site_logo_dark">Logo for dark backgrounds</label>
                @if ($logoDarkUrl)
                    <div class="site-media-preview site-media-preview-dark mb-2">
                        <img src="{{ $logoDarkUrl }}" alt="Dark logo" class="site-media-preview-img">
                    </div>
                @else
                    <p class="small text-muted mb-2">Optional. Falls back to main logo.</p>
                @endif
                <input type="file" name="site_logo_dark" id="site_logo_dark" class="form-control" accept="image/*,.svg">
                @if (! empty($site['site_logo_dark']))
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_site_logo_dark" id="remove_site_logo_dark" value="1">
                        <label class="form-check-label" for="remove_site_logo_dark">Remove dark logo</label>
                    </div>
                @endif
            </div>
            <div class="col-md-4">
                <label class="form-label" for="site_favicon">Favicon</label>
                <div class="site-media-preview site-media-preview-favicon mb-2">
                    <img src="{{ $faviconUrl }}" alt="Favicon" class="site-media-preview-img">
                </div>
                <input type="file" name="site_favicon" id="site_favicon" class="form-control" accept="image/*,.ico,.svg">
                @if (! empty($site['site_favicon']))
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_site_favicon" id="remove_site_favicon" value="1">
                        <label class="form-check-label" for="remove_site_favicon">Remove favicon</label>
                    </div>
                @endif
            </div>
        </div>
    </x-panel-form-card>

    <x-panel-form-card title="Contact & support" subtitle="Public contact details and support inbox.">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="site_email">Public email</label>
                <input type="email" name="site_email" id="site_email" class="form-control" value="{{ old('site_email', $site['site_email'] ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="support_email">Support email</label>
                <input type="email" name="support_email" id="support_email" class="form-control" value="{{ old('support_email', $site['support_email'] ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="site_phone">Phone</label>
                <input name="site_phone" id="site_phone" class="form-control" value="{{ old('site_phone', $site['site_phone'] ?? '') }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="site_address">Address</label>
                <textarea name="site_address" id="site_address" class="form-control" rows="2">{{ old('site_address', $site['site_address'] ?? '') }}</textarea>
            </div>
        </div>
    </x-panel-form-card>

    <x-panel-form-card title="SEO" subtitle="Default meta tags for public pages.">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label" for="meta_description">Meta description</label>
                <textarea name="meta_description" id="meta_description" class="form-control" rows="2">{{ old('meta_description', $site['meta_description'] ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label" for="meta_keywords">Meta keywords</label>
                <input name="meta_keywords" id="meta_keywords" class="form-control" value="{{ old('meta_keywords', $site['meta_keywords'] ?? '') }}" placeholder="Comma-separated">
            </div>
        </div>
    </x-panel-form-card>

    <x-panel-form-card title="Social links" subtitle="Full URLs to your profiles.">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="social_facebook">Facebook</label>
                <input name="social_facebook" id="social_facebook" class="form-control" value="{{ old('social_facebook', $site['social_facebook'] ?? '') }}" placeholder="https://">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="social_instagram">Instagram</label>
                <input name="social_instagram" id="social_instagram" class="form-control" value="{{ old('social_instagram', $site['social_instagram'] ?? '') }}" placeholder="https://">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="social_twitter">X (Twitter)</label>
                <input name="social_twitter" id="social_twitter" class="form-control" value="{{ old('social_twitter', $site['social_twitter'] ?? '') }}" placeholder="https://">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="social_youtube">YouTube</label>
                <input name="social_youtube" id="social_youtube" class="form-control" value="{{ old('social_youtube', $site['social_youtube'] ?? '') }}" placeholder="https://">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="social_linkedin">LinkedIn</label>
                <input name="social_linkedin" id="social_linkedin" class="form-control" value="{{ old('social_linkedin', $site['social_linkedin'] ?? '') }}" placeholder="https://">
            </div>
        </div>
    </x-panel-form-card>

    <x-panel-form-card title="Locale & theme" subtitle="Currency, timezone, colors, and footer.">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="currency_code">Currency code</label>
                <input name="currency_code" id="currency_code" class="form-control" value="{{ old('currency_code', $site['currency_code'] ?? 'INR') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="currency_symbol">Currency symbol</label>
                <input name="currency_symbol" id="currency_symbol" class="form-control" value="{{ old('currency_symbol', $site['currency_symbol'] ?? '₹') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="timezone">Timezone</label>
                <select name="timezone" id="timezone" class="form-select">
                    @php $tz = old('timezone', $site['timezone'] ?? 'Asia/Kolkata'); @endphp
                    <optgroup label="Common">
                        @foreach (['Asia/Kolkata', 'UTC', 'America/New_York', 'Europe/London', 'Asia/Dubai', 'Asia/Singapore'] as $commonTz)
                            <option value="{{ $commonTz }}" @selected($tz === $commonTz)>{{ $commonTz }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="All timezones">
                        @foreach ($timezones as $identifier)
                            @if (! in_array($identifier, ['Asia/Kolkata', 'UTC', 'America/New_York', 'Europe/London', 'Asia/Dubai', 'Asia/Singapore'], true))
                                <option value="{{ $identifier }}" @selected($tz === $identifier)>{{ $identifier }}</option>
                            @endif
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="theme_primary_color">Primary brand color</label>
                <div class="input-group">
                    <input type="color" class="form-control form-control-color" id="theme_primary_color_picker" value="{{ old('theme_primary_color', $site['theme_primary_color'] ?? '#ff8c00') }}" aria-label="Pick primary color">
                    <input name="theme_primary_color" id="theme_primary_color" class="form-control" value="{{ old('theme_primary_color', $site['theme_primary_color'] ?? '#ff8c00') }}" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$">
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="theme_accent_color">Accent color</label>
                <div class="input-group">
                    <input type="color" class="form-control form-control-color" id="theme_accent_color_picker" value="{{ old('theme_accent_color', $site['theme_accent_color'] ?? '#7c3aed') }}" aria-label="Pick accent color">
                    <input name="theme_accent_color" id="theme_accent_color" class="form-control" value="{{ old('theme_accent_color', $site['theme_accent_color'] ?? '#7c3aed') }}" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$">
                </div>
            </div>
            <div class="col-12">
                <label class="form-label" for="footer_text">Footer text</label>
                <input name="footer_text" id="footer_text" class="form-control" value="{{ old('footer_text', $site['footer_text'] ?? '') }}">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1" @checked(old('maintenance_mode', $site['maintenance_mode'] ?? false))>
                    <label class="form-check-label" for="maintenance_mode">Maintenance mode (flag for apps; wire public routes when ready)</label>
                </div>
            </div>
        </div>
    </x-panel-form-card>

    <div class="mb-4">
        @admincan('settings_site', 'edit')
            <button type="submit" class="btn btn-brand">Save site settings</button>
        @endadmincan
    </div>
</form>

@push('scripts')
<script>
document.querySelectorAll([
    ['theme_primary_color_picker', 'theme_primary_color'],
    ['theme_accent_color_picker', 'theme_accent_color'],
]).forEach(function (pair) {
    var picker = document.getElementById(pair[0]);
    var text = document.getElementById(pair[1]);
    if (!picker || !text) return;
    picker.addEventListener('input', function () { text.value = picker.value; });
    text.addEventListener('input', function () {
        if (/^#[0-9A-Fa-f]{6}$/.test(text.value)) picker.value = text.value;
    });
});
</script>
@endpush
