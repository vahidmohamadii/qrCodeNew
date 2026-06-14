<?php

declare(strict_types=1);

namespace QrCatalog\Services;

use PDO;
use function QrCatalog\Support\now;

final class CompanyInfoService
{
    public function __construct(private PDO $db)
    {
    }

    /** @return array<string, mixed> */
    public function get(): array
    {
        $this->ensureExists();
        $statement = $this->db->query('SELECT * FROM company_infos WHERE id = 1 LIMIT 1');

        return $this->map($statement->fetch() ?: []);
    }

    /** @param array<string, mixed> $data */
    public function update(array $data): array
    {
        $this->ensureExists();
        $statement = $this->db->prepare(
            'UPDATE company_infos
             SET company_name = :company_name,
                 description = :description,
                 mission = :mission,
                 vision = :vision,
                 services = :services,
                 contact_information = :contact_information,
                 address = :address,
                 email = :email,
                 phone_number = :phone_number,
                 social_media_links = :social_media_links,
                 updated_at = :updated_at
             WHERE id = 1'
        );

        $statement->execute([
            'company_name' => (string) ($data['companyName'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'mission' => (string) ($data['mission'] ?? ''),
            'vision' => (string) ($data['vision'] ?? ''),
            'services' => (string) ($data['services'] ?? ''),
            'contact_information' => (string) ($data['contactInformation'] ?? ''),
            'address' => (string) ($data['address'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'phone_number' => (string) ($data['phoneNumber'] ?? ''),
            'social_media_links' => $data['socialMediaLinks'] ?? null,
            'updated_at' => now(),
        ]);

        return $this->get();
    }

    private function ensureExists(): void
    {
        $count = (int) $this->db->query('SELECT COUNT(*) FROM company_infos WHERE id = 1')->fetchColumn();
        if ($count > 0) {
            return;
        }

        $now = now();
        $statement = $this->db->prepare(
            'INSERT INTO company_infos
             (id, company_name, description, mission, vision, services, contact_information, address, email, phone_number, social_media_links, created_at, updated_at)
             VALUES
             (1, :company_name, :description, :mission, :vision, :services, :contact_information, :address, :email, :phone_number, :social_media_links, :created_at, :updated_at)'
        );
        $statement->execute([
            'company_name' => 'Namelenam',
            'description' => 'Catalog and QR code platform',
            'mission' => 'Help customers find product details quickly.',
            'vision' => 'Provide a polished catalog experience.',
            'services' => 'Product management, QR labels, and public product pages.',
            'contact_information' => 'Support team',
            'address' => 'Demo address',
            'email' => 'info@example.com',
            'phone_number' => '+1 000 000 0000',
            'social_media_links' => '',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /** @param array<string, mixed> $row */
    private function map(array $row): array
    {
        return [
            'companyName' => $row['company_name'] ?? '',
            'description' => $row['description'] ?? '',
            'mission' => $row['mission'] ?? '',
            'vision' => $row['vision'] ?? '',
            'services' => $row['services'] ?? '',
            'contactInformation' => $row['contact_information'] ?? '',
            'address' => $row['address'] ?? '',
            'email' => $row['email'] ?? '',
            'phoneNumber' => $row['phone_number'] ?? '',
            'socialMediaLinks' => $row['social_media_links'] ?? null,
        ];
    }
}
