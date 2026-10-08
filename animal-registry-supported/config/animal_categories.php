<?php

return [
    'companion' => [
        'label' => 'Pet / Companion',
        'species' => ['Dog', 'Cat', 'Rabbit', 'Pet bird', 'Other (specify)'],
        'fields' => [
            ['key' => 'other_species_name', 'label' => 'Specify animal type', 'type' => 'text', 'required' => true, 'show_when' => 'other_species'],
            ['key' => 'color_markings', 'label' => 'Color / markings', 'type' => 'text'],
            ['key' => 'neutered_spayed', 'label' => 'Neutered / spayed', 'type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No', 'unknown' => 'Unknown']],
            ['key' => 'license_no', 'label' => 'License no.', 'type' => 'text'],
        ],
    ],
    'livestock' => [
        'label' => 'Farm Livestock',
        'species' => ['Cow', 'Carabao', 'Pig', 'Goat', 'Sheep', 'Horse', 'Other (specify)'],
        'fields' => [
            ['key' => 'other_species_name', 'label' => 'Specify animal type', 'type' => 'text', 'required' => true, 'show_when' => 'other_species'],
            ['key' => 'farm_herd_name', 'label' => 'Farm / herd name', 'type' => 'text'],
            ['key' => 'ear_tag_no', 'label' => 'Ear tag no.', 'type' => 'text'],
            ['key' => 'purpose', 'label' => 'Purpose', 'type' => 'select', 'options' => ['breeding' => 'Breeding', 'meat' => 'Meat', 'dairy' => 'Dairy', 'draft' => 'Draft']],
            ['key' => 'pen_barn', 'label' => 'Pen / barn', 'type' => 'text'],
            ['key' => 'pregnancy_status', 'label' => 'Pregnancy status', 'type' => 'select', 'show_when' => 'female', 'options' => ['not_pregnant' => 'Not pregnant', 'pregnant' => 'Pregnant', 'unknown' => 'Unknown']],
        ],
    ],
    'poultry' => [
        'label' => 'Poultry',
        'species' => ['Chicken (layer/broiler/native)', 'Duck', 'Turkey', 'Quail', 'Other (specify)'],
        'fields' => [
            ['key' => 'other_species_name', 'label' => 'Specify animal type', 'type' => 'text', 'required' => true, 'show_when' => 'other_species'],
            ['key' => 'flock_batch_id', 'label' => 'Flock / batch ID', 'type' => 'text'],
            ['key' => 'coop_house', 'label' => 'Coop / house', 'type' => 'text'],
            ['key' => 'purpose', 'label' => 'Purpose', 'type' => 'select', 'options' => ['egg' => 'Egg', 'meat' => 'Meat']],
            ['key' => 'date_placed', 'label' => 'Date placed', 'type' => 'date'],
        ],
    ],
    'gamefowl' => [
        'label' => 'Gamefowl (Pang-sabong)',
        'species' => ['Gamefowl', 'Other (specify)'],
        'fields' => [
            ['key' => 'other_species_name', 'label' => 'Specify animal type', 'type' => 'text', 'required' => true, 'show_when' => 'other_species'],
            ['key' => 'age_class', 'label' => 'Age class', 'type' => 'select', 'options' => ['stag' => 'Stag', 'cock' => 'Cock', 'pullet' => 'Pullet', 'hen' => 'Hen']],
            ['key' => 'bloodline', 'label' => 'Bloodline / strain', 'type' => 'text'],
            ['key' => 'leg_band_no', 'label' => 'Leg band no.', 'type' => 'text'],
            ['key' => 'wing_band_no', 'label' => 'Wing band no.', 'type' => 'text'],
            ['key' => 'color_plumage', 'label' => 'Color / plumage', 'type' => 'text'],
            ['key' => 'gamefarm_name', 'label' => 'Gamefarm name', 'type' => 'text'],
            ['key' => 'sire', 'label' => 'Sire', 'type' => 'text'],
            ['key' => 'dam', 'label' => 'Dam', 'type' => 'text'],
        ],
    ],
    'wildlife' => [
        'label' => 'Wildlife',
        'species' => ['Other wildlife', 'Other (specify)'],
        'fields' => [
            ['key' => 'wildlife_species', 'label' => 'Species (free text)', 'type' => 'text', 'required' => true],
            ['key' => 'capture_rescue_location', 'label' => 'Capture / rescue location', 'type' => 'text'],
            ['key' => 'rescue_date', 'label' => 'Rescue date', 'type' => 'date'],
            ['key' => 'release_status', 'label' => 'Release status', 'type' => 'select', 'options' => ['in_care' => 'In care', 'released' => 'Released', 'transferred' => 'Transferred', 'unknown' => 'Unknown']],
        ],
    ],
];
