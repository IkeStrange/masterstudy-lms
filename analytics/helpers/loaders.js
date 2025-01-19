function hideLoaders(selector) {
    const elements = document.querySelectorAll(selector);

    elements.forEach(element => {
        const loaders = element.querySelectorAll('.masterstudy-analytics-loader');
        loaders.forEach(loader => {
            loader.style.display = 'none';
        });
    });
}

function showLoaders(selector) {
    const elements = document.querySelectorAll(selector);

    elements.forEach(element => {
        const loaders = element.querySelectorAll('.masterstudy-analytics-loader');
        loaders.forEach(loader => {
            loader.style.display = 'flex';
        });
    });
}