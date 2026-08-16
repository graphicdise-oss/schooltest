<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;

class Announcement extends Model
{
    protected $table = 'announcements';
    protected $primaryKey = 'announcement_id';

    protected $fillable = [
        'title', 'message_type', 'send_type', 'requires_ack', 'delivery_mode',
        'target_type', 'target_level_id', 'target_section_id', 'body',
        'attachment_path', 'created_by',
    ];

    protected $casts = ['requires_ack' => 'boolean'];

    public function recipients()
    {
        return $this->hasMany(AnnouncementRecipient::class, 'announcement_id', 'announcement_id');
    }

    public function targetLevel()
    {
        return $this->belongsTo(Level::class, 'target_level_id', 'level_id');
    }

    public function targetSection()
    {
        return $this->belongsTo(ClassSection::class, 'target_section_id', 'section_id');
    }

    public function getTargetLabelAttribute(): string
    {
        return match ($this->target_type) {
            'level'   => 'เฉพาะระดับชั้น ' . ($this->targetLevel->name ?? '-'),
            'section' => 'เฉพาะห้อง ' . ($this->targetSection->full_name ?? '-'),
            default   => 'ทั้งหมด (ทุกคนในโรงเรียน)',
        };
    }
}
