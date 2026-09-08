import './app.css'
import { createInertiaApp } from '@inertiajs/vue3'
import { createApp, h } from 'vue'

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('../resources/js/Pages/**/*.vue', { eager: true });
        return pages[`../resources/js/Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) }).use(plugin).mount(el)
    },
})
