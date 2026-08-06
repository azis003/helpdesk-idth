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
}
