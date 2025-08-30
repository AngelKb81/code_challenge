<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // Computer Hardware
            [
                'name' => 'MacBook Pro 14" M3',
                'category' => 'Computer',
                'status' => 'available',
                'brand' => 'Apple',
                'description' => 'MacBook Pro 14 pollici con chip M3, 16GB RAM, 512GB SSD',
                'quantity' => 5,
                'serial_number' => 'MBP-001',
                'location' => 'Magazzino IT - Scaffale A1',
                'purchase_price' => 2499.00,
                'purchase_date' => '2024-01-15',
                'warranty_expiry' => '2026-01-15',
                'notes' => 'Configurazione standard per sviluppatori'
            ],
            [
                'name' => 'Dell OptiPlex 7090',
                'category' => 'Computer',
                'status' => 'available',
                'brand' => 'Dell',
                'description' => 'Desktop Dell OptiPlex con Intel i7, 32GB RAM, 1TB SSD',
                'quantity' => 8,
                'serial_number' => 'DELL-002',
                'location' => 'Magazzino IT - Scaffale A2',
                'purchase_price' => 1299.00,
                'purchase_date' => '2024-02-10',
                'warranty_expiry' => '2027-02-10',
                'notes' => 'Per postazioni di lavoro standard'
            ],
            [
                'name' => 'Monitor LG UltraWide 34"',
                'category' => 'Monitor',
                'status' => 'available',
                'brand' => 'LG',
                'description' => 'Monitor LG 34" UltraWide 3440x1440 USB-C',
                'quantity' => 15,
                'serial_number' => 'LG-MON-003',
                'location' => 'Magazzino IT - Scaffale B1',
                'purchase_price' => 699.00,
                'purchase_date' => '2024-03-05',
                'warranty_expiry' => '2027-03-05',
                'notes' => 'Ideale per programmatori e designer'
            ],

            // Office Equipment
            [
                'name' => 'Stampante Epson EcoTank',
                'category' => 'Stampante',
                'status' => 'available',
                'brand' => 'Epson',
                'description' => 'Stampante multifunzione Epson EcoTank L3260',
                'quantity' => 3,
                'serial_number' => 'EPS-001',
                'location' => 'Magazzino Ufficio - Scaffale C1',
                'purchase_price' => 299.00,
                'purchase_date' => '2024-01-20',
                'warranty_expiry' => '2026-01-20',
                'notes' => 'Stampa, scansione, copia wireless'
            ],
            [
                'name' => 'Proiettore Epson EB-FH06',
                'category' => 'Proiettore',
                'status' => 'available',
                'brand' => 'Epson',
                'description' => 'Proiettore Full HD 3500 lumens per sale riunioni',
                'quantity' => 2,
                'serial_number' => 'PROJ-001',
                'location' => 'Magazzino AV - Armadio A',
                'purchase_price' => 899.00,
                'purchase_date' => '2024-02-15',
                'warranty_expiry' => '2027-02-15',
                'notes' => 'Con cavi HDMI e supporto da soffitto'
            ],

            // Furniture
            [
                'name' => 'Scrivania Regolabile Elettrica',
                'category' => 'Arredamento',
                'status' => 'available',
                'brand' => 'IKEA',
                'description' => 'Scrivania sit-stand regolabile elettricamente 160x80cm',
                'quantity' => 10,
                'serial_number' => 'DESK-001',
                'location' => 'Deposito Mobili - Area A',
                'purchase_price' => 599.00,
                'purchase_date' => '2024-01-10',
                'warranty_expiry' => '2029-01-10',
                'notes' => 'Include controller e memoria posizioni'
            ],
            [
                'name' => 'Sedia Ergonomica Herman Miller',
                'category' => 'Arredamento',
                'status' => 'available',
                'brand' => 'Herman Miller',
                'description' => 'Sedia da ufficio ergonomica Aeron taglia B',
                'quantity' => 12,
                'serial_number' => 'CHAIR-001',
                'location' => 'Deposito Mobili - Area B',
                'purchase_price' => 1299.00,
                'purchase_date' => '2024-03-01',
                'warranty_expiry' => '2036-03-01',
                'notes' => 'Garanzia 12 anni, regolazioni complete'
            ],

            // Networking Equipment
            [
                'name' => 'Switch Cisco Catalyst 24 porte',
                'category' => 'Networking',
                'status' => 'reserved',
                'brand' => 'Cisco',
                'description' => 'Switch gestito 24 porte Gigabit con 4 porte SFP+',
                'quantity' => 2,
                'serial_number' => 'CSC-SW-001',
                'location' => 'Server Room - Rack 1',
                'purchase_price' => 2199.00,
                'purchase_date' => '2024-01-05',
                'warranty_expiry' => '2029-01-05',
                'notes' => 'Riservato per upgrade rete principale'
            ],
            [
                'name' => 'Access Point WiFi 6E',
                'category' => 'Networking',
                'status' => 'available',
                'brand' => 'Ubiquiti',
                'description' => 'Access Point UniFi WiFi 6E ad alte prestazioni',
                'quantity' => 6,
                'serial_number' => 'UBNT-AP-001',
                'location' => 'Magazzino IT - Scaffale C2',
                'purchase_price' => 449.00,
                'purchase_date' => '2024-02-20',
                'warranty_expiry' => '2026-02-20',
                'notes' => 'Include mounting kit e PoE injector'
            ],

            // Mobile Devices
            [
                'name' => 'iPad Pro 12.9" M2',
                'category' => 'Tablet',
                'status' => 'available',
                'brand' => 'Apple',
                'description' => 'iPad Pro 12.9 pollici con chip M2, 256GB WiFi',
                'quantity' => 4,
                'serial_number' => 'IPD-001',
                'location' => 'Cassaforte Mobile - Ripiano 1',
                'purchase_price' => 1399.00,
                'purchase_date' => '2024-03-10',
                'warranty_expiry' => '2026-03-10',
                'notes' => 'Con Apple Pencil 2 e Magic Keyboard'
            ],
            [
                'name' => 'iPhone 15 Pro',
                'category' => 'Telefono',
                'status' => 'maintenance',
                'brand' => 'Apple',
                'description' => 'iPhone 15 Pro 256GB colore Natural Titanium',
                'quantity' => 1,
                'serial_number' => 'IPH-001',
                'location' => 'Assistenza Tecnica',
                'purchase_price' => 1299.00,
                'purchase_date' => '2024-04-01',
                'warranty_expiry' => '2026-04-01',
                'notes' => 'In riparazione per problema al display'
            ],

            // Audio/Video Equipment
            [
                'name' => 'Microfono Shure SM7B',
                'category' => 'Audio',
                'status' => 'available',
                'brand' => 'Shure',
                'description' => 'Microfono dinamico professionale per broadcast',
                'quantity' => 3,
                'serial_number' => 'SHR-MIC-001',
                'location' => 'Studio Recording - Armadio A',
                'purchase_price' => 459.00,
                'purchase_date' => '2024-02-25',
                'warranty_expiry' => '2026-02-25',
                'notes' => 'Include asta e filtro antivento'
            ],
            [
                'name' => 'Telecamera Sony FX30',
                'category' => 'Video',
                'status' => 'not_available',
                'brand' => 'Sony',
                'description' => 'Telecamera cinema Sony FX30 4K Super 35mm',
                'quantity' => 1,
                'serial_number' => 'SNY-CAM-001',
                'location' => 'In prestito - Reparto Marketing',
                'purchase_price' => 2199.00,
                'purchase_date' => '2024-03-15',
                'warranty_expiry' => '2026-03-15',
                'notes' => 'Attualmente in uso per progetto video aziendale'
            ],

            // Office Supplies
            [
                'name' => 'Distruggi Documenti Fellowes',
                'category' => 'Ufficio',
                'status' => 'available',
                'brand' => 'Fellowes',
                'description' => 'Distruggi documenti a microtaglio P-5 per 12 fogli',
                'quantity' => 4,
                'serial_number' => 'FEL-SHR-001',
                'location' => 'Magazzino Ufficio - Scaffale D1',
                'purchase_price' => 189.00,
                'purchase_date' => '2024-01-25',
                'warranty_expiry' => '2026-01-25',
                'notes' => 'Livello di sicurezza P-5 per documenti riservati'
            ]
        ];

        foreach ($items as $itemData) {
            Item::create($itemData);
        }
    }
}
