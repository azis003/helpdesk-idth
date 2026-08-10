<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Baru = 'baru';
    case Diproses = 'diproses';
    case Dikerjakan = 'dikerjakan';
    case MenungguPersetujuan = 'menunggu_persetujuan';
    case MenungguPemohon = 'menunggu_pemohon';
    case MenungguPihakKetiga = 'menunggu_pihak_ketiga';
    case MenungguKonfirmasi = 'menunggu_konfirmasi';
    case Ditutup = 'ditutup';
    case Ditolak = 'ditolak';
    case TidakDisetujui = 'tidak_disetujui';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Diproses => 'Diproses',
            self::Dikerjakan => 'Dikerjakan',
            self::MenungguPersetujuan => 'Menunggu Persetujuan',
            self::MenungguPemohon => 'Menunggu Pemohon',
            self::MenungguPihakKetiga => 'Menunggu Pihak Ketiga',
            self::MenungguKonfirmasi => 'Menunggu Konfirmasi',
            self::Ditutup => 'Ditutup',
            self::Ditolak => 'Ditolak',
            self::TidakDisetujui => 'Tidak Disetujui',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /**
     * Tiket yang masih ditangani tim TI dan belum menunggu pemohon.
     *
     * @return list<self>
     */
    public static function activeCases(): array
    {
        return [
            self::Baru,
            self::Diproses,
            self::Dikerjakan,
            self::MenungguPersetujuan,
            self::MenungguPihakKetiga,
        ];
    }

    /**
     * Tiket yang berhenti sampai pemohon memberi tanggapan.
     *
     * @return list<self>
     */
    public static function requesterActionCases(): array
    {
        return [
            self::MenungguPemohon,
            self::MenungguKonfirmasi,
        ];
    }

    /**
     * Status akhir yang tidak lagi memerlukan tindak lanjut.
     *
     * @return list<self>
     */
    public static function closedCases(): array
    {
        return [
            self::Ditutup,
            self::Ditolak,
            self::TidakDisetujui,
            self::Dibatalkan,
        ];
    }

    public function isClosed(): bool
    {
        return in_array($this, self::closedCases(), true);
    }

    public function needsRequesterAction(): bool
    {
        return in_array($this, self::requesterActionCases(), true);
    }

    /**
     * @param  list<self>  $cases
     * @return list<string>
     */
    public static function valuesOf(array $cases): array
    {
        return array_map(static fn (self $case): string => $case->value, $cases);
    }
}
