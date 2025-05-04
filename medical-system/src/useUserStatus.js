// useUserStatus.js
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import {getLoginInfo} from './api/api.js'



export function useUserStatus() {
    const isLoggedIn = ref(false);
    const userInfo = ref(null);
    const router = useRouter();

    const checkUserStatus = async () => {
        try {
            const response = await getLoginInfo();
            isLoggedIn.value = true;
            userInfo.value = response.data.data;
        } catch (error) {
            isLoggedIn.value = false;
            userInfo.value = null;
            // 若未登录，重定向到登录页面
            router.push('/login'); 
        }
    };

    onMounted(() => {
        checkUserStatus();
    });

    return {
        isLoggedIn,
        userInfo,
        checkUserStatus
    };
}