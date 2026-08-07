<?php

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use LogicException;

class AuditLogBuilder extends Builder
{
    public function update(array $values)
    {
        throw new LogicException('Audit log bersifat append-only.');
    }

    public function delete()
    {
        throw new LogicException('Audit log bersifat append-only.');
    }

    public function forceDelete()
    {
        throw new LogicException('Audit log bersifat append-only.');
    }
}
