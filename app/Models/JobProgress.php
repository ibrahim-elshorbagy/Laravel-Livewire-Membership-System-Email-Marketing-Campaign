<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobProgress extends Model
{
    protected $guarded = ['id'];



    protected $casts = [
        'total_items' => 'integer',
        'processed_items' => 'integer',
        'percentage' => 'float'
    ];

    public function updateProgress($processedItems, $totalItems = null)
    {
        $this->processed_items = $processedItems;
        if ($totalItems) {
            $this->total_items = $totalItems;
        }
        $this->percentage = ($this->processed_items / $this->total_items) * 100;
        $this->save();
    }
}
