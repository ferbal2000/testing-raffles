<?php

return [
    'index' => [
        'title' => 'Sorteos',
        'description' => 'Revisá los sorteos persistidos desde la administración.',
        'placeholder' => 'Sin definir',
        'columns' => [
            'id' => 'ID',
            'status' => 'Estado',
            'starts_at' => 'Inicio',
            'ends_at' => 'Fin',
            'created_at' => 'Creado',
        ],
        'empty' => [
            'title' => 'Todavía no hay sorteos.',
            'description' => 'Aún no hay sorteos cargados.',
        ],
    ],
];
