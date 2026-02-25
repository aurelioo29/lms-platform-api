<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google_id',
        'avatar',
        'username_changed_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'username_changed_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    // =========================
    // Role helpers
    // =========================
    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::Teacher;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isDeveloper(): bool
    {
        return $this->role === UserRole::Developer;
    }

    // Keep these notifications
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPassword($token));
    }

    // =========================
    // Lms Relationships
    // =========================

    // Student enrollments
    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(
            Course::class,
            'course_enrollments'
        )->withPivot([
            'status',
            'method',
            'enrolled_at',
            'enrolled_by',
        ]);
    }

    // Courses created (admin)
    public function createdCourses()
    {
        return $this->hasMany(Course::class, 'created_by');
    }
}
