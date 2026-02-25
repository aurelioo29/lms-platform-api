<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManagedUserRequest;
use App\Http\Requests\Admin\UpdateManagedUserRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    // =========================
    // TEACHERS
    // =========================
    public function indexTeachers(Request $request)
    {
        $sort = $request->get('sort', 'id');
        $dir = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // whitelist kolom biar aman (anti SQL injection)
        if (! in_array($sort, ['id', 'created_at', 'name', 'email'], true)) {
            $sort = 'id';
        }

        $perPage = min(max((int) $request->get('per_page', 20), 1), 100);

        $q = User::query()
            ->where('role', UserRole::Teacher->value);

        // search
        if ($request->filled('q')) {
            $term = trim((string) $request->get('q'));
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $paginator = $q->orderBy($sort, $dir)->paginate($perPage);

        return response()->json([
            'data' => $paginator,
        ]);
    }

    public function storeTeacher(StoreManagedUserRequest $request)
    {
        return $this->storeByRole($request, UserRole::Teacher);
    }

    public function showTeacher(User $user)
    {
        return $this->showByRole($user, UserRole::Teacher);
    }

    public function updateTeacher(UpdateManagedUserRequest $request, User $user)
    {
        return $this->updateByRole($request, $user, UserRole::Teacher);
    }

    public function destroyTeacher(Request $request, User $user)
    {
        return $this->destroyByRole($request, $user, UserRole::Teacher);
    }

    // =========================
    // STUDENTS
    // =========================
    public function indexStudents(Request $request)
    {
        $sort = $request->get('sort', 'id');
        $dir = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, ['id', 'created_at', 'name', 'email'], true)) {
            $sort = 'id';
        }

        $perPage = min(max((int) $request->get('per_page', 20), 1), 100);

        $q = User::query()
            ->where('role', UserRole::Student->value);

        if ($request->filled('q')) {
            $term = trim((string) $request->get('q'));
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $paginator = $q->orderBy($sort, $dir)->paginate($perPage);

        return response()->json(['data' => $paginator]);
    }

    public function storeStudent(StoreManagedUserRequest $request)
    {
        return $this->storeByRole($request, UserRole::Student);
    }

    public function showStudent(User $user)
    {
        return $this->showByRole($user, UserRole::Student);
    }

    public function updateStudent(UpdateManagedUserRequest $request, User $user)
    {
        return $this->updateByRole($request, $user, UserRole::Student);
    }

    public function destroyStudent(Request $request, User $user)
    {
        return $this->destroyByRole($request, $user, UserRole::Student);
    }

    // =========================
    // INTERNAL HELPERS
    // =========================
    private function indexByRole(Request $request, UserRole $role)
    {
        $q = User::query()->where('role', $role->value);

        if ($request->filled('q')) {
            $keyword = $request->get('q');
            $q->where(function ($qq) use ($keyword) {
                $qq->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        $perPage = min(max((int) $request->get('per_page', 20), 1), 100);

        return response()->json([
            'data' => $q->orderBy('id', 'desc')->paginate($perPage),
        ]);
    }

    private function storeByRole(StoreManagedUserRequest $request, UserRole $role)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $role->value,
            'password' => Hash::make($data['password']),
        ]);

        ActivityLogger::log(
            userId: $request->user()->id,
            courseId: null,
            eventType: 'admin_create_user',
            refType: 'user',
            refId: $user->id,
            meta: [
                'created_user_id' => $user->id,
                'created_role' => $role->value,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        if (($data['send_verification'] ?? true) === true) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'message' => 'User created.',
            'data' => $this->payload($user),
        ], 201);
    }

    private function showByRole(User $user, UserRole $role)
    {
        if ($this->roleValue($user) !== $role->value) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json([
            'data' => $this->payload($user),
        ]);
    }

    private function updateByRole(UpdateManagedUserRequest $request, User $user, UserRole $role)
    {
        if ($this->roleValue($user) !== $role->value) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $data = $request->validated();

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }
        if (array_key_exists('email', $data)) {
            $user->email = $data['email'];
        }

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        ActivityLogger::log(
            userId: $request->user()->id,
            courseId: null,
            eventType: 'admin_update_user',
            refType: 'user',
            refId: $user->id,
            meta: [
                'updated_user_id' => $user->id,
                'role_scope' => $role->value,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'message' => 'User updated.',
            'data' => $this->payload($user),
        ]);
    }

    private function destroyByRole(Request $request, User $user, UserRole $role)
    {
        if ($this->roleValue($user) !== $role->value) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $user->delete();

        ActivityLogger::log(
            userId: $request->user()->id,
            courseId: null,
            eventType: 'admin_delete_user',
            refType: 'user',
            refId: $user->id,
            meta: [
                'deleted_user_id' => $user->id,
                'role_scope' => $role->value,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json(['message' => 'User deleted.']);
    }

    private function payload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $this->roleValue($user),
            'avatar' => $user->avatar,
            'email_verified_at' => $user->email_verified_at,
        ];
    }

    private function roleValue(User $user): string
    {
        // Handles enum cast OR string
        return is_object($user->role) ? $user->role->value : (string) $user->role;
    }
}
