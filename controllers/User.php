<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/Utilities.php';

function getCustomerUsers(): array {
    $stmt = db()->query("SELECT id, name FROM users WHERE role = 'customer' ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

class UserController {

    public function search($query, $page = 1, $perPage = 10): array {
        $stmt = "
            SELECT id, name, email, role, profile_pic
            FROM users
            WHERE role = 'customer'
            AND name LIKE ?
            ORDER BY name
        ";
        $like = "%$query%";
        return paginate($stmt, $page, $perPage, [$like]);
    }

    public function index($page = 1 , $perPage = 10): array {

        $stmt = "SELECT id, name, email, role, profile_pic FROM users WHERE role = 'customer' ORDER BY name";
        return paginate($stmt, $page, $perPage);
    }

    public function show(int $id): ?array {
        $stmt = db()->prepare("SELECT id, name, email, role, profile_pic FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function store(array $data): bool {
        if ($this->emailExists($data['email'])) {
            throw new InvalidArgumentException('Email is already in use.');
        }

        $stmt = db()->prepare("INSERT INTO users (name, email, password_hash, role, profile_pic, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $imageController = new ImageController();
        $data['profile_pic'] = $this->uploadProfilePicture($imageController, $data['profile_pic'] ?? null);
        return $stmt->execute([$data['name'], $data['email'], password_hash($data['password_hash'], PASSWORD_DEFAULT), 'customer', $data['profile_pic']]);
    }

    public function update(int $id, array $data): bool {
        $existingUser = $this->show($id);

        if (!$existingUser) {
            return false;
        }

        if ($this->emailExists($data['email'], $id)) {
            throw new InvalidArgumentException('Email is already in use.');
        }

        $imageController = new ImageController();
        $uploadedProfilePic = $this->uploadProfilePicture($imageController, $data['profile_pic'] ?? null);
        $profilePic = $uploadedProfilePic ?? $existingUser['profile_pic'];
        $oldProfilePic = (string)($existingUser['profile_pic'] ?? '');

        if (!empty($data['password_hash'])) {
            $stmt = db()->prepare("UPDATE users SET name = ?, email = ?, password_hash = ?, role = ?, profile_pic = ?, updated_at = NOW() WHERE id = ?");
            $updated = $stmt->execute([
                $data['name'],
                $data['email'],
                password_hash($data['password_hash'], PASSWORD_DEFAULT),
                $data['role'],
                $profilePic,
                $id,
            ]);

            if ($updated && $uploadedProfilePic !== null && $oldProfilePic !== '' && $oldProfilePic !== $uploadedProfilePic) {
                $this->deleteProfilePicture($oldProfilePic);
            }

            return $updated;
        }

        $stmt = db()->prepare("UPDATE users SET name = ?, email = ?, role = ?, profile_pic = ?, updated_at = NOW() WHERE id = ?");
        $updated = $stmt->execute([$data['name'], $data['email'], $data['role'], $profilePic, $id]);

        if ($updated && $uploadedProfilePic !== null && $oldProfilePic !== '' && $oldProfilePic !== $uploadedProfilePic) {
            $this->deleteProfilePicture($oldProfilePic);
        }

        return $updated;
    }

    public function destroy(int $id): bool {
        $stmt = db()->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function uploadProfilePicture(ImageController $imageController, mixed $file): ?string {
        if (!is_array($file) || !isset($file['error'])) {
            return null;
        }

        return $imageController->uploadImage($file);
    }

    private function emailExists(string $email, ?int $excludeId = null): bool {
        if ($excludeId === null) {
            $stmt = db()->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return (int)$stmt->fetchColumn() > 0;
        }

        $stmt = db()->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function deleteProfilePicture(string $relativePath): void {
        if (!str_starts_with($relativePath, 'uploads/') || str_contains($relativePath, '..')) {
            return;
        }

        $fullPath = dirname(__DIR__) . '/' . $relativePath;

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
