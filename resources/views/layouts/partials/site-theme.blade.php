@php
    $primary = site_setting('theme_primary_color', '#ff8c00');
    $accent = site_setting('theme_accent_color', '#7c3aed');
@endphp
<style>
    :root {
        --oniono-orange: {{ $primary }};
        --oniono-purple: {{ $accent }};
    }
</style>
