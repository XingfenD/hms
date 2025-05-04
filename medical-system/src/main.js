

import { createApp } from 'vue'
import Antd from 'ant-design-vue';
import App from './App.vue'
import router from './router'
import 'ant-design-vue/dist/reset.css';
import axios from 'axios';
import UserStatusChecker from './components/UserStatusChecker.vue';
import ChatAssistant from './components/ChatAssistant.vue';

const app = createApp(App)

app.use(router)
app.use(Antd)
app.component('UserStatusChecker', UserStatusChecker); 
app.component('ChatAssistant', ChatAssistant);

app.mount('#app')
