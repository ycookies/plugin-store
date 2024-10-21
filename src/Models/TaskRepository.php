<?php

namespace Dcat\Admin\PluginStore\Models;

use Illuminate\Database\Eloquent\Builder;

class TaskRepository
{
    /**
     * @return Builder
     */
    public function query()
    {
        return Task::query();
    }

    /**
     * @param int $id
     */
    public function findOrFail($id)
    {
        return Task::findOrFail($id);
    }
}
