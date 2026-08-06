<?php

namespace Database\Seeders;

use App\Models\ServiceFieldDefinition;
use App\Models\ServiceType;
use App\Models\ServiceTypeVariant;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'code' => 'SVC-01',
                'name' => 'Kendala jaringan atau konektivitas',
                'ticket_class' => 'INC',
                'sort_order' => 1,
                'fields' => [
                    $this->field('incident_type', 'Jenis gangguan', 'select', true, 1, 'Pilih gangguan yang paling sesuai.', options: [
                        ['value' => 'no_connection', 'label' => 'Tidak ada koneksi'],
                        ['value' => 'slow_connection', 'label' => 'Koneksi lambat'],
                        ['value' => 'intermittent', 'label' => 'Koneksi putus-nyambung'],
                        ['value' => 'service_access', 'label' => 'Tidak dapat mengakses layanan tertentu'],
                        ['value' => 'wifi', 'label' => 'Masalah Wi-Fi'],
                        ['value' => 'vpn', 'label' => 'Masalah VPN'],
                        ['value' => 'other', 'label' => 'Lainnya'],
                    ]),
                    $this->field('connection_medium', 'Media koneksi', 'select', true, 2, options: [
                        ['value' => 'wifi', 'label' => 'Wi-Fi'],
                        ['value' => 'lan', 'label' => 'LAN'],
                        ['value' => 'vpn', 'label' => 'VPN'],
                        ['value' => 'internet', 'label' => 'Internet'],
                        ['value' => 'other', 'label' => 'Lainnya'],
                    ]),
                    $this->field('affected_scope', 'Cakupan pengguna terdampak', 'select', true, 3, options: [
                        ['value' => 'self', 'label' => 'Saya sendiri'],
                        ['value' => 'room', 'label' => 'Satu ruangan'],
                        ['value' => 'floor', 'label' => 'Satu lantai'],
                        ['value' => 'building', 'label' => 'Satu gedung'],
                        ['value' => 'multiple', 'label' => 'Beberapa pengguna/area'],
                    ]),
                    $this->field('started_at', 'Mulai mengalami gangguan', 'datetime', true, 4),
                    $this->field('error_message', 'Pesan error', 'textarea', false, 5),
                    $this->field('attempted_steps', 'Langkah yang sudah dicoba', 'textarea', false, 6),
                ],
            ],
            [
                'code' => 'SVC-02',
                'name' => 'Permintaan tarik data',
                'ticket_class' => 'REQ',
                'sort_order' => 2,
                'fields' => [
                    $this->field('data_name', 'Nama data atau laporan', 'text', true, 1),
                    $this->field('purpose', 'Tujuan penggunaan data', 'textarea', true, 2),
                    $this->field('period_start', 'Mulai periode data', 'date', true, 3),
                    $this->field('period_end', 'Akhir periode data', 'date', true, 4),
                    $this->field('requested_columns', 'Kolom atau informasi yang diminta', 'textarea', true, 5),
                    $this->field('output_format', 'Format hasil yang diharapkan', 'select', true, 6, options: [
                        ['value' => 'xlsx', 'label' => 'Excel'],
                        ['value' => 'csv', 'label' => 'CSV'],
                        ['value' => 'pdf', 'label' => 'PDF'],
                        ['value' => 'other', 'label' => 'Format lain'],
                    ]),
                    $this->field('filter_criteria', 'Filter atau kriteria data', 'textarea', false, 7),
                ],
            ],
            [
                'code' => 'SVC-03',
                'name' => 'Perubahan data yang tidak dapat dilakukan melalui aplikasi',
                'ticket_class' => 'CHG',
                'sort_order' => 3,
                'fields' => [
                    $this->field('change_target', 'Target sistem atau data', 'text', true, 1),
                    $this->field('change_type', 'Jenis perubahan', 'select', true, 2, options: [
                        ['value' => 'data_correction', 'label' => 'Koreksi data'],
                        ['value' => 'mass_update', 'label' => 'Pembaruan massal'],
                        ['value' => 'migration', 'label' => 'Migrasi data'],
                        ['value' => 'other', 'label' => 'Lainnya'],
                    ]),
                    $this->field('current_state', 'Kondisi data saat ini', 'textarea', true, 3),
                    $this->field('desired_state', 'Kondisi data yang diharapkan', 'textarea', true, 4),
                    $this->field('business_reason', 'Alasan bisnis', 'textarea', true, 5),
                    $this->field('verification_criteria', 'Kriteria verifikasi hasil', 'textarea', true, 6),
                    $this->field('impact_estimate', 'Perkiraan dampak', 'select', false, 7, options: [
                        ['value' => 'low', 'label' => 'Rendah'],
                        ['value' => 'medium', 'label' => 'Sedang'],
                        ['value' => 'high', 'label' => 'Tinggi'],
                        ['value' => 'unknown', 'label' => 'Belum diketahui'],
                    ]),
                    $this->field('requested_execution_window', 'Waktu pelaksanaan yang diharapkan', 'datetime', false, 8),
                ],
            ],
            [
                'code' => 'SVC-04',
                'name' => 'Perubahan pada aplikasi',
                'ticket_class' => 'CHG',
                'sort_order' => 4,
                'fields' => [
                    $this->field('application_name', 'Nama aplikasi', 'text', true, 1),
                    $this->field('module_or_feature', 'Modul atau fitur', 'text', true, 2),
                    $this->field('change_type', 'Jenis perubahan', 'select', true, 3, options: [
                        ['value' => 'bug_fix', 'label' => 'Perbaikan masalah'],
                        ['value' => 'configuration', 'label' => 'Perubahan konfigurasi'],
                        ['value' => 'enhancement', 'label' => 'Penyempurnaan fitur'],
                        ['value' => 'integration', 'label' => 'Integrasi'],
                        ['value' => 'other', 'label' => 'Lainnya'],
                    ]),
                    $this->field('current_behavior', 'Perilaku saat ini', 'textarea', true, 4),
                    $this->field('desired_behavior', 'Perilaku yang diharapkan', 'textarea', true, 5),
                    $this->field('business_impact', 'Dampak bisnis', 'textarea', true, 6),
                    $this->field('acceptance_criteria', 'Kriteria penerimaan', 'textarea', true, 7),
                    $this->field('reproduction_steps', 'Langkah reproduksi masalah', 'textarea', false, 8),
                ],
            ],
            [
                'code' => 'SVC-05',
                'name' => 'Permintaan atau perbaikan hardware',
                'ticket_class' => null,
                'sort_order' => 5,
                'fields' => [
                    $this->field('request_subtype', 'Subjenis layanan', 'select', true, 1, 'Subjenis menentukan kelas nomor tiket.', options: [
                        ['value' => 'repair', 'label' => 'Perbaikan'],
                        ['value' => 'request', 'label' => 'Permintaan'],
                    ]),
                    $this->field('hardware_type', 'Jenis hardware', 'select', true, 2, options: [
                        ['value' => 'laptop', 'label' => 'Laptop'],
                        ['value' => 'desktop', 'label' => 'Komputer desktop'],
                        ['value' => 'monitor', 'label' => 'Monitor'],
                        ['value' => 'printer', 'label' => 'Printer'],
                        ['value' => 'network_device', 'label' => 'Perangkat jaringan'],
                        ['value' => 'accessory', 'label' => 'Aksesori/periferal'],
                        ['value' => 'other', 'label' => 'Lainnya'],
                    ]),
                    $this->field('symptom_or_need', 'Gejala kerusakan atau kebutuhan', 'textarea', true, 3),
                    $this->field('asset_tag', 'Nomor aset', 'text', false, 4, 'Isi jika perangkat sudah memiliki nomor aset.'),
                    $this->field('quantity', 'Jumlah unit', 'number', false, 5, 'Isi untuk permintaan hardware.', ['min:1']),
                    $this->field('business_justification', 'Alasan kebutuhan', 'textarea', false, 6),
                    $this->field('needed_by', 'Dibutuhkan paling lambat', 'date', false, 7),
                ],
            ],
            [
                'code' => 'SVC-06',
                'name' => 'Permintaan software',
                'ticket_class' => 'REQ',
                'sort_order' => 6,
                'fields' => [
                    $this->field('software_name', 'Nama software', 'text', true, 1),
                    $this->field('purpose', 'Tujuan penggunaan', 'textarea', true, 2),
                    $this->field('target_platform', 'Platform atau perangkat tujuan', 'text', true, 3),
                    $this->field('user_device_count', 'Jumlah pengguna atau perangkat', 'number', true, 4, validationRules: ['min:1']),
                    $this->field('license_action', 'Jenis kebutuhan lisensi', 'select', true, 5, options: [
                        ['value' => 'new', 'label' => 'Lisensi baru'],
                        ['value' => 'renewal', 'label' => 'Perpanjangan'],
                        ['value' => 'upgrade', 'label' => 'Pembaruan/upgrade'],
                        ['value' => 'free_or_open_source', 'label' => 'Gratis atau open source'],
                    ]),
                    $this->field('version_or_edition', 'Versi atau edisi', 'text', false, 6),
                    $this->field('needed_by', 'Dibutuhkan paling lambat', 'date', false, 7),
                    $this->field('constraints', 'Kendala teknis atau lisensi', 'textarea', false, 8),
                ],
            ],
            [
                'code' => 'SVC-07',
                'name' => 'Usulan sistem atau aplikasi baru',
                'ticket_class' => 'CHG',
                'sort_order' => 7,
                'fields' => [
                    $this->field('application_name', 'Nama aplikasi atau sistem', 'text', true, 1),
                    $this->field('background_problem', 'Latar belakang atau permasalahan', 'textarea', true, 2),
                    $this->field('objective', 'Tujuan pengembangan', 'textarea', true, 3),
                    $this->field('target_users', 'Pengguna yang akan dilayani', 'textarea', true, 4),
                    $this->field('main_features', 'Fitur atau kebutuhan utama', 'textarea', true, 5),
                    $this->field('integrations', 'Aplikasi yang perlu diintegrasikan', 'textarea', false, 6),
                    $this->field('data_types', 'Jenis data yang akan dikelola', 'textarea', true, 7),
                    $this->field('target_time', 'Target waktu', 'date', true, 8),
                    $this->field('target_time_reason', 'Alasan target waktu', 'textarea', true, 9),
                    $this->field('ti_assessment', 'Verifikasi dan penilaian', 'textarea', false, 10, visibility: 'internal'),
                    $this->field('complexity', 'Tingkat kompleksitas', 'select', false, 11, visibility: 'internal', options: [
                        ['value' => 'low', 'label' => 'Rendah'],
                        ['value' => 'medium', 'label' => 'Sedang'],
                        ['value' => 'high', 'label' => 'Tinggi'],
                    ]),
                    $this->field('security_data_risk', 'Risiko keamanan atau data', 'textarea', false, 12, visibility: 'internal'),
                    $this->field('ti_priority', 'Prioritas Tim TI', 'select', false, 13, visibility: 'internal', options: [
                        ['value' => 'critical', 'label' => 'Kritis'],
                        ['value' => 'high', 'label' => 'Tinggi'],
                        ['value' => 'medium', 'label' => 'Sedang'],
                        ['value' => 'low', 'label' => 'Rendah'],
                    ]),
                    $this->field('planned_start', 'Rencana mulai', 'date', false, 14, visibility: 'internal'),
                    $this->field('ti_owner', 'PIC atau tim pengembang', 'text', false, 15, visibility: 'internal'),
                    $this->field('follow_up_notes', 'Catatan dan rencana tindak lanjut', 'textarea', false, 16, visibility: 'internal'),
                ],
            ],
        ];

        foreach ($services as $serviceData) {
            $fields = $serviceData['fields'];
            unset($serviceData['fields']);

            $service = ServiceType::query()->updateOrCreate(
                ['code' => $serviceData['code']],
                [
                    'name' => $serviceData['name'],
                    'ticket_class' => $serviceData['ticket_class'],
                    'sort_order' => $serviceData['sort_order'],
                    'is_active' => true,
                ],
            );

            if ($service->code === 'SVC-05') {
                ServiceTypeVariant::query()->updateOrCreate(
                    ['service_type_id' => $service->id, 'code' => 'repair'],
                    ['label' => 'Perbaikan', 'ticket_class' => 'INC', 'sort_order' => 1, 'is_active' => true],
                );
                ServiceTypeVariant::query()->updateOrCreate(
                    ['service_type_id' => $service->id, 'code' => 'request'],
                    ['label' => 'Permintaan', 'ticket_class' => 'REQ', 'sort_order' => 2, 'is_active' => true],
                );
            }

            foreach ($fields as $fieldData) {
                $options = $fieldData['options'] ?? [];
                unset($fieldData['options']);

                $field = ServiceFieldDefinition::query()->updateOrCreate(
                    ['service_type_id' => $service->id, 'key' => $fieldData['key'], 'version' => 1],
                    [...$fieldData, 'is_active' => true],
                );

                foreach ($options as $index => $option) {
                    $field->options()->updateOrCreate(
                        ['value' => $option['value']],
                        ['label' => $option['label'], 'sort_order' => $index, 'is_active' => true],
                    );
                }
            }
        }
    }

    /**
     * @param  list<array{value:string,label:string}>  $options
     * @param  list<string>  $validationRules
     * @return array<string, mixed>
     */
    private function field(
        string $key,
        string $label,
        string $fieldType,
        bool $required,
        int $sortOrder,
        ?string $helpText = null,
        array $validationRules = [],
        string $visibility = 'requester',
        array $options = [],
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'help_text' => $helpText,
            'field_type' => $fieldType,
            'visibility' => $visibility,
            'is_required' => $required,
            'sort_order' => $sortOrder,
            'validation_rules' => $validationRules,
            'visibility_rules' => null,
            'options' => $options,
        ];
    }
}
