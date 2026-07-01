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
            'open_participation' => 'Abrir participación',
            'close_participation' => 'Cerrar participación',
        ],
        'registration_count' => '{0} 0 inscripciones|{1} 1 inscripción|[2,*] :count inscripciones',
        'flash' => [
            'participation_open_success' => 'La participación del sorteo se abrió.',
            'participation_close_success' => 'La participación del sorteo se cerró.',
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
        'actions' => [
            'back_to_index' => 'Volver al listado',
        ],
        'columns' => [
            'name' => 'Nombre',
            'email' => 'Email',
            'created_at' => 'Registrada',
            'linked_account' => 'Cuenta',
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
