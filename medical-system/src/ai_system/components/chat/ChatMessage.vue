<template>
  <div 
    :class="['chat-message', role, { 'animate': animate }]"
  >
    <div class="avatar">
      <div v-if="role === 'user'" class="user-avatar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
      </div>
      <div v-else class="ai-avatar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
        </svg>
      </div>
    </div>
    <div class="content-wrapper">
      <div class="message-header">
        <span class="role-label">{{ roleLabel }}</span>
        <span class="timestamp">{{ formattedTime }}</span>
      </div>
      <div class="content-container">
        <div v-if="rendered" v-html="renderedContent" class="content"></div>
        <div v-else class="content">{{ content }}</div>
      </div>
      <div v-if="role === 'assistant'" class="message-actions">
        <button class="action-button copy-button" @click="copyContent" title="复制内容">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
          </svg>
          <span v-if="copied" class="copied-indicator">已复制!</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { marked } from 'marked';

export default {
  name: 'ChatMessage',
  props: {
    role: {
      type: String,
      required: true,
      validator: (value) => ['user', 'assistant', 'system'].includes(value)
    },
    content: {
      type: String,
      required: true
    },
    rendered: {
      type: Boolean,
      default: true
    },
    timestamp: {
      type: Date,
      default: () => new Date()
    }
  },
  setup(props) {
    const renderedContent = computed(() => {
      if (props.role === 'assistant') {
        return marked(props.content);
      }
      return props.content;
    });
    
    const roleLabel = computed(() => {
      if (props.role === 'user') return '您';
      if (props.role === 'assistant') return '医疗助手';
      return '系统';
    });
    
    const formattedTime = computed(() => {
      return props.timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    });
    
    const animate = ref(false);
    const copied = ref(false);
    
    const copyContent = () => {
      navigator.clipboard.writeText(props.content).then(() => {
        copied.value = true;
        setTimeout(() => {
          copied.value = false;
        }, 2000);
      });
    };
    
    onMounted(() => {
      // 添加一个小延迟使动画在DOM更新后生效
      setTimeout(() => {
        animate.value = true;
      }, 100);
    });

    return {
      renderedContent,
      roleLabel,
      formattedTime,
      animate,
      copyContent,
      copied
    };
  }
};
</script>

<style scoped>
.chat-message {
  display: flex;
  margin-bottom: 24px;
  align-items: flex-start;
  opacity: 0;
  transform: translateY(10px);
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.chat-message.animate {
  opacity: 1;
  transform: translateY(0);
}

.user {
  flex-direction: row-reverse;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: bold;
  flex-shrink: 0;
  box-shadow: var(--shadow-sm);
  position: relative;
  margin: 0 12px;
}

.user-avatar {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  border: 2px solid white;
}

.ai-avatar {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--secondary-color), #1e8e82);
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  border: 2px solid white;
}

.avatar svg {
  width: 20px;
  height: 20px;
}

.content-wrapper {
  position: relative;
  max-width: 80%;
  width: auto;
  min-width: 0; /* 防止内容撑开父容器 */
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
  font-size: 0.8em;
  color: var(--text-secondary);
}

.user .message-header {
  flex-direction: row-reverse;
}

.content-container {
  position: relative;
}

.content {
  padding: 14px 18px;
  border-radius: 18px;
  overflow-wrap: break-word;
  box-shadow: var(--shadow-sm);
  transition: box-shadow 0.3s;
  position: relative;
  z-index: 1;
  overflow-x: auto;
  width: 100%;
  box-sizing: border-box;
  max-width: 100%; /* 确保内容不会超出容器 */
  word-break: break-word; /* 确保长单词也能换行 */
}

.user .content {
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  color: white;
  border-top-right-radius: 4px;
}

.assistant .content {
  background-color: white;
  color: var(--text-color);
  border-top-left-radius: 4px;
  border-left: 3px solid var(--secondary-color);
}

.chat-message:hover .content {
  box-shadow: var(--shadow-md);
}

/* 消息操作按钮 */
.message-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 5px;
  opacity: 0;
  transition: opacity 0.2s;
}

.chat-message:hover .message-actions {
  opacity: 1;
}

.action-button {
  background: none;
  border: none;
  padding: 5px;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  color: var(--text-secondary);
  position: relative;
}

.action-button:hover {
  background: var(--background-secondary);
  color: var(--primary-color);
}

.action-button svg {
  width: 16px;
  height: 16px;
}

.copied-indicator {
  position: absolute;
  bottom: 100%;
  right: 0;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  margin-bottom: 5px;
  animation: fadeInOut 2s;
}

@keyframes fadeInOut {
  0% { opacity: 0; }
  20% { opacity: 1; }
  80% { opacity: 1; }
  100% { opacity: 0; }
}

/* 确保Markdown渲染正常 */
.content :deep(pre) {
  background: #2d2d2d;
  color: #ccc;
  padding: 12px;
  border-radius: 8px;
  overflow-x: auto;
  margin: 15px 0;
  position: relative;
}

.content :deep(pre)::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 30px;
  background: #3a3a3a;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
  border-bottom: 1px solid #444;
}

.content :deep(pre) code {
  padding-top: 25px;
  display: block;
  font-family: 'Fira Code', 'Consolas', monospace;
  line-height: 1.5;
}

.content :deep(code) {
  font-family: 'Fira Code', 'Consolas', monospace;
  background: rgba(0, 0, 0, 0.05);
  padding: 2px 5px;
  border-radius: 3px;
}

.assistant .content :deep(a) {
  color: var(--primary-color);
  text-decoration: none;
  border-bottom: 1px dotted;
}

.user .content :deep(a) {
  color: #f0f0f0;
  text-decoration: underline;
}

.content :deep(ul), .content :deep(ol) {
  padding-left: 20px;
}

.content :deep(li) {
  margin-bottom: 5px;
}

.content :deep(table) {
  border-collapse: collapse;
  width: 100%;
  margin: 15px 0;
}

.content :deep(th), .content :deep(td) {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.content :deep(th) {
  background-color: #f2f2f2;
}

.content :deep(blockquote) {
  border-left: 4px solid #ddd;
  padding: 10px 15px;
  color: #666;
  margin: 15px 0;
}

/* 添加打字机效果的光标 */
.assistant .content.typing::after {
  content: "|";
  animation: blink 1s infinite;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}

/* 响应式调整 */
@media (max-width: 768px) {
  .content-wrapper {
    max-width: 75%;
  }
  
  .avatar {
    width: 35px;
    height: 35px;
    margin: 0 8px;
  }
  
  .avatar svg {
    width: 18px;
    height: 18px;
  }
  
  .content {
    padding: 10px 14px;
    font-size: 0.95em;
  }
}
</style>
