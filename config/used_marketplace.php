<?php

return [
    /*
    |--------------------------------------------------------------------------
    | K71 Used — Second-Hand Marketplace Configuration
    |--------------------------------------------------------------------------
    */

    'conditions' => [
        'new_unused' => [
            'label' => 'New / Unused (Open Box)',
            'short' => 'New / Unused',
            'description' => 'Unopened or opened without prior use; all original accessories intact.',
            'badge_bg' => 'bg-emerald-500/15 text-emerald-300 border-emerald-400/30',
            'light_badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'schema_url' => 'https://schema.org/NewCondition',
            'aliases' => ['brand new', 'open box', 'sealed', 'unused', 'new / unused', 'new_unused'],
        ],
        'like_new' => [
            'label' => 'Like New (Minimal Wear)',
            'short' => 'Like New',
            'description' => 'Gently handled with no functional flaws and negligible cosmetic signs.',
            'badge_bg' => 'bg-[#ECD2F3]/18 text-[#ECD2F3] border-[#ECD2F3]/35',
            'light_badge' => 'bg-[#F8EFFA] text-[#623562] border-[#623562]/20',
            'schema_url' => 'https://schema.org/UsedCondition',
            'aliases' => ['like new', 'mint', 'like new (mint)', 'like_new'],
        ],
        'good' => [
            'label' => 'Good (Normal Use)',
            'short' => 'Good',
            'description' => 'Works reliably; may show minor cosmetic marks from regular use.',
            'badge_bg' => 'bg-sky-500/15 text-sky-300 border-sky-400/30',
            'light_badge' => 'bg-sky-50 text-sky-800 border-sky-200',
            'schema_url' => 'https://schema.org/UsedCondition',
            'aliases' => ['good', 'good condition', 'gently used', 'normal use'],
        ],
        'fair' => [
            'label' => 'Fair (Visible Wear)',
            'short' => 'Fair',
            'description' => 'Functional with noticeable scuffs, wear, or disclosed minor defects.',
            'badge_bg' => 'bg-amber-500/15 text-amber-300 border-amber-400/30',
            'light_badge' => 'bg-amber-50 text-amber-800 border-amber-200',
            'schema_url' => 'https://schema.org/UsedCondition',
            'aliases' => ['fair', 'heavily used', 'well used', 'fair / used', 'visible wear'],
        ],
        'for_parts' => [
            'label' => 'For Parts / Needs Repair',
            'short' => 'For Parts',
            'description' => 'Sold as-is with known faults; suitable for repair, spare parts, or salvage.',
            'badge_bg' => 'bg-rose-500/15 text-rose-300 border-rose-400/30',
            'light_badge' => 'bg-rose-50 text-rose-800 border-rose-200',
            'schema_url' => 'https://schema.org/DamagedCondition',
            'aliases' => ['for parts', 'needs repair', 'for_parts', 'damaged', 'not working'],
        ],
    ],

    'statuses' => [
        'draft' => ['label' => 'Draft', 'public' => false],
        'active' => ['label' => 'Active', 'public' => true],
        'paused' => ['label' => 'Paused', 'public' => false],
        'reserved' => ['label' => 'Reserved', 'public' => true],
        'sold' => ['label' => 'Sold', 'public' => true],
        'removed' => ['label' => 'Removed', 'public' => false],
    ],

    'fulfillment_modes' => [
        'local_pickup' => [
            'label' => 'Buyer Pickup at Seller Locality',
            'description' => 'Inspect in person at a safe area agreed after enquiry.',
        ],
        'public_meetup' => [
            'label' => 'Public Place Meetup',
            'description' => 'Meet in a public location during daytime hours.',
        ],
        'seller_shipping' => [
            'label' => 'Seller-Arranged Courier / Parcel',
            'description' => 'Seller dispatches via buyer-agreed courier with stated charge.',
        ],
    ],

    'packaging_options' => [
        'original_box_and_accessories' => 'Original Box + All Accessories',
        'original_box_only' => 'Original Box Only',
        'item_with_accessories' => 'No Box (Item + Accessories)',
        'item_only' => 'Item Only (No Packaging)',
    ],

    'default_cities' => [
        'Kishangarh, Rajasthan',
        'Makrana, Rajasthan',
        'Ajmer, Rajasthan',
        'Jaipur, Rajasthan',
        'Jodhpur, Rajasthan',
        'Kota, Rajasthan',
        'Udaipur, Rajasthan',
        'Delhi NCR',
        'Mumbai, Maharashtra',
        'Ahmedabad, Gujarat',
        'Bengaluru, Karnataka',
        'Pune, Maharashtra',
        'Hyderabad, Telangana',
    ],

    'offer_expiry_days' => 7,

    'max_photos_per_listing' => 10,
    'max_photo_size_kb' => 8192, // 8 MB per photo

    'prohibited_keywords' => [
        'ak-47',
        'ak47',
        'handgun',
        'pistol',
        'revolver',
        'ammunition',
        'live ammo',
        'explosive',
        'detonator',
        'cocaine',
        'heroin',
        'methamphetamine',
        'mdma',
        'narcotic',
        'fake currency',
        'counterfeit note',
        'stolen phone',
        'iclock bypass',
        'icloud unlock service',
        'human organ',
        'endangered ivory',
        'prescription narcotic',
    ],

    'report_reasons' => [
        'scam_or_fraud' => 'Suspected scam or advance-fee request',
        'prohibited_item' => 'Prohibited or illegal item',
        'fake_or_counterfeit' => 'Counterfeit / replica sold as genuine',
        'misleading_condition' => 'Undisclosed damage / misleading photos',
        'duplicate_spam' => 'Duplicate or spam listing',
        'abusive_behavior' => 'Abusive messages or harassment',
        'wrong_category' => 'Listed in wrong category',
        'already_sold' => 'Item already sold / seller unresponsive',
    ],
];

