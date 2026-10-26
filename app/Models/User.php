<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        // Avatar mặc định dựa trên chữ cái đầu của Display Name (dùng SVG/UI-Avatars)
        $name = urlencode($this->display_name);
        return "https://ui-avatars.com/api/?name={$name}&background=4f46e5&color=fff&size=128";
    }
    /**
     * Các trường được phép gán giá trị hàng loạt.
     */
    protected $fillable = [
        'display_name',
        'email',
        'password',
        'avatar',
        'preferences',
    ];

    /**
     * Các trường ẩn khi trả về JSON/Array.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Ép kiểu dữ liệu (Casting).
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function labels()
    {
        return $this->hasMany(Label::class);
    }
}