<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import MedicalChatLayout from './components/layouts/MedicalChatLayout.vue';

// 检测设备类型
const isMobile = ref(false);
const checkDeviceType = () => {
  isMobile.value = window.innerWidth < 768;
};

// 监听窗口大小变化
onMounted(() => {
  checkDeviceType();
  window.addEventListener('resize', checkDeviceType);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', checkDeviceType);
});
</script>

<template>
  <div class="app">
    <MedicalChatLayout :is-mobile="isMobile" />
  </div>
</template>

<style>
:root {
  /* 主色调 */
  --primary-color: #1976D2;
  --primary-dark: #1565C0;
  --primary-light: #BBDEFB;
  --secondary-color: #26A69A;
  --secondary-light: #B2DFDB;
  
  /* 中性色 */
  --background-color: #FFFFFF;
  --background-secondary: #F5F7FA;
  --border-color: #E0E0E0;
  --text-color: #212121;
  --text-secondary: #757575;
  
  /* 功能色 */
  --success-color: #4CAF50;
  --warning-color: #FFC107;
  --error-color: #F44336;
  
  /* 尺寸 */
  --border-radius: 10px;
  --spacing-xs: 5px;
  --spacing-sm: 10px;
  --spacing-md: 15px;
  --spacing-lg: 20px;
  --spacing-xl: 30px;
  
  /* 阴影 */
  --shadow-sm: 0 2px 5px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 5px 15px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html, body {
  height: 100%;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  background-color: var(--background-secondary);
  color: var(--text-color);
  line-height: 1.5;
}

#app {
  height: 100vh;
  width: 100%;
}

.app {
  height: 100%;
  flex-direction: column;
}

/* 通用动画 */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}

/* 按钮样式 */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  outline: none;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-dark);
}

.btn-secondary {
  background-color: var(--background-secondary);
  color: var(--text-color);
  border: 1px solid var(--border-color);
}

.btn-secondary:hover {
  background-color: var(--border-color);
}

.btn-icon {
  width: 36px;
  height: 36px;
  padding: 0;
  border-radius: 50%;
}

.btn-icon svg {
  width: 20px;
  height: 20px;
}

/* 表单元素样式 */
input, textarea, select {
  font-family: inherit;
  font-size: 14px;
}

/* 滚动条样式 */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: var(--background-secondary);
}

::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #aaa;
}

/* 辅助功能 */
.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/* 响应式工具类 */
.mobile-only {
  display: none;
}

.desktop-only {
  display: block;
}

@media (max-width: 767px) {
  .mobile-only {
    display: block;
  }
  
  .desktop-only {
    display: none;
  }
}
</style>
