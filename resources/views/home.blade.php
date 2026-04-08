<x-layouts.site :settings="$settings" :title="$settings['clinic_name']">
    <main>
        <section class="hero-grid">
            <div class="hero-copy">
                <span class="eyebrow">Cirugía estética consciente</span>
                <h1>{{ $settings['hero_title'] }}</h1>
                <p>{{ $settings['hero_subtitle'] }}</p>
                <div class="hero-actions">
                    <a href="#contacto" class="btn-primary">Agendar valoración</a>
                    <a href="#procedimientos" class="btn-secondary">Ver procedimientos</a>
                </div>
            </div>
            <div class="hero-card">
                <div class="hero-card-panel">
                    <p class="mini-label">Operación comercial lista</p>
                    <h2>Web + Filament + CRM + WhatsApp</h2>
                    <p>Esta base ya está pensada para replicarse por cliente y luego exponerse como API para app móvil.</p>
                </div>
            </div>
        </section>

        <section class="stats-grid">
            @foreach ($stats as $stat)
                <article class="stat-card">
                    <strong>{{ $stat->value }}</strong>
                    <span>{{ $stat->label }}</span>
                </article>
            @endforeach
        </section>

        <section id="procedimientos" class="section-block">
            <div class="section-head">
                <span class="eyebrow">Servicios premium</span>
                <h2>Procedimientos destacados</h2>
            </div>
            <div class="card-grid">
                @foreach ($featuredServices as $service)
                    <article class="service-card">
                        <span class="service-tag">{{ $service->is_premium ? 'Premium' : 'Procedimiento' }}</span>
                        <h3>{{ $service->name }}</h3>
                        <p>{{ $service->short_description }}</p>
                        <div class="service-links">
                            <a href="{{ route('services.show', $service) }}">Más información</a>
                            <a href="{{ $service->whatsapp_url }}" target="_blank" rel="noreferrer">WhatsApp</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="doctor" class="section-block split-section">
            <div class="doctor-profile">
                @if (!empty($doctor?->photo_path))
                    <img src="{{ asset('storage/' . $doctor->photo_path) }}" alt="{{ $doctor->name }}" class="doctor-photo">
                @endif
                <div>
                    <span class="eyebrow">Perfil médico</span>
                    <h2>{{ $doctor?->name ?? 'Especialista principal' }}</h2>
                    <p class="lead-text">{{ $doctor?->subtitle }}</p>
                    <p>{{ $doctor?->bio }}</p>
                    <div class="doctor-meta">
                        <span>{{ $doctor?->specialty }}</span>
                        <span>{{ $doctor?->university }}</span>
                    </div>
                </div>
            </div>
            <div class="doctor-box">
                <h3>Tecnología complementaria</h3>
                <ul class="tech-list">
                    @foreach ($technologies as $technology)
                        <li>
                            <strong>{{ $technology->name }}</strong>
                            <span>{{ $technology->description }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section id="testimonios" class="section-block">
            <div class="section-head">
                <span class="eyebrow">Prueba social</span>
                <h2>Testimonios reales</h2>
            </div>
            <div class="testimonial-grid">
                @foreach ($testimonials as $testimonial)
                    <article class="testimonial-card">
                        <div class="testimonial-rating">{{ str_repeat('★', (int) $testimonial->rating) }}</div>
                        <p>“{{ $testimonial->content }}”</p>
                        <strong>{{ $testimonial->patient_name }}</strong>
                        <span>{{ ucfirst($testimonial->source) }}</span>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="contacto" class="section-block contact-section">
            <div>
                <span class="eyebrow">Conversión</span>
                <h2>Solicita tu valoración</h2>
                <p>Este formulario guarda el lead localmente y lo envía a TayrAI cuando la integración está activa.</p>
                <div class="contact-data">
                    <span>{{ $settings['phone'] }}</span>
                    <span>{{ $settings['email'] }}</span>
                    <span>{{ $settings['address'] }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('leads.store') }}" class="contact-form">
                @csrf
                <input type="hidden" name="source" value="website">
                <input type="text" name="name" placeholder="Nombre completo" value="{{ old('name') }}" required>
                <input type="text" name="phone" placeholder="WhatsApp / Teléfono" value="{{ old('phone') }}" required>
                <input type="email" name="email" placeholder="Correo" value="{{ old('email') }}">
                <select name="service_id">
                    <option value="">Procedimiento de interés</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
                <textarea name="message" rows="5" placeholder="Cuéntanos qué procedimiento te interesa">{{ old('message') }}</textarea>
                <button type="submit" class="btn-primary">Enviar solicitud</button>
            </form>
        </section>
    </main>
</x-layouts.site>
