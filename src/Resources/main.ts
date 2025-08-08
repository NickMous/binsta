import { createApp, type Component } from 'vue'
import './style.css'
import router from './router'
import {createPinia} from "pinia";

const components: Record<string, Component> = {}
const modules = import.meta.glob('./components/*.vue', { eager: true })
for (const path in modules) {
    const module = modules[path] as { default: Component & { __name: string } }
    components[module.default.__name] = module.default
}

const pinia = createPinia();

const mainElement = document.getElementById('app');

if (mainElement) {
    const app = createApp({
        components,
        template: mainElement.innerHTML
    });
    app.use(pinia)
    app.use(router)
    app.mount(mainElement)
}
