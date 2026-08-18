<?php

namespace App\Services\MaterialMovement;

use App\DTOs\MaterialMovementData;
use App\Models\MaterialMovement;

class MaterialMovementRelationService
{
    public function findAssignmentId(
        MaterialMovementData $data
    ): ?int {
        if ($data->type !== 'return') {
            return null;
        }

        return MaterialMovement::query()
            ->where('type', 'assignment')
            ->where(
                'movement_number',
                $data->movementNumber
            )
            ->value('id');
    }

    public function syncPendingReturns(
        MaterialMovement $movement
    ): void {
        if ($movement->type !== 'assignment') {
            return;
        }

        MaterialMovement::query()
            ->where('type', 'return')
            ->whereNull('assignment_id')
            ->where(
                'assignment_reference',
                $movement->movement_number
            )
            ->update([
                'assignment_id' => $movement->id,
            ]);
    }
}
