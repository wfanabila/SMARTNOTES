document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.filter-pill');
    const tableRows = document.querySelectorAll('tbody tr');

    const updateRowDisplay = (filter) => {
        tableRows.forEach((row) => {
            const badge = row.querySelector('.badge');
            const status = badge ? badge.textContent.trim().toLowerCase() : '';
            row.style.display = filter === 'all' || status === filter ? '' : 'none';
        });
    };

    const setActiveFilter = (button) => {
        filterButtons.forEach((btn) => btn.classList.remove('active'));
        button.classList.add('active');
    };

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;
            setActiveFilter(button);
            updateRowDisplay(filter);
        });
    });

    const updateRowStatus = (row, newStatus) => {
        const badge = row.querySelector('.badge');
        if (!badge) return;

        badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
        badge.className = `badge ${newStatus}`;

        const activeFilter = document.querySelector('.filter-pill.active');
        const filter = activeFilter ? activeFilter.dataset.filter : 'all';
        updateRowDisplay(filter);
    };

    document.querySelectorAll('button[data-action="approve"]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            updateRowStatus(row, 'approved');
        });
    });

    document.querySelectorAll('button[data-action="reject"]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            updateRowStatus(row, 'rejected');
        });
    });

    document.querySelectorAll('button[data-action="delete"]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            row.remove();
        });
    });

    document.querySelectorAll('button[data-action="view"]').forEach((button) => {
        button.addEventListener('click', () => {
            alert('View note details feature is not available in this demo.');
        });
    });
});