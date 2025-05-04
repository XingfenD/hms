<!-- UserStatusChecker.vue -->
<template>
    <!-- 这里不添加任何模板内容，因为主要是执行检查逻辑 -->
    <div></div>
</template>

<script setup>
import { useUserStatus } from '../useUserStatus';

const { isLoggedIn, userInfo } = useUserStatus();
</script>