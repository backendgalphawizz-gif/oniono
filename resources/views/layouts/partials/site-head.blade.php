<link rel="icon" href="{{ site_favicon_url() }}" type="image/png">
@if (site_setting('meta_description'))
    <meta name="description" content="{{ site_setting('meta_description') }}">
@endif
@if (site_setting('meta_keywords'))
    <meta name="keywords" content="{{ site_setting('meta_keywords') }}">
@endif
@include('layouts.partials.site-theme')
