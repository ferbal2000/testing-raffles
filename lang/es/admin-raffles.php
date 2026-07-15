<?php

return [
    'index' => [
        'title' => 'Sorteos',
        'description' => 'Revisá los sorteos persistidos desde la administración.',
        'placeholder' => 'Sin definir',
        'actions' => [
            'create' => 'Crear sorteo',
            'edit' => 'Editar',
            'registrations' => 'Inscripciones',
            'publish' => 'Publicar',
            'publish_confirm' => '¿Publicar este sorteo?',
            'open_participation' => 'Abrir participación',
            'close_participation' => 'Cerrar participación',
            'close' => 'Cerrar sorteo',
            'close_confirm' => '¿Cerrar este sorteo? Si la participación está activa, se cerrará. No se puede reabrir en esta versión.',
        ],
        'registration_count' => '{0} 0 inscripciones|{1} 1 inscripción|[2,*] :count inscripciones',
        'flash' => [
            'publish_success' => 'El sorteo se publicó.',
            'close_success' => 'El sorteo se cerró.',
            'participation_open_success' => 'La participación del sorteo se abrió.',
            'participation_close_success' => 'La participación del sorteo se cerró.',
        ],
        'errors' => [
            'close_unavailable' => 'Este sorteo ya no se puede cerrar.',
        ],
        'columns' => [
            'id' => 'ID',
            'status' => 'Estado',
            'starts_at' => 'Inicio',
            'ends_at' => 'Fin',
            'created_at' => 'Creado',
            'actions' => 'Acciones',
        ],
        'empty' => [
            'title' => 'Todavía no hay sorteos.',
            'description' => 'Aún no hay sorteos cargados.',
        ],
    ],
    'create' => [
        'title' => 'Crear sorteo',
        'description' => 'Cargá opcionalmente las fechas de disponibilidad iniciales del sorteo.',
        'fields' => [
            'starts_at' => [
                'label' => 'Inicio',
                'help' => 'Dejalo vacío si el sorteo no tiene fecha de inicio todavía.',
            ],
            'ends_at' => [
                'label' => 'Fin',
                'help' => 'Dejalo vacío si el sorteo no tiene fecha de cierre todavía.',
            ],
        ],
        'actions' => [
            'submit' => 'Crear sorteo',
            'cancel' => 'Volver al listado',
        ],
        'flash' => [
            'success' => 'El sorteo se creó en borrador.',
        ],
    ],
    'edit' => [
        'title' => 'Editar sorteo',
        'description' => 'Actualizá opcionalmente las fechas de disponibilidad del sorteo.',
        'fields' => [
            'starts_at' => [
                'label' => 'Inicio',
                'help' => 'Dejalo vacío si el sorteo no tiene fecha de inicio.',
            ],
            'ends_at' => [
                'label' => 'Fin',
                'help' => 'Dejalo vacío si el sorteo no tiene fecha de cierre.',
            ],
        ],
        'actions' => [
            'submit' => 'Guardar cambios',
            'cancel' => 'Volver al listado',
        ],
        'flash' => [
            'success' => 'El sorteo se actualizó.',
        ],
    ],
    'registrations' => [
        'title' => 'Inscripciones del sorteo #:id',
        'description' => 'Consultá las inscripciones ya registradas para este sorteo.',
        'summary_title' => 'Resumen de inscripciones',
        'summary_count' => '{0} 0 inscripciones registradas para este sorteo.|{1} 1 inscripción registrada para este sorteo.|[2,*] :count inscripciones registradas para este sorteo.',
        'summary' => [
            'active_label' => 'Activas',
            'active_count' => '{0} 0 activas|{1} 1 activa|[2,*] :count activas',
            'flagged_label' => 'Para revisión',
            'flagged_count' => '{0} 0 para revisión|{1} 1 para revisión|[2,*] :count para revisión',
            'cancelled_label' => 'Canceladas',
            'cancelled_count' => '{0} 0 canceladas|{1} 1 cancelada|[2,*] :count canceladas',
            'total_label' => 'Total registradas',
            'total_count' => '{0} 0 inscripciones registradas|{1} 1 inscripción registrada|[2,*] :count inscripciones registradas',
        ],
        'actions' => [
            'back_to_index' => 'Volver al listado',
            'flag' => 'Marcar para revisión',
            'flag_confirm' => '¿Marcar esta inscripción para revisión?',
            'cancel' => 'Cancelar inscripción',
            'cancel_confirm' => '¿Cancelar esta inscripción?',
            'restore' => 'Quitar de revisión',
            'restore_confirm' => '¿Quitar esta inscripción de revisión y restaurarla a activa?',
            'none_available' => 'Sin acciones disponibles',
        ],
        'flash' => [
            'flag_success' => 'La inscripción se marcó para revisión.',
            'cancel_success' => 'La inscripción se canceló.',
            'restore_success' => 'La inscripción se quitó de revisión y se restauró a activa.',
        ],
        'errors' => [
            'status_unavailable' => 'Esta acción ya no está disponible para esta inscripción.',
        ],
        'columns' => [
            'name' => 'Nombre',
            'email' => 'Email',
            'status' => 'Estado',
            'created_at' => 'Registrada',
            'linked_account' => 'Cuenta',
            'actions' => 'Acciones',
        ],
        'status' => [
            'active' => 'Activa',
            'flagged' => 'Para revisión',
            'cancelled' => 'Cancelada',
        ],
        'linked_account' => [
            'yes' => 'Cuenta vinculada',
            'no' => 'Sin cuenta vinculada',
        ],
        'empty' => [
            'title' => 'Todavía no hay inscripciones para este sorteo.',
            'description' => 'Cuando alguien se registre, la lista aparecerá acá.',
        ],
    ],
];
