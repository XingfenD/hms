<template>
  <div class="medical-chat-layout" :class="{ 'mobile-layout': isMobile }">
    <!-- PC端布局 -->
    <template v-if="!isMobile">
      <div class="sidebar" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
        <div class="sidebar-toggle" @click="toggleSidebar">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path v-if="sidebarCollapsed" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
            <path v-else d="M11 19l-7-7 7-7M19 19l-7-7 7-7"></path>
          </svg>
        </div>
        <div class="sidebar-content" v-show="!sidebarCollapsed">
          <PatientInfoForm @update:patientInfo="updatePatientInfo" />
        </div>
      </div>
      <div class="main-content">
        <header class="chat-header">
          <div class="logo-container">
            <div class="logo-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
              </svg>
            </div>
            <h1>智慧医疗助手</h1>
          </div>
          <div class="header-actions">
            <button class="btn btn-secondary btn-icon" @click="exportChat" title="导出聊天记录">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
            </button>
          </div>
        </header>
        <EnhancedChatBox :patientInfo="patientInfo" ref="chatBoxRef" />
      </div>
    </template>
    
    <!-- 移动端布局 -->
    <template v-else>
      <header class="mobile-header">
        <div class="logo-container">
          <div class="logo-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
          </div>
          <h1>智慧医疗助手</h1>
        </div>
        <div class="header-actions">
          <button class="btn btn-secondary btn-icon" @click="exportChat" title="导出聊天记录">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="7 10 12 15 17 10"></polyline>
              <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
          </button>
        </div>
      </header>
      
      <div class="mobile-content">
        <div v-if="activeTab === 'chat'" class="tab-content">
          <EnhancedChatBox :patientInfo="patientInfo" ref="chatBoxRef" />
        </div>
        <div v-else-if="activeTab === 'patient'" class="tab-content">
          <PatientInfoForm @update:patientInfo="updatePatientInfo" />
        </div>
      </div>
      
      <nav class="mobile-tabs">
        <div 
          class="tab-item" 
          :class="{ active: activeTab === 'chat' }"
          @click="activeTab = 'chat'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
          <span>聊天</span>
        </div>
        <div 
          class="tab-item" 
          :class="{ active: activeTab === 'patient' }"
          @click="activeTab = 'patient'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span>患者信息</span>
        </div>
      </nav>
    </template>
    
    <!-- 导出对话框 -->
    <div class="export-dialog" v-if="showExportDialog">
      <div class="export-dialog-content">
        <h3>导出聊天记录</h3>
        <div class="export-options">
          <div class="export-option">
            <input type="radio" id="json-format" value="json" v-model="exportFormat">
            <label for="json-format">JSON格式</label>
          </div>
          <div class="export-option">
            <input type="radio" id="md-format" value="md" v-model="exportFormat">
            <label for="md-format">Markdown格式</label>
          </div>
        </div>
        <div class="export-actions">
          <button class="btn btn-secondary" @click="showExportDialog = false">取消</button>
          <button class="btn btn-primary" @click="confirmExport">导出</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import PatientInfoForm from '../patient/PatientInfoForm.vue';
import EnhancedChatBox from '../chat/EnhancedChatBox.vue';

export default {
  name: 'MedicalChatLayout',
  components: {
    PatientInfoForm,
    EnhancedChatBox
  },
  props: {
    isMobile: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    // PC端侧边栏状态
    const sidebarCollapsed = ref(false);
    
    // 移动端标签页状态
    const activeTab = ref('chat');
    
    // 患者信息
    const patientInfo = ref({});
    
    // 聊天框引用
    const chatBoxRef = ref(null);
    
    // 导出对话框状态
    const showExportDialog = ref(false);
    const exportFormat = ref('json');
    
    // 切换侧边栏
    const toggleSidebar = () => {
      sidebarCollapsed.value = !sidebarCollapsed.value;
      // 保存用户偏好
      localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value);
    };
    
    // 更新患者信息
    const updatePatientInfo = (info) => {
      console.log('接收到患者信息更新:', info); // 添加调试日志
      patientInfo.value = JSON.parse(JSON.stringify(info)); // 使用深拷贝确保数据完整性
    };
    
    // 导出聊天记录
    const exportChat = () => {
      showExportDialog.value = true;
    };
    
    // 确认导出
    const confirmExport = () => {
      if (chatBoxRef.value) {
        chatBoxRef.value.exportChatHistory(exportFormat.value);
      }
      showExportDialog.value = false;
    };
    
    // 组件挂载时，恢复侧边栏状态
    onMounted(() => {
      const savedState = localStorage.getItem('sidebarCollapsed');
      if (savedState !== null) {
        sidebarCollapsed.value = savedState === 'true';
      }
      
      // 添加：从localStorage加载患者信息
      const savedInfo = localStorage.getItem('patientInfo');
      if (savedInfo) {
        try {
          patientInfo.value = JSON.parse(savedInfo);
          console.log('从localStorage加载患者信息:', patientInfo.value);
        } catch (e) {
          console.error('加载保存的患者信息失败', e);
        }
      }
    });
    
    return {
      sidebarCollapsed,
      activeTab,
      patientInfo,
      chatBoxRef,
      showExportDialog,
      exportFormat,
      toggleSidebar,
      updatePatientInfo,
      exportChat,
      confirmExport
    };
  }
};
</script>

