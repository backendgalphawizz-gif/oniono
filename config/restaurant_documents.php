<?php

return [
    /*
    | Restaurant business documents (India food delivery).
    | Keys are stored in restaurant_documents.type
    |
    | required_for_registration — must upload at signup step 2
    | required_for_approval — admin must have uploaded + verified before approve
    */
    'documents' => [
        'fssai_license' => [
            'label' => 'FSSAI license',
            'short_label' => 'FSSAI',
            'description' => 'Valid Food Safety and Standards Authority license (Form C / registration).',
            'max_kb' => 4096,
            'mimes' => 'pdf,jpg,jpeg,png',
            'required_for_registration' => false,
            'required_for_approval' => false,
        ],
        'gst_certificate' => [
            'label' => 'GST certificate',
            'short_label' => 'GST',
            'description' => 'GST registration certificate for the business.',
            'max_kb' => 4096,
            'mimes' => 'pdf,jpg,jpeg,png',
            'required_for_registration' => false,
            'required_for_approval' => false,
        ],
        'pan_card' => [
            'label' => 'PAN card',
            'short_label' => 'PAN',
            'description' => 'Business or proprietor PAN (clear scan).',
            'max_kb' => 4096,
            'mimes' => 'pdf,jpg,jpeg,png',
            'required_for_registration' => true,
            'required_for_approval' => true,
        ],
        'owner_id_proof' => [
            'label' => 'Owner ID proof',
            'short_label' => 'ID proof',
            'description' => 'Aadhaar, passport, or driving licence of the owner.',
            'max_kb' => 4096,
            'mimes' => 'pdf,jpg,jpeg,png',
            'required_for_registration' => false,
            'required_for_approval' => false,
        ],
        'shop_establishment' => [
            'label' => 'Shop / trade license',
            'short_label' => 'Trade license',
            'description' => 'Shop & Establishment, trade license, or municipal food establishment permit.',
            'max_kb' => 4096,
            'mimes' => 'pdf,jpg,jpeg,png',
            'required_for_registration' => false,
            'required_for_approval' => false,
        ],
        'bank_proof' => [
            'label' => 'Bank proof',
            'short_label' => 'Bank',
            'description' => 'Cancelled cheque or bank statement showing account name and IFSC.',
            'max_kb' => 4096,
            'mimes' => 'pdf,jpg,jpeg,png',
            'required_for_registration' => false,
            'required_for_approval' => false,
        ],
    ],
];
