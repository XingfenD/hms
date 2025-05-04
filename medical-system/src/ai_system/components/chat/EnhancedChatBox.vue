<template>
  <div class="enhanced-chat-box">
    <div class="messages-container" ref="messagesContainer">
      <div v-if="messages.length === 0" class="empty-state">
        <div class="welcome">
          <div class="welcome-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
          </div>
          <h2>欢迎使用智慧医疗助手</h2>
          <p>您可以向智慧医疗助手提问任何医疗相关问题，获得即时的回复。<br>推荐您填写“患者信息”表单，系统会结合您提供的信息进行分析。</p>
          
          <div class="suggestion-chips">
            <div class="chip" @click="usePrompt('作为智慧医疗助手，请介绍一下你自己')">助手介绍</div>
            <div class="chip" @click="usePrompt('根据我提供的症状，请给出可能的诊断和建议')">症状分析</div>
            <div class="chip" @click="usePrompt('请解释这种药物的作用和可能的副作用')">药物咨询</div>
            <div class="chip" @click="usePrompt('我应该如何预防常见的季节性疾病？')">健康建议</div>
          </div>
        </div>
      </div>
      <template v-else>
        <ChatMessage 
          v-for="(message, index) in messages" 
          :key="index" 
          :role="message.role" 
          :content="message.content" 
        />
      </template>
      <ChatMessage 
        v-if="isLoading" 
        role="assistant" 
        :content="loadingText" 
        :rendered="false"
      />
      <div v-if="streamingContent" class="streaming">
        <ChatMessage role="assistant" :content="streamingContent" />
      </div>
    </div>
    
    <div class="input-area">
      <div class="input-wrapper">
        <textarea 
          ref="inputField"
          v-model="userInput" 
          placeholder="请输入您的问题..."
          @keydown.enter.prevent="handleEnterKey"
          rows="1"
        ></textarea>
        <div class="input-actions">
          <button 
            class="action-button clear-button" 
            @click="clearInput" 
            v-if="userInput.trim()"
            title="清空输入"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="15" y1="9" x2="9" y2="15"></line>
              <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
          </button>
        </div>
      </div>
      <button 
        class="send-button" 
        @click="sendMessage" 
        :disabled="isLoading || !userInput.trim()"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="22" y1="2" x2="11" y2="13"></line>
          <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, nextTick, watch } from 'vue';
import ChatMessage from './ChatMessage.vue';
import { API_BASE_URL, API_PATHS, getWebSocketUrl } from '../../config';

