<?php

namespace App\Traits;

use App\Events\TableRowCreated;
use App\Events\TableRowUpdated;
use App\Events\TableRowDeleted;

trait BroadcastsTableUpdates
{
    /**
     * Broadcast that a row was created
     */
    protected function broadcastCreated($tableName, $model)
    {
        if (config('broadcasting.default') !== 'null') {
            event(new TableRowCreated($tableName, [
                'id' => $model->id,
                'data' => $model->toArray(),
            ]));
        }
    }

    /**
     * Broadcast that a row was updated
     */
    protected function broadcastUpdated($tableName, $model)
    {
        if (config('broadcasting.default') !== 'null') {
            event(new TableRowUpdated($tableName, [
                'id' => $model->id,
                'data' => $model->toArray(),
            ]));
        }
    }

    /**
     * Broadcast that a row was deleted
     */
    protected function broadcastDeleted($tableName, $id)
    {
        if (config('broadcasting.default') !== 'null') {
            event(new TableRowDeleted($tableName, $id));
        }
    }
}
