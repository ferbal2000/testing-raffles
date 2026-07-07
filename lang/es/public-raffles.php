<?php

return [
    'title' => 'Detalle del sorteo',
    'status_label' => 'Estado',
    'availability_label' => 'Disponibilidad',
    'starts_at_label' => 'Inicio informado',
    'ends_at_label' => 'Cierre informado',
    'empty_date' => 'Sin fecha informada',
    'participation' => [
        'title' => 'Completá tus datos para participar',
        'description' => 'Dejanos tu nombre y tu correo para registrarte en este sorteo.',
        'closed' => 'La inscripción está cerrada por ahora.',
        'name_label' => 'Nombre',
        'email_label' => 'Correo electrónico',
        'submit' => 'Quiero participar',
        'success' => 'Tu participación quedó registrada.',
        'duplicate' => 'Ese correo ya estaba registrado para este sorteo.',
        'unavailable' => 'La participación no está disponible en este momento.',
        'errors' => 'Revisá los datos del formulario e intentá de nuevo.',
        'registration_count' => '{0} Todavía no hay personas registradas en este sorteo.|{1} 1 persona ya está registrada en este sorteo.|[2,*] :count personas ya están registradas en este sorteo.',
    ],
    'status' => [
        'published' => 'Sorteo publicado',
        'unknown' => 'Estado no disponible',
    ],
    'availability' => [
        'open' => 'Participación disponible',
        'closed' => 'Participación no disponible',
    ],
];
