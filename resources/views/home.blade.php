@php
    $clinicName = $settings['clinic_name'] ?? 'Doctor Kevin Rejuvenezk';
    $heroKicker = $settings['hero_kicker'] ?? 'Medicina estética avanzada';
    $heroTitle = $settings['hero_title'] ?? 'Rejuvenecimiento facial con resultados naturales.';
    $heroSubtitle = $settings['hero_subtitle'] ?? 'Tecnología avanzada, criterio médico y protocolos personalizados para armonizar tu rostro sin perder expresión.';
    $heroPrimaryCta = $settings['hero_primary_cta'] ?? 'Reservar valoración';
    $heroSecondaryCta = $settings['hero_secondary_cta'] ?? 'Ver tratamientos';
    $heroCardKicker = $settings['hero_card_kicker'] ?? 'Primera cita';
    $heroCardTitle = $doctor?->subtitle ?: ($settings['hero_card_title'] ?? 'Diagnóstico inteligente de piel');
    $heroCardText = $doctor?->bio ?: ($settings['hero_card_text'] ?? 'Análisis de calidad cutánea y plan estético por etapas para resultados armónicos y progresivos.');
    $heroStackPrimaryLabel = $settings['hero_stack_primary_label'] ?? 'Protocolo estrella';
    $heroStackSecondaryLabel = $settings['hero_stack_secondary_label'] ?? 'Especialista';
    $servicesKicker = $settings['services_kicker'] ?? 'Nuestros tratamientos';
    $servicesTitle = $settings['services_title'] ?? 'Tratamientos seleccionados con enfoque médico estético';
    $servicesLead = $settings['services_lead'] ?? 'Cada protocolo combina diagnóstico, técnica y naturalidad para construir resultados elegantes, progresivos y coherentes con tu rostro.';
    $benefitsKicker = $settings['benefits_kicker'] ?? 'Nuestra promesa';
    $benefitsTitle = $settings['benefits_title'] ?? 'Una experiencia estética pensada para verse bien y sentirse bien.';
    $benefitsLead = $settings['benefits_lead'] ?? 'Combinamos precisión médica, criterio facial y acompañamiento cercano para que cada decisión se tome con confianza.';
    $rawResultsKicker = trim((string) ($settings['testimonials_kicker'] ?? ''));
    $testimonialsKicker = in_array(mb_strtolower($rawResultsKicker), ['testimonios', 'testimonio'])
        ? 'Resultados'
        : ($rawResultsKicker !== '' ? $rawResultsKicker : 'Resultados');
    $testimonialsTitle = $settings['testimonials_title'] ?? 'Resultados y testimonios reales';
    $socialKicker = $settings['social_kicker'] ?? ('Comunidad ' . $clinicName);
    $socialTitle = $settings['social_title'] ?? 'Conecta con nuestras redes y canales';
    $socialLead = $settings['social_lead'] ?? 'Comparte resultados, conoce casos reales y recibe novedades de nuestros tratamientos en tiempo real.';
    $ctaKicker = $settings['cta_kicker'] ?? 'Agenda tu cita';
    $ctaTitle = $settings['cta_title'] ?? 'Empieza tu valoración con una experiencia clara y personalizada.';
    $ctaBody = $settings['cta_body'] ?? 'Atención médica estética, seguimiento ordenado y una propuesta pensada para tus objetivos reales.';
    $ctaNote = $settings['cta_note'] ?? 'Respuesta comercial por WhatsApp o correo con orientación inicial y siguientes pasos.';
    $topbarCtaLabel = $settings['topbar_cta_label'] ?? 'Agenda ahora';
    $ctaWhatsappLabel = $settings['cta_whatsapp_label'] ?? 'WhatsApp';
    $ctaEmailLabel = $settings['cta_email_label'] ?? 'Solicitar información';
    $whatsappRawUrl = trim((string) ($settings['whatsapp_url'] ?? ''));
    $whatsappDigits = preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '');
    $hasWhatsappLink = $whatsappRawUrl !== '' || $whatsappDigits !== '';
    $whatsappUrl = $whatsappRawUrl !== ''
        ? $whatsappRawUrl
        : ($whatsappDigits ? 'https://wa.me/' . $whatsappDigits : '#contacto');
    $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
    $normalizeMediaPath = function (?string $path): ?string {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $normalized = trim($path);

        if (filter_var($normalized, FILTER_VALIDATE_URL)) {
            $normalized = parse_url($normalized, PHP_URL_PATH) ?: $normalized;
        }

        $normalized = ltrim(rawurldecode($normalized), '/');

        foreach (['media/', 'storage/', 'public/', 'app/public/'] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = substr($normalized, strlen($prefix));
            }
        }

        return $normalized !== '' ? $normalized : null;
    };
    $clinicLogoPath = $normalizeMediaPath($clinic?->logo_path);
    $doctorPhotoPath = $normalizeMediaPath($doctor?->photo_path);
    $clinicLogoUrl = $clinicLogoPath && $publicDisk->exists($clinicLogoPath)
        ? $publicDisk->url($clinicLogoPath)
        : null;
    $doctorPhotoUrl = $doctorPhotoPath && $publicDisk->exists($doctorPhotoPath)
        ? $publicDisk->url($doctorPhotoPath)
        : null;
    $resolveMediaUrl = function (?string $value) use ($normalizeMediaPath, $publicDisk): ?string {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $candidate = trim($value);

        if (filter_var($candidate, FILTER_VALIDATE_URL)) {
            return $candidate;
        }

        $normalized = $normalizeMediaPath($candidate);

        if (! $normalized) {
            return null;
        }

        return $publicDisk->exists($normalized)
            ? $publicDisk->url($normalized)
            : null;
    };
    $brandInitials = collect(preg_split('/\s+/', trim($clinicName)))
        ->filter()
        ->take(2)
        ->map(fn ($segment) => strtoupper(substr($segment, 0, 1)))
        ->implode('');
    $bannerMedia = null;

    $featuredBannerPath = $normalizeMediaPath($featuredServices->first()?->banner_path);
    $featuredImagePath = $normalizeMediaPath($featuredServices->first()?->image_path);

    if ($featuredBannerPath) {
        $bannerMedia = $publicDisk->exists($featuredBannerPath)
            ? $publicDisk->url($featuredBannerPath)
            : null;
    } elseif ($featuredImagePath && $publicDisk->exists($featuredImagePath)) {
        $bannerMedia = $publicDisk->url($featuredImagePath);
    }

    $homeBannerDefinitions = collect([
        [
            'kicker' => 'Medicina estética',
            'title' => 'Resultados naturales con criterio médico',
            'text' => 'Valoración personalizada y protocolos diseñados para tu rostro.',
            'image' => $resolveMediaUrl($settings['home_banner_1_image'] ?? null),
        ],
        [
            'kicker' => 'Tecnología avanzada',
            'title' => 'Tratamientos seguros y progresivos',
            'text' => 'Combinamos diagnóstico, precisión clínica y seguimiento cercano.',
            'image' => $resolveMediaUrl($settings['home_banner_2_image'] ?? null),
        ],
        [
            'kicker' => 'Atención profesional',
            'title' => 'Plan integral para armonizar tu belleza',
            'text' => 'Una experiencia estética clara, elegante y enfocada en confianza.',
            'image' => $resolveMediaUrl($settings['home_banner_3_image'] ?? null),
        ],
    ]);

    $fallbackBannerImages = collect([
        $doctorPhotoUrl,
        $bannerMedia,
        $resolveMediaUrl($settings['hero_banner_image'] ?? null),
        $clinicLogoUrl,
    ])->filter()->values();

    $heroBanners = $homeBannerDefinitions
        ->map(function (array $banner, int $index) use ($fallbackBannerImages) {
            if (!empty($banner['image'])) {
                return $banner;
            }

            return [
                ...$banner,
                'image' => $fallbackBannerImages[$index] ?? $fallbackBannerImages->first(),
            ];
        })
        ->values();

    $heroMetrics = $stats->take(2)->map(fn ($stat) => [
        'value' => $stat->value,
        'label' => $stat->label,
    ]);

    if ($heroMetrics->isEmpty()) {
        $heroMetrics = collect([
            ['value' => '+4,800', 'label' => 'Pacientes atendidos'],
            ['value' => '12 años', 'label' => 'Experiencia clínica'],
        ]);
    }

    $heroEditorialNote = $doctor?->name
        ? 'Valoración dirigida por ' . $doctor->name . ' con enfoque médico estético y protocolos a medida.'
        : 'Valoraciones personalizadas con criterio médico, tecnología avanzada y resultados armónicos.';

    $heroSubtitle = trim((string) $heroSubtitle);
    if (mb_strlen($heroSubtitle) > 210) {
        $heroSubtitle = \Illuminate\Support\Str::limit($heroSubtitle, 210, '...');
    }

    if (trim((string) $heroTitle) === 'Medicina estética y cirugía con enfoque premium') {
        $heroTitle = 'Medicina estética y cirugía con enfoque médico';
    }

    $heroEditorialNote = trim((string) $heroEditorialNote);
    if (mb_strlen($heroEditorialNote) > 150) {
        $heroEditorialNote = \Illuminate\Support\Str::limit($heroEditorialNote, 150, '...');
    }

    $instagramHandle = '@drkevin_rejuvenezk';

    if (!empty($settings['instagram_url'])) {
        $instagramPath = trim((string) parse_url($settings['instagram_url'], PHP_URL_PATH), '/');

        if ($instagramPath !== '') {
            $instagramHandle = '@' . $instagramPath;
        }
    }

    $socialCards = collect([
        ['label' => 'Instagram', 'icon' => 'instagram', 'handle' => $instagramHandle, 'text' => 'Antes y después, reels y tips de cuidado', 'url' => $settings['instagram_url'] ?? null],
        ['label' => 'Facebook', 'icon' => 'facebook', 'handle' => 'Perfil oficial', 'text' => 'Novedades, eventos y promociones especiales', 'url' => $settings['facebook_url'] ?? null],
        ['label' => 'TikTok', 'icon' => 'tiktok', 'handle' => '@rejuvenezk', 'text' => 'Videos de procedimientos y contenido educativo', 'url' => $settings['tiktok_url'] ?? null],
        ['label' => 'YouTube', 'icon' => 'youtube', 'handle' => 'Rejuvenezk TV', 'text' => 'Testimonios completos y explicación médica', 'url' => $settings['youtube_url'] ?? null],
        ['label' => 'WhatsApp', 'icon' => 'whatsapp', 'handle' => $settings['phone'] ?: 'Agenda inmediata', 'text' => 'Atención comercial y seguimiento de valoraciones', 'url' => $hasWhatsappLink ? $whatsappUrl : null],
        ['label' => 'Correo', 'icon' => 'mail', 'handle' => $settings['email'] ?: 'contacto@rejuvenezk.com', 'text' => 'Solicita información detallada y propuestas personalizadas', 'url' => !empty($settings['email']) ? 'mailto:' . $settings['email'] : null],
    ]);

    $socialBannerCards = $socialCards->filter(fn ($card) => ! empty($card['url']))->values();

    $socialIcons = [
        'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm0 1.9A3.85 3.85 0 0 0 3.9 7.75v8.5a3.85 3.85 0 0 0 3.85 3.85h8.5a3.85 3.85 0 0 0 3.85-3.85v-8.5a3.85 3.85 0 0 0-3.85-3.85h-8.5Zm8.9 1.5a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.9A3.1 3.1 0 1 0 12 15.1 3.1 3.1 0 0 0 12 8.9Z"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 22v-8h2.7l.5-3h-3.2V9.1c0-.9.3-1.5 1.7-1.5h1.6V5c-.8-.1-1.7-.2-2.6-.2-2.6 0-4.2 1.6-4.2 4.4V11H8v3h2.5v8h3Z"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.9 3c.2 1.8 1.2 3.4 2.8 4.3 1 .6 2.1.9 3.3.9v3.1c-1.4 0-2.7-.3-3.9-.9-.8-.4-1.5-.9-2.2-1.5v6.1c0 3.4-2.7 6-6.1 6a6 6 0 0 1-6-6c0-3.4 2.7-6.1 6-6.1.3 0 .6 0 .9.1v3.1a3 3 0 1 0 2.2 2.9V3h3Z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12s0-3-.4-4.4a3.1 3.1 0 0 0-2.2-2.2C18 5 12 5 12 5s-6 0-7.4.4a3.1 3.1 0 0 0-2.2 2.2C2 9 2 12 2 12s0 3 .4 4.4a3.1 3.1 0 0 0 2.2 2.2C6 19 12 19 12 19s6 0 7.4-.4a3.1 3.1 0 0 0 2.2-2.2C22 15 22 12 22 12Zm-12.3 3.5V8.5l5.8 3.5-5.8 3.5Z"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.2a9.8 9.8 0 0 0-8.4 14.9L2 22l5-1.6A9.8 9.8 0 1 0 12 2.2Zm0 17.8c-1.5 0-3-.4-4.2-1.2l-.3-.2-2.9.9.9-2.8-.2-.3A7.8 7.8 0 1 1 12 20Zm4.3-5.9c-.2-.1-1.3-.6-1.5-.7s-.4-.1-.6.1-.7.7-.8.8-.3.2-.5.1a6.4 6.4 0 0 1-1.9-1.2 7.2 7.2 0 0 1-1.3-1.6c-.1-.2 0-.4.1-.5l.4-.4c.1-.1.2-.2.2-.4l.1-.3c0-.1-.6-1.6-.8-2.1-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.4.1-.6.3-.2.2-.8.8-.8 1.9s.8 2.2.9 2.3c.1.2 1.6 2.5 3.9 3.5.5.2 1 .4 1.4.5.6.2 1.1.2 1.5.1.5-.1 1.3-.5 1.5-1 .2-.5.2-.9.1-1 0-.1-.2-.2-.4-.3Z"/></svg>',
        'mail' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5.5h18A1.5 1.5 0 0 1 22.5 7v10A1.5 1.5 0 0 1 21 18.5H3A1.5 1.5 0 0 1 1.5 17V7A1.5 1.5 0 0 1 3 5.5Zm0 1.7a.3.3 0 0 0-.3.3v.2l9.3 6.3 9.3-6.3v-.2a.3.3 0 0 0-.3-.3H3Zm18 9.6a.3.3 0 0 0 .3-.3V9.8l-8.8 6a.9.9 0 0 1-1 0l-8.8-6v6.7c0 .2.1.3.3.3H21Z"/></svg>',
    ];

    $testimonialsToShow = $testimonials->take(3);
    $reviewsUrl = trim((string) ($settings['reviews_url'] ?? ''));
    $testimonialsCount = $testimonials->count();
    $testimonialsAvg = $testimonialsCount > 0 ? number_format((float) $testimonials->avg('rating'), 1) : '4.9';
    $credentialKicker = $settings['credentials_kicker'] ?? 'Respaldo médico';
    $credentialTitle = $settings['credentials_title'] ?? 'Certificaciones y trayectoria para decidir con confianza';

    $credentialBadges = collect([
        $settings['credential_badge_1'] ?? 'Miembro de sociedad científica',
        $settings['credential_badge_2'] ?? 'Protocolos estandarizados y trazables',
        $settings['credential_badge_3'] ?? 'Tecnología con enfoque médico-estético',
    ])->filter();

    $benefitCards = collect([
        [
            'title' => 'Resultados armoniosos',
            'text' => 'Realzamos tus facciones con una mirada natural, sin exageraciones ni cambios ajenos a tu rostro.',
            'label' => 'Naturalidad',
        ],
        [
            'title' => 'Seguridad clínica',
            'text' => 'Protocolos médicos, tecnología confiable y decisiones guiadas por evaluación profesional.',
            'label' => 'Confianza',
        ],
        [
            'title' => 'Criterio personalizado',
            'text' => 'Cada tratamiento se diseña según tu anatomía, tus objetivos y el ritmo adecuado para ti.',
            'label' => 'Precision',
        ],
    ]);

    $aboutKicker = $settings['about_kicker'] ?? 'Quiénes somos';
    $aboutTitle = $settings['about_title'] ?? 'Medicina estética con criterio humano y respaldo médico';
    $aboutLead = $settings['about_lead'] ?? 'Somos un centro médico estético especializado en el cuidado integral de la salud y la belleza. Nuestro equipo está conformado por profesionales certificados, comprometidos con resultados naturales y progresivos.';
    $aboutDoctorLine = $settings['about_doctor_line'] ?? ('En ' . $clinicName . ' combinamos ciencia avanzada y diagnóstico personalizado para ofrecer tratamientos seguros, eficaces y mínimamente invasivos.');
    $aboutMission = $settings['about_mission'] ?? 'Brindar soluciones estéticas integrales que realcen la belleza natural de nuestros pacientes, combinando vanguardia médica con un trato humano y personalizado para mejorar su autoestima y calidad de vida.';
    $aboutVision = $settings['about_vision'] ?? 'Ser una clínica estética líder en la región, reconocida por la calidez en el servicio y la excelencia en resultados naturales, logrando que cada paciente se sienta la mejor versión de sí mismo.';

    $aboutImageUrl = null;
    $aboutImagePathCandidate = trim((string) ($settings['about_image_url'] ?? ''));
    if ($aboutImagePathCandidate !== '') {
        if (filter_var($aboutImagePathCandidate, FILTER_VALIDATE_URL)) {
            $aboutImageUrl = $aboutImagePathCandidate;
        } else {
            $normalizedAboutImagePath = $normalizeMediaPath($aboutImagePathCandidate);
            if ($normalizedAboutImagePath && $publicDisk->exists($normalizedAboutImagePath)) {
                $aboutImageUrl = $publicDisk->url($normalizedAboutImagePath);
            }
        }
    }

    if (! $aboutImageUrl) {
        $aboutImageUrl = $bannerMedia ?: $clinicLogoUrl;
    }

    $aboutImageAlt = $settings['about_image_alt'] ?? ('Especialista de ' . $clinicName);
    $aboutEntryTitle = $settings['about_entry_title'] ?? 'Conoce la clínica, su historia y el enfoque médico detrás de cada tratamiento.';
    $aboutEntryLead = $settings['about_entry_lead'] ?? 'La portada ahora prioriza tratamientos e imágenes. Si quieres profundizar en nuestra identidad, abre la sección dedicada.';
    $aboutEntryCta = $settings['about_entry_cta'] ?? 'Ver quiénes somos';
    $resultsKicker = $settings['results_kicker'] ?? 'Casos y resultados';
    $resultsTitle = $settings['results_title'] ?? 'Imágenes que capturan el tipo de cambio que buscan tus pacientes.';
    $resultsLead = $settings['results_lead'] ?? 'Menos discurso y más evidencia visual: tratamientos, armonización y resultados reales en una sola mirada.';

    $galleryPreview = $galleryItems->map(function ($item) use ($normalizeMediaPath, $publicDisk) {
        $candidates = [
            $item->image_path,
            $item->after_image_path,
            $item->before_image_path,
        ];

        foreach ($candidates as $candidate) {
            $normalized = $normalizeMediaPath($candidate);

            if ($normalized && $publicDisk->exists($normalized)) {
                return [
                    'url' => $publicDisk->url($normalized),
                    'title' => $item->title ?: ($item->service?->name ?? 'Resultado Rejuvenezk'),
                    'label' => $item->service?->name ?: 'Caso destacado',
                ];
            }
        }

        return null;
    })->filter()->take(4)->values();

    $resolveVideoItem = function ($item) {
        $raw = trim((string) ($item->video_url ?? ''));

        if ($raw === '') {
            return null;
        }

        $title = $item->title ?: ($item->service?->name ?? 'Video de resultado');
        $label = $item->service?->name ?: 'Caso real';
        $poster = 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 1920 1080%27%3E%3Cdefs%3E%3ClinearGradient id=%27g%27 x1=%270%25%27 y1=%270%25%27 x2=%27100%25%27 y2=%27100%25%27%3E%3Cstop offset=%270%25%27 stop-color=%27%230f0f10%27/%3E%3Cstop offset=%2745%25%27 stop-color=%27%23221b1a%27/%3E%3Cstop offset=%27100%25%27 stop-color=%27%239b8860%27/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width=%271920%27 height=%271080%27 fill=%27url(%23g)%27/%3E%3Ccircle cx=%27960%27 cy=%27540%27 r=%27170%27 fill=%27white%27 opacity=%270.12%27/%3E%3Cpolygon points=%27924,466 924,614 1044,540%27 fill=%27white%27 opacity=%270.85%27/%3E%3C/svg%3E';

        if (! filter_var($raw, FILTER_VALIDATE_URL)) {
            return [
                'url' => \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($raw, '/')),
                'is_iframe' => false,
                'title' => $title,
                'label' => $label,
                'poster' => $poster,
            ];
        }

        $host = strtolower((string) parse_url($raw, PHP_URL_HOST));

        if (str_contains($host, 'youtu.be') || str_contains($host, 'youtube.com')) {
            $videoId = null;

            if (str_contains($host, 'youtu.be')) {
                $videoId = trim((string) parse_url($raw, PHP_URL_PATH), '/');
            } else {
                parse_str((string) parse_url($raw, PHP_URL_QUERY), $queryParts);
                $videoId = $queryParts['v'] ?? null;
            }

            if ($videoId) {
                $youtubeUrl = 'https://www.youtube.com/embed/' . $videoId . '?controls=1&rel=0&playsinline=1';
                return [
                    'url' => $youtubeUrl,
                    'is_iframe' => true,
                    'title' => $title,
                    'label' => $label,
                ];
            }
        }

        if (str_contains($host, 'vimeo.com')) {
            $videoId = trim((string) parse_url($raw, PHP_URL_PATH), '/');

            if ($videoId) {
                $vimeoUrl = 'https://player.vimeo.com/video/' . $videoId . '?autoplay=0&muted=0&title=0&byline=0&portrait=0&playsinline=1';
                return [
                    'url' => $vimeoUrl,
                    'is_iframe' => true,
                    'title' => $title,
                    'label' => $label,
                ];
            }
        }

        return [
            'url' => $raw,
            'is_iframe' => false,
            'title' => $title,
            'label' => $label,
            'poster' => $poster,
        ];
    };

    $videoResultsPreview = $galleryItems
        ->filter(fn ($item) => $item->type === 'video' && ! empty($item->video_url))
        ->map($resolveVideoItem)
        ->filter()
        ->take(6)
        ->values();

    // ===== DK · Rediseño (Fase 1) =====
    $dkWhatsappText = $settings['whatsapp_booking_text'] ?? 'Hola, quiero agendar una valoración';
    $dkWhatsappBookingUrl = $hasWhatsappLink
        ? $whatsappUrl . (str_contains($whatsappUrl, '?') ? '&' : '?') . 'text=' . rawurlencode($dkWhatsappText)
        : '#formulario';
    $dkWhatsappLinkAttrs = $hasWhatsappLink ? 'target="_blank" rel="noopener noreferrer"' : '';

    // Franja de confianza: estadísticas reales si existen; si no, textos editables en Ajustes (branding.trust_item_N).
    $dkTrustIcons = ['shield', 'doctor', 'spark', 'heart'];
    $dkTrustItems = $stats->isNotEmpty()
        ? $stats->take(4)->values()->map(fn ($stat, $i) => ['value' => $stat->value, 'label' => $stat->label, 'icon' => $dkTrustIcons[$i % 4]])
        : collect([
            $settings['trust_item_1'] ?? 'Valoración sin costo',
            $settings['trust_item_2'] ?? 'Atención por médico certificado',
            $settings['trust_item_3'] ?? 'Resultados naturales',
            $settings['trust_item_4'] ?? 'Seguimiento personalizado',
        ])->filter(fn ($text) => trim((string) $text) !== '')->values()
            ->map(fn ($text, $i) => ['value' => null, 'label' => $text, 'icon' => $dkTrustIcons[$i % 4]]);

    // Antes/después: solo pares completos cuyas dos imágenes existen en el disco público.
    $beforeAfter = $galleryItems->map(function ($item) use ($normalizeMediaPath, $publicDisk) {
        $before = $normalizeMediaPath($item->before_image_path);
        $after = $normalizeMediaPath($item->after_image_path);

        if (! $before || ! $after || ! $publicDisk->exists($before) || ! $publicDisk->exists($after)) {
            return null;
        }

        return [
            'before' => $publicDisk->url($before),
            'after' => $publicDisk->url($after),
            'title' => $item->title ?: ($item->service?->name ?? 'Resultado'),
            'label' => $item->service?->name,
        ];
    })->filter()->take(4)->values();

    $dkDataPolicyUrl = route('legal.data-policy');
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $clinicName }} | Medicina Estética Premium</title>
    <meta name="description" content="{{ $heroSubtitle }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body landing-body dk-has-mobilebar">
    <div class="ambient ambient-left"></div>
    <div class="ambient ambient-right"></div>

    <header class="topbar">
        <div class="container topbar-inner">
            <a href="#inicio" class="brand brand-lockup" aria-label="Ir al inicio de {{ $clinicName }}">
                @if ($clinicLogoUrl)
                    <span class="brand-logo-shell">
                        <img src="{{ $clinicLogoUrl }}" alt="Logo de {{ $clinicName }}" class="brand-logo-mark">
                    </span>
                @else
                    <span class="brand-monogram">{{ $brandInitials ?: 'DK' }}</span>
                @endif
                <span class="brand-copy">
                    <strong>{{ $clinicName }}</strong>
                    <small>{{ $doctor?->name ?: 'Medicina estética facial y corporal' }}</small>
                </span>
            </a>
            <nav class="nav-links" aria-label="Navegación principal">
                <a href="{{ route('about') }}" data-nav-external="about">Quiénes somos</a>
                <a href="#servicios" class="is-active" data-nav-section="servicios">Servicios</a>
                <a href="#resultados" data-nav-section="resultados">Resultados</a>
                <a href="#contacto" data-nav-section="contacto">Contacto</a>
                @include('partials.dk-patient-nav-link')
            </nav>
            <div class="topbar-actions">
                <a href="{{ $whatsappUrl }}" class="btn btn-outline topbar-cta" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>{{ $topbarCtaLabel }}</a>
                <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="mobile-nav-panel" aria-label="Abrir menú de navegación">
                    <span class="nav-toggle-icon"><span></span><span></span><span></span></span>
                    <span class="nav-toggle-label">Menú</span>
                </button>
            </div>
        </div>
    </header>

    <div class="mobile-nav-overlay" hidden></div>
    <div class="mobile-nav-panel" id="mobile-nav-panel" hidden>
        <div class="mobile-nav-shell">
            <div class="mobile-nav-head">
                <div class="mobile-nav-brand">
                    <span>{{ $clinicName }}</span>
                    <small>Navegación</small>
                </div>
                <button type="button" class="mobile-nav-close" aria-label="Cerrar menú de navegación">×</button>
            </div>
            <nav class="mobile-nav-links" aria-label="Navegación móvil">
                <a href="#inicio" data-nav-section="inicio">Inicio</a>
                <a href="{{ route('about') }}" data-nav-external="about">Quiénes somos</a>
                <a href="#servicios" class="is-active" data-nav-section="servicios">Servicios</a>
                <a href="#resultados" data-nav-section="resultados">Resultados</a>
                <a href="#contacto" data-nav-section="contacto">Contacto</a>
                @include('partials.dk-patient-nav-link')
            </nav>
            <a href="{{ $whatsappUrl }}" class="btn btn-primary mobile-nav-cta" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>{{ $topbarCtaLabel }}</a>
        </div>
    </div>

    @if (session('success'))
        <div class="container landing-flash-wrap">
            <div class="flash-success landing-flash">{{ session('success') }}</div>
        </div>
    @endif

    <aside class="social-dock" aria-label="Redes sociales de {{ $clinicName }}">
        @foreach ($socialCards->take(4) as $card)
            <a href="{{ $card['url'] ?: '#redes' }}" @if($card['url']) target="_blank" rel="noopener noreferrer" @endif aria-label="{{ $card['label'] }}">
                {!! $socialIcons[$card['icon']] ?? '' !!}
            </a>
        @endforeach
    </aside>

    @include('partials.dk-contact-actions', ['clinicName' => $clinicName, 'bookingUrl' => $dkWhatsappBookingUrl, 'hasWhatsapp' => $hasWhatsappLink, 'phone' => $settings['phone'] ?? ''])

    <main id="inicio">
        <section class="dk-hero" aria-labelledby="dk-hero-title">
            <div class="container dk-hero-grid">
                <div class="dk-hero-copy">
                    <p class="kicker dk-hero-kicker">{{ $heroKicker }}</p>
                    <h1 id="dk-hero-title" class="dk-hero-title">{{ $heroTitle }}</h1>
                    <p class="dk-hero-subtitle">{{ $heroSubtitle }}</p>

                    <div class="dk-hero-actions">
                        <a href="#formulario" class="dk-btn-primary">{{ $heroPrimaryCta }}</a>
                        <a href="{{ $dkWhatsappBookingUrl }}" class="dk-hero-wa" {!! $dkWhatsappLinkAttrs !!}>
                            @if ($hasWhatsappLink)
                                <span class="dk-hero-wa-icon">{!! $socialIcons['whatsapp'] !!}</span>
                                Escríbenos por WhatsApp
                            @else
                                Déjanos tus datos y te llamamos
                            @endif
                        </a>
                    </div>

                    <a href="{{ route('about') }}" class="dk-doctor-chip">
                        <span class="dk-doctor-chip-avatar" aria-hidden="true">
                            @if ($doctorPhotoUrl)
                                <img src="{{ $doctorPhotoUrl }}" alt="" loading="lazy">
                            @else
                                {{ $brandInitials ?: 'DK' }}
                            @endif
                        </span>
                        <span class="dk-doctor-chip-copy">
                            <strong>{{ $doctor?->name ?: $clinicName }}</strong>
                            <small>{{ $doctor?->specialty ?: 'Medicina estética facial y corporal' }}</small>
                        </span>
                    </a>
                </div>

                <figure class="dk-hero-media">
                    @if ($doctorPhotoUrl)
                        <img src="{{ $doctorPhotoUrl }}" alt="Foto de {{ $doctor?->name ?: $clinicName }}" class="dk-hero-photo" fetchpriority="high">
                    @elseif ($heroBanners->first()['image'] ?? null)
                        <img src="{{ $heroBanners->first()['image'] }}" alt="{{ $clinicName }}" class="dk-hero-photo" fetchpriority="high" onerror="this.remove()">
                    @elseif ($clinicLogoUrl)
                        <div class="dk-hero-fallback"><img src="{{ $clinicLogoUrl }}" alt="Logo de {{ $clinicName }}"></div>
                    @else
                        <div class="dk-hero-fallback" aria-hidden="true"><span>{{ $brandInitials ?: 'DK' }}</span></div>
                    @endif
                </figure>
            </div>
        </section>

        @if ($dkTrustItems->isNotEmpty())
            <section class="dk-trust" aria-label="Por qué elegirnos">
                <div class="container">
                    <ul class="dk-trust-list">
                        @foreach ($dkTrustItems as $item)
                            <li class="dk-trust-item">
                                <span class="dk-trust-icon" aria-hidden="true">
                                    @switch($item['icon'])
                                        @case('shield')
                                            <svg viewBox="0 0 24 24" focusable="false"><path d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3Zm-1.2 13.6-3.5-3.5 1.4-1.4 2.1 2.1 4.9-4.9 1.4 1.4-6.3 6.3Z"/></svg>
                                            @break
                                        @case('doctor')
                                            <svg viewBox="0 0 24 24" focusable="false"><path d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5Zm4.5 2.5h-1.5V15h-1.5v1.5H12V18h1.5v1.5H15V18h1.5v-1.5Z"/></svg>
                                            @break
                                        @case('spark')
                                            <svg viewBox="0 0 24 24" focusable="false"><path d="M12 2l1.9 5.6L19.5 9.5l-5.6 1.9L12 17l-1.9-5.6L4.5 9.5l5.6-1.9L12 2Zm6.5 11 .9 2.6 2.6.9-2.6.9-.9 2.6-.9-2.6-2.6-.9 2.6-.9.9-2.6Z"/></svg>
                                            @break
                                        @default
                                            <svg viewBox="0 0 24 24" focusable="false"><path d="M12 21s-7.5-4.6-9.4-9.3C1.3 8.4 3.3 5 6.7 5c2 0 3.3 1 4.3 2.4h2C14 6 15.3 5 17.3 5c3.4 0 5.4 3.4 4.1 6.7C19.5 16.4 12 21 12 21Z"/></svg>
                                    @endswitch
                                </span>
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

        <section id="servicios" class="section section-soft section-beige">
            <div class="container reveal reveal-2">
                <div class="services-head">
                    <div>
                        <p class="kicker">{{ $servicesKicker }}</p>
                        <h2 class="section-title">{{ $servicesTitle }}</h2>
                    </div>
                    <p class="section-intro service-intro">{{ $servicesLead }}</p>
                </div>

                <nav class="dk-svc-shortcuts" aria-label="Accesos rápidos a tratamientos">
                    <a href="{{ route('services.index', ['categoria' => 'facial']) }}#cat-facial">Tratamientos faciales <span aria-hidden="true">→</span></a>
                    <a href="{{ route('services.index', ['categoria' => 'corporal']) }}#cat-corporal">Tratamientos corporales <span aria-hidden="true">→</span></a>
                    <a href="{{ route('services.index') }}">Ver catálogo completo <span aria-hidden="true">→</span></a>
                </nav>

                @if ($serviceCategories->count() > 1)
                    <div class="services-tabs" role="tablist" aria-label="Categorías de tratamientos">
                        @foreach ($serviceCategories as $i => $category)
                            <button
                                class="services-tab {{ $i === 0 ? 'is-active' : '' }}"
                                role="tab"
                                aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                                aria-controls="cat-{{ $category->id }}"
                                data-tab="cat-{{ $category->id }}"
                                type="button"
                            >{{ $category->name }}</button>
                        @endforeach
                    </div>
                @endif

                @forelse ($serviceCategories as $i => $category)
                    <div
                        class="services-panel {{ $i === 0 ? 'is-active' : '' }}"
                        id="cat-{{ $category->id }}"
                        role="tabpanel"
                    >
                        <div class="svc-carousel-wrap">
                            <div class="svc-carousel" aria-label="Tratamientos de {{ $category->name }}">
                                @foreach ($category->services as $service)
                                    @php
                                        $serviceImagePath = $normalizeMediaPath($service->image_path);
                                    @endphp
                                    <article class="svc-card">
                                        @if ($serviceImagePath && $publicDisk->exists($serviceImagePath))
                                            <figure class="svc-card-media">
                                                <img src="{{ $publicDisk->url($serviceImagePath) }}" alt="{{ $service->name }}" loading="lazy">
                                            </figure>
                                        @else
                                            <div class="svc-card-media svc-card-media-empty">
                                                <span>{{ mb_strtoupper(mb_substr($service->name, 0, 2)) }}</span>
                                            </div>
                                        @endif
                                        <div class="svc-card-copy">
                                            <h3>{{ $service->name }}</h3>
                                            <p>{{ \Illuminate\Support\Str::limit($service->short_description ?: 'Tratamiento personalizado con enfoque médico y resultados naturales.', 90) }}</p>
                                            <div class="svc-card-links dk-svc-actions">
                                                <a href="{{ route('services.show', $service) }}" class="svc-card-link">Ver detalle</a>
                                                <a href="{{ $hasWhatsappLink ? $service->whatsapp_url : '#formulario' }}" class="dk-btn-agendar" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif aria-label="Agendar {{ $service->name }}">Agendar</a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach

                                {{-- CTA card at the end --}}
                                <article class="svc-card svc-card-cta">
                                    <div class="svc-card-cta-inner">
                                        <strong>¿No encuentras lo que buscas?</strong>
                                        <p>Agenda una valoracion y diseñamos un protocolo a tu medida.</p>
                                        <a href="{{ $whatsappUrl }}" class="btn btn-primary" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>WhatsApp</a>
                                        <a href="{{ route('services.index') }}" class="btn btn-ghost">Ver catalogo completo</a>
                                    </div>
                                </article>
                            </div>
                            <div class="svc-carousel-hint" aria-hidden="true">Desliza para ver todos los tratamientos →</div>
                        </div>
                    </div>
                @empty
                    <div class="services-panel is-active">
                        <div class="svc-carousel-wrap">
                            <div class="svc-carousel" aria-label="Tratamientos">
                                @foreach ($featuredServices as $service)
                                    @php
                                        $serviceImagePath = $normalizeMediaPath($service->image_path);
                                    @endphp
                                    <article class="svc-card">
                                        @if ($serviceImagePath && $publicDisk->exists($serviceImagePath))
                                            <figure class="svc-card-media">
                                                <img src="{{ $publicDisk->url($serviceImagePath) }}" alt="{{ $service->name }}" loading="lazy">
                                            </figure>
                                        @else
                                            <div class="svc-card-media svc-card-media-empty">
                                                <span>{{ mb_strtoupper(mb_substr($service->name, 0, 2)) }}</span>
                                            </div>
                                        @endif
                                        <div class="svc-card-copy">
                                            <h3>{{ $service->name }}</h3>
                                            <p>{{ \Illuminate\Support\Str::limit($service->short_description ?: 'Tratamiento personalizado con enfoque médico y resultados naturales.', 90) }}</p>
                                            <div class="svc-card-links dk-svc-actions">
                                                <a href="{{ route('services.show', $service) }}" class="svc-card-link">Ver detalle</a>
                                                <a href="{{ $hasWhatsappLink ? $service->whatsapp_url : '#formulario' }}" class="dk-btn-agendar" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif aria-label="Agendar {{ $service->name }}">Agendar</a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                                <article class="svc-card svc-card-cta">
                                    <div class="svc-card-cta-inner">
                                        <strong>¿No encuentras lo que buscas?</strong>
                                        <p>Agenda una valoración y diseñamos un protocolo a tu medida.</p>
                                        <a href="{{ $whatsappUrl }}" class="btn btn-primary" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>WhatsApp</a>
                                        <a href="{{ route('services.index') }}" class="btn btn-ghost">Ver catalogo completo</a>
                                    </div>
                                </article>
                            </div>
                            <div class="svc-carousel-hint" aria-hidden="true">Desliza para ver todos los tratamientos →</div>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        @if ($beforeAfter->isNotEmpty())
            <section id="antes-despues" class="section dk-ba" aria-labelledby="dk-ba-title">
                <div class="container">
                    <p class="kicker">{{ $settings['before_after_kicker'] ?? 'Antes y después' }}</p>
                    <h2 id="dk-ba-title" class="section-title">{{ $settings['before_after_title'] ?? 'Cambios reales, resultados naturales' }}</h2>

                    <div class="dk-ba-grid">
                        @foreach ($beforeAfter as $pair)
                            <figure class="dk-ba-item">
                                <div class="dk-ba-slider" style="--dk-pos: 50%">
                                    <img src="{{ $pair['after'] }}" alt="Después: {{ $pair['title'] }}" class="dk-ba-img dk-ba-after" loading="lazy">
                                    <img src="{{ $pair['before'] }}" alt="Antes: {{ $pair['title'] }}" class="dk-ba-img dk-ba-before" loading="lazy">
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

                <script>
                    document.querySelectorAll('.dk-ba-range').forEach((range) => {
                        const slider = range.closest('.dk-ba-slider');
                        const update = () => {
                            slider.style.setProperty('--dk-pos', range.value + '%');
                            range.setAttribute('aria-valuetext', range.value + '% de la imagen de antes visible');
                        };
                        range.addEventListener('input', update);
                        update();
                    });
                </script>
            </section>
        @endif

        <section id="resultados" class="section section-white">
            <div class="container reveal reveal-2">
                <p class="kicker">{{ $testimonialsKicker }}</p>
                <h2 class="section-title">{{ $testimonialsTitle }}</h2>
                <div class="testimonials-overview">
                    <div class="testimonials-pill">
                        <strong>Pacientes reales</strong>
                        <span>Atención médica personalizada</span>
                    </div>
                    <div class="testimonials-pill">
                        <strong>Valoración 4.9/5</strong>
                        <span>Resultados naturales y progresivos</span>
                    </div>
                </div>
            </div>

            @if ($videoResultsPreview->isNotEmpty())
                <div class="container results-video-band reveal reveal-2">
                    <div class="results-video-carousel" id="videoResultsCarousel" aria-label="Videos de resultados de {{ $clinicName }}">
                        @foreach ($videoResultsPreview as $video)
                            <article class="results-video-card">
                                <div class="results-video-media">
                                    @if ($video['is_iframe'])
                                        <iframe
                                            src="{{ $video['url'] }}"
                                            title="{{ $video['title'] }}"
                                            loading="lazy"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowfullscreen
                                        ></iframe>
                                    @else
                                        <video controls preload="metadata" playsinline poster="{{ $video['poster'] ?? '' }}">
                                            <source src="{{ $video['url'] }}">
                                        </video>
                                        <button type="button" class="results-video-play-overlay" aria-label="Reproducir video {{ $video['title'] }}">
                                            <span class="results-video-play-badge" aria-hidden="true"></span>
                                            <span class="results-video-play-copy">Reproducir</span>
                                        </button>
                                        <div class="results-video-player-controls" aria-hidden="true">
                                            <button type="button" class="results-video-toggle" aria-label="Alternar reproducción"></button>
                                            <button type="button" class="results-video-fullscreen" aria-label="Pantalla completa"></button>
                                        </div>
                                    @endif
                                </div>
                                <div class="results-video-meta">
                                    <strong>{{ $video['title'] }}</strong>
                                    <span>{{ $video['label'] }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="result-visual-dots" id="videoResultsDots" aria-hidden="true"></div>
                </div>
            @endif

            @if ($galleryPreview->isNotEmpty())
                <div class="container result-visual-band reveal reveal-2">
                    <article class="result-visual-copy">
                        <p class="kicker">Galería visual</p>
                        <h3>{{ $resultsTitle }}</h3>
                        <p>{{ $resultsLead }}</p>
                        <span class="result-visual-hint">Desliza para ver más casos</span>
                    </article>

                    <div class="result-visual-grid" id="resultCarousel" aria-label="Galería visual de {{ $clinicName }}">
                        @foreach ($galleryPreview as $item)
                            <figure class="result-visual-card">
                                <img src="{{ $item['url'] }}" alt="{{ $item['title'] }}" loading="lazy">
                                <figcaption>
                                    <strong>{{ $item['title'] }}</strong>
                                    <span>{{ $item['label'] }}</span>
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                    <div class="result-visual-dots" id="resultDots" aria-hidden="true"></div>
                </div>
            @endif

            <div class="container testimonials-grid reveal reveal-3">
                @forelse ($testimonialsToShow as $testimonial)
                    <blockquote class="quote-card">
                        <div class="quote-meta">
                            <span class="quote-avatar">{{ mb_strtoupper(mb_substr($testimonial->patient_name ?: 'PR', 0, 2)) }}</span>
                            <div class="quote-author">
                                <strong>{{ $testimonial->patient_name }}</strong>
                                <small>{{ $testimonial->reviewed_at ? 'Paciente ' . $testimonial->reviewed_at->format('Y') : 'Paciente verificado' }}</small>
                            </div>
                        </div>
                        <div class="testimonial-rating">{{ str_repeat('★', max(1, (int) $testimonial->rating)) }}</div>
                        <p>"{{ $testimonial->content }}"</p>
                        <cite>Experiencia en {{ $clinicName }}</cite>
                    </blockquote>
                @empty
                    <blockquote class="quote-card">
                        <div class="quote-meta">
                            <span class="quote-avatar">PR</span>
                            <div class="quote-author">
                                <strong>Paciente Rejuvenezk</strong>
                                <small>Paciente verificado</small>
                            </div>
                        </div>
                        <div class="testimonial-rating">★★★★★</div>
                        <p>"Me encantó porque respetaron mis facciones y me explicaron cada paso. Me veo fresca, no cambiada."</p>
                        <cite>Experiencia en {{ $clinicName }}</cite>
                    </blockquote>
                    <blockquote class="quote-card">
                        <div class="quote-meta">
                            <span class="quote-avatar">PR</span>
                            <div class="quote-author">
                                <strong>Paciente Rejuvenezk</strong>
                                <small>Paciente verificado</small>
                            </div>
                        </div>
                        <div class="testimonial-rating">★★★★★</div>
                        <p>"El trato es impecable y los resultados se notan desde la primera sesion. Volvere sin duda."</p>
                        <cite>Experiencia en {{ $clinicName }}</cite>
                    </blockquote>
                    <blockquote class="quote-card">
                        <div class="quote-meta">
                            <span class="quote-avatar">PR</span>
                            <div class="quote-author">
                                <strong>Paciente Rejuvenezk</strong>
                                <small>Paciente verificado</small>
                            </div>
                        </div>
                        <div class="testimonial-rating">★★★★★</div>
                        <p>"Senti confianza total por el enfoque medico. El plan fue claro y super personalizado."</p>
                        <cite>Experiencia en {{ $clinicName }}</cite>
                    </blockquote>
                @endforelse
            </div>
        </section>

        <!-- Banners promocionales (carrusel; antes estaba arriba) -->
        <section class="banners-carousel-wrap section dk-promos">
            <div class="banners-carousel" role="region" aria-label="Banners promocionales">
                <div class="banners-carousel-inner">
                    @foreach ($heroBanners as $index => $banner)
                        <article class="banner-slide tone-{{ $index % 3 }}" data-slide="{{ $index }}">
                            @if (!empty($banner['image']))
                                <img src="{{ $banner['image'] }}" alt="{{ $banner['title'] }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}" onerror="this.remove()">
                            @endif

                            <div class="banner-slide-overlay">
                                <span>{{ $banner['kicker'] }}</span>
                                <strong>{{ $banner['title'] }}</strong>
                                <p>{{ $banner['text'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($heroBanners->count() > 1)
                    <!-- Controls -->
                    <button class="banner-control banner-prev" aria-label="Banner anterior">
                        <span aria-hidden="true">‹</span>
                    </button>
                    <button class="banner-control banner-next" aria-label="Banner siguiente">
                        <span aria-hidden="true">›</span>
                    </button>

                    <!-- Indicators -->
                    <div class="banner-indicators">
                        @foreach ($heroBanners as $index => $banner)
                            <button class="banner-indicator{{ $index === 0 ? ' active' : '' }}" data-index="{{ $index }}" aria-label="Ir a banner {{ $index + 1 }}" @if($index === 0) aria-current="true" @endif></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section id="contacto" class="section section-white cta-wrap">
            <div class="container contact-layout reveal reveal-2">
                <div class="cta">
                    <div>
                        <p class="kicker">{{ $ctaKicker }}</p>
                        <h2>{{ $ctaTitle }}</h2>
                        <p>{{ $settings['address'] ?: $ctaBody }}</p>
                        <p class="cta-note">{{ $ctaNote }}</p>
                    </div>
                    <div class="cta-actions">
                        <a class="btn btn-primary" href="{{ $dkWhatsappBookingUrl }}" @if($hasWhatsappLink) target="_blank" rel="noopener noreferrer" @endif>{{ $ctaWhatsappLabel }}</a>
                        <a class="btn btn-ghost" href="{{ !empty($settings['email']) ? 'mailto:' . $settings['email'] : '#formulario' }}">{{ $settings['email'] ?: $ctaEmailLabel }}</a>
                    </div>
                </div>

                <form id="formulario" method="POST" action="{{ route('leads.store') }}" class="contact-form landing-contact-form dk-lead-form reveal reveal-3">
                    @csrf
                    <input type="hidden" name="source" value="website">

                    <p class="dk-form-title">Solicita tu valoración</p>

                    @if ($errors->any())
                        <script>document.addEventListener('DOMContentLoaded', () => document.getElementById('formulario')?.scrollIntoView({ block: 'center' }));</script>
                        <div class="dk-form-errors" role="alert">
                            <p>Revisa los datos marcados:</p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="dk-field">
                        <label for="lead-name">Nombre</label>
                        <input id="lead-name" type="text" name="name" autocomplete="name" value="{{ old('name') }}" required maxlength="200"
                            @error('name') aria-invalid="true" aria-describedby="lead-name-error" @enderror>
                        @error('name')<p id="lead-name-error" class="dk-field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="dk-field">
                        <label for="lead-phone">WhatsApp o teléfono</label>
                        <input id="lead-phone" type="tel" name="phone" autocomplete="tel" inputmode="tel" value="{{ old('phone') }}" required maxlength="30"
                            @error('phone') aria-invalid="true" aria-describedby="lead-phone-error" @enderror>
                        @error('phone')<p id="lead-phone-error" class="dk-field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="dk-field">
                        <label for="lead-service">Tratamiento de interés</label>
                        <select id="lead-service" name="service_id">
                            <option value="">Aún no lo sé / quiero asesoría</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="dk-btn-primary dk-btn-block">Solicitar valoración</button>

                    <p class="dk-form-legal">Al enviar aceptas nuestra <a href="{{ $dkDataPolicyUrl }}">política de tratamiento de datos</a>.</p>
                </form>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container footer-inner">
            <div class="footer-brand-lockup">
                @if ($clinicLogoUrl)
                    <span class="footer-logo-shell">
                        <img src="{{ $clinicLogoUrl }}" alt="Logo de {{ $clinicName }}" class="footer-logo-mark">
                    </span>
                @endif
                <div>
                    <p>{{ $clinicName }}</p>
                    <small>{{ $doctor?->name ?: 'Dirección médica estética' }}</small>
                </div>
            </div>
            @if ($socialBannerCards->isNotEmpty())
                <div class="footer-social" aria-label="Redes sociales">
                    @foreach ($socialBannerCards as $card)
                        <a href="{{ $card['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $card['label'] }}" class="footer-social-link">
                            {!! $socialIcons[$card['icon']] ?? '' !!}
                        </a>
                    @endforeach
                </div>
            @endif
            <div class="dk-footer-meta">
                <p>{{ $settings['phone'] ?: 'Atención comercial premium' }}</p>
                <a href="{{ route('legal.data-policy') }}" class="dk-footer-legal">Política de datos</a>
            </div>
        </div>
    </footer>
</body>
</html>
