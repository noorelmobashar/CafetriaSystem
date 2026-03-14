<?php

declare(strict_types=1);

return [
    'categories' => [
        ['name' => 'Hot Drinks'],
        ['name' => 'Cold Drinks'],
        ['name' => 'Bakery'],
    ],
    'products' => [
        ['name' => 'Tea', 'price' => 5.00, 'category' => 'Hot Drinks', 'image_path' => null, 'available' => true],
        ['name' => 'Coffee', 'price' => 6.00, 'category' => 'Hot Drinks', 'image_path' => null, 'available' => true],
        ['name' => 'Nescafe', 'price' => 8.00, 'category' => 'Hot Drinks', 'image_path' => null, 'available' => true],
        ['name' => 'Cola', 'price' => 10.00, 'category' => 'Cold Drinks', 'image_path' => null, 'available' => true],
        ['name' => 'Croissant', 'price' => 14.00, 'category' => 'Bakery', 'image_path' => null, 'available' => true],
    ],
    'users' => [
        ['name' => 'Admin User', 'email' => 'admin@company.com', 'password_hash' => 'Admin@123', 'role' => 'admin', 'profile_pic' => null],
        ['name' => 'Alaa Hassan', 'email' => 'employee@company.com', 'password_hash' => 'Employee@123', 'role' => 'customer', 'profile_pic' => null],
        ['name' => 'Mariam Adel', 'email' => 'mariam@company.com', 'password_hash' => 'Employee@123', 'role' => 'customer', 'profile_pic' => null],
        ['name' => 'Omar Samy', 'email' => 'omar@company.com', 'password_hash' => 'Employee@123', 'role' => 'customer', 'profile_pic' => null],
    ],
    'orders' => [
        [
            'user_email' => 'employee@company.com',
            'status' => 'processing',
            'room_snapshot' => '300',
            'notes' => 'SEED: 1 tea extra sugar',
            'items' => [
                ['product' => 'Tea', 'quantity' => 1],
            ],
        ],
        [
            'user_email' => 'mariam@company.com',
            'status' => 'out for delivery',
            'room_snapshot' => '400',
            'notes' => 'SEED: coffee x2',
            'items' => [
                ['product' => 'Coffee', 'quantity' => 2],
            ],
        ],
        [
            'user_email' => 'omar@company.com',
            'status' => 'done',
            'room_snapshot' => '200',
            'notes' => 'SEED: nescafe and cola',
            'items' => [
                ['product' => 'Nescafe', 'quantity' => 1],
                ['product' => 'Cola', 'quantity' => 1],
            ],
        ],
    ],
];
