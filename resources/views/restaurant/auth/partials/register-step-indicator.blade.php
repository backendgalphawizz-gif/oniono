@php
    $current = $current ?? 1;
@endphp
<nav class="auth-register-steps mb-4" aria-label="Registration progress">
    <ol class="auth-register-steps__list">
        <li class="auth-register-steps__item @if ($current >= 1) is-active @endif @if ($current > 1) is-done @endif">
            <span class="auth-register-steps__num">1</span>
            <span class="auth-register-steps__label">Business details</span>
        </li>
        <li class="auth-register-steps__item @if ($current >= 2) is-active @endif">
            <span class="auth-register-steps__num">2</span>
            <span class="auth-register-steps__label">Payout &amp; docs</span>
        </li>
    </ol>
</nav>
