@if ($page['trustItems']->isNotEmpty())
    <section class="dk-trust" aria-label="Por qué elegirnos">
        <div class="container">
            <ul class="dk-trust-list">
                @foreach ($page['trustItems'] as $item)
                    <li class="dk-trust-item">
                        <span class="dk-trust-icon"><x-site-icon :name="$item['icon']" /></span>
                        <span class="dk-trust-copy">
                            @if ($item['value'])
                                <strong>{{ $item['value'] }}</strong>
                            @endif
                            <span>{{ $item['label'] }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
