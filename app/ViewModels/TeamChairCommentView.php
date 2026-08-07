<?php

namespace App\ViewModels;

use Illuminate\Support\Carbon;

/**
 * The deliberately small public-comment projection used by Ketua Tim Kerja.
 *
 * Keeping this separate from TicketComment prevents internal metadata,
 * visibility details, and attachment relations from reaching a view.
 */
final class TeamChairCommentView
{
    public function __construct(
        public readonly ?string $authorName,
        public readonly string $body,
        public readonly ?Carbon $createdAt,
    ) {}
}
