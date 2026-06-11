<x-filament-panels::page>
    @if (session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form id="homeBannersForm" method="POST" action="{{ route('admin.home-banners.upload') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Carrusel de banners</h3>
            <p class="mt-1 text-sm text-gray-600">Sube hasta 3 imágenes. Se optimizan antes de enviarse para evitar fallos de carga.</p>

            <div class="mt-5 grid gap-5">
                @foreach ([1, 2, 3] as $slot)
                    @php
                        $pathProp = 'banner' . $slot . 'Path';
                        $url = $this->getBannerUrl($this->{$pathProp});
                    @endphp

                    <div class="rounded-lg border border-gray-200 p-4">
                        <label for="home_banner_{{ $slot }}_image" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-700">
                            Banner {{ $slot }}
                        </label>

                        @if ($url)
                            <img src="{{ $url }}" alt="Banner {{ $slot }} actual" class="mb-3 h-auto w-full rounded-md border border-gray-200 object-cover" style="max-height: 210px;">
                        @endif

                        <input
                            type="file"
                            id="home_banner_{{ $slot }}_image"
                            name="home_banner_{{ $slot }}_image"
                            accept="image/jpeg,image/png,image/webp"
                            class="banner-file-input block w-full text-sm text-gray-700"
                            data-preview-id="banner-preview-{{ $slot }}"
                        >

                        <p class="mt-2 text-xs text-gray-500">Formatos permitidos: JPG, PNG o WebP. Se comprime automáticamente a 1200x525 antes de subir.</p>
                        <p id="banner-preview-{{ $slot }}" class="mt-1 text-xs text-gray-500"></p>
                    </div>
                @endforeach
            </div>
        </section>

        <div>
            <x-filament::button type="submit" icon="heroicon-m-check">
                Guardar banners
            </x-filament::button>
        </div>
    </form>

    <script>
        (function () {
            const inputs = document.querySelectorAll('.banner-file-input');
            if (!inputs.length) return;

            const MAX_W = 1200;
            const MAX_H = 525;
            const QUALITY = 0.82;

            const loadImage = (file) => new Promise((resolve, reject) => {
                const img = new Image();
                const url = URL.createObjectURL(file);
                img.onload = () => {
                    URL.revokeObjectURL(url);
                    resolve(img);
                };
                img.onerror = reject;
                img.src = url;
            });

            const compressImage = async (file) => {
                const img = await loadImage(file);
                const canvas = document.createElement('canvas');

                const sourceRatio = img.width / img.height;
                const targetRatio = MAX_W / MAX_H;

                let sx = 0;
                let sy = 0;
                let sw = img.width;
                let sh = img.height;

                if (sourceRatio > targetRatio) {
                    sw = img.height * targetRatio;
                    sx = (img.width - sw) / 2;
                } else {
                    sh = img.width / targetRatio;
                    sy = (img.height - sh) / 2;
                }

                canvas.width = MAX_W;
                canvas.height = MAX_H;

                const ctx = canvas.getContext('2d');
                if (!ctx) return file;

                ctx.drawImage(img, sx, sy, sw, sh, 0, 0, MAX_W, MAX_H);

                const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/webp', QUALITY));
                if (!blob) return file;

                const extensionless = file.name.replace(/\.[^.]+$/, '');
                return new File([blob], `${extensionless}.webp`, { type: 'image/webp' });
            };

            inputs.forEach((input) => {
                input.addEventListener('change', async () => {
                    const file = input.files && input.files[0];
                    const previewId = input.getAttribute('data-preview-id');
                    const previewEl = previewId ? document.getElementById(previewId) : null;

                    if (!file) {
                        if (previewEl) previewEl.textContent = '';
                        return;
                    }

                    if (previewEl) previewEl.textContent = 'Optimizando imagen...';

                    try {
                        const optimized = await compressImage(file);
                        const dt = new DataTransfer();
                        dt.items.add(optimized);
                        input.files = dt.files;

                        const kb = Math.round(optimized.size / 1024);
                        if (previewEl) {
                            previewEl.textContent = `Archivo listo: ${optimized.name} (${kb} KB)`;
                        }
                    } catch (error) {
                        if (previewEl) {
                            previewEl.textContent = 'No se pudo optimizar automáticamente. Se enviará el archivo original.';
                        }
                    }
                });
            });
        })();
    </script>
</x-filament-panels::page>
