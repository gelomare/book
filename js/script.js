document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const file = urlParams.get('file');

    if (file) {
        fetch(`fb2parser.php?file=${file}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('content').innerHTML = data;
            })
            .catch(error => console.error('Ошибка:', error));
    }
});