<?php

return [
    'index' => [
        'title' => 'Sorteos',
        'description' => 'Revisá los sorteos persistidos desde la administración.',
        'placeholder' => 'Sin definir',
        'actions' => [
            'create' => 'Crear sorteo',
            'edit' => 'Editar',
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
];
