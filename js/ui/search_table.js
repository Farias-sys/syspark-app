document.getElementById('searchInput').addEventListener('input', e => {
    const term = e.target.value.toLowerCase();

    ['currentTableBody', 'historyTableBody', 'registeredTableBody', 'spotsTableBody'].forEach(tbodyId => {
        document.querySelectorAll(`#${tbodyId} tr`).forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });
});