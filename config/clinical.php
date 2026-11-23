<?php

/*
| Plantillas de indicaciones y cuidados posteriores para las fórmulas médicas.
| El médico elige una plantilla y la ajusta a cada paciente antes de emitir.
| IMPORTANTE: textos de referencia; deben ser revisados y aprobados por el médico tratante.
*/

return [
    /*
    | Panel médico (/medico), historia clínica y fórmulas en el portal del paciente.
    | Apagado por defecto: se activa SOLO tras la revisión médico-legal (ver DEPLOYMENT.md).
    | Con false, /medico y las fórmulas del portal no existen (404) ni aparecen en menús.
    */
    'medical_panel_enabled' => (bool) env('MEDICAL_PANEL_ENABLED', false),

    'care_templates' => [
        'toxina' => [
            'label' => 'Toxina botulínica',
            'text' => "• No acostarse ni inclinar la cabeza hacia abajo durante las primeras 4 horas.\n"
                ."• No masajear, frotar ni presionar las zonas tratadas durante 24 horas.\n"
                ."• Evitar ejercicio intenso, sauna, baño turco y exposición al calor por 24 horas.\n"
                ."• No consumir alcohol durante las primeras 24 horas.\n"
                ."• Puede realizar gestos faciales suaves las primeras horas.\n"
                ."• El efecto se aprecia entre el día 3 y el 14. Control a los 15 días.\n"
                .'• Consulte de inmediato si presenta dificultad para tragar, hablar o respirar, o caída marcada del párpado.',
        ],
        'acido_hialuronico' => [
            'label' => 'Relleno con ácido hialurónico',
            'text' => "• Aplicar compresas frías (envueltas en tela) 10 minutos cada hora durante el primer día.\n"
                ."• No masajear la zona salvo indicación médica.\n"
                ."• Evitar maquillaje en la zona durante 12 horas.\n"
                ."• Evitar ejercicio intenso, calor, sauna y alcohol por 48 horas.\n"
                ."• Es normal la inflamación leve, enrojecimiento o pequeños hematomas durante algunos días.\n"
                .'• Consulte de inmediato si nota palidez, coloración violácea o dolor intenso en la zona tratada.',
        ],
        'peeling' => [
            'label' => 'Peeling químico',
            'text' => "• Lavar el rostro solo con agua y limpiador suave; secar sin frotar.\n"
                ."• Aplicar la crema reparadora indicada varias veces al día.\n"
                ."• Protector solar FPS 50+ cada 3 horas, aun en interiores.\n"
                ."• No retirar la piel que se descama; dejar que caiga sola.\n"
                ."• Suspender retinoides, ácidos y exfoliantes durante 7 días.\n"
                .'• Evitar sol directo, piscina y calor durante 2 semanas.',
        ],
        'laser' => [
            'label' => 'Láser / luz pulsada',
            'text' => "• Aplicar frío local si hay sensación de calor o ardor.\n"
                ."• Usar la crema reparadora indicada y protector solar FPS 50+.\n"
                ."• Evitar exposición solar directa, cámaras de bronceo y calor por 2 semanas.\n"
                ."• No usar productos con alcohol, ácidos ni retinoides por 5 días.\n"
                .'• No rascar ni retirar costras.',
        ],
        'bioestimulador' => [
            'label' => 'Bioestimuladores de colágeno',
            'text' => "• Realizar el masaje indicado: 5 minutos, 5 veces al día, durante 5 días.\n"
                ."• Aplicar frío local las primeras horas si hay inflamación.\n"
                ."• Evitar ejercicio intenso, calor y alcohol por 48 horas.\n"
                .'• Los resultados son progresivos (6 a 12 semanas). Control según indicación.',
        ],
        'general' => [
            'label' => 'Cuidados generales posteriores',
            'text' => "• Mantener la zona tratada limpia.\n"
                ."• Protector solar FPS 50+ diario.\n"
                ."• Evitar ejercicio intenso, calor y alcohol durante 24 a 48 horas.\n"
                .'• Consulte si presenta dolor intenso, fiebre, secreción o cualquier síntoma que le preocupe.',
        ],
    ],

    // Vigencia por defecto de una fórmula (días).
    'prescription_valid_days' => 30,
];
