@php
    $clinicName = $settings['clinic_name'] ?? 'Doctor Kevin Rejuvenezk';
    $contactEmail = trim((string) ($settings['email'] ?? ''));
    $contactPhone = trim((string) ($settings['phone'] ?? ''));
    $contactAddress = trim((string) ($settings['address'] ?? ''));
@endphp

<x-layouts.site :settings="$settings" :title="'Política de tratamiento de datos | ' . $clinicName">
    <main class="dk-portal dk-portal-wide">
        <article class="dk-legal" aria-labelledby="dk-legal-title">
            <div class="dk-alert dk-alert-warn dk-legal-draft" role="note">
                <strong>Borrador: revisar con asesor legal antes de publicar.</strong>
                Este texto es una base de referencia y debe ajustarse a la realidad de la clínica.
            </div>

            <p class="kicker dk-portal-kicker">Documento legal · Versión {{ $policyVersion }}</p>
            <h1 id="dk-legal-title" class="dk-portal-title">Política de tratamiento de datos personales</h1>
            <p class="dk-portal-lead">
                En cumplimiento de la Ley Estatutaria 1581 de 2012, el Decreto 1377 de 2013 (compilado en el Decreto 1074 de 2015)
                y demás normas concordantes, {{ $clinicName }} informa cómo recolecta, usa, almacena y protege tus datos personales.
            </p>

            <h2>1. Responsable del tratamiento</h2>
            <ul>
                <li><strong>Nombre:</strong> {{ $clinicName }}</li>
                <li><strong>Dirección:</strong> {{ $contactAddress !== '' ? $contactAddress : '[completar dirección]' }}</li>
                <li><strong>Correo:</strong> {{ $contactEmail !== '' ? $contactEmail : '[completar correo de atención]' }}</li>
                <li><strong>Teléfono:</strong> {{ $contactPhone !== '' ? $contactPhone : '[completar teléfono]' }}</li>
                <li><strong>NIT / documento:</strong> [completar]</li>
            </ul>

            <h2>2. Datos que recolectamos</h2>
            <p>Datos de identificación y contacto (nombre, correo electrónico, número de celular), información sobre los tratamientos de interés o realizados, fechas de procedimientos y recordatorios de renovación, y los datos técnicos mínimos necesarios para la seguridad del sitio (por ejemplo, dirección IP).</p>
            <p><strong>Datos sensibles:</strong> la información relacionada con tu salud y con los procedimientos estéticos realizados es un dato sensible. No estás obligado(a) a autorizar su tratamiento ni a responder preguntas sobre datos sensibles; si lo haces, los trataremos con medidas reforzadas de seguridad y solo para las finalidades aquí descritas.</p>

            <h2>3. Finalidades</h2>
            <ul>
                <li>Agendar valoraciones y citas y responder tus solicitudes.</li>
                <li>Llevar el registro de tus procedimientos y mostrarte su historial en el portal de pacientes.</li>
                <li>Recordarte cuándo corresponde renovar un tratamiento.</li>
                <li>Solo si lo autorizas de forma expresa: enviarte promociones, novedades y campañas por WhatsApp o correo electrónico.</li>
                <li>Cumplir obligaciones legales y atender requerimientos de autoridades competentes.</li>
            </ul>

            <h2>4. Derechos del titular</h2>
            <p>Como titular de los datos tienes derecho a:</p>
            <ul>
                <li>Conocer, actualizar y rectificar tus datos personales.</li>
                <li>Solicitar prueba de la autorización otorgada.</li>
                <li>Ser informado(a) sobre el uso que se ha dado a tus datos.</li>
                <li>Presentar quejas ante la Superintendencia de Industria y Comercio (SIC) por infracciones a la ley, una vez agotado el trámite de consulta o reclamo ante el responsable.</li>
                <li>Revocar la autorización y/o solicitar la supresión de tus datos cuando no exista un deber legal o contractual de conservarlos.</li>
                <li>Acceder de forma gratuita a tus datos personales.</li>
            </ul>
            <p>Puedes retirar en cualquier momento tu autorización para recibir promociones desde <em>Mi cuenta → Mi perfil</em> o escribiéndonos.</p>

            <h2>5. Consultas y reclamos</h2>
            <p>Puedes ejercer tus derechos escribiendo a {{ $contactEmail !== '' ? $contactEmail : '[correo de atención]' }}, indicando tu nombre, tu documento, la descripción de la solicitud y un dato de contacto.</p>
            <ul>
                <li><strong>Consultas:</strong> se atenderán en un máximo de diez (10) días hábiles, prorrogables por cinco (5) días hábiles más, informando el motivo.</li>
                <li><strong>Reclamos</strong> (corrección, actualización, supresión o incumplimiento): se atenderán en un máximo de quince (15) días hábiles, prorrogables por ocho (8) días hábiles más, informando el motivo.</li>
            </ul>

            <h2>6. Seguridad y conservación</h2>
            <p>Aplicamos medidas técnicas, humanas y administrativas razonables para proteger tus datos frente a acceso no autorizado, pérdida o alteración. Las contraseñas del portal se guardan cifradas. Conservamos los datos durante el tiempo necesario para cumplir las finalidades descritas y las obligaciones legales aplicables, incluidas las relativas a la historia clínica cuando corresponda.</p>

            <h2>7. Transferencia y transmisión</h2>
            <p>Podemos usar proveedores tecnológicos (alojamiento web, mensajería, CRM) que tratan datos por cuenta nuestra bajo acuerdos de confidencialidad. [Completar con los encargados reales y, si aplica, la transferencia internacional.]</p>

            <h2>8. Vigencia y cambios</h2>
            <p>Esta política rige desde su publicación (versión {{ $policyVersion }}). Cualquier cambio sustancial se informará en este sitio antes de aplicarse.</p>
        </article>
    </main>
</x-layouts.site>
