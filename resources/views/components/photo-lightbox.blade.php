{{--
    Grid thumbnail foto + lightbox fullscreen dengan zoom in/out.
    Pemakaian: <x-photo-lightbox :urls="$collectionOfUrls" />
    Kontrol: klik thumbnail utk buka; tombol +/-, scroll mouse, atau
    double-click utk zoom; drag utk geser saat di-zoom; panah kiri/kanan
    utk pindah foto; Esc utk tutup.
--}}
@props(['urls' => [], 'gridClass' => 'grid grid-cols-3 sm:grid-cols-5 gap-3'])

@php($urls = collect($urls)->values())

<div x-data="{
        photos: @js($urls),
        open: false,
        index: 0,
        scale: 1,
        x: 0,
        y: 0,
        dragging: false,
        startX: 0,
        startY: 0,
        min: 1,
        max: 5,
        show(i) { this.index = i; this.reset(); this.open = true; document.body.classList.add('overflow-hidden'); },
        close() { this.open = false; document.body.classList.remove('overflow-hidden'); },
        reset() { this.scale = 1; this.x = 0; this.y = 0; },
        zoom(delta) {
            this.scale = Math.min(this.max, Math.max(this.min, +(this.scale + delta).toFixed(2)));
            if (this.scale === 1) { this.x = 0; this.y = 0; }
        },
        wheel(e) { this.zoom(e.deltaY < 0 ? 0.25 : -0.25); },
        toggleZoom() { this.scale > 1 ? this.reset() : (this.scale = 2); },
        prev() { if (this.photos.length > 1) { this.index = (this.index - 1 + this.photos.length) % this.photos.length; this.reset(); } },
        next() { if (this.photos.length > 1) { this.index = (this.index + 1) % this.photos.length; this.reset(); } },
        pointerDown(e) {
            if (this.scale <= 1) return;
            this.dragging = true;
            this.startX = e.clientX - this.x;
            this.startY = e.clientY - this.y;
            e.target.setPointerCapture?.(e.pointerId);
        },
        pointerMove(e) {
            if (!this.dragging) return;
            this.x = e.clientX - this.startX;
            this.y = e.clientY - this.startY;
        },
        pointerUp() { this.dragging = false; },
     }"
     @keydown.escape.window="open && close()"
     @keydown.arrow-left.window="open && prev()"
     @keydown.arrow-right.window="open && next()">

    @if ($slot->isNotEmpty())
        {{-- Thumbnail custom: elemen di slot panggil show(index) sendiri. --}}
        {{ $slot }}
    @else
        <div class="{{ $gridClass }}">
            @foreach ($urls as $i => $url)
                <button type="button" @click="show({{ $i }})" class="block focus:outline-none focus:ring-2 focus:ring-action rounded-md">
                    <img src="{{ $url }}" alt="Lampiran {{ $i + 1 }}" loading="lazy"
                         class="w-full aspect-square rounded-md object-cover border border-hairline hover:opacity-90 cursor-zoom-in">
                </button>
            @endforeach
        </div>
    @endif

    <template x-teleport="body">
        <div x-show="open" x-cloak x-transition.opacity
             class="fixed inset-0 z-[100] bg-black/90 flex flex-col select-none" style="display:none;">

            {{-- Toolbar --}}
            <div class="flex items-center justify-between gap-2 px-4 py-3 text-white">
                <span class="text-sm text-white/70" x-text="(index + 1) + ' / ' + photos.length"></span>
                <div class="flex items-center gap-1">
                    <button type="button" @click="zoom(-0.5)" :disabled="scale <= min" title="Zoom out"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-full hover:bg-white/10 disabled:opacity-30">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3M8 11h6"/></svg>
                    </button>
                    <span class="w-12 text-center text-xs tabular-nums" x-text="Math.round(scale * 100) + '%'"></span>
                    <button type="button" @click="zoom(0.5)" :disabled="scale >= max" title="Zoom in"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-full hover:bg-white/10 disabled:opacity-30">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3M8 11h6M11 8v6"/></svg>
                    </button>
                    <button type="button" @click="reset()" title="Ukuran asli"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-full hover:bg-white/10">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                    </button>
                    <a :href="photos[index]" target="_blank" title="Buka di tab baru"
                       class="w-9 h-9 inline-flex items-center justify-center rounded-full hover:bg-white/10">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    </a>
                    <button type="button" @click="close()" title="Tutup (Esc)"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-full hover:bg-white/10">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>
            </div>

            {{-- Gambar --}}
            <div class="relative flex-1 overflow-hidden flex items-center justify-center"
                 @click.self="close()" @wheel.prevent="wheel($event)">
                <img :src="photos[index]" alt="" draggable="false"
                     @dblclick="toggleZoom()"
                     @pointerdown.prevent="pointerDown($event)"
                     @pointermove="pointerMove($event)"
                     @pointerup="pointerUp()" @pointercancel="pointerUp()"
                     :style="`transform: translate(${x}px, ${y}px) scale(${scale}); transition: ${dragging ? 'none' : 'transform .15s ease-out'}`"
                     :class="scale > 1 ? (dragging ? 'cursor-grabbing' : 'cursor-grab') : 'cursor-zoom-in'"
                     class="max-w-[95vw] max-h-[85vh] object-contain touch-none">

                <template x-if="photos.length > 1">
                    <div>
                        <button type="button" @click="prev()" title="Sebelumnya"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 inline-flex items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                        <button type="button" @click="next()" title="Berikutnya"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 inline-flex items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            <p class="pb-3 text-center text-xs text-white/50">Scroll / tombol +− utk zoom · double-click utk zoom cepat · drag utk geser</p>
        </div>
    </template>
</div>
