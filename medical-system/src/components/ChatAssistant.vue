<template>
    <div>
      <!-- 河灵小助手聊天窗口 -->
      <div v-if="showChat" 
           class="chat-container"
           :style="{ left: buttonX + 'px', top: buttonY - 500 + 'px' }">
        <div class="chat-header">
          <h2>河灵小助手</h2>
          <button class="btn btn-secondary btn-icon" @click="showChat = false" title="关闭">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 6L6 18M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <!-- 这里可以添加 EnhancedChatBox 组件 -->
        <EnhancedChatBox ref="chatBoxRef" />
      </div>
  
      <!-- 右下角卡通人物按钮 -->
      <button
        class="cartoon-button"
        @mousedown="startDrag"
        @mousemove="drag"
        @mouseup="endDrag"
        @mouseleave="endDrag"
        :style="{ left: buttonX + 'px', top: buttonY + 'px' }"
      >
        <img src="@/assets/cartoon.png" alt="卡通人物" />
      </button>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import EnhancedChatBox from '@/ai_system/components/chat/EnhancedChatBox.vue'; // 根据实际路径引入
  
  const showChat = ref(false);
  const chatBoxRef = ref(null);
  
  // 拖动相关变量
  const isDragging = ref(false);
  const offsetX = ref(0);
  const offsetY = ref(0);
  // 修改初始位置，将按钮置于左下角
  const buttonX = ref(20); 
  const buttonY = ref(window.innerHeight - 100); 
  const clickStartTime = ref(0);
  const CLICK_TIME_THRESHOLD = 200; // 点击时间阈值，单位：毫秒
  
  const startDrag = (e) => {
    isDragging.value = true;
    offsetX.value = e.clientX - buttonX.value;
    offsetY.value = e.clientY - buttonY.value;
    clickStartTime.value = Date.now();
    e.preventDefault();
  };
  
  const drag = (e) => {
    if (isDragging.value) {
      const newX = e.clientX - offsetX.value;
      const newY = e.clientY - offsetY.value;
  
      // 边界限制
      const minX = 0;
      const maxX = window.innerWidth - 80;
      const minY = 0;
      const maxY = window.innerHeight - 80;
  
      buttonX.value = Math.min(Math.max(newX, minX), maxX);
      buttonY.value = Math.min(Math.max(newY, minY), maxY);
    }
  };
  
  const endDrag = (e) => {
    if (isDragging.value) {
      isDragging.value = false;
      const clickEndTime = Date.now();
      if (clickEndTime - clickStartTime.value < CLICK_TIME_THRESHOLD) {
        showChat.value = true;
      }
    }
  };
  </script>
  
  <style scoped>
  /* 聊天窗口样式 */
  .chat-container {
    position: fixed;
    width: 350px;
    height: 500px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    z-index: 9; /* 确保聊天窗口在按钮之上 */
  }
  
  .chat-header {
    padding: 15px 20px;
    background: linear-gradient(135deg, #1976D2, #1565C0);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    z-index: 5;
  }
  
  .chat-header h2 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
  }
  
  /* 右下角卡通人物按钮样式 */
  .cartoon-button {
    position: fixed;
    background: none;
    border: none;
    cursor: pointer;
    z-index: 10;
    width: 80px; /* 根据实际图片大小调整 */
    height: 80px; /* 根据实际图片大小调整 */
  }
  
  .cartoon-button img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
  </style>        