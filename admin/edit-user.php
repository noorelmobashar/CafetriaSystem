<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/User.php';

$userController = new UserController();
$errorMessage = null;
$passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

$id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
} else {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
}

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid user id.';
    header('Location: users.php');
    exit;
}

$user = $userController->show($id);
if (!$user || ($user['role'] ?? '') !== 'customer') {
    $_SESSION['error_message'] = 'User not found.';
    header('Location: users.php');
    exit;
}

$form = [
    'name' => (string)$user['name'],
    'email' => (string)$user['email'],
    'password' => '',
    'role' => 'customer',
    'profile_pic' => (string)($user['profile_pic'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $form['name'] = trim((string)($_POST['name'] ?? ''));
    $form['email'] = trim((string)($_POST['email'] ?? ''));
    $form['password'] = trim((string)($_POST['password'] ?? ''));
    $form['role'] = 'customer';

    if (strlen($form['name']) < 10) {
      $errorMessage = 'User name must be at least 10 characters.';
    } elseif ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'A valid email is required.';
    } elseif ($form['password'] !== '' && !preg_match($passwordRegex, $form['password'])) {
      $errorMessage = 'Password must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character.';
    } else {
        try {
            $payload = [
                'name' => $form['name'],
                'email' => $form['email'],
                'role' => $form['role'],
                'profile_pic' => $_FILES['profile_pic'] ?? null,
            ];

            if ($form['password'] !== '') {
              $payload['password_hash'] = $form['password'];
            }

            $updated = $userController->update($id, $payload);

            if ($updated) {
                $_SESSION['success_message'] = 'User updated successfully.';
                header('Location: users.php');
                exit;
            }

            $errorMessage = 'No changes were saved.';
        } catch (InvalidArgumentException $exception) {
          $errorMessage = $exception->getMessage();
        } catch (PDOException $exception) {
          $errorMessage = $exception->getCode() === '23000'
            ? 'Email is already in use.'
            : 'Failed to update user. Please try again.';
        } catch (Throwable $exception) {
            $errorMessage = 'Failed to update user. Please try again.';
        }
    }
}

$pageTitle = 'Cafetria System | Edit User';
$basePath = '..';
$pageKey = 'admin-edit-user';
$pageRole = 'admin';
$currentPage = 'users';
$headerBadge = 'Administrator';
$headerTitle = 'Edit user';
$headerSubtitle = 'Update employee account details.';
require __DIR__ . '/../includes/page-start.php';
?>

<main class="relative min-h-screen px-4 py-6 md:px-6 lg:px-8">
  <div class="mx-auto max-w-7xl space-y-6">
    <?php require __DIR__ . '/../includes/dashboard-header.php'; ?>
    <?php require __DIR__ . '/../includes/admin-nav.php'; ?>

    <?php if ($errorMessage): ?>
      <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <section class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
      <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-600">Employee base</p>
          <h2 class="mt-2 text-2xl font-bold text-slate-900">Edit User</h2>
        </div>
        <a href="users.php" class="rounded-2xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">Back to users</a>
      </div>

      <form method="POST" enctype="multipart/form-data" class="grid gap-5 md:grid-cols-2">
        <input type="hidden" name="id" value="<?= (int)$id ?>">

        <div class="md:col-span-2">
          <label for="user-name" class="mb-2 block text-sm font-semibold text-slate-700">User name</label>
          <input id="user-name" name="name" type="text" value="<?= htmlspecialchars($form['name']) ?>" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
        </div>

        <div class="md:col-span-2">
          <label for="user-email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
          <input id="user-email" name="email" type="email" value="<?= htmlspecialchars($form['email']) ?>" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
        </div>

        <div class="md:col-span-2">
          <label for="user-password" class="mb-2 block text-sm font-semibold text-slate-700">Password (leave blank to keep current)</label>
          <input id="user-password" name="password" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
        </div>

        <div class="md:col-span-2">
          <label for="user-picture" class="mb-2 block text-sm font-semibold text-slate-700">Profile picture (optional)</label>
          <input id="user-picture" name="profile_pic" type="file" accept="image/png,image/jpeg,image/gif" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
          <?php if ($form['profile_pic'] !== ''): ?>
            <p class="mt-2 text-xs text-slate-500">Current image:</p>
            <img src="../<?= htmlspecialchars($form['profile_pic']) ?>" alt="<?= htmlspecialchars($form['name']) ?>" class="mt-2 h-16 w-16 rounded-full object-cover" />
          <?php endif; ?>
        </div>

        <div class="md:col-span-2">
          <button type="submit" name="update_user" value="1" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Update User</button>
        </div>
      </form>
    </section>
  </div>
</main>

<?php require __DIR__ . '/../includes/page-end.php'; ?>