<style scoped>
.medical-chat-layout {
  display: flex;
  height: 100%;
  width: 100%;
  position: relative;
}

/* PC端布局样式 */
.sidebar {
  width: 30%;
  height: 100%;
  background-color: var(--background-secondary);
  border-right: 1px solid var(--border-color);
  transition: width 0.3s ease;
  position: relative;
  display: flex;
  flex-direction: column;
}

.sidebar-collapsed {
  width: 50px;
}

.sidebar-toggle {
  position: absolute;
  top: 15px;
  right: -15px;
  width: 30px;
  height: 30px;
  background-color: var(--primary-color);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  transition: all 0.3s;
}

.sidebar-toggle:hover {
  background-color: var(--primary-dark);
  transform: scale(1.1);
}

.sidebar-toggle svg {
  width: 16px;
  height: 16px;
  color: white;
}

.sidebar-content {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.chat-header {
  padding: 15px 20px;
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: var(--shadow-sm);
  z-index: 5;
}

.logo-container {
  display: flex;
  align-items: center;
}

.logo-icon {
  width: 36px;
  height: 36px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.logo-icon svg {
  width: 20px;
  height: 20px;
  color: var(--primary-color);
}

.chat-header h1 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  background: linear-gradient(to right, #ffffff, #e0e0e0);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  color: transparent;
}

.header-actions {
  display: flex;
  gap: 10px;
}

/* 移动端布局样式 */
.mobile-layout {
  flex-direction: column;
}

.mobile-header {
  padding: 12px 15px;
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: var(--shadow-sm);
  z-index: 5;
}

.mobile-header .logo-icon {
  width: 32px;
  height: 32px;
}

.mobile-header h1 {
  font-size: 1.2rem;
}

.mobile-content {
  flex: 1;
  overflow: hidden;
  position: relative;
}

.tab-content {
  height: 100%;
  width: 100%; /* 确保内容宽度占满父容器 */
  overflow: hidden;
  animation: fadeIn 0.3s;
  display: flex; /* 使用弹性布局 */
  flex-direction: column; /* 垂直排列子元素 */
}

.mobile-tabs {
  display: flex;
  background: white;
  border-top: 1px solid var(--border-color);
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
  z-index: 5;
}

.tab-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 10px;
  cursor: pointer;
  color: var(--text-secondary);
  transition: all 0.2s;
}

.tab-item.active {
  color: var(--primary-color);
}

.tab-item svg {
  width: 24px;
  height: 24px;
  margin-bottom: 4px;
}

.tab-item span {
  font-size: 12px;
}

/* 导出对话框 */
.export-dialog {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s;
}

.export-dialog-content {
  background: white;
  border-radius: var(--border-radius);
  padding: 20px;
  width: 90%;
  max-width: 400px;
  box-shadow: var(--shadow-lg);
}

.export-dialog h3 {
  margin-top: 0;
  margin-bottom: 15px;
  color: var(--text-color);
}

.export-options {
  margin-bottom: 20px;
}

.export-option {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.export-option input {
  margin-right: 10px;
}

.export-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* 平板适配 */
@media (min-width: 768px) and (max-width: 1024px) {
  .sidebar {
    width: 40%;
  }
}
</style>
