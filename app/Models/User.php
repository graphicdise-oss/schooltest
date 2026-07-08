<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'personnels';
    protected $primaryKey = 'personnel_id';

    protected $fillable = [
        'employee_code', 'password', 'thai_firstname', 'thai_lastname',
        'email', 'role', 'department', 'status'
    ];

    protected $hidden = [
        'password',
    ];

    private $menuKeysCache = false; // false = ยังไม่โหลด, null = เห็นทุกเมนู, array = รายการที่อนุญาต

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'superadmin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    // รายการ menu_key ที่อนุญาต (null = เห็นทุกเมนู)
    public function allowedMenuKeys()
    {
        if ($this->menuKeysCache !== false) {
            return $this->menuKeysCache;
        }

        // admin เห็นทุกเมนู
        if ($this->isAdmin()) {
            return $this->menuKeysCache = null;
        }

        $type = \App\Models\Personne\PersonnelType::where('name', $this->personnel_type)->first();

        // ไม่มีประเภท หรือยังไม่ตั้งค่าสิทธิ์เลย = เห็นทุกเมนู (กันล็อกเอาต์)
        if (!$type || $type->permissions()->count() === 0) {
            return $this->menuKeysCache = null;
        }

        return $this->menuKeysCache = $type->permissions()
            ->where('is_allowed', true)->pluck('menu_key')->all();
    }

    // มีสิทธิ์เห็นกลุ่มเมนูนี้ไหม (ผ่านถ้ามีสิทธิ์อย่างน้อย 1 key ในกลุ่ม)
    public function canArea(array $keys): bool
    {
        $allowed = $this->allowedMenuKeys();
        if ($allowed === null) {
            return true;
        }
        return count(array_intersect($keys, $allowed)) > 0;
    }
}