export default {
  name: 'EnhancedChatBox',
  components: {
    ChatMessage
  },
  props: {
    patientInfo: {
      type: Object,
      default: () => ({})
    }
  },
  setup(props) {
    const userInput = ref('');
    const messages = ref([]);
    const isLoading = ref(false);
    const streamingContent = ref('');
    const messagesContainer = ref(null);
    const sessionId = ref(null);
    const inputField = ref(null);
    const socket = ref(null);
    const patientInfoAdded = ref(false);
    
    // 添加对patientInfo的监听
    watch(() => props.patientInfo, (newInfo, oldInfo) => {
      console.log('EnhancedChatBox接收到新的患者信息:', newInfo);
  
      if (Object.keys(newInfo).length > 0 && 
        (Object.keys(oldInfo || {}).length === 0 || JSON.stringify(newInfo) !== JSON.stringify(oldInfo))) {
        patientInfoAdded.value = true;
      }
    }, { deep: true, immediate: true });
    
    // 动态的加载提示文字
    const loadingText = ref("思考中.");
    const loadingTexts = ["思考中..", "思考中...", "思考中...."];
    let loadingInterval;
    
    const startLoadingAnimation = () => {
      let index = 0;
      loadingInterval = setInterval(() => {
        loadingText.value = loadingTexts[index];
        index = (index + 1) % loadingTexts.length;
      }, 800);
    };
    
    const stopLoadingAnimation = () => {
      clearInterval(loadingInterval);
    };

    // 自动滚动到底部
    const scrollToBottom = async () => {
      await nextTick();
      if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
      }
    };

    // 监听消息变化，自动滚动
    watch([messages, streamingContent], () => {
      scrollToBottom();
    });

    // 调整输入框高度
    const adjustTextareaHeight = () => {
      if (inputField.value) {
        inputField.value.style.height = 'auto';
        inputField.value.style.height = `${Math.min(inputField.value.scrollHeight, 150)}px`;
      }
    };

    // 监听输入变化，调整高度
    watch(userInput, () => {
      adjustTextareaHeight();
    });

    // 处理回车键
    const handleEnterKey = (event) => {
      if (event.shiftKey) return;
      sendMessage();
    };

    // 清空输入
    const clearInput = () => {
      userInput.value = '';
      adjustTextareaHeight();
    };

    // 使用预设提示
    const usePrompt = (prompt) => {
      userInput.value = prompt;
      adjustTextareaHeight();
      sendMessage();
    };

    // 获取发送给后端的消息历史
    const getChatHistoryForAPI = () => {
      // 创建一个新的消息数组用于API请求
      let apiMessages = [...messages.value.map(msg => ({
        role: msg.role,
        content: msg.content
      }))];
      
      // 如果有患者信息但没有显示在聊天中，在API请求中添加它
      if (Object.keys(props.patientInfo).length > 0 && !messages.value.some(msg => msg.isPatientInfo)) {
        apiMessages.unshift({
          role: 'system',
          content: `患者信息：${JSON.stringify(props.patientInfo)}`
        });
      }
      
      return apiMessages;
    };

    // 初始化WebSocket连接
    const setupWebSocket = () => {
      const wsUrl = getWebSocketUrl();
      
      if (socket.value) {
        socket.value.close();
      }

      socket.value = new WebSocket(wsUrl);
      
      socket.value.onopen = () => {
        console.log('WebSocket连接已建立');
      };
      
      socket.value.onmessage = (event) => {
        const data = JSON.parse(event.data);
        
        if (!data.finished) {
          if (!streamingContent.value && isLoading.value) {
            isLoading.value = false;
            stopLoadingAnimation();
          }

          streamingContent.value += data.content;
          sessionId.value = data.session_id;
        } else {
          // 当流式消息完成时
          if (streamingContent.value) {
            messages.value.push({
              role: 'assistant',
              content: streamingContent.value
            });
            streamingContent.value = '';
          }
          isLoading.value = false;
          stopLoadingAnimation();
        }
      };
      
      socket.value.onerror = (error) => {
        console.error('WebSocket错误:', error);
        isLoading.value = false;
        stopLoadingAnimation();
        // 尝试回退到HTTP API
        fallbackToHttpApi();
      };
      
      socket.value.onclose = () => {
        console.log('WebSocket连接已关闭');
      };
    };
    
    // 使用HTTP API作为后备方案
    const fallbackToHttpApi = async () => {
      try {
        const apiUrl = `${API_BASE_URL}${API_PATHS.CHAT}`;
        const chatHistory = getChatHistoryForAPI();
        
        const response = await fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            message: chatHistory[chatHistory.length - 1].content,
            session_id: sessionId.value,
            messages: chatHistory,  // 发送完整对话历史
          })
        });
        
        const data = await response.json();
        messages.value.push({
          role: 'assistant',
          content: data.message
        });
        sessionId.value = data.session_id;
      } catch (error) {
        console.error('API错误:', error);
        messages.value.push({
          role: 'assistant',
          content: '抱歉，服务器连接出现问题。请稍后再试。'
        });
      } finally {
        isLoading.value = false;
        stopLoadingAnimation();
      }
    };

    // 发送消息
    const sendMessage = () => {
      const trimmedInput = userInput.value.trim();
      if (!trimmedInput || isLoading.value) return;
      
      // 添加用户消息到本地消息列表
      messages.value.push({
        role: 'user',
        content: trimmedInput
      });
      
      isLoading.value = true;
      startLoadingAnimation();
      
      // 获取发送到后端的完整对话历史
      const chatHistory = getChatHistoryForAPI();
      
      if (socket.value && socket.value.readyState === WebSocket.OPEN) {
        socket.value.send(JSON.stringify({
          message: trimmedInput,
          session_id: sessionId.value,
          messages: chatHistory,  // 发送完整对话历史，已包含患者信息
        }));
      } else {
        console.log('WebSocket未连接，尝试重新连接...');
        setupWebSocket();
        setTimeout(() => {
          if (socket.value && socket.value.readyState === WebSocket.OPEN) {
            socket.value.send(JSON.stringify({
              message: trimmedInput,
              session_id: sessionId.value,
              messages: chatHistory,  // 发送完整对话历史，已包含患者信息
            }));
          } else {
            fallbackToHttpApi();
          }
        }, 1000);
      }
      
      userInput.value = '';
      adjustTextareaHeight();
    };

    // 导出聊天历史
    const exportChatHistory = (format) => {
      if (messages.value.length === 0) {
        alert('没有聊天记录可导出');
        return;
      }
      
      let content = '';
      let now = new Date();
      let filename = `医疗聊天记录_${now.toISOString().slice(0, 10)}_${now.getHours()}${now.getMinutes()}${now.getSeconds()}`;
            
      if (format === 'json') {
        // 过滤掉内部使用的标记，如isPatientInfo
        const exportMessages = messages.value.map(msg => ({
          role: msg.role,
          content: msg.content
        }));
        
        content = JSON.stringify(exportMessages, null, 2);
        filename += '.json';
        
        // 创建并下载文件
        const blob = new Blob([content], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
      } else if (format === 'md') {
        // 创建Markdown格式
        content = '# 医疗聊天记录\n\n';
        content += `导出时间: ${new Date().toLocaleString()}\n\n`;
        
        // 添加患者信息摘要（如果有）
        if (Object.keys(props.patientInfo).length > 0) {
          content += '## 患者信息摘要\n\n';
          
          // 基本信息
          if (props.patientInfo.basic) {
            const basic = props.patientInfo.basic;
            content += '### 基本信息\n\n';
            if (basic.gender) content += `- 性别: ${basic.gender}\n`;
            if (basic.age) content += `- 年龄: ${basic.age}\n`;
            if (basic.height) content += `- 身高: ${basic.height}cm\n`;
            if (basic.weight) content += `- 体重: ${basic.weight}kg\n`;
            content += '\n';
          }
          
          // 主要症状
          if (props.patientInfo.symptoms && props.patientInfo.symptoms.mainSymptoms) {
            content += '### 主要症状\n\n';
            content += props.patientInfo.symptoms.mainSymptoms + '\n\n';
          }
          
          // 过敏信息
          if (props.patientInfo.allergiesAndContraindications && props.patientInfo.allergiesAndContraindications.drugAllergies) {
            content += '### 过敏信息\n\n';
            content += props.patientInfo.allergiesAndContraindications.drugAllergies + '\n\n';
          }
        }
        
        // 添加对话内容
        content += '## 对话内容\n\n';
        
        // 过滤掉系统消息(患者信息)，只显示用户和助手的对话
        const userAssistantMessages = messages.value.filter(msg => msg.role !== 'system');
        
        userAssistantMessages.forEach((msg, index) => {
          const role = msg.role === 'user' ? '您' : '医疗助手';
          content += `### ${role} (${index + 1})\n\n${msg.content}\n\n`;
        });
        
        filename += '.md';
        
        // 创建并下载文件
        const blob = new Blob([content], { type: 'text/markdown' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
      }
    };

    onMounted(() => {
      setupWebSocket();
      scrollToBottom();
      adjustTextareaHeight();
    });

    return {
      userInput,
      messages,
      isLoading,
      streamingContent,
      messagesContainer,
      inputField,
      sendMessage,
      handleEnterKey,
      loadingText,
      usePrompt,
      clearInput,
      exportChatHistory
    };
  }
};
</script>

<style scoped>
.enhanced-chat-box {
  display: flex;
  flex-direction: column;
  height: 100%;
  width: 100%;
  background-color: var(--background-secondary);
  overflow: hidden;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  scroll-behavior: smooth;
  background-color: var(--background-secondary);
  background-image: radial-gradient(#e3e3e3 1px, transparent 1px);
  background-size: 20px 20px;
  width: 100%;
  box-sizing: border-box;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: stretch;
}

.empty-state {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
  text-align: center;
}

.welcome {
  max-width: 800px;
  padding: 30px;
  background: white;
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-md);
  animation: fadeIn 0.5s ease-out;
}

.welcome-icon {
  width: 70px;
  height: 70px;
  margin: 0 auto 20px;
  background: var(--primary-color);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.welcome-icon svg {
  width: 40px;
  height: 40px;
  color: white;
}

.welcome h2 {
  margin-bottom: 15px;
  color: var(--text-color);
  font-size: 1.8em;
}

.welcome p {
  font-size: 1.1em;
  line-height: 1.5;
  margin-bottom: 25px;
  color: var(--text-secondary);
}

.suggestion-chips {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
  margin-top: 20px;
}

.chip {
  background: var(--background-secondary);
  padding: 10px 16px;
  border-radius: 20px;
  font-size: 0.9em;
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
}

.chip:hover {
  background: var(--border-color);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.input-area {
  display: flex;
  align-items: flex-end;
  padding: 18px 20px;
  border-top: 1px solid var(--border-color);
  background: white;
  position: relative;
  z-index: 10;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}

.input-wrapper {
  flex: 1;
  position: relative;
  background: var(--background-secondary);
  border-radius: 24px;
  transition: all 0.3s;
  border: 1px solid var(--border-color);
}

.input-wrapper:focus-within {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
}

textarea {
  width: 100%;
  min-height: 24px;
  max-height: 150px;
  padding: 14px 40px 14px 18px;
  border: none;
  border-radius: 24px;
  resize: none;
  font-family: inherit;
  font-size: 15px;
  outline: none;
  background: transparent;
}

.input-actions {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
}

.action-button {
  background: none;
  border: none;
  padding: 5px;
  cursor: pointer;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
  transition: all 0.2s;
}

.action-button:hover {
  background: rgba(0, 0, 0, 0.05);
  color: var(--text-color);
}

.action-button svg {
  width: 18px;
  height: 18px;
}

.send-button {
  min-width: 46px;
  height: 46px;
  margin-left: 12px;
  background-color: var(--primary-color);
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  box-shadow: var(--shadow-md);
}

.send-button svg {
  width: 22px;
  height: 22px;
}

.send-button:hover:not(:disabled) {
  background-color: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.send-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
  box-shadow: none;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* 响应式调整 */
@media (max-width: 768px) {
  .messages-container {
    padding: 15px;
  }
  
  .welcome {
    padding: 20px;
    margin: 0 10px;
  }
  
  .welcome-icon {
    width: 60px;
    height: 60px;
  }

  .welcome-icon svg {
    width: 35px;
    height: 35px;
  }

  .welcome h2 {
    font-size: 1.5em;
  }

  .welcome p {
    font-size: 1em;
  }

  .chip {
    padding: 8px 14px;
    font-size: 0.85em;
  }
  
  .input-area {
    padding: 12px 15px;
  }
  
  textarea {
    padding: 12px 40px 12px 15px;
    font-size: 14px;
  }
  
  .send-button {
    min-width: 42px;
    height: 42px;
  }
  
  .send-button svg {
    width: 20px;
    height: 20px;
  }
}
</style>