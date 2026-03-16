<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/User.php';

$page = (int) ($_GET['page'] ?? 1);

$userController = new UserController();
$data = $userController->index($page, 5);
$users = $data['data'];
$totalPages = $data['totalPages'];


$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$pageTitle = 'Cafetria System | Users';
$basePath = '..';
$pageKey = 'admin-users';
$pageRole = 'admin';
$currentPage = 'users';
$headerBadge = 'Administrator';
$headerTitle = 'Manage employees';
$headerSubtitle = 'Maintain user profiles, access credentials, and staff account details in one place.';
require __DIR__ . '/../includes/page-start.php';
?>
<main class="relative min-h-screen px-4 py-6 md:px-6 lg:px-8">
  <div class="mx-auto max-w-7xl space-y-6">
    <?php require __DIR__ . '/../includes/dashboard-header.php'; ?>
    <?php require __DIR__ . '/../includes/admin-nav.php'; ?>

    <?php if ($successMessage): ?>
      <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
      <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <section class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-600">Employee base</p>
          <h2 class="mt-2 text-2xl font-bold text-slate-900">Users</h2>
        </div>
        <a href="create-user.php" class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Add user</a>
      </div>

      <div class="mt-6 overflow-hidden rounded-[1.5rem] border border-slate-200">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
              <tr>
                <th class="px-4 py-3 font-semibold">User</th>
                <th class="px-4 py-3 font-semibold">Email</th>
                <th class="px-4 py-3 font-semibold">Image</th>
                <th class="px-4 py-3 font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
              <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                  <tr>
                    <td class="px-4 py-3"><?= htmlspecialchars((string)$user['name']) ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars((string)$user['email']) ?></td>
                    <td class="px-4 py-3">
                      <?php if (!empty($user['profile_pic'])): ?>
                        <img src="../<?= htmlspecialchars((string)$user['profile_pic']) ?>" alt="<?= htmlspecialchars((string)$user['name']) ?>" class="h-10 w-10 rounded-full object-cover" />
                      <?php else: ?>
                        No image
                      <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                      <div class="flex flex-wrap gap-2">
                        <a href="edit-user.php?id=<?= (int)$user['id'] ?>" class="inline-flex items-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-800">Edit</a>
                        <form method="POST" action="delete-user.php" onsubmit="return confirm('Delete this user?');">
                          <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                          <button type="submit" class="inline-flex items-center rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td class="px-4 py-3" colspan="5">No users found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
          <div class="mt-4 flex gap-2">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <a
                href="?page=<?php echo $i; ?>"
                class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-slate-900 text-white' : 'bg-white'; ?>">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>
          </div>
        </div>
      </div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/page-end.php'; ?>