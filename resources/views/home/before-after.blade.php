{{-- Comparador antes/después. Recibe $pairs (App\Support\Media::beforeAfter). El deslizador está en resources/js/app.js. --}}
@if ($pairs->isNotEmpty())
    <section id="antes-despues" class="section dk-ba" aria-labelledby="dk-ba-title">
        <div class="container">
            <p class="kicker">{{ $settings['before_after_kicker'] ?? 'Antes y después' }}</p>
            <h2 id="dk-ba-title" class="section-title">{{ $settings['before_after_title'] ?? 'Cambios reales, resultados naturales' }}</h2>

            <div class="dk-ba-grid">
                @foreach ($pairs as $pair)
                    <figure class="dk-ba-item">
                        <div class="dk-ba-slider" style="--dk-pos: 50%">
                            <img src="{{ $pair['after'] }}" alt="Después: {{ $pair['title'] }}" class="dk-ba-img dk-ba-after" loading="lazy" decoding="async">
                            <img src="{{ $pair['before'] }}" alt="Antes: {{ $pair['title'] }}" class="dk-ba-img dk-ba-before" loading="lazy" decoding="async">
                            <span class="dk-ba-tag dk-ba-tag-before" aria-hidden="true">Antes</span>
                            <span class="dk-ba-tag dk-ba-tag-after" aria-hidden="true">Después</span>
                            <span class="dk-ba-handle" aria-hidden="true"></span>
                            <input type="range" class="dk-ba-range" min="0" max="100" step="1" value="50"
                                   aria-label="Deslizar para comparar antes y después: {{ $pair['title'] }}">
                        </div>
                        <figcaption>
                            <strong>{{ $pair['title'] }}</strong>
                            @if ($pair['label'] && $pair['label'] !== $pair['title'])
                                <span>{{ $pair['label'] }}</span>
                            @endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            <p class="dk-ba-note">Los resultados pueden variar según cada paciente.</p>
        </div>
    </section>
@endif
