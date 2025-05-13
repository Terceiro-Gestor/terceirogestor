const app = Vue.createApp({});

// Função para carregar componentes HTML dinamicamente
async function loadComponent(url) {
    const response = await fetch(url);
    const template = await response.text();
    return {
        template
    };
}

// Registrar os componentes
(async () => {
    app.component("header-component", await loadComponent("/app/Views/components/header.html"));
    app.component("footer-component", await loadComponent("/app/Views/components/footer.html"));
    app.component("menu-component", await loadComponent("/app/Views/components/menu.html"));
    // Montar o Vue no elemento com ID "app"
    app.mount("#app");
})();