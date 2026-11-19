<?php

return [
    /*
    | Required documents for delivery partners (India).
    | Keys are stored in delivery_documents.type
    */
    'required' => [
        'photo_id' => [
            'label' => 'Photo ID (Aadhaar)',
            'short_label' => 'ID proof',
            'description' => 'Government photo ID — Aadhaar front & back or passport.',
            'max_kb' => 4096,
            'mimes' => 'pdf,jpg,jpeg,png',
        ],
        'driving_license' => [
            'label' => 'Driving licence',
            'short_label' => 'DL',
            'description' => 'Valid two-wheeler (or applicable) driving licence.',
            'max_kb' => 4096,
            'mimes' => 'pdf,jpg,jpeg,png',
        ],
        'vehicle_rc' => [
            'label' => 'Vehicle RC',
            'short_label' => 'RC',
            'description' => 'Registration certificate (RC book) of the delivery vehicle.',
            'max_kb' => 4096,
            'mimes' => 'pdf,jpg,jpeg,png',
        ],
        'vehicle_insurance' => [
            'label' => 'Vehicle insurance',
            'description' => 'Valid insurance policy for the vehicle.',
            'max_kb' => 4096,
            'mimes' => 'pdf,jpg,jpeg,png',
        ],
        'pan_card' => [
            'label' => 'PAN card',
            'description' => 'Partner PAN card (clear scan).',
            'max_kb' => 4096,
            'mimes' => 'pdf,jpg,jpeg,png',
        ],
        'bank_proof' => [
            'label' => 'Bank proof',
            'description' => 'Cancelled cheque or bank statement with name and IFSC.',
            'max_kb' => 4096,
            'mimes' => 'pdf,jpg,jpeg,png',
        ],
    ],
];
