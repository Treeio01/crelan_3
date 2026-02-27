@extends('layouts.app')

@section('title', 'Botodel Icon')

@section('content')
    <section class="activation-page" style="max-width: 960px; margin: 0 auto;">
        <div class="activation-topbar" style="margin-top: 18px;">BOTODEL</div>

        <div class="activation-card" style="padding: 20px;">
            <div style="font-size: 14px; color: #6a6a6a; letter-spacing: 0.16em; text-transform: uppercase; margin-bottom: 16px;">
                Icon Catalog
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 16px;">
                @foreach ($icons as $icon)
                    @php
                        $iconId = $icon['id'] ?? 'Logo_0';
                        $iconNumber = (int) str_replace('Logo_', '', $iconId);
                    @endphp
                    <div style="border: 1px solid #e5e5e5; border-radius: 12px; padding: 14px; text-align: center; background: #fff;">
                        <div style="font-size: 13px; color: #6a6a6a; letter-spacing: 0.12em; text-transform: uppercase;">
                            #{{ $iconNumber }}
                        </div>
                        <div style="display: flex; justify-content: center; align-items: center; padding: 12px 0 6px;">
                            {!! $icon['content'] ?? '' !!}
                        </div>
                        <div style="font-size: 11px; color: #9a9a9a;">
                            {{ $iconId }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
