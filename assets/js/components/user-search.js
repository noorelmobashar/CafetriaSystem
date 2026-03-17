const userSearchInput = document.getElementById('user-search-input');
const usersTableBody = document.getElementById('users-table-body');
const usersPagination = document.getElementById('users-pagination');
let userSearchTimeout;

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

function renderUsers(users) {
    if (!usersTableBody) {
    return;
    }

    if (!Array.isArray(users) || users.length === 0) {
    usersTableBody.innerHTML = `
        <tr>
        <td class="px-4 py-3" colspan="5">No users found</td>
        </tr>
    `;
    return;
    }

    usersTableBody.innerHTML = users.map((user) => `
    <tr>
        <td class="px-4 py-3">${escapeHtml(user.name)}</td>
        <td class="px-4 py-3">${escapeHtml(user.email)}</td>
        <td class="px-4 py-3">
        ${user.profile_pic
            ? `<img src="../${escapeHtml(user.profile_pic)}" alt="${escapeHtml(user.name)}" class="h-10 w-10 rounded-full object-cover" />`
            : 'No image'}
        </td>
        <td class="px-4 py-3">
        <div class="flex flex-wrap gap-2">
            <a href="edit-user.php?id=${Number(user.id)}" class="inline-flex items-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-800">Edit</a>
            <form method="POST" action="delete-user.php" onsubmit="return confirm('Delete this user?');">
            <input type="hidden" name="id" value="${Number(user.id)}">
            <button type="submit" class="inline-flex items-center rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">Delete</button>
            </form>
        </div>
        </td>
    </tr>
    `).join('');
}

function renderPagination(totalPages, currentPage, query) {
    if (!usersPagination) {
    return;
    }

    if (totalPages <= 1) {
    usersPagination.innerHTML = '';
    return;
    }

    usersPagination.innerHTML = Array.from({ length: totalPages }, (_, index) => {
    const pageNumber = index + 1;
    return `
        <button
        type="button"
        data-page="${pageNumber}"
        data-query="${escapeHtml(query)}"
        class="px-3 py-1 rounded border ${pageNumber === currentPage ? 'bg-slate-900 text-white' : 'bg-white'}">
        ${pageNumber}
        </button>
    `;
    }).join('');
}

function updateUrl(query, page) {
    const url = new URL(window.location.href);

    if (query) {
    url.searchParams.set('search', query);
    } else {
    url.searchParams.delete('search');
    }

    if (page > 1) {
    url.searchParams.set('page', String(page));
    } else {
    url.searchParams.delete('page');
    }

    window.history.replaceState({}, '', url);
}

function fetchUsers(query, page = 1) {
    if (!usersTableBody) {
    return;
    }

    usersTableBody.innerHTML = `
    <tr>
        <td class="px-4 py-3" colspan="5">Searching...</td>
    </tr>
    `;

    fetch(`../admin/ajax-search-users.php?q=${query}&page=${page}`)
    .then((response) => {
        if (!response.ok) {
        throw new Error(`Request failed with status ${response.status}`);
        }

        return response.json();
    })
    .then((data) => {
        if (!data.success) {
        throw new Error('Unable to load users.');
        }

        renderUsers(data.users);
        renderPagination(data.totalPages, data.currentPage, query);
        updateUrl(query, data.currentPage);
    })
    .catch((error) => {
        console.error('User search error:', error);
        usersTableBody.innerHTML = `
        <tr>
            <td class="px-4 py-3 text-red-600" colspan="5">Error loading users.</td>
        </tr>
        `;
        if (usersPagination) {
        usersPagination.innerHTML = '';
        }
    });
}

userSearchInput.addEventListener('input', (event) => {
    clearTimeout(userSearchTimeout);
    const query = event.target.value.trim();

    userSearchTimeout = setTimeout(() => {
    fetchUsers(query, 1);
    }, 300);
});

userSearchInput.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
});


usersPagination.addEventListener('click', (event) => {
    const button = event.target.closest('button[data-page]');

    if (!button) {
        return;
    }

    fetchUsers(button.dataset.query || '', Number(button.dataset.page));
});
