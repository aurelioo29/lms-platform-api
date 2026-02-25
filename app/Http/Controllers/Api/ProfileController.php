<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateEmailRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateUsernameRequest;
use App\Http\Requests\Profile\UploadAvatarRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    // POST /api/auth/profile/avatar
    public function updateAvatar(UploadAvatarRequest $request)
    {
        $user = $request->user();

        // delete old avatar if it's a local storage path
        if ($user->avatar) {
            $this->deleteAvatarFileIfExists($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        ActivityLogger::log(
            userId: $user->id,
            courseId: null,
            eventType: 'profile_avatar_updated',
            refType: 'user',
            refId: $user->id,
            meta: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $path,
            ]
        );

        return response()->json([
            'message' => 'Avatar updated.',
            'user' => $this->userPayload($user),
        ]);
    }

    // PATCH /api/auth/profile/username
    public function updateUsername(UpdateUsernameRequest $request)
    {
        $user = $request->user();

        $user->name = $request->validated()['name'];
        $user->username_changed_at = now();
        $user->save();

        ActivityLogger::log(
            userId: $user->id,
            courseId: null,
            eventType: 'profile_username_updated',
            refType: 'user',
            refId: $user->id,
            meta: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'message' => 'Display name updated.',
            'user' => $this->userPayload($user),
        ]);
    }

    // PATCH /api/auth/profile/email (no reset verification)
    public function updateEmail(UpdateEmailRequest $request)
    {
        $user = $request->user();
        $email = $request->validated()['email'];

        // If same email, return ok
        if (Str::lower($email) === Str::lower($user->email)) {
            return response()->json([
                'message' => 'Email is unchanged.',
                'user' => $this->userPayload($user),
            ]);
        }

        $user->email = $email;
        $user->save();

        ActivityLogger::log(
            userId: $user->id,
            courseId: null,
            eventType: 'profile_email_updated',
            refType: 'user',
            refId: $user->id,
            meta: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_email' => $user->getOriginal('email'),
                'new_email' => $email,
            ]
        );

        return response()->json([
            'message' => 'Email updated.',
            'user' => $this->userPayload($user),
        ]);
    }

    // PATCH /api/auth/profile/password
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user();

        $data = $request->validated();

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
                'errors' => ['current_password' => ['Current password is incorrect.']],
            ], 422);
        }

        $user->password = Hash::make($data['password']);
        $user->setRememberToken(Str::random(60));
        $user->save();

        ActivityLogger::log(
            userId: $user->id,
            courseId: null,
            eventType: 'profile_password_updated',
            refType: 'user',
            refId: $user->id,
            meta: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'message' => 'Password updated.',
        ]);
    }

    private function deleteAvatarFileIfExists(string $path): void
    {
        // guard: if stored as URL, don't delete
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function userPayload($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->value,
            'google_id' => $user->google_id,
            'avatar' => $user->avatar,
            'username_changed_at' => $user->username_changed_at,
        ];
    }

    public function removeAvatar(Request $request)
    {
        $user = $request->user();

        // avatar value contoh: "storage/avatars/xxx.png" atau "avatars/xxx.png"
        $avatar = (string) ($user->avatar ?? '');

        // Hapus file jika ada dan lokal
        if ($avatar) {
            // Normalize jadi path file di public/
            $relative = ltrim($avatar, '/'); // "storage/avatars/xxx.png"
            $fullPath = public_path($relative);

            // Kalau file ada, delete
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }

        // Set avatar null di DB
        $user->avatar = null;
        $user->save();

        ActivityLogger::log(
            userId: $user->id,
            courseId: null,
            eventType: 'profile_avatar_removed',
            refType: 'user',
            refId: $user->id,
            meta: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'message' => 'Avatar removed.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->value,
                'google_id' => $user->google_id,
                'avatar' => $user->avatar,
            ],
        ]);
    }
}
