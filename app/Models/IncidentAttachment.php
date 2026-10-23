<?php

namespace App\Models;

class IncidentAttachment extends BaseModel
{
    protected $fillable = ['path', 'original_name', 'mime_type', 'size'];

    public function getIsImageAttribute(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true);
    }
}
