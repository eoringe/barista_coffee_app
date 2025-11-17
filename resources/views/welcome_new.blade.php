@extends('layouts.app')

@section('content')
    @include('partials.loading_indicator')
    
    <main class="flex" style="flex-direction:column; gap:24px">
        @include('partials.menu_header')
        @include('partials.menu_grid')
    </main>

    @include('partials.modal')
@endsection

@push('styles')
<style>
    /* Menu Items List */
    .menu-items-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
        padding: 4px 0;
    }

    .menu-items-list .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .menu-items-list .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Scrollbar styling */
    .menu-items-list::-webkit-scrollbar {
        width: 8px;
    }

    .menu-items-list::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 4px;
    }

    .menu-items-list::-webkit-scrollbar-track {
        background: transparent;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .menu-items-list {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        }
    }
</style>
@endpush